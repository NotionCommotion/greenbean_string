<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class SandboxEdit extends Greenbeandashboardpagecontroller
{
    public function view()
    {
        $rs=$this->serverBridge->getPageContent([
            'pointList'=>['/points'],
            'chartList'=>['/charts'],
        ]);
        $rs['page']=$args['page'];
        $rs['html']=$this->base->getHtml($args['page']);
        $rs['js']=[];
        $rs['css']=[];
        //$rs['menu_main']=$this->base->getMenu('/sandbox');
        $this->setAssets();
        $this->twig('dashboard/greenbean/sandboxEdit.php', $rs);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'tinymce', '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.7.9/tinymce.min.js', ['local'=>false]],
            ['javascript', 'sandbox_edit', 'js/sandbox_edit.js'],
            ['css', 'sandbox_edit', 'css/sandbox_edit.css'],
        ]);
    }
}
