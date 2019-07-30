<?php
namespace Concrete\Package\GreenbeanDataIntegrator;

use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Package\Package as Package;
use Concrete\Core\Page\Single as SinglePage;
use Greenbean\Concrete5\GreenbeanDataIntegrator\RouteList;

defined('C5_EXECUTE') OR die("Access Denied.");

class Controller extends Package implements ProviderAggregateInterface
{

    protected $appVersionRequired = '8.2';
    protected $pkgVersion = '0.3';
    protected $pkgHandle = 'greenbean_data_integrator';
    protected $pkgName = 'Greenbean Data Integrator';
    protected $pkgDescription = 'Interface to the Greenbean data Api';
    protected $pkgAutoloaderRegistries = [
        'src/' => 'Greenbean\\Concrete5\\GreenbeanDataIntegrator'
    ];
    //route with array with optional elements for page properties, future blocks (maybe), exclude_nav, and maybe more.
    private const  SINGLE_PAGES = [
        '/dataReporter' => ['cName'=>'Data Reporter'],
        '/dashboard/greenbean' => ['cName'=>'Greenbean', 'cDescription'=>'Greenbean Energy and Environmmental Data Manager'],
        '/dashboard/greenbean/report' => ['cName'=>'Report Manager'],
        '/dashboard/greenbean/report_detail' => ['exclude_nav'=>true],
        '/dashboard/greenbean/point' => ['cName'=>'Point Manager'],
        '/dashboard/greenbean/chart' => ['cName'=>'Chart Manager'],
        '/dashboard/greenbean/datasource' => ['cName'=>'Data Source Manager'],
        '/dashboard/greenbean/datasource_edit' => ['exclude_nav'=>true],
        '/dashboard/greenbean/sandbox' => ['cName'=>'Sandbox'],
        '/dashboard/greenbean/sandbox_edit' => ['exclude_nav'=>true],
        '/dashboard/greenbean/settings' => ['cName'=>'Account Settings'],
        '/dashboard/greenbean/manual' => ['cName'=>'Users Manual'],
        '/dashboard/greenbean/helpdesk' => ['cName'=>'Help Desk'],
        '/dashboard/greenbean/configure' => ['cName'=>'Configure'],
    ];

    private const  PAGE_PROPERTIES = ['cName'=>null, 'cCacheFullPageContent'=>null, 'cCacheFullPageContentLifetimeCustom'=>null, 'cCacheFullPageContentOverrideLifetime'=>null, 'cDescription'=>null, 'cDatePublic'=>null, 'uID'=>null, 'pTemplateID'=>null, 'ptID'=>null, 'cHandle'=>null, 'cFilename'=>null];

    public function install() {
        $pkg = parent::install();
        //BlockType::installBlockTypeFromPackage('event_calendar', $pkg);
        $this->installSinglePages($pkg);
    }

    public function upgrade() {
        parent::upgrade();
        $pkg = Package::getByHandle('greenbean_data_integrator');
        //if (!is_object(BlockType::getByHandle('greenbean_data_integrator'))) { BlockType::installBlockTypeFromPackage('greenbean_data_integrator', $this);}
        $this->installSinglePages($pkg);
    }

    public function on_start() {
        require_once $this->getPackagePath() . '/vendor/autoload.php';

        $config = $this->getFileConfig()->get('server');
        $this->app->bind('someDescription', function(Application $app) use($config) {
            $headers=['X-GreenBean-Key' => $config['api']];
            if($user = $app->make('session')->get('greenbeen-user')) {
                $headers['X-GreenBean-UserId'] = $user['id'];
            }
            return new \Greenbean\ServerBridge\ServerBridge(
                new \GuzzleHttp\Client(['base_uri' => 'https://'.$config['host'],'headers' => $headers])
            );
        });
        $router = $this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);
    }

    public function getEntityManagerProvider()
    {
        $provider = new StandardPackageProvider($this->app, $this, [
            'src/Entity' => 'Concrete\Package\GreenbeanDataIntegrator\Entity\UserAgent',
            //'src/Testing/Entity' => 'PortlandLabs\Testing\Entity'
        ]);
        return $provider;
    }

    private function installSinglePages($pkg) {
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