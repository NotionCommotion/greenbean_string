<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Settings extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $rs=$this->serverBridge->getPageContent([
            'defaultValues'=>'/account',
            'virtualLans'=>'/tags/lans'
        ]);
        $rs['datalogger']=['ip'=>$this->serverBridge->getHost(),'key'=>$this->serverBridge->getConfigParam(['headers','X-GreenBean-Key'])];
        if(!$rs['defaultValues']) {
            syslog(LOG_ERR, 'Account is missing default data');
            $rs['defaultValues']=$this->base->getDefaultValues();
        }
        if(!$rs['virtualLans']) {
            syslog(LOG_ERR, 'Account is missing virtual LAN');
            $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        }
        $rs['menu_main']=$this->base->getMenu('/account');
        //print_r($rs);exit;
        return $this->view->render($response, 'account.html', $rs);
        $this->setAssets();
        $this->twig('dashboard/greenbean/settings.php', ['foo'=>123]);
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