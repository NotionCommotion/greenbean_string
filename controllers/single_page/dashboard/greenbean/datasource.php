<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Datasource extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $this->setAssets();
        $this->twig('dashboard/greenbean/datasource.php', ['foo'=>123]);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'sources.js', '/lib/gb/js/sources.js'],
            ['javascript', 'sortfixedtable', '/lib/plugins/sortfixedtable/jquery.sortfixedtable.js'],
            ['css', 'sortfixedtable.css', '/lib/plugins/sortfixedtable/sortfixedtable.css'],
        ]);
    }
}