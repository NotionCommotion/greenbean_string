<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Manual extends GreenbeanDashboardPageController
{
    public function view($id=1)
    {
        $rs=$this->getServerBridge()->callApi('get', 'manual/'.$id);
        $rs['menu']=$this->getMenu('/dashboard/greenbean/manual');
        if(empty($rs['errors'])) {
            $this->addAssets([['manual']]);

            //Temp solution
            $rs['content']=str_replace('/lib/manual/', 'http://cdn.greenbeantech.net/libraries/greenbean-public/1.0/manual/', $rs['content']);
            $this->twig('dashboard/greenbean/manual.php', $rs);
        }
        else {
            $this->twig('dashboard/greenbean/error.php', $rs);
        }
    }
}