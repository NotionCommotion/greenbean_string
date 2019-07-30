<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard;
class Greenbean extends GreenbeanDashboardPageController
{
    public function view()
    {
        $this->setAssets();
        $this->twig('dashboard/greenbean.php');
    }
}