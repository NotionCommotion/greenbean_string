<?php
namespace Greenbean\Concrete5\Datalogger;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    private const  PRIVATE_ROUTES =[
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

    private const  PUBLIC_ROUTES=[
        '/query/initialize'=>['get'],           //Used for initial page set up and will return all point and widget values/previousValue/units and chart option json.  ?p=1.2.3&c=1.2.3&w=1.2.3
        '/query'=>['get'],                      //Returns point and widget values without units.  p=1.2.3&w=1.2.3
        '/query/custom'=>['get'],               //params includes p (points ID array), range (3d, etc), offset (1y, etc), boundary (true/false whether to be fixed on the range units)
        '/query/timeline'=>['get'],             //Returns next timechart value
        '/query/point/{id:[0-9]+}'=>['get'],    //returns single point value
        '/query/chart/{id:[0-9]+}'=>['get'],    //returns single chart option
        '/query/trend'=>['get'],                //Returns trend data, and based on Accept parameter, will be csv, json, or highchart json
    ];

    public function loadRoutes($router)
    {
        foreach (self::PRIVATE_ROUTES as $route=>$methods) {
            foreach ($methods as $method) {
                $router->$method('/api'.$route, 'proxy');
            }
        }
    }

    private function addProxyRoutes(array $routes, $router) {
        foreach ($routes as $route=>$methods) {
            foreach ($methods as $method) {
                $router->$method('/api'.$route, function() {
                    $request = \Concrete\Core\Http\Request::createFromGlobals();
                    //$email = $request->request->get("email");
                    $uri = $request->getUri();
                    $path = substr($uri->getPath(), 4); //Remove "/api" from uri
                    $request=$request->withUri($uri->withPath($path));
                    $response=new \Symfony\Component\HttpFoundation\JsonResponse(['bla'=>'hello']);
                    return $response;
                    return $this->serverBridge->proxy($request, $response);
                });
            }
        }
    }
}