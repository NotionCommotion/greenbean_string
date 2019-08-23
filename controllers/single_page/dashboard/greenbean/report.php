<?php
namespace Concrete\Package\GreenbeanString\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanString\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Report extends GreenbeanDashboardPageController
{
    public function view($id=null)
    {
        if(!is_null($id)) {
            if($this->is_digit($id) && $rs=$this->getServerBridge()->getPageContent(['report'=>"/reports/$id"])['report']??null) {
                $this->_view($rs);
            }
            else {
                $this->displayError(['Invalid page'], '/dashboard/greenbean/report', false);
            }
        }
        else {
            $this->_view($this->gbHelper->getDefaultReportValues());
        }
    }

    private function _view($rs)
    {
        $rs['menu']=$this->getMenu('/dashboard/greenbean/report');
        $this->addAssets([
            //['bootstrap-editable'],
            //['javascript', 'bootstrap-editable'],
            ['javascript', 'bootstrap-datepicker'],
            ['highcharts'],
            ['javascript', 'reports'],
            //['javascript', 'moment'],
        ]);
        $this->twig('dashboard/greenbean/report.php', $rs);
    }
}