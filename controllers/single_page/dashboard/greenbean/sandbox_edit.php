<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class SandboxEdit extends GreenbeanDashboardPageController
{
    public function view($page)
    {
        $rs=$this->getServerBridge()->getPageContent([
            'pointList'=>['/points'],
            'chartList'=>['/charts'],
        ]);
        $rs['page']=$args['page'];
        $rs['html']=$this->gbHelper->getHtml($page);
        $rs['js']=[];
        $rs['css']=[];
        $this->twig('dashboard/greenbean/sandboxEdit.php', $rs);
        $this->setAssets();
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
