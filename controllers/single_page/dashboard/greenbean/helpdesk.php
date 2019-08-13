<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Helpdesk extends GreenbeanDashboardPageController
{
    public function view()
    {
        $params=$this->getParameters();
        $rs=$this->getServerBridge()->getPageContent([
            'tickets'=>['/helpdesk', $params],
            'message_types'=>'/helpdesk/topics',
        ]);
        $rs['menu']=$this->getMenu('/dashboard/greenbean/helpdesk');
        if(empty($rs['errors'])) {
            if(!empty($params['statusId'])) $rs['statusId']=$params['statusId'];
            $this->addAssets([
                ['javascript', 'handlebars'],
                //['bootstrap-editable'],
                ['javascript', 'bootstrap-editable'],
                ['javascript', 'helpdesk'],
                ['javascript', 'editableAutocomplete'],
                ['sortfixedtable']
            ]);
            $this->twig('dashboard/greenbean/helpdesk.php', $rs);
        }
        else {
            $this->twig('dashboard/greenbean/error.php', $rs);
        }
    }

}