<?php
namespace Greenbean\Concrete5\GreenbeanString;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use Greenbean\Concrete5\GreenbeanString\ValidGbUserMiddleware;
use Concrete\Core\Http\Middleware\OAuthAuthenticationMiddleware;
class RouteList implements RouteListInterface
{
    //path, array of methods, optional array of regex validation.  Sequential arrays are integers, associate arrays are name=>regex
    //Confirm sympony router cannot do like twig router such as {id:[0-9]+}.
    private const  PRIVATE_ROUTES =[
        ['/account', ['put']],
        ['/account/timezones', ['get']],
        ['/units/time', ['get']],
        ['/tags/lans', ['get','post']],
        ['/tags/lans/{id}', ['delete','put'], ['id']],
        ['/sources/{id}', ['delete','get','put'], ['id']],  //GET currently not used since rendered in page
        ['/sources/{id}/points', ['get'], ['id']],
        ['/sources/{id}/gateway', ['post'], ['id']],   //methods supported in POST: restartService|restartNetwork|rebootDevice
        ['/sources', ['post']],
        ['/sources/{id}/discovery/{deviceId}', ['post'], ['id', 'deviceId']],
        ['/sources/{id}/discoveryDevicesOnline', ['get'], ['id']],//Might not be implemented if JavaScript is used to individual query each request.
        ['/points', ['post', 'get']],  //GET used for searching points
        ['/points/validation', ['get']],   // Do before /api/points/{id}.  Used for validation
        ['/points/{id}', ['get','put','delete'], ['id']],
        ['/points/{id}/custom', ['get','post'], ['id']],
        ['/points/{id}/custom/{pointsIdSub}', ['delete','put'], ['id','pointsIdSub']],

        ['/bacnet/devices', ['get']], //All bacnet devices for all sources
        ['/bacnet/objects', ['get']], //All bacnet objects for all sources
        ['/sources/{protocol}/defaults', ['get']], //get default values for a new bacnet source.  Currently only bacnet
        ['/sources/{id}/bacnet/devices', ['get'], ['id']], //All bacnet devices for given id
        ['/sources/{id}/bacnet/deviceobjects', ['get'], ['id']], //All bacnet devices with objects for given id
        ['/sources/{id}/bacnet/devices/{deviceId}', ['get'], ['id','deviceId']],
        ['/sources/{id}/bacnet/devices/{deviceId}/objects', ['get'], ['id','deviceId']],
        ['/sources/{id}/bacnet/devices/{deviceId}/objects/{objectId}/{typeId}', ['get'], ['id','deviceId','objectId','typeId']],

        ['/charts', ['get', 'post']],//GET used for searching charts
        ['/charts/validation', ['get']],
        ['/chart/{id}/options', ['get','put'], ['id']],   //get or update entire Highchart option object
        ['/chart/{id}/options/{params}', ['put'], ['id', 'params'=>'.*']],   //Update single property in the Highchart option object.  Future add GET.
        ['/chart/{id}/series', ['post'], ['id']],//POST is currently only used to allow time chart to ADD a series.  Maybe make endpoint as /chart/{id} and use for other charts as well by including the parameter description (i.e. addSeries, addPoint, etc)to add?
        ['/chart/{id}/category', ['post'], ['id']],//Not currently implemented by API.
        ['/chart/{id}', ['delete','get','post','put'], ['id']], //POST used to add point item based on given series and category for category charts and just the points ID and legend for pie chart and time chart.
        ['/chart/clone/{id}', ['post'], ['id']],
        ['/chart/{id}/series/{seriesOffset}', ['delete','post','put'], ['id','seriesOffset']],  //Only used to allow pie chart to add a category
        ['/chart/{id}/category/{categoryOffset}', ['delete','put'], ['id','categoryOffset']],  //Only used by category charts.
        ['/chart/{id}/series/{seriesOffset}/data/{dataOffset}', ['delete','put'], ['id','seriesOffset','dataOffset']], // Old approach was to use POST used to update point value and send NULL to delete point.  Only used with category chart.
        ['/charts/themes', ['get']],    //Not currently used to proxy since charts display uses this endpoint directly.
        ['/reports', ['get','post']],
        ['/reports/{id}', ['delete','get', 'put'], ['id']], //POST removed.  GET not used as /reports and /reports/{id} will directly get this data serverside
        ['/reports/validation', ['get']],  //Not used?
        ['/manual', ['get']],
        ['/manual/{id}', ['get'], ['id']],
        ['/helpdesk', ['get','post']],  //GET Not used, and performed directy in HelpDesk class
        ['/helpdesk/topics', ['get']], //Not used, and performed directy in HelpDesk class
        ['/helpdesk/search', ['get']], //Not yet implemented
        ['/helpdesk/validation', ['get']],
        ['/helpdesk/{id}', ['delete','get','post','put'], ['id']],
        ['/points/custom/report', ['get']],
        ['/tools/import/validate', ['post']], //Will include files
        ['/tools/import/update', ['post']],   //Will include files
    ];

    private const  PUBLIC_ROUTES=[
        ['/query/initialize', ['get']],           //Used for initial page set up and will return all point and widget values/previousValue/units and chart option json.  ?p=1.2.3&c=1.2.3&w=1.2.3
        ['/query', ['get']],                      //Returns point and widget values without units.  p=1.2.3&w=1.2.3
        ['/query/custom', ['get']],               //params includes p (points ID array), range (3d, etc), offset (1y, etc), boundary (true/false whether to be fixed on the range units)
        ['/query/timeline', ['get']],             //Returns next timechart value
        ['/query/point/{id}', ['get'], ['id']],    //returns single point value
        ['/query/chart/{id}', ['get'], ['id']],    //returns single chart option
        ['/query/trend', ['get']],                //Returns trend data, and based on Accept parameter, will be csv, json, or highchart json
    ];

    public function loadRoutes($router)
    {
        //Ideally, I would use the router for all endpoints including view.  Can't get view working.

        $this->addProxyRoutes($router, self::PUBLIC_ROUTES);

        $router->buildGroup()
        ->addMiddleware(ValidGbUserMiddleware::class)
        ->routes(function($groupRouter) {
            $this->addProxyRoutes($groupRouter, self::PRIVATE_ROUTES);
            $groupRouter->post('/dashboard/greenbean/api/sandbox', 'Concrete\Package\GreenbeanString\Controller\Api\SandboxRouteController::create');
            $groupRouter->delete('/dashboard/greenbean/api/sandbox/{id}', 'Concrete\Package\GreenbeanString\Controller\Api\SandboxRouteController::delete');//->setRequirements(['id' => '[0-9]+']);
            $groupRouter->put('/dashboard/greenbean/api/sandbox/{page}', 'Concrete\Package\GreenbeanString\Controller\Api\SandboxRouteController::save')->setRequirements(['page' => '[0-9]+']);
        });
    }

    private function addProxyRoutes($router, array $routes)
    {
        //$routes is something like: [['/path/{id}', ['post','put'], ['id']]],
        foreach ($routes as $route) {
            foreach ($route[1] as $method) {
                $r=$router->$method('/dashboard/greenbean/api'.$route[0], 'Concrete\Package\GreenbeanString\Controller\Api\ProxyRouteController::proxyRoute');
                if(isset($route[2])) {
                    foreach($route[2] as $key=>$value) {
                        $r->setRequirements(is_int($key)?[$value=>'[0-9]+']:[$key => $value]);

                    }
                }
            }
        }
    }
}