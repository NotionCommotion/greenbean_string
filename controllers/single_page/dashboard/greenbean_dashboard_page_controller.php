<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\Core\Support\Facade\Events;
use Greenbean\Concrete5\GreenbeanDataIntegrator\GbHelper;
use Package;

abstract class GreenbeanDashboardPageController extends DashboardPageController
{
    private $twig, $serverBridge;
    protected $gbHelper;
    protected const PKGHANDLE = 'greenbean_data_integrator';    //How should this be accomplished?

    public function __construct(...$args)
    {
        $this->gbHelper=new GbHelper(); //How should this be injected?
        parent::__construct(...$args);
    }

    /*
    public function on_start()
    {
    //Events::addListener('on_start', function($event) {syslog(LOG_ERR, 'GreenbeanDashboardPageController::on_start: '.$this->getControllerActionPath());});
    }
    */

    public function on_before_render() {
        //Doesn't work with on_start()?
        if($this->getControllerActionPath()==='/dashboard/greenbean/configure' || $this->validCredentials()) {
            parent::on_before_render();
        }
        else {
            $this->redirect('/dashboard/greenbean/configure');
        }
    }

    //Future.  Inject path to templates, etc
    protected function twig(string $template, array $variables=[], bool $render=true):string
    {
        if(!$this->twig) {
            $this->twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__.'/../../../single_pages'), [
                //'cache' => 'path/to/cache'    // Add when complete.  See auto_reload option
                'debug'=>true,
                //'strict_variables'=>true
            ]);
        }
        $variables['base_url'] = Package::getByHandle(self::PKGHANDLE)->getRelativePath();
        $variables['img_url'] = $variables['base_url'] . '/images';
        $html = $this->twig->render($template, $variables);
        if($render) {
            $this->set('html', $html);
            $this->render('/twig');
        }
        return $html;
    }

    protected function validCredentials(bool $confirm=true):bool
    {
        if($this->app->make('session')->has('greenbeen-user') && Package::getByHandle(self::PKGHANDLE)->getFileConfig()->get('server')) {
            //If user is logged on and package has applicable server credentials, don't worry about verifying server.
            //If invalid server credentials, call will delete server credential file and cause re-entering this data.
            return true;
        }
        $sb = $this->getServerBridge();
        try {
            $u = new User();
            $ui = UserInfo::getByID($u->getUserID());
            $user=$sb->callApi('GET', '/users', ["username"=>$ui->getUserName(), "password"=>$ui->getUserPassword()]);
            $this->app->make('session')->set('greenbeen-user', $user);
            return true;
        }
        catch(\Greenbean\ServerBridge\ServerBridgeException $e) {
            syslog(LOG_ERR,'GreenbeanDashboardPageController::validCredentials(): '.$e->getMessage());
            //how do I inform user that credentials were bad?
            return false;
        }
    }

    protected function getServerBridge()
    {
        if(!$this->serverBridge) $this->serverBridge = $this->app->make('\Greenbean\ServerBridge\ServerBridge');
        return $this->serverBridge;
    }

    protected function addAssets(array $assets=[])
    {
        //Thise assets are required for all pages, and will be loaded first.
        $assets = array_merge([
            //['javascript', 'jquery'],
            ['jquery/ui'],  //force to be loaded before others.
            ['bootstrap'],
            ['javascript', 'url-search-params'],
            ['javascript', 'throbber'],
            ['javascript', 'blockUI'],
            ['jquery.validate'],
            ['javascript', 'printIt'], //must be located before common.js
            ['common'],
            ['manual'],
            ], $assets);
        foreach($assets as $asset) {
            $this->requireAsset(...$asset);
        }
    }
}