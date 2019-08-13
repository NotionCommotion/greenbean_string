<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
use Doctrine\ORM\EntityManager;
use Greenbean\Concrete5\GreenbeanDataIntegrator\Entity\SandboxPage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Error\ErrorList\ErrorList;

class Sandbox extends GreenbeanDashboardPageController
{
    public function view($id=null)
    {
        $errors = new ErrorList;
        syslog(LOG_ERR, json_encode(get_class_methods($errors)));
        if($id) {
            $em = $this->app->make(EntityManager::class);
            $repo = $em->getRepository(SandboxPage::class);
            if($page=$repo->find($id)) {
                $this->addAssets([['highcharts'],['dynamic_update']]);
                $rs=array_merge($page->asArray(), /*$this->gbHelper->getResourceFiles($page), */ ['menu'=>$this->getMenu('/dashboard/greenbean/sandbox'), 'displayUnit'=>\Package::getByHandle(self::PKGHANDLE)->getFileConfig()->get('settings.displayUnit')]);
                $this->twig('dashboard/greenbean/sandbox/detail.php', $rs );
            }
            else {
                $errors = new ErrorList;
                $errors->add("Page $id does not exist");
                $this->redirect('/dashboard/sandbox');
            }
        }
        else {
            $this->addAssets();
            $em = $this->app->make(EntityManager::class);
            $repo = $em->getRepository(SandboxPage::class);
            $pages = $repo->createQueryBuilder('s')->select('s.id','s.name')->getQuery()->execute();
            $this->twig('dashboard/greenbean/sandbox/index.php', ['pages'=>$pages, 'menu'=>$this->getMenu('/dashboard/greenbean/sandbox')]);
        }
    }

    public function create()
    {
        $em = $this->app->make(EntityManager::class);
        $page=SandboxPage::create($this->post('name'));
        $em->persist($page);
        $em->flush();
        return new JsonResponse($page, 200);
    }

    public function delete($id)
    {
        $em = $this->app->make(EntityManager::class);
        $repo = $em->getRepository(SandboxPage::class);
        if($page=$repo->find($id)) {
            $em->remove($page);
            $em->flush();
            return new JsonResponse(null, 204);
        }
        else {
            $errors = new ErrorList;
            $errors->add("Page $id does not exist");
            return $errors->createResponse(400);
        }
    }

    public function edit($page)
    {
        syslog(LOG_ERR, 'xxxxxxxxxxxx');
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