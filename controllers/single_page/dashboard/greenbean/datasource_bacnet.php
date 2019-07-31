<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class DatasourceBacnet extends GreenbeanDashboardPageController
{
    public function view($id)
    {
        //Currently only allows BACnet sources
        $rs=$this->getServerBridge()->getPageContent([
            'source'=>"/sources/$id",
            'virtualLans'=>'/tags/lans'
        ]);
        if(!$rs['virtualLans']) $rs['virtualLans']=['virtualLans'=>[], 'virtualLanId'=>null];
        $this->setAssets();
        $this->twig('dashboard/greenbean/bacnet_datasource.php', $rs);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'jquery.initialize', '//cdn.jsdelivr.net/npm/jquery.initialize@1.3.0/jquery.initialize.min.js', ['local'=>false]],
            ['javascript', 'jstree', '//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.6/jstree.min.js', ['local'=>false]],
            ['css', 'jstree', '//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.6/themes/default/style.min.css', ['local'=>false]],
            ['css', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'source_bacnet_gateway', 'js/source_bacnet_gateway.js'],
            ['javascript', 'editableAutocomplete', 'js/jquery.editableAutocomplete.js'],
            ['javascript', 'toolTip', 'plugin/toolTip/jquery.toolTip.js'],
            ['css', 'toolTip', 'plugin/toolTip/toolTip.css'],
        ]);
    }
}