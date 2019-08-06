<?php
namespace Concrete\Package\GreenbeanDataIntegrator;

use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Package\Package as Package;
use Concrete\Core\Page\Single as SinglePage;
use Greenbean\Concrete5\GreenbeanDataIntegrator\RouteList;
use Greenbean\ServerBridge\ServerBridge;
use Concrete\Core\Application\Application;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Asset\Asset;

defined('C5_EXECUTE') OR die("Access Denied.");

class Controller extends Package implements ProviderAggregateInterface
{

    protected $appVersionRequired = '8.2';
    protected $pkgVersion = '0.1';
    protected $pkgHandle = 'greenbean_data_integrator';
    protected $pkgName = 'Greenbean Data Integrator';
    protected $pkgDescription = 'Interface to the Greenbean data Api';
    protected $pkgAutoloaderRegistries = [
        'src/' => 'Greenbean\\Concrete5\\GreenbeanDataIntegrator'
    ];
    //route with array with optional elements for page properties, future blocks (maybe), exclude_nav, and maybe more.
    //Confirm sympony router cannot do like twig router such as {id:[0-9]+}.
    private const  SINGLE_PAGES = [
        '/data_reporter' => ['cName'=>'Data Reporter'],
        '/dashboard/greenbean' => ['cName'=>'Greenbean', 'cDescription'=>'Greenbean Energy and Environmmental Data Manager'],
        '/dashboard/greenbean/report' => ['cName'=>'Report Manager'],
        '/dashboard/greenbean/point' => ['cName'=>'Point Manager'],
        '/dashboard/greenbean/chart' => ['cName'=>'Chart Manager'],
        '/dashboard/greenbean/source' => ['cName'=>'Data Source Manager'],
        '/dashboard/greenbean/sandbox' => ['cName'=>'Sandbox'],
        '/dashboard/greenbean/sandbox/edit' => ['exclude_nav'=>true],
        '/dashboard/greenbean/settings' => ['cName'=>'Account Settings'],
        '/dashboard/greenbean/manual' => ['cName'=>'Users Manual'],
        '/dashboard/greenbean/helpdesk' => ['cName'=>'Help Desk'],
        '/dashboard/greenbean/configure' => ['exclude_nav'=>true],
    ];

    private const  PAGE_PROPERTIES = ['cName'=>null, 'cCacheFullPageContent'=>null, 'cCacheFullPageContentLifetimeCustom'=>null, 'cCacheFullPageContentOverrideLifetime'=>null, 'cDescription'=>null, 'cDatePublic'=>null, 'uID'=>null, 'pTemplateID'=>null, 'ptID'=>null, 'cHandle'=>null, 'cFilename'=>null];

    //path, array of methods, optional array of regex validation.  Sequential arrays are integers, associate arrays are name=>regex
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

    //Forth element is an optional group name
    private const  ASSETS = [
        /*
        Some assets included by default (versions listed below).  See https://documentation.concrete5.org/developers/appendix/asset-list
        Specified in public/concrete/config/app.php.  Line 881 specifies which ones are included on dasahboard
        jquery - 1.12.4
        jquery/ui - v1.11.4
        bootstrap.  3.4.0?
        */

        ['javascript', 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js', ['local'=>false, 'position' => Asset::ASSET_POSITION_HEADER]],   //, 'version' => '3.4.1']], //Default is v1.12.4?    //Not used?
        //['javascript', 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js', ['local' => false]],   //, 'version' => '2.2']],
        ['javascript', 'jquery/ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', ['local'=>false], 'jquery/ui'],    //Not used?         //, 'position' => Asset::ASSET_POSITION_HEADER
        ['css', 'jquery/ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', ['local'=>false], 'jquery/ui'],    //Not used?

        ['javascript', 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', ['local'=>false], 'bootstrap'],    //Not used?
        ['css', 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', ['local'=>false], 'bootstrap'],    //Not used?

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

        ['javascript', 'dynamic_update', '//cdn.greenbeantech.net/libraries/greenbean-public/1.0/dynamic_update.js', ['local'=>false], 'dynamic_update'],
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

        ['javascript', 'charts', 'js/charts.js', []],
        ['javascript', 'configure', 'js/configure.js', []],
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
        //BlockType::installBlockTypeFromPackage('event_calendar', $pkg);
        $this->installSinglePages($pkg);
    }

    public function upgrade()
    {
        parent::upgrade();
        $pkg = Package::getByHandle('greenbean_data_integrator');
        //if (!is_object(BlockType::getByHandle('greenbean_data_integrator'))) { BlockType::installBlockTypeFromPackage('greenbean_data_integrator', $this);}
        $this->installSinglePages($pkg);
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

        $user = $this->app->make('session')->get('greenbeen-user');
        //Future - Change to use RouteList and RouteListInterface
        $router=$this->app->make('router');
        $this->addProxyRoutes($router, self::PRIVATE_ROUTES, $user, false);
        $this->addProxyRoutes($router, self::PUBLIC_ROUTES, $user, true);
        if($user && $config = $this->getFileConfig()->get('server')) {
            $this->app->bind(ServerBridge::class, function(Application $app) use($user, $config) {
                $headers=['X-GreenBean-Key' => $config['api']];
                if($user) {
                    $headers['X-GreenBean-UserId'] = $user['id'];
                }
                return new ServerBridge(
                    new \GuzzleHttp\Client(['base_uri' => 'https://'.$config['host'],'headers' => $headers]),
                    new \Greenbean\ServerBridge\SymfonyHttpClientHandler()
                );
            });
        }
    }

    private function addProxyRoutes($router, array $routes, $user, $public)
    {
        //['/path/{id}', ['post','put'], ['id']],
        foreach ($routes as $route) {
            foreach ($route[1] as $method) {
                $r=$router->$method('/dashboard/greenbean/api'.$route[0], function() use($user, $public){
                    if($user || $public) {
                        $request = \Concrete\Core\Http\Request::createFromGlobals();
                        $response=null; //new \Symfony\Component\HttpFoundation\JsonResponse(); //Include array arguement for content
                        return $this->app->make('\Greenbean\ServerBridge\ServerBridge')->proxy($request, $response, function($path){
                            return substr($path, 25);       //Remove "/dashboard/greenbean/api/" from uri
                        });
                    }
                });
                if(isset($route[2])) {
                    foreach($route[2] as $key=>$value) {
                        $r->setRequirements(is_int($key)?[$value=>'[0-9]+']:[$key => $value]);

                    }
                }
            }
        }
    }

    public function getEntityManagerProvider()
    {
        $provider = new StandardPackageProvider($this->app, $this, [
            'src/Entity' => 'Concrete\Package\GreenbeanDataIntegrator\Entity\UserAgent',
            //'src/Testing/Entity' => 'PortlandLabs\Testing\Entity'
        ]);
        return $provider;
    }

    private function installSinglePages($pkg)
    {
        //??? Loader::model('single_page');
        foreach(self::SINGLE_PAGES as $route=>$properties) {
            $sp = SinglePage::add($route, $pkg);    //returns \Concrete\Core\Page\Page
            if($spProps=array_intersect_key($properties, self::PAGE_PROPERTIES)) {
                if(isset($spProps['cName'])) {
                    $spProps['cName']=t($spProps['cName']);
                }
                $sp->update($spProps);    //Returns null
            }
            if(!empty($properties['exclude_nav'])) {
                $sp->setAttribute('exclude_nav', true);    //Will not put in menu.  Returns Concrete\Core\Entity\Attribute\Value\PageValue
            }
        }
    }
}