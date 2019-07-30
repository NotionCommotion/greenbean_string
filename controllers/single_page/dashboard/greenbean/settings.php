<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Settings extends GreenbeanDashboardPageController
{
    public function view()
    {
        $rs=$this->getServerBridge()->getPageContent([
            'defaultValues'=>'/account',
            'virtualLans'=>'/tags/lans'
        ]);
        $rs['datalogger']=['ip'=>$this->getServerBridge()->getHost(),'key'=>$this->getServerBridge()->getConfigParam(['headers','X-GreenBean-Key'])];
        if(!$rs['defaultValues']) {
            syslog(LOG_ERR, 'Account is missing default data');
            $rs['defaultValues']=$this->gbHelper->getDefaultValues();
        }
        if(!$rs['virtualLans']) {
            syslog(LOG_ERR, 'Account is missing virtual LAN');
            $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        }
        $this->twig('dashboard/greenbean/settings.php', $rs);
        $this->setAssets();
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'handlebars', '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js', ['local'=>false]],
            ['css', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'settings', 'js/settings.js'],
            ['javascript', 'editableAutocomplete', 'js/jquery.editableAutocomplete.js'],
            ['javascript', 'upload', 'plugin/upload/upload.js'],
            ['css', 'upload', 'plugin/upload/upload.css'],
        ]);
    }
}