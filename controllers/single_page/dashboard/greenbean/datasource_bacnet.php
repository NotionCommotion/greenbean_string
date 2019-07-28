<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Bacnetdatasource extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $this->setAssets();
        $this->twig('dashboard/greenbean/bacnet_datasource.php', ['foo'=>123]);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'jquery.initialize', '//cdn.jsdelivr.net/npm/jquery.initialize@1.3.0/jquery.initialize.min.js', ['local'=>false]],
            ['javascript', 'jstree', '//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.6/jstree.min.js', ['local'=>false]],
            ['css', 'jstree.css', '//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.6/themes/default/style.min.css', ['local'=>false]],
            ['css', 'bootstrap-editable.css', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'source_bacnet_gatewa.js', '/lib/gb/js/source_bacnet_gatewa.js'],
            ['javascript', 'editableAutocomplete', '/lib/gb/js/jquery.editableAutocomplete.js'],
            ['javascript', 'toolTip', '/lib/plugins/toolTip/jquery.toolTip.js'],
            ['css', 'toolTip.css', '/lib/plugins/toolTip/toolTip.css'],
        ]);
    }
}