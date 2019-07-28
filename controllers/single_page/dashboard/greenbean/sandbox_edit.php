<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\Greenbeandashboardpagecontroller;
class Sandbox extends Greenbeandashboardpagecontroller
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
        $rs['menu_main']=$this->base->getMenu('/preview');
        return $this->view->render($response, 'front_edit.html', $rs);
        $this->setAssets();
        $this->twig('dashboard/greenbean/sandbox.php', ['foo'=>123]);
    }

    protected function getAssets(array $assets=[])
    {
        //parent will add base assets required by all views
        return array_merge(parent::getAssets($assets), [
            ['javascript', 'tinymce', '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.7.9/tinymce.min.js', ['local'=>false]],
            ['javascript', 'preview_edit', 'js/preview_edit.js'],
            ['css', 'preview_edit', 'css/preview_edit.css'],
        ]);
    }
}
