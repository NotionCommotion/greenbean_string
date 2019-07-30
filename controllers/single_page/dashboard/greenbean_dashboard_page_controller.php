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

    public function on_start()
    {
        //Events::addListener('on_start', function($event) {syslog(LOG_ERR, 'GreenbeanDashboardPageController::on_start: '.$this->getControllerActionPath());});

        //Why bother doing this in on_start() instead of just the view?
        if($assets=$this->getAssets()) {
            $assetList = \Concrete\Core\Asset\AssetList::getInstance();
            foreach($assets as $asset) {
                $asset=array_merge($asset, count($asset)===3?[[],'greenbean_data_integrator']:['greenbean_data_integrator']);
                $assetList->register(...$asset);    //returns JavascriptAsset if javascript, etc
            }
        }
    }

    public function on_before_render() {
        //Doesn't work with on_start()?
        if($this->getControllerActionPath()==='/dashboard/greenbean/configure' || $this->app->make('session')->has('greenbeen-user') || $this->validCredentials()) {
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
        $html = $this->twig->render($template, $variables);
        if($render) {
            $this->set('html', $html);
            $this->render('/twig');
        }
        return $html;
    }

    protected function validCredentials(bool $confirm=true):bool
    {
        //$em=$this->getEntityManager();
        $credentials = Package::getByHandle(self::PKGHANDLE)->getFileConfig()->get('server');   //getConfig() for DB
        $sb = new \Greenbean\ServerBridge\ServerBridge(
            new \GuzzleHttp\Client([
                'base_uri' => 'https://'.$credentials['host'],
                'headers' => ['X-GreenBean-Key' => $credentials['api']],
            ])
        );
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

    /**
    * Override in specific controller to add additional assets.
    * Follows standard concrete5 approach except package name must not be included.
    *
    * @param mixed $assets
    */
    protected function getAssets(array $assets=[])
    {
        //jquery, jquery-ui, bootstrap, and others are included by default.  See https://documentation.concrete5.org/developers/appendix/asset-list
        return array_merge($assets, [
            //['javascript', 'jquery'], //Removes it?
            //['javascript', 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.js', ['local'=>false]], //Default is v1.12.4?
            ['javascript', 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', ['local'=>false]],
            ['css', 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', ['local'=>false]],
            ['javascript', 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', ['local'=>false]],
            ['css', 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', ['local'=>false]],
            ['javascript', 'url-search-params', '//cdnjs.cloudflare.com/ajax/libs/url-search-params/0.10.0/url-search-params.js', ['local'=>false]],
            ['javascript', 'throbber', '//cdn.greenbeantech.net/libraries/throbber.js-master/throbber.js', ['local'=>false]],
            ['javascript', 'blockUI', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', ['local'=>false]],
            ['javascript', 'jquery.validate', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js', ['local'=>false]],
            ['javascript', 'additional-methods', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/additional-methods.js', ['local'=>false]],
            ['javascript', 'printIt', 'plugin/printIt/jquery.printIt.js'], //must be located before common.js
            ['javascript', 'common.js', 'js/common.js'],
            ['javascript', 'manual.js', 'js/manual.js'],
            ['javascript', 'my-validation-methods', 'js/my-validation-methods.js'],
            ['css', 'my.style.css', 'css/style.css'],
            ['css', 'manual.css', 'css/manual.css'],
        ]);
    }

    //How should this be accompished?
    //Pass array to override with given assets or null to override and include no assets.
    protected function setAssets(?array $assets=[])
    {
        //$this->requireAsset('jquery/ui'); //v1.11.4
        if($assets) {
            foreach($assets as $asset) {
                $this->requireAsset($asset[0], $asset[1]);
            }
        }
        elseif($assets && $assets=$this->getAssets()) {
            foreach($assets as $asset) {
                $this->requireAsset($asset[0], $asset[1]);
            }
        }
    }
}