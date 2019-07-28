<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Manual extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $this->setAssets();
        $this->twig('dashboard/greenbean/manual.php', ['foo'=>123]);
    }
}