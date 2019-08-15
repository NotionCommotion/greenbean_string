<?php
namespace Greenbean\Concrete5\GreenbeanDataIntegrator\Controller;
use Greenbean\Concrete5\GreenbeanDataIntegrator\GbHelper;
use Doctrine\ORM\EntityManager;
use Greenbean\Concrete5\GreenbeanDataIntegrator\Entity\SandboxPage;
use Concrete\Core\Support\Facade\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Http\Request;

class SandboxRouteController
{

    //public function __construct(User $currentUser) {}

    public function create()
    {
        $request = \Concrete\Core\Http\Request::createFromGlobals();
        //getContent() returns raw, post() just given method.  Consider using getContentType() and getRequestFormat()
        $em = Application::getFacadeApplication()->make(EntityManager::class);
        $page=SandboxPage::create($request->request()['name']);
        $em->persist($page);
        $em->flush();
        return new JsonResponse($page, 200);
    }

    public function delete()
    {
        //How are route parameters passed?  https://stackoverflow.com/questions/57510757/how-do-i-pass-router-parameters-to-a-concrete5-controller
        $em = Application::getFacadeApplication()->make(EntityManager::class);
        $repo = $em->getRepository(SandboxPage::class);
        if($page=$repo->find($this->getId())) {
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

    public function save()
    {
        $em = Application::getFacadeApplication()->make(EntityManager::class);
        $repo = $em->getRepository(SandboxPage::class);
        $request = \Concrete\Core\Http\Request::createFromGlobals();
        if($page=$repo->find($this->getId($request))) {
            $body = $this->parseBody($request);
            $page->setHtml($body['html']);
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

    private function getId(?Request $request=null)
    {
        //How should this be done?
        if(!$request) $request = \Concrete\Core\Http\Request::createFromGlobals();
        $parts = explode('/', $request->getPath());
        return $parts[count($parts)-1];
    }

    private function parseBody(Request $request)
    {
        //How should this be done?
        $contentType=$request->getContentType();
        switch($contentType) {
            case 'json': return json_decode($request->getContent(), true);
            case 'form': parse_str($request->getContent(), $arr); return $arr;
            default: throw new \Exception("Unsupported content type: $contentType");
        }
    }

    /**
    * Used to replace single pages with router so logic is in one location.
    * Documentation at https://documentation.concrete5.org/developers/routing/views is incorrect.
    * Not yet working.
    *
    * @param mixed $template
    * @param mixed $variables
    * @param bool $render
    * @return string
    */
    protected function twig(string $template, array $variables=[], bool $render=false):string
    {
        $msg='RouteController::twig() not working';
        syslog(LOG_ERR, $msg);
        exit($msg);
        // Concrete\Core\Support\Facade\Events;
        // $factory = $app->make(\Concrete\Core\Http\ResponseFactoryInterface::class);
        if(!$this->twig) {
            $this->twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__.'/../../single_pages'), [
                //'cache' => 'path/to/cache'    // Add when complete.  See auto_reload option
                'debug'=>true,
                //'strict_variables'=>true
            ]);
        }
        $variables['gb_root_base'] = \Package::getByHandle(self::PKGHANDLE)->getRelativePath();
        $variables['gb_img_base'] = $variables['gb_root_base'] . '/images';
        syslog(LOG_INFO, '$variables: '.json_encode($variables));
        $html = $this->twig->render($template, $variables);
        //syslog(LOG_INFO, '$html: '.json_encode($html));
        if($render) {
            $this->set('html', $html);
            $this->render('/twig');
        }
        return $html;
    }
}