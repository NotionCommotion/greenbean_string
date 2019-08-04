<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Chart extends GreenbeanDashboardPageController
{
    public function view()
    {
        $rs=$this->getServerBridge()->getPageContent([
            'charts'=>['/charts', array_merge($this->getParameters(),['verbose'=>true])],
            'aggrTypes'=>'/points/aggregate/types',
            'timeUnit'=>'/units/time',
            'chartTypes'=>'/charts/themes',
            'defaultValues'=>'/account',
        ]);
        //$rs['chartTypes']=$this->gbHelper->sortChartTypes($rs['chartTypes']);
        if(!$rs['defaultValues']) $rs['defaultValues']=$this->gbHelper->getDefaultValues();
        $this->addAssets([
            ['javascript', 'handlebars'],
            //['bootstrap-editable'],
            ['javascript', 'bootstrap-editable'],
            ['javascript', 'table-dragger'],
            ['javascript', 'charts'],
            ['javascript', 'editableAutocomplete'],
            ['sortfixedtable']
        ]);
        $this->twig('dashboard/greenbean/chart.php', $rs);
    }
}