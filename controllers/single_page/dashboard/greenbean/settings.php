<?php
namespace Concrete\Package\GreenbeanString\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanString\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Settings extends GreenbeanDashboardPageController
{
    public function view()
    {
        $rs=$this->getServerBridge()->getPageContent([
            'defaultValues'=>'/account',
            'virtualLans'=>'/tags/lans'
        ]);
        $rs['datalogger']=['ip'=>$this->getServerBridge()->getHost(),'key'=>$this->getServerBridge()->getConfigParam(['headers','X-GreenBean-Key'])];
        $rs['menu']=$this->getMenu('/dashboard/greenbean/settings');
        if(!$rs['defaultValues']) {
            syslog(LOG_ERR, 'Account is missing default data');
            $rs['defaultValues']=$this->gbHelper->getDefaultValues();
        }
        if(!$rs['virtualLans']) {
            syslog(LOG_ERR, 'Account is missing virtual LAN');
            $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        }
        $this->addAssets([
            ['javascript', 'handlebars'],
            //['bootstrap-editable'],
            //['javascript', 'bootstrap-editable'],
            ['javascript', 'settings'],
            ['javascript', 'editableAutocomplete'],
            ['upload'],
        ]);
        $this->twig('dashboard/greenbean/settings.php', $rs);
    }
}