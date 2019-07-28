<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Sandbox extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $rs=[
            'page'=>$args['page'],
            'html'=>$this->base->getHtml($args['page']),
            'displayUnit'=>$this->settings['config']['displayUnit'],
            'menu_main'=>$this->base->getMenu('/sandbox')
        ];
        $rs=array_merge($rs, $rs['html']?$this->base->getResourceFiles($args['page']):['js'=>[],'css'=>[]]);
        $this->setAssets();
        $this->twig('dashboard/greenbean/sandbox.php', $rs);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'highcharts', '//code.highcharts.com/highcharts.js', ['local'=>false]],
            ['javascript', 'highcharts-more', '//code.highcharts.com/highcharts-more.js', ['local'=>false]],
            ['javascript', 'solid-gauge', '//code.highcharts.com/modules/solid-gauge.js', ['local'=>false]],
            ['javascript', 'dynamic_update', '//cdn.greenbeantech.net/libraries/greenbean-public/1.0/dynamic_update.js', ['local'=>false]],
            ['css', 'dynamic_update', '//cdn.greenbeantech.net/libraries/greenbean-public/1.0/dynamic_update.css', ['local'=>false]],
        ]);
    }
}