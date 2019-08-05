<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Source extends GreenbeanDashboardPageController
{
    public function view($id=null)
    {
        return $id?$this->viewItem($id):$this->viewList();
    }

    private function viewList()
    {
        $rs=$this->getServerBridge()->getPageContent([
            'sources'=>['/sources', array_merge($this->getParameters(),['verbose'=>true])],
            'virtualLans'=>'/tags/lans',
            'defaultValues'=>'/account',
        ]);
        syslog(LOG_INFO, json_encode($rs));
        //Remove next two lines after account settings is fixed on server.
        $rs['defaultValues']['gateway']=$rs['defaultValues']['client'];
        unset($rs['defaultValues']['client']);
        $rs['sources']=$this->gbHelper->sortSources($rs['sources']);
        if(!$rs['defaultValues']) $rs['defaultValues']=$this->gbHelper->getDefaultValues();
        if(!$rs['virtualLans']) $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        $this->addAssets([['javascript', 'sources'],['sortfixedtable']]);
        $this->twig('dashboard/greenbean/source.php', $rs);
    }

    private function viewItem($id)
    {
        //Currently only allows BACnet sources
        $rs=$this->getServerBridge()->getPageContent([
            'source'=>"/sources/$id",
            'virtualLans'=>'/tags/lans'
        ]);
        if(!$rs['virtualLans']) $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        $this->addAssets([
            ['javascript', 'jquery.initialize'],
            ['jstree'],
            //['bootstrap-editable'],
            ['javascript', 'bootstrap-editable'],
            ['javascript', 'source_bacnet'],
            ['javascript', 'editableAutocomplete'],
            ['toolTip']
        ]);
        $this->twig('dashboard/greenbean/source_bacnet.php', $rs);
    }
}