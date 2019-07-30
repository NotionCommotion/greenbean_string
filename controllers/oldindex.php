<?php
namespace DashboardWebsite;

// Remove after testing
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
openlog('WebServer', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);

session_start();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__.'/../vendor/autoload.php';

function addProxyRoutes(array $routes, $app) {
    foreach ($routes as $route=>$methods) {
        foreach ($methods as $method) {
            $app->$method('/api'.$route, function(Request $request, Response $response) {
                $uri = $request->getUri();
                $path = substr($uri->getPath(), 4); //Remove "/api" from uri
                $request=$request->withUri($uri->withPath($path));
                return $this->serverBridge->proxy($request, $response);
            });
        }
    }
}

$c = new \Slim\Container(['settings' => array_merge(
    [
        'displayErrorDetails'=>true,                // set to false in production
        //'addContentLengthHeader'=>false,          // Allow the web server to send the content-length header
        'determineRouteBeforeAppMiddleware'=>true   //Required to allow logon to work.
    ],parse_ini_file(__DIR__.'/../config.ini',true)
)]);

$c['view'] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__.'/../templates', [
        //'cache' => 'path/to/cache'    // See auto_reload option
        'debug' => true,
        'strict_variables'=> true
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));
    $view->addExtension(new \Twig_Extension_Debug());
    /*
    $filter = new \Twig_SimpleFilter('yesNoNa', function ($v) {
    return isset($v)?($v?'Yes':'No'):'N/A';
    //return is_null($v)?'N/A':($v?'Yes':'No');
    });
    $view->getEnvironment()->addFilter($filter);
    $filter = new \Twig_SimpleFilter('yesNo', function ($v) {
    return $v?'Yes':'No';
    });
    $view->getEnvironment()->addFilter($filter);
    */
    return $view;
};
$c['logger'] = function($c) {
    $config=$c['settings']['logger'];
    $logger = new \Monolog\Logger($config['name']);
    $file_handler = new \Monolog\Handler\StreamHandler($config['file']);
    $logger->pushHandler($file_handler);
    return $logger;
};
$c['pdo'] = function ($c) {
    $db = $c['settings']['database'];
    return new \PDO('sqlite:../'.$db['name']);
};

$c['base'] = function ($c) {
    return new Base($c->get('serverBridge'), $c->get('logger'), $c->get('pdo'), $c['settings']['config'], $c['settings']['user']);
};

$c['serverBridge'] = function ($c) {
    $user=$c['settings']['user'];
    $headers=empty($user->id)
    ?['X-GreenBean-Key' => $c['settings']['server']['key']]
    :['X-GreenBean-Key' => $c['settings']['server']['key'], "X-GreenBean-UserId"=>$user->id];
    return new \Greenbean\ServerBridge\ServerBridge(
        new \GuzzleHttp\Client([
            'base_uri' => $c['settings']['server']['scheme'].'://'.$c['settings']['server']['host'],
            'headers' => $headers,
            'timeout'  => $c['settings']['server']['curlWaitTime'],
        ])
    );
};

$c['getPageContent'] = function () {
    return function (array $routes, $version) {
        $rs=$this->serverBridge->getPageContent($routes, $version);
        syslog(LOG_INFO, 'getPageContent: routes: '.json_encode($routes).' response: '.json_encode($rs));
        return $rs;
    };
};

$app = new \Slim\App($c);

// ******************************************* HTML ENDPOINTS **********************************************

