<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Helpdesk extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $params=$request->getQueryParams();
        $rs=$this->serverBridge->getPageContent([
            'tickets'=>['/helpdesk', $params],
            'message_types'=>'/helpdesk/topics',
        ]);
        //print_r($rs);exit;
        if(!empty($params['statusId'])) $rs['statusId']=$params['statusId'];
        $rs['menu_main']=$this->base->getMenu('/helpdesk');
        return $this->view->render($response, 'helpdesk.html',$rs);
       $this->setAssets();
        $this->twig('dashboard/greenbean/helpdesk.php', ['foo'=>123]);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'handlebars', '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js', ['local'=>false]],
            ['css', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', ['local'=>false]],
            ['javascript', 'bootstrap-editable', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', ['local'=>false]],
            ['javascript', 'helpdesk', 'js/helpdesk.js'],
            ['javascript', 'editableAutocomplete', 'js/jquery.editableAutocomplete.js'],
            ['javascript', 'sortfixedtable', 'plugin/sortfixedtable/jquery.sortfixedtable.js'],
            ['css', 'sortfixedtable', 'plugin/sortfixedtable/sortfixedtable.css'],
        ]);
    }
}