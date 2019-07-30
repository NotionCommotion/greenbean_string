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
        $this->twig('dashboard/greenbean/chart.php', $rs);
        $this->setAssets();
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'handlebars', '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js', ['local'=>false]],
            ['css', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'table-dragger', '//gitcdn.link/repo/sindu12jun/table-dragger/dev/dist/table-dragger.js', ['local'=>false]],
            ['javascript', 'charts', 'js/charts.js'],
            ['javascript', 'editableAutocomplete', 'js/jquery.editableAutocomplete.js'],
            ['javascript', 'sortfixedtable', 'plugin/sortfixedtable/jquery.sortfixedtable.js'],
            ['css', 'sortfixedtable', 'plugin/sortfixedtable/sortfixedtable.css'],
        ]);
    }
}