$app->group('', function(\Slim\App $app) {

    $app->get('/account', function (Request $request, Response $response) {    //Also supports virtual LANS
    });

    $app->get('/sources', function (Request $request, Response $response) {
    });

    $app->get('/sources/{id:[0-9]+}', function (Request $request, Response $response, $arg) {
    });

    $app->get('{route:/|/points}', function (Request $request, Response $response) {
    });

    $app->get('/charts', function (Request $request, Response $response) {
    });

    $app->get('/reports', function (Request $request, Response $response) {
    })->setName('reports');;

    $app->get('/reports/{id:[0-9]+}', function (Request $request, Response $response, $args) {
     });

    $app->get('/helpdesk', function (Request $request, Response $response) {
     });

    $app->get('/preview[/{page:[0-9]+}]', function (Request $request, Response $response, $args) {
    });

    $app->get('/preview/edit[/{page:[0-9]+}]', function (Request $request, Response $response, $args) {
    });

    // ******************************************* LOCAL AJAX ENDPOINTS **********************************************
    $app->put('/preview/edit/{page:[0-9]+}', function (Request $request, Response $response, $args) {
        $this->base->updatePage($args['page'],$request->getParsedBodyParam('content'));
        return $response->withJson(null);
    });

    $app->get('/resources/{page:[0-9]+}', function (Request $request, Response $response, $arg) {
        $rsp=$this->base->getResources($arg['page']);
        return $response->withJson($rsp);
    });
    $app->post('/resources/{page:[0-9]+}', function (Request $request, Response $response, $arg) {
        $id=$this->base->addResource($arg['page'], $request->getUploadedFiles());
        return $response->withRedirect("/resources/$arg[page]/$id");
    });
    $app->put('/resources/{page:[0-9]+}/{resourceId:[0-9]+}', function (Request $request, Response $response, $arg) {
        $this->base->updateResourceLink($arg['page'], $arg['resourceId'], $request->getParsedBodyParam('linked'));
        return $response->withJson(null);
    });
    $app->delete('/resources/{resourceId:[0-9]+}', function (Request $request, Response $response, $arg) {
        $this->base->deleteResource($arg['resourceId']);
        return $response->withJson(null);
    });

    // ******************************************* PROXY ENDPOINTS **********************************************

    $proxyEndpoints=[
        '/account'=>['put'],
        '/account/timezones'=>['get'],
        '/units/time'=>['get'],
        '/tags/lans'=>['get','post'],
        '/tags/lans/{id:[0-9]+}'=>['delete','put'],
        '/sources/{id:[0-9]+}'=>['delete','get','put'],  //GET currently not used since rendered in page
        '/sources/{id:[0-9]+}/points'=>['get'],
        '/sources/{id:[0-9]+}/gateway'=>['post'],   //methods supported in POST: restartService|restartNetwork|rebootDevice
        '/sources'=>['post'],
        '/sources/{id:[0-9]+}/discovery/{deviceId:[0-9]+}'=>['post'],
        '/sources/{sourceId:[0-9]+}/discoveryDevicesOnline'=>['get'],//Might not be implemented if JavaScript is used to individual query each request.
        '/points'=>['post', 'get'],  //GET used for searching points
        '/points/validation'=>['get'],   // Do before /api/points/{id}.  Used for validation
        '/points/{id:[0-9]+}'=>['get','put','delete'],
        '/points/{id:[0-9]+}/custom'=>['get','post'],
        '/points/{id:[0-9]+}/custom/{pointsIdSub:[0-9]+}'=>['delete','put'],

        '/bacnet/devices'=>['get'], //All bacnet devices for all sources
        '/bacnet/objects'=>['get'], //All bacnet objects for all sources
        '/sources/{protocol}/defaults'=>['get'], //get default values for a new bacnet source.  Currently only bacnet
        '/sources/{sourceId:[0-9]+}/bacnet/devices'=>['get'], //All bacnet devices for given sourceId
        '/sources/{sourceId:[0-9]+}/bacnet/deviceobjects'=>['get'], //All bacnet devices with objects for given sourceId
        '/sources/{sourceId:[0-9]+}/bacnet/devices/{deviceId:[0-9]+}'=>['get'],
        '/sources/{sourceId:[0-9]+}/bacnet/devices/{deviceId:[0-9]+}/objects'=>['get'],
        '/sources/{sourceId:[0-9]+}/bacnet/devices/{deviceId:[0-9]+}/objects/{objectId:[0-9]+}/{typeId:[0-9]+}'=>['get'],

        '/charts'=>['get', 'post'],//GET used for searching charts
        '/charts/validation'=>['get'],
        '/chart/{chartId:[0-9]+}/options'=>['get','put'],   //get or update entire Highchart option object
        '/chart/{chartId:[0-9]+}/options/{params:.*}'=>['put'],   //Update single property in the Highchart option object.  Future add GET.
        '/chart/{chartId:[0-9]+}/series'=>['post'],//POST is currently only used to allow time chart to ADD a series.  Maybe make endpoint as /chart/{id:[0-9]+} and use for other charts as well by including the parameter description (i.e. addSeries, addPoint, etc)to add?
        '/chart/{chartId:[0-9]+}/category'=>['post'],//Not currently implemented by API.
        '/chart/{chartId:[0-9]+}'=>['delete','get','post','put'], //POST used to add point item based on given series and category for category charts and just the points ID and legend for pie chart and time chart.
        '/chart/clone/{chartId:[0-9]+}'=>['post'],
        '/chart/{chartId:[0-9]+}/series/{seriesOffset:[0-9]+}'=>['delete','post','put'],  //Only used to allow pie chart to add a category
        '/chart/{chartId:[0-9]+}/category/{categoryOffset:[0-9]+}'=>['delete','put'],  //Only used by category charts.
        '/chart/{chartId:[0-9]+}/series/{seriesOffset:[0-9]+}/data/{dataOffset:[0-9]+}'=>['delete','put'], // Old approach was to use POST used to update point value and send NULL to delete point.  Only used with category chart.
        '/charts/themes'=>['get'],    //Not currently used to proxy since charts display uses this endpoint directly.
        '/reports'=>['get','post'],
        '/reports/{id:[0-9]+}'=>['delete','get', 'put'], //POST removed.  GET not used as /reports and /reports/{id:[0-9]+} will directly get this data serverside
        '/reports/validation'=>['get'],  //Not used?
        '/manual'=>['get'],
        '/manual/{id:[0-9]+}'=>['get'],
        '/helpdesk'=>['get','post'],  //GET Not used, and performed directy in HelpDesk class
        '/helpdesk/topics'=>['get'], //Not used, and performed directy in HelpDesk class
        '/helpdesk/search'=>['get'], //Not yet implemented
        '/helpdesk/validation'=>['get'],
        '/helpdesk/{id:[0-9]+}'=>['delete','get','post','put'],
        '/points/custom/report'=>['get'],
        '/tools/import/validate'=>['post'], //Will include files
        '/tools/import/update'=>['post'],   //Will include files
    ];

    addProxyRoutes($proxyEndpoints, $app);
    // Proxy endpoints where the response needs to be modified before or after sending to the proxy.  Consider putting in above but add a callback function
    /*
    $app->get('/api/reports/data', function (Request $request, Response $response) {
    //Client must include applicable Content-Type (application/json, text/csv, text/html, and future application/pdf)
    //syslog(LOG_INFO, 'Requested content type from browser: '.$request->getHeader('Accept'));
    $mimeType=$this->serverBridge->getMimeType($request->getQueryParam('Accept'));
    return $this->serverBridge->proxy($request->withHeader('Accept', $mimeType), $response);
    });
    */

    $app->get('/api/points/bacnet', function (Request $request, Response $response) {
        //Used to support adding real points.  Not used??? Returns objects in a given source.
        $response=$this->serverBridge->proxy($request, $response);
        if($response->getStatusCode()===200) {
            $body=$response->getBody();
            $data = json_decode($body);
            foreach($data as $key=>$row) {
                $data[$key]=['id'=>['deviceId'=>$row->deviceId, 'objectId'=>$row->objectId, 'objectType'=>$row->objectType,'sourceId'=>$row->sourceId], 'name'=>"$row->deviceName:$row->objectName"];
            }
            $body->write(json_encode($data));
        }
        return $response;
    });
})->add(function(Request $request, Response $response, $next) {
    //Authentication middleware applied to all above routes.
    $key=$this['settings']['server']['key'];
    if(isset($_SESSION[$key]['user'])) {
        $this['settings']['user']=$_SESSION[$key]['user'];
        return $next($request, $response);
    }
    else {
        return $response->withRedirect('/logon');
    }
});


