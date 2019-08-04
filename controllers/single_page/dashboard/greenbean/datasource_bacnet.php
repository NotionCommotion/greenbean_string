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
        $this->addAssets([
            ['javascript', 'jquery.initialize'],
            ['jstree'],
            //['bootstrap-editable'],
            ['javascript', 'bootstrap-editable'],
            ['javascript', 'source_bacnet_gateway'],
            ['javascript', 'editableAutocomplete'],
            ['toolTip']
        ]);
        $this->twig('dashboard/greenbean/bacnet_datasource.php', $rs);
    }

}