<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
class SandboxEdit extends GreenbeanDashboardPageController
{
    public function view($page=1)
    {
        $rs['menu']=$this->getMenu('/dashboard/greenbean/sandbox');
        if(empty($rs['errors'])) {
            $rs=$this->getServerBridge()->getPageContent([
                'pointList'=>['/points'],
                'chartList'=>['/charts'],
            ]);
            $rs['page']=$args['page'];
            $rs['html']=$this->gbHelper->getHtml($page);
            $rs['js']=[];
            $rs['css']=[];
            $this->addAssets([['javascript', 'tinymce'], ['sandbox_edit']]);
            $this->twig('dashboard/greenbean/sandboxEdit.php', $rs);
        }
        else {
            $this->twig('dashboard/greenbean/error.php', $rs);
        }
    }
}