//Public routes which are not in middleware
$app->get('/logon', function (Request $request, Response $response) {
    return $this->view->render($response, 'logon.html');
})->setName('logon');

$app->delete('/logon', function (Request $request, Response $response) {
    $key=$this['settings']['server']['key'];
    unset($_SESSION[$key]['user']);
    return $response;
    return $response->withRedirect($this->router->pathFor('logon'));
});

$app->post('/logon', function (Request $request, Response $response) {
    try {
        $user=$this->serverBridge->callApi('GET', '/users', $request->getParsedBody());
        $key=$this['settings']['server']['key'];
        $_SESSION[$key]['user']=$user;
        return $response->withRedirect('/reports');
    }
    catch(\Greenbean\ServerBridge\ServerBridgeException $e){
        return $this->view->render($response, 'logon.html', ['error'=>$e->getMessage()]);
        //Why doesn't this work?
        $url = $this->router->pathFor('logon', ['error' => $e->getMessage()]);
        return $response->withStatus(302)->withHeader('Location', $url);
    }
});

$proxyEndpoints=[
    '/query/initialize'=>['get'],           //Used for initial page set up and will return all point and widget values/previousValue/units and chart option json.  ?p=1.2.3&c=1.2.3&w=1.2.3
    '/query'=>['get'],                      //Returns point and widget values without units.  p=1.2.3&w=1.2.3
    '/query/custom'=>['get'],               //params includes p (points ID array), range (3d, etc), offset (1y, etc), boundary (true/false whether to be fixed on the range units)
    '/query/timeline'=>['get'],             //Returns next timechart value
    '/query/point/{id:[0-9]+}'=>['get'],    //returns single point value
    '/query/chart/{id:[0-9]+}'=>['get'],    //returns single chart option
    '/query/trend'=>['get'],                //Returns trend data, and based on Accept parameter, will be csv, json, or highchart json
];
addProxyRoutes($proxyEndpoints, $app);

//enableCor() must be executed after all endpoints have been added
(new \Greenbean\SlimErrorHandler\SlimErrorHandler($app, [], 'application/json;charset=utf-8', true))->setHandlers()->enableCor(true);

$app->run();