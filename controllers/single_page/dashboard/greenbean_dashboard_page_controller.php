<?php
namespace Concrete\Package\GreenbeanString\Controller\SinglePage\Dashboard;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\Core\Support\Facade\Events;
use Greenbean\Concrete5\GreenbeanString\GbHelper;

abstract class GreenbeanDashboardPageController extends DashboardPageController
{
    private $twig, $serverBridge;
    protected $gbHelper;
    protected const PKGHANDLE = 'greenbean_string';    //How should this be accomplished?

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
        $variables['gb_root_base'] = \Package::getByHandle(self::PKGHANDLE)->getRelativePath();
        $variables['gb_img_base'] = $variables['gb_root_base'] . '/images';
        $variables['gb_url_base'] = '/dashboard/greenbean';
        $variables['gb_api_base'] = $variables['gb_url_base'].'/api';
        $html = $this->twig->render($template, $variables);
        if($render) {
            $this->set('html', $html);
            $this->render('/twig');
        }
        return $html;
    }

    protected function validCredentials(bool $confirm=true):bool
    {
        if($this->app->make('session')->has('greenbeen-user') && \Package::getByHandle(self::PKGHANDLE)->getFileConfig()->get('server')) {
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

    protected function getMenu($active=null) {
        return [
            'active'=>$active,
            'links'=>[
                ['name'=>'Reports','path'=>'/dashboard/greenbean/report'],
                ['name'=>'Points','path'=>'/dashboard/greenbean/point'],
                ['name'=>'Charts','path'=>'/dashboard/greenbean/chart'],
                ['name'=>'Data Sources','path'=>'/dashboard/greenbean/source'],
                ['name'=>'Sandbox','path'=>'/dashboard/greenbean/sandbox'],
                ['name'=>'Account Settings','path'=>'/dashboard/greenbean/settings'],
                ['name'=>'Users Manual','class'=>'manual'],
                ['name'=>'Help Desk','path'=>'/dashboard/greenbean/helpdesk'],
            ]
        ];
    }

    protected function addAssets(array $assets=[])
    {
        //Thise assets are required for all pages, and will be loaded first.
        $assets = array_merge([
            //['javascript', 'jquery'],
            ['jquery/ui'],  //force to be loaded before others.
            ['javascript', 'jquery-ui-autocomplete'],   //Only required if using C5's old version of ui
            //['bootstrap'],  //Use new version.
            //['javascript', 'bootstrap/*'],  //Won't work because loads them after bootstrap-editable
            //If using just C5's bootstrap javascript.
            ['javascript', 'bootstrap/tooltip'],
            ['javascript', 'bootstrap/popover'],
            ['javascript', 'bootstrap-editable'],
            ['css', 'font-awesome'],
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

    protected function is_digit($v) {
        return is_int($v)||ctype_digit(($v));
    }

    protected function displayError(array $errors, string $menu, bool $errorFromApi=true)
    {
        if($errorFromApi) $errors=$this->combineError($errors);
        $rs=['menu'=>$this->getMenu($menu), 'errors'=>$errors];
        $this->addAssets();
        $this->twig('dashboard/greenbean/error.php', $rs);
    }

    //Error provided by API as name => values array
    protected function combineError(array $errors):array
    {
        $errorsOut=[];
        foreach($errors as $name =>$error) {
            $errorsOut[]="$name: $error";
        }
        return $errorsOut;
    }
}