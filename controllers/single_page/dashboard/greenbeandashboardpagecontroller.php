<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard;
use Concrete\Core\Page\Controller\DashboardPageController;
use Greenbean\Concrete5\Datalogger\CommonTrait;

abstract class Greenbeandashboardpagecontroller extends DashboardPageController
{
    use CommonTrait;

    /**
    * Override in specific controller to add additional assets.
    * Follows standard concrete5 approach except package name must not be included.
    *
    * @param mixed $assets
    */
    protected function getAssets(array $assets=[])
    {
        //jquery, jquery-ui, bootstrap, and others are included by default.  See https://documentation.concrete5.org/developers/appendix/asset-list
        return array_merge($assets, [
            ['javascript', 'url-search-params', '//cdnjs.cloudflare.com/ajax/libs/url-search-params/0.10.0/url-search-params.js', ['local'=>false]],
            ['javascript', 'throbber', '//cdn.greenbeantech.net/libraries/throbber.js-master/throbber.js', ['local'=>false]],
            ['javascript', 'blockUI', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', ['local'=>false]],
            ['javascript', 'jquery.validate', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js', ['local'=>false]],
            ['javascript', 'additional-methods', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/additional-methods.js', ['local'=>false]],
            ['javascript', 'common.js', '/lib/gb/js/common.js'],
            ['javascript', 'printIt', '/lib/gb/js/printIt.js'],
            ['javascript', 'manual.js', '/lib/gb/js/manual.js'],
            ['javascript', 'my-validation-methods', '/lib/gb/js/my-validation-methods.js'],
            ['css', 'my.style.css', '/lib/gb/css/style.css'],
            ['css', 'manual.css', '/lib/gb/css/manual.css'],
        ]);
    }
}