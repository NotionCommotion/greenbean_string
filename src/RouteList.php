<?php
namespace Greenbean\Concrete5\GreenbeanDataIntegrator;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
class RouteList implements RouteListInterface
{
    public function loadRoutes($router)
    {
        /*
        $router->delete('/dashboard/greenbean/api/sandbox/{page}', 'Greenbean\Concrete5\GreenbeanDataIntegrator\Test::fuckyou'); //->setRequirements(['page' => '[0-9]+']);
        $router->delete('/dxxashboard/greenbean/api/sandbox/{page}', '\Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean\Sandbox::delete')->setRequirements(['page' => '[0-9]+']);
        $router->delete('/dashboard/greenbean/api/sandbox/{page}', 'Application\Api\Controller\Test::delete')->setRequirements(['page' => '[0-9]+']);
        $router->get('/dashboard/greenbean/xxx/{page}', '\Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean\Test::fuckyou');

        $router->delete('/dashboard/greenbean/api/sandbox/{page}', '\Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean\test::delete');
        */

        $router->delete('/dashboard/greenbean/api/sandbox/test/{page}', function($page){
            return "page $page";
        })->setRequirements(['page' => '[0-9]+']);
    }
}