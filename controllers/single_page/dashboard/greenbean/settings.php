<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Settings extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $this->setAssets();
        $this->twig('dashboard/greenbean/settings.php', ['foo'=>123]);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'handlebars', '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js', ['local'=>false]],
            ['css', 'bootstrap-editable.css', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'settings.js', '/lib/gb/js/settings.js'],
            ['javascript', 'editableAutocomplete', '/lib/gb/js/jquery.editableAutocomplete.js'],
            ['javascript', 'upload.js', '/lib/plugins/upload/upload.js'],
            ['css', 'upload.css', '/lib/plugins/upload/upload.css'],
        ]);
    }
}