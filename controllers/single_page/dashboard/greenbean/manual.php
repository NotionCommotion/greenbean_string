<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Manual extends GreenbeanDashboardPageController
{
    public function view()
    {
        $this->addAssets();
        $this->twig('dashboard/greenbean/manual.php');
    }
}