<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard;
class Greenbean extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $this->setAssets();
        $this->twig('dashboard/greenbean.php', ['foo'=>123]);
    }
}