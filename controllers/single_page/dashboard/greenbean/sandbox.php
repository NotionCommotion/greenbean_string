<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Sandbox extends GreenbeanDashboardPageController
{
    public function view($page)
    {
        $rs=[
            'page'=>$page,
            'html'=>$this->gbHelper->getHtml($page),
            'displayUnit'=>$this->settings['config']['displayUnit'],
            'menu_main'=>$this->gbHelper->getMenu('/sandbox')
        ];
        $rs=array_merge($rs, $rs['html']?$this->gbHelper->getResourceFiles($page):['js'=>[],'css'=>[]]);
        $this->addAssets([['highcharts'],['dynamic_update']]);
        $this->twig('dashboard/greenbean/sandbox.php', $rs);
    }
}