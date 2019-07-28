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

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'editableAutocomplete', '/lib/gb/js/jquery.editableAutocomplete.js'],
            ['javascript', 'my-validation-methods', '/lib/gb/js/my-validation-methods.js'],
            ['javascript', 'sortfixedtable', '/lib/plugins/sortfixedtable/jquery.sortfixedtable.js'],
            ['javascript', 'charts.js', '/lib/gb/js/charts.js'],
            ['javascript', 'blockUI', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'handlebars', '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js', ['local'=>false]],
            ['javascript', 'jquery.validate', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js', ['local'=>false]],
            ['javascript', 'additional-methods', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/additional-methods.js', ['local'=>false]],
            ['javascript', 'table-dragger', '//gitcdn.link/repo/sindu12jun/table-dragger/dev/dist/table-dragger.js', ['local'=>false]],
            ['css', 'sortfixedtable.css', '/lib/plugins/sortfixedtable/sortfixedtable.css'],
            ['css', 'bootstrap-editable.css', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
        ]);
    }
}