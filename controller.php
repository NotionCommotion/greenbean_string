<?php
namespace Concrete\Package\GreenbeanDataIntegrator;

use Concrete\Core\Package\Package as Package;
use Concrete\Core\Page\Single as SinglePage;
use Greenbean\Concrete5\Datalogger\RouteList;

defined('C5_EXECUTE') OR die("Access Denied.");

class Controller extends Package
{

    protected $appVersionRequired = '8.1.0';
    protected $pkgVersion = '0.4';
    protected $pkgHandle = 'greenbean_data_integrator';
    protected $pkgName = 'Greenbean Data Integrator';
    protected $pkgDescription = 'Interface to the Greenbean data Api';
    protected $pkgAutoloaderRegistries = [
        'src/' => 'Greenbean\\Concrete5\\Datalogger'
    ];
    //route with array with optional elements for page properties, future blocks (maybe), exclude_nav, and maybe more.
    private const  SINGLE_PAGES = [
        '/dataReporter' => ['cName'=>'Data Reporter'],
        '/dashboard/greenbean/report' => ['cName'=>'Report Manager'],
        '/dashboard/greenbean' => ['cName'=>'Greenbean', 'cDescription'=>'Greenbean Energy and Environmmental Data Manager'],
        '/dashboard/greenbean/point' => ['cName'=>'Point Manager'],
        '/dashboard/greenbean/chart' => ['cName'=>'Chart Manager'],
        '/dashboard/greenbean/datasource' => ['cName'=>'Data Source Manager'],
        '/dashboard/greenbean/datasource_edit' => ['exclude_nav'=>true],
        '/dashboard/greenbean/sandbox' => ['cName'=>'Sandbox'],
        '/dashboard/greenbean/sandbox_edit' => ['exclude_nav'=>true],
        '/dashboard/greenbean/settings' => ['cName'=>'Account Settings'],
        '/dashboard/greenbean/manual' => ['cName'=>'Users Manual'],
        '/dashboard/greenbean/helpdesk' => ['cName'=>'Help Desk'],
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
        $router = $this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);
    }

    private function installSinglePages($pkg) {
        //??? Loader::model('single_page');
        foreach(self::SINGLE_PAGES as $route=>$properties) {
            $sp = SinglePage::add($route, $pkg);    //returns \Concrete\Core\Page\Page
            if($spProps=array_intersect_key($properties, self::PAGE_PROPERTIES)) {
                if(isset($spProps['cName'])) {
                    $spProps['cName']=t($spProps['cName']);  //What does t() do?
                }
                $sp->update($spProps);    //Returns null
            }
            if(!empty($properties['exclude_nav'])) {
                $sp->setAttribute('exclude_nav', true);    //Will not put in menu.  Returns Concrete\Core\Entity\Attribute\Value\PageValue
            }
        }
    }
}