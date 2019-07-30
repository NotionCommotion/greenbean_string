<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard;
use Concrete\Core\Page\Controller\DashboardPageController;
use Greenbean\Concrete5\GreenbeanDataIntegrator\CommonTrait;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Package;

abstract class GreenbeanDashboardPageController extends DashboardPageController
{
    use CommonTrait;
    protected const PKGHANDLE = 'greenbean_data_integrator';    //How should this be accomplished?

    public function on_before_render() {
        //Doesn't work with on_start()?
        if($this->getControllerActionPath()==='/dashboard/greenbean/configure' || $this->app->make('session')->has('greenbeen-user') || $this->validCredentials()) {
            parent::on_before_render();
        }
        else {
            $this->redirect('/dashboard/greenbean/configure');
        }
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
}