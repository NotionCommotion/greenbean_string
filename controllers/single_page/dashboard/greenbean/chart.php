<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Chart extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $rs=$this->serverBridge->getPageContent([
            'charts'=>['/charts', array_merge($request->getQueryParams(),['verbose'=>true])],
            'aggrTypes'=>'/points/aggregate/types',
            'timeUnit'=>'/units/time',
            'chartTypes'=>'/charts/themes',
            'defaultValues'=>'/account',
        ]);
        //print_r($rs['chartTypes']);exit;
        //$rs['chartTypes']=$this->base->sortChartTypes($rs['chartTypes']);
        if(!$rs['defaultValues']) $rs['defaultValues']=$this->base->getDefaultValues();
        $rs['menu_main']=$this->base->getMenu('/charts');
        return $this->view->render($response, 'charts.html', $rs);
        $this->setAssets();
        $this->twig('dashboard/greenbean/chart.php', ['foo'=>123]);
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