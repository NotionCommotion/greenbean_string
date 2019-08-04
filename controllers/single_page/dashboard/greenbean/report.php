<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Report extends GreenbeanDashboardPageController
{
    public function view()
    {
        $this->addAssets([
            //['bootstrap-editable'],
            ['javascript', 'bootstrap-editable'],
            ['javascript', 'bootstrap-datepicker'],
            ['highcharts'],
            ['javascript', 'reports'],
            //['javascript', 'moment'],
        ]);
        $this->twig('dashboard/greenbean/report.php');
    }
}