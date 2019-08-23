<?php
namespace Concrete\Package\GreenbeanString\Controller\SinglePage\Dashboard;
class Greenbean extends GreenbeanDashboardPageController
{
    public function view()
    {
        $this->addAssets();
        $this->twig('dashboard/greenbean.php');
    }
}