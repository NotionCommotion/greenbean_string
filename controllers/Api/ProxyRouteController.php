<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\Api;
use Symfony\HttpFoundation\JsonResponse;
use Greenbean\Concrete5\GreenbeanDataIntegrator\GbHelper;
use Greenbean\ServerBridge\ServerBridge;
class ProxyRouteController
{

    protected $serverBridge;
    public function __construct(ServerBridge $serverBridge)
    {
        $this->serverBridge = $serverBridge;
    }

    public function proxyRoute()
    {
        $request = \Concrete\Core\Http\Request::createFromGlobals();
        $response=null; //new \Symfony\Component\HttpFoundation\JsonResponse(); //Include array arguement for content
        return $this->serverBridge->proxy($request, $response, function($path){
            return substr($path, 25);       //Remove "/dashboard/greenbean/api/" from uri
        });
    }
}