<?php
namespace Greenbean\Concrete5\GreenbeanDataIntegrator;
use Symfony\HttpFoundation\JsonResponse;
use Greenbean\Concrete5\GreenbeanDataIntegrator\GbHelper;
use Greenbean\ServerBridge\ServerBridge;
class RouteController
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

    /**
    * Used to replace single pages with router.
    * Documentation at https://documentation.concrete5.org/developers/routing/views is incorrect.
    * Not yet working.
    *
    * @param mixed $template
    * @param mixed $variables
    * @param bool $render
    * @return string
    */
    protected function twig(string $template, array $variables=[], bool $render=false):string
    {
        $msg='RouteController::twig() not working';
        syslog(LOG_ERR, $msg);
        exit($msg);
        // Concrete\Core\Support\Facade\Events;
        // $factory = $app->make(\Concrete\Core\Http\ResponseFactoryInterface::class);
        if(!$this->twig) {
            $this->twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__.'/../../single_pages'), [
                //'cache' => 'path/to/cache'    // Add when complete.  See auto_reload option
                'debug'=>true,
                //'strict_variables'=>true
            ]);
        }
        $variables['base_url'] = \Package::getByHandle(self::PKGHANDLE)->getRelativePath();
        $variables['img_url'] = $variables['base_url'] . '/images';
        syslog(LOG_INFO, '$variables: '.json_encode($variables));
        $html = $this->twig->render($template, $variables);
        //syslog(LOG_INFO, '$html: '.json_encode($html));
        if($render) {
            $this->set('html', $html);
            $this->render('/twig');
        }
        return $html;
    }
}