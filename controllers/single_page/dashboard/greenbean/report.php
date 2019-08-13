<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Report extends GreenbeanDashboardPageController
{
    public function view($id=null)
    {
        if($id) {
            $rs=$this->getServerBridge()->getPageContent(['report'=>"/reports/$id"]);
            $rs=empty($rs['report'])?$this->gbHelper->getDefaultReportValues():$rs['report'];
        }
        else {
            $rs=[];
        }
        $rs['menu']=$this->getMenu('/dashboard/greenbean/report');
        $this->addAssets([
            //['bootstrap-editable'],
            ['javascript', 'bootstrap-editable'],
            ['javascript', 'bootstrap-datepicker'],
            ['highcharts'],
            ['javascript', 'reports'],
            //['javascript', 'moment'],
        ]);
        $this->twig('dashboard/greenbean/report.php', $rs);
    }
}