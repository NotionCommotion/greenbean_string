<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\Api;
use Symfony\HttpFoundation\JsonResponse;
use Greenbean\Concrete5\GreenbeanDataIntegrator\GbHelper;
use Greenbean\ServerBridge\ServerBridge;
class ProxyRouteController
{

    protected $serverBridge, $gbHelper, $gbUser;
    public function __construct(ServerBridge $serverBridge, GbHelper $gbHelper, $gbUser)
    {
        $this->serverBridge = $serverBridge;
        $this->gbHelper = $gbHelper;
        $this->gbUser = $gbUser;
    }

    public function privateProxy()
    {
        if($this->gbUser) return $this->publicProxy();   //Change to use middleware
        else syslog(LOG_ERR, 'Invalid request to proxy');
    }

    public function publicProxy()
    {
        $request = \Concrete\Core\Http\Request::createFromGlobals();
        $response=null; //new \Symfony\Component\HttpFoundation\JsonResponse(); //Include array arguement for content
        return $this->serverBridge->proxy($request, $response, function($path){
            return substr($path, 25);       //Remove "/dashboard/greenbean/api/" from uri
        });
    }
}