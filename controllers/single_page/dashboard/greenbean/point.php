<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Point extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $rs=$this->serverBridge->getPageContent([
            'aggrTypes'=>'/points/aggregate/types',
            'timeUnit'=>'/units/time',
            'pointSources'=>['/sources', ['fields'=>['protocol']]],    //add extra field "property" as well as default id and name
            'virtualLans'=>'/tags/lans',
            'defaultValues'=>'/account',
            'points'=>['/points', array_merge($request->getQueryParams(),['verbose'=>true])],
        ]);
        //if(!$rs['defaultValues']) $rs['defaultValues']=$this->base->getDefaultValues();
        //$rs['defaultValues']['virtualLanId']=$rs['virtualLans']['virtualLanId']??null;
        $rs['defaultValues']['virtualLanId']=$rs['defaultValues']['base']['virtualLanId'];
        //$rs['virtualLans']=$rs['virtualLans']['virtualLans']??[];
        //$rs['menu_main']=$this->base->getMenu('/points');
        $this->setAssets();
        $this->twig('dashboard/greenbean/points.php', $rs);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'handlebars', '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js', ['local'=>false]],
            ['css', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'points', 'js/points.js'],
            ['javascript', 'editableAutocomplete', 'js/jquery.editableAutocomplete.js'],
            ['javascript', 'sortfixedtable', 'plugin/sortfixedtable/jquery.sortfixedtable.js'],
            ['css', 'sortfixedtable', 'plugin/sortfixedtable/sortfixedtable.css'],
        ]);
    }
}