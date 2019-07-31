<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class ReportDetail extends GreenbeanDashboardPageController
{
    public function view($id)
    {
        $rs=$this->getServerBridge()->getPageContent(['report'=>"/reports/$id"]);
        $rs=empty($rs['report'])?$this->gbHelper->getDefaultReportValues():$rs['report'];
        $this->setAssets();
        $this->twig('dashboard/greenbean/report_detail.php', $rs);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['css', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.js', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//code.highcharts.com/highcharts.js', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//code.highcharts.com/highcharts-more.js', ['local'=>false]],
            ['javascript', 'reports', 'js/reports.js'],
            //['javascript', 'moment',  '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js',, ['local'=>false]],
        ]);
    }
}