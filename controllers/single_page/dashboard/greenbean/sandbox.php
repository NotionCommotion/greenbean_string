<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
use Doctrine\ORM\EntityManager;
use Greenbean\Concrete5\GreenbeanDataIntegrator\Entity\SandboxPage;
use Concrete\Core\Error\ErrorList\ErrorList;

class Sandbox extends GreenbeanDashboardPageController
{
    public function view($id=null)
    {
        $errors = new ErrorList;
        if($id) {
            $em = $this->app->make(EntityManager::class);
            $repo = $em->getRepository(SandboxPage::class);
            if($page=$repo->find($id)) {
                $this->addAssets([['highcharts'],['dynamic_update']]);
                $rs=array_merge($page->asArray(), /*$this->gbHelper->getResourceFiles($page), */ ['menu'=>$this->getMenu('/dashboard/greenbean/sandbox'), 'displayUnit'=>\Package::getByHandle(self::PKGHANDLE)->getFileConfig()->get('settings.displayUnit')]);
                $this->twig('dashboard/greenbean/sandbox_detail.php', $rs );
            }
            else {
                $errors = new ErrorList;
                $errors->add("Page $id does not exist");
                $this->redirect('/dashboard/sandbox');
            }
        }
        else {
            $this->addAssets([['javascript', 'sandbox']]);
            $em = $this->app->make(EntityManager::class);
            $repo = $em->getRepository(SandboxPage::class);
            $pages = $repo->createQueryBuilder('s')->select('s.id','s.name')->getQuery()->execute();
            $this->twig('dashboard/greenbean/sandbox.php', ['pages'=>$pages, 'menu'=>$this->getMenu('/dashboard/greenbean/sandbox')]);
        }
    }

    //Display the edit page
    public function edit($id=null)
    {
        $em = $this->app->make(EntityManager::class);
        $repo = $em->getRepository(SandboxPage::class);
        if($page=$repo->find($id)) {
            $rs=$this->getServerBridge()->getPageContent([
                'pointList'=>['/points'],
                'chartList'=>['/charts'],
            ]);
            $rs['menu']=$this->getMenu('/dashboard/greenbean/sandbox');
            if(empty($rs['errors'])) {
                $rs['page']=$id;
                $rs['html']=$page->getHtml();
                $rs['js']=[];
                $rs['css']=[];
                $this->addAssets([['javascript', 'tinymce'], ['sandbox_edit']]);
                $this->twig('dashboard/greenbean/sandbox_edit.php', $rs);
            }
            else {
                $this->twig('dashboard/greenbean/error.php', $rs);
            }
        }
        else {
            $errors = new ErrorList;
            $errors->add("Page $id does not exist");
            $this->redirect('/dashboard/sandbox');
            /*
            $rs['menu']=$this->getMenu('/dashboard/greenbean/sandbox');
            $this->twig('dashboard/greenbean/error.php', $rs);
            */
        }
    }
}