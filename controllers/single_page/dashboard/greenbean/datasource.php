<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Datasource extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $rs=$this->serverBridge->getPageContent([
            'sources'=>['/sources', array_merge($request->getQueryParams(),['verbose'=>true])],
            'virtualLans'=>'/tags/lans',
            'defaultValues'=>'/account',
        ]);
        //print_r($rs);exit;
        //Remove next two lines after account settings is fixed on server.
        $rs['defaultValues']['gateway']=$rs['defaultValues']['client'];
        unset($rs['defaultValues']['client']);
        $rs['sources']=$this->base->sortSources($rs['sources']);
        if(!$rs['defaultValues']) $rs['defaultValues']=$this->base->getDefaultValues();
        if(!$rs['virtualLans']) $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        $rs['menu_main']=$this->base->getMenu('/sources');
        return $this->view->render($response, 'sources.html', $rs);
        $this->setAssets();
        $this->twig('dashboard/greenbean/datasource.php', ['foo'=>123]);
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