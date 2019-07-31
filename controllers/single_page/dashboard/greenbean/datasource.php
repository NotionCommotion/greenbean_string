<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Datasource extends GreenbeanDashboardPageController
{
    public function view()
    {
        $rs=$this->getServerBridge()->getPageContent([
            'sources'=>['/sources', array_merge($this->getParameters(),['verbose'=>true])],
            'virtualLans'=>'/tags/lans',
            'defaultValues'=>'/account',
        ]);
        //Remove next two lines after account settings is fixed on server.
        $rs['defaultValues']['gateway']=$rs['defaultValues']['client'];
        unset($rs['defaultValues']['client']);
        $rs['sources']=$this->gbHelper->sortSources($rs['sources']);
        if(!$rs['defaultValues']) $rs['defaultValues']=$this->gbHelper->getDefaultValues();
        if(!$rs['virtualLans']) $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        $this->setAssets();
        $this->twig('dashboard/greenbean/datasource.php', $rs);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'sources', 'js/sources.js'],
            ['javascript', 'sortfixedtable', 'plugin/sortfixedtable/jquery.sortfixedtable.js'],
            ['css', 'sortfixedtable', 'plugin/sortfixedtable/sortfixedtable.css'],
        ]);
    }
}