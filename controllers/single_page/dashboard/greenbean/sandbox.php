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
        $request=$this->getRequest();
        if(!$request->isMethod('delete')) {
            $errors = new ErrorList;
            $errors->add("Invalid request");
            return $errors->createResponse(400);
        }
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
                //syslog(LOG_ERR, json_encode($page->asArray()));
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

    public function save($id)
    {
        //What is the correct way to do this?
        $request=$this->getRequest();
        if(!$request->isMethod('put')) {
            $errors = new ErrorList;
            $errors->add("Invalid request");
            return $errors->createResponse(400);
        }
        $em = $this->app->make(EntityManager::class);
        $repo = $em->getRepository(SandboxPage::class);
        if($page=$repo->find($id)) {
            $page->setHtml(urldecode($request->getContent()));
            $em->persist($page);
            $em->flush();
            return new JsonResponse($page, 200);
        }
        else {
            $errors = new ErrorList;
            $errors->add("Page $id does not exist");
            return $errors->createResponse(400);
        }
    }
}