<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
use Doctrine\ORM\EntityManager;
use Greenbean\Concrete5\GreenbeanDataIntegrator\Entity\SandboxPage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Error\ErrorList\ErrorList;

class Edit extends GreenbeanDashboardPageController
{
    public function view($id)
    {
        syslog(LOG_ERR, 'xxxxxxxxxxxx');
        $rs=$this->getServerBridge()->getPageContent([
            'pointList'=>['/points'],
            'chartList'=>['/charts'],
        ]);
        if(empty($rs['errors'])) {
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