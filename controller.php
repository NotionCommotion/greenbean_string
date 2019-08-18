<?php
namespace Concrete\Package\GreenbeanDataIntegrator;

use Concrete\Core\Package\Package as Package;
use Concrete\Core\Page\Single as SinglePage;
use Greenbean\ServerBridge\ServerBridge;
use Concrete\Core\Application\Application;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Asset\Asset;
use Concrete\Core\Backup\ContentImporter;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean\Sandbox;
use Greenbean\Concrete5\GreenbeanDataIntegrator\RouteList;
//use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
//use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Doctrine\ORM\EntityManager;
use Greenbean\Concrete5\GreenbeanDataIntegrator\Entity\SandboxPage;

defined('C5_EXECUTE') OR die("Access Denied.");

class Controller extends Package    // implements ProviderAggregateInterface
{

    protected $appVersionRequired = '8.2';
    protected $pkgVersion = '0.4';
    protected $pkgHandle = 'greenbean_data_integrator';
    protected $pkgName = 'Greenbean Data Integrator';
    protected $pkgDescription = 'Interface to the Greenbean data Api';
    protected $pkgAutoloaderRegistries = [
        'src/' => 'Greenbean\\Concrete5\\GreenbeanDataIntegrator'
    ];

    //Forth element is an optional group name
    private const  ASSETS = [
        /*
        Some assets included by default (versions listed below).  See https://documentation.concrete5.org/developers/appendix/asset-list
        Specified in public/concrete/config/app.php.  Line 881 specifies which ones are included on dasahboard
        jquery - 1.12.4
        jquery/ui - v1.11.4
        bootstrap.  3.4.0?
        */

        //Use old shipped jquery and jqueryUi versions
        //['javascript', 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js', ['local'=>false, 'position' => Asset::ASSET_POSITION_HEADER]],   //, 'version' => '3.4.1']], //Default is v1.12.4?    //Not used?
        //['javascript', 'jquery/ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', ['local'=>false], 'jquery/ui'],    //Not used?         //, 'position' => Asset::ASSET_POSITION_HEADER
        //['css', 'jquery/ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', ['local'=>false], 'jquery/ui'],    //Not used?

        //Only needed if shipped old jquery-ui is used!
        ['javascript', 'jquery-ui-autocomplete', 'js/jquery-ui-autocomplete.js', []],

        //C5 has bugs regarding bootstrap icons.  See link for what I did: https://www.concrete5.org/community/forums/customizing_c5/using-bootstrop-glyphicon-with-c5/#965410

        //['javascript', 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', ['local'=>false], 'bootstrap'],    //Not used?
        //['css', 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', ['local'=>false], 'bootstrap'],    //Not used?

        //['css', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false], 'bootstrap-editable'],
        //['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false], 'bootstrap-editable'],

        ['css', 'bootstrap-datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css', ['local'=>false], 'bootstrap-datepicker'],
        ['javascript', 'bootstrap-datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.js', ['local'=>false], 'bootstrap-datepicker'],

        ['javascript', 'highcharts', '//code.highcharts.com/highcharts.js', ['local'=>false], 'highcharts'],
        ['javascript', 'highcharts-more', '//code.highcharts.com/highcharts-more.js', ['local'=>false], 'highcharts'],
        ['javascript', 'solid-gauge', '//code.highcharts.com/modules/solid-gauge.js', ['local'=>false], 'highcharts'],

        ['javascript', 'jquery.validate', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js', ['local'=>false], 'jquery.validate'],
        ['javascript', 'additional-methods', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/additional-methods.js', ['local'=>false], 'jquery.validate'],
        ['javascript', 'my-validation-methods', 'js/my-validation-methods.js', [], 'jquery.validate'],

        ['javascript', 'sortfixedtable', 'plugin/sortfixedtable/jquery.sortfixedtable.js', [], 'sortfixedtable'],
        ['css', 'sortfixedtable', 'plugin/sortfixedtable/sortfixedtable.css', [], 'sortfixedtable'],

        ['javascript', 'jstree', '//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/jstree.min.js', ['local'=>false], 'jstree'],
        ['css', 'jstree', '//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/themes/default/style.min.css', ['local'=>false], 'jstree'],

        ['javascript', 'toolTip', 'plugin/toolTip/jquery.toolTip.js', [], 'toolTip'],
        ['css', 'toolTip', 'plugin/toolTip/toolTip.css', [], 'toolTip'],

        ['javascript', 'dynamic_update', '//cdn.greenbeantech.net/libraries/greenbean-public/1.0/dynamic_update_c5.js', ['local'=>false], 'dynamic_update'],
        ['css', 'dynamic_update', '//cdn.greenbeantech.net/libraries/greenbean-public/1.0/dynamic_update.css', ['local'=>false], 'dynamic_update'],

        ['javascript', 'upload', 'plugin/upload/upload.js', [], 'upload'],
        ['css', 'upload', 'plugin/upload/upload.css', [], 'upload'],

        ['javascript', 'url-search-params', '//cdnjs.cloudflare.com/ajax/libs/url-search-params/0.10.0/url-search-params.js', ['local'=>false]],
        ['javascript', 'throbber', '//cdn.greenbeantech.net/libraries/throbber.js-master/throbber.js', ['local'=>false]],
        ['javascript', 'blockUI', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', ['local'=>false]],
        ['javascript', 'printIt', 'plugin/printIt/jquery.printIt.js', []], //must be located before common.js
        ['javascript', 'handlebars', '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js', ['local'=>false]],
        ['javascript', 'table-dragger', '//gitcdn.link/repo/sindu12jun/table-dragger/dev/dist/table-dragger.js', ['local'=>false]],
        ['javascript', 'editableAutocomplete', 'js/jquery.editableAutocomplete.js', []],
        ['javascript', 'jquery.initialize', '//cdn.jsdelivr.net/npm/jquery.initialize@1.3.0/jquery.initialize.min.js', ['local'=>false]],
        ['javascript', 'moment',  '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js', ['local'=>false]],    //Not used?
        ['javascript', 'tinymce', '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.7.9/tinymce.min.js', ['local'=>false]],

        //Add last
        ['css', 'common', 'css/style.css', [], 'common'],
        ['javascript', 'common', 'js/common.js', [], 'common'],
        ['css', 'manual.css', 'css/manual.css', [],'manual'],
        ['javascript', 'manual.js', 'js/manual.js', [],'manual'],
        ['javascript', 'sandbox_edit', 'js/sandbox_edit.js', [], 'sandbox_edit'],
        ['css', 'sandbox_edit', 'css/sandbox_edit.css', [], 'sandbox_edit'],

        ['javascript', 'sandbox', 'js/sandbox.js', []],
        ['javascript', 'charts', 'js/charts.js', []],
        //['javascript', 'configure', 'js/configure.js', []],
        ['javascript', 'sources', 'js/sources.js', []],
        ['javascript', 'source_bacnet', 'js/source_bacnet.js', []],
        ['javascript', 'helpdesk', 'js/helpdesk.js', []],
        ['javascript', 'points', 'js/points.js', []],
        ['javascript', 'reports', 'js/reports.js', []],
        ['javascript', 'settings', 'js/settings.js', []],
    ];

    public function install()
    {
        $pkg = parent::install();
        $this->installSinglePages();
        $this->addInitialSandboxPages();
    }

    public function upgrade()
    {
        parent::upgrade();
        $this->installSinglePages();
        $this->addInitialSandboxPages();
    }

    public function uninstall()
    {
        syslog(LOG_ERR, 'Consider not deleting database upon removing Greenbean Data Integrator');
        parent::uninstall();
        $db = \Database::connection();
        $db->query('DROP TABLE IF EXISTS SandboxPages');
    }

    public function on_start()
    {
        require_once $this->getPackagePath() . '/vendor/autoload.php';

        //Registrer assets
        $groups=[];
        $al = AssetList::getInstance();
        foreach(self::ASSETS as $asset) {
            if(isset($asset[4])) $groups[$asset[4]][]=array_slice($asset, 0, 2);
            $asset[4]='greenbean_data_integrator';
            $al->register(...$asset);    //returns JavascriptAsset if javascript, etc
        }
        foreach($groups as $name => $assets) {
            $al->registerGroup($name, $assets);
        }

        $gbUser = $this->app->make('session')->get('greenbeen-user');

        $this->app->when('Greenbean\Concrete5\GreenbeanDataIntegrator\ValidGbUserMiddleware')->needs('$gbUser')->give($gbUser);

        if($config = $this->getFileConfig()->get('server')) {
            $this->app->bind(ServerBridge::class, function(Application $app) use($gbUser, $config) {
                $headers=['X-GreenBean-Key' => $config['api']];
                if($gbUser) {
                    $headers['X-GreenBean-UserId'] = $gbUser['id'];
                }
                return new ServerBridge(
                    new \GuzzleHttp\Client(['base_uri' => 'https://'.$config['host'],'headers' => $headers]),
                    new \Greenbean\ServerBridge\SymfonyHttpClientHandler()
                );
            });
        }

        $router=$this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);
    }

    private function installSinglePages()
    {
        $contentImporter = $this->app->make(ContentImporter::class);
        $contentImporter->importContentFile($this->getPackagePath() . '/install.xml');
    }

    private function addInitialSandboxPages()
    {
        $em = $this->app->make(EntityManager::class);
        $repo = $em->getRepository(SandboxPage::class);
        $r = $repo->createQueryBuilder('s')->select('s.id')->setMaxResults(1)->getQuery()->execute();
        if (empty($r)) {
            foreach ([
                SandboxPage::create('Sample page', '<p>First use the Point Manager and Chart Manager to create data objects, and then use the insert icon to add them to this page.</p>'),
                ] as $page) {
                $em->persist($page);
            }
            $em->flush();
        }
    }
}