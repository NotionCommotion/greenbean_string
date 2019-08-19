<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class Point extends GreenbeanDashboardPageController
{
    public function view()
    {
        $rs=$this->getServerBridge()->getPageContent([
            'aggrTypes'=>'/points/aggregate/types',
            'timeUnit'=>'/units/time',
            'pointSources'=>['/sources', ['fields'=>['protocol']]],    //add extra field "property" as well as default id and name
            'virtualLans'=>'/tags/lans',
            'defaultValues'=>'/account',
            'points'=>['/points', array_merge($this->getParameters(), ['verbose'=>true])],
        ]);
        $rs['menu']=$this->getMenu('/dashboard/greenbean/point');
        if(empty($rs['errors'])) {
            $rs['defaultValues']['virtualLanId']=$rs['defaultValues']['base']['virtualLanId'];
            $this->addAssets([
                ['javascript', 'handlebars'],
                //['bootstrap-editable'],
                //['javascript', 'bootstrap-editable'],
                ['javascript', 'points'],
                ['javascript', 'editableAutocomplete'],
                ['sortfixedtable']
                ]
            );
            $this->twig('dashboard/greenbean/point.php', $rs);
        }
        else {
            $this->displayError($rs['errors'], '/dashboard/greenbean/point');
        }
    }
}