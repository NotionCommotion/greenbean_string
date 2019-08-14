<?php
namespace Greenbean\Concrete5\GreenbeanDataIntegrator\Controller;
use Greenbean\Concrete5\GreenbeanDataIntegrator\GbHelper;
use Doctrine\ORM\EntityManager;
use Greenbean\Concrete5\GreenbeanDataIntegrator\Entity\SandboxPage;
class SandboxRouteController
{
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
        $variables['base_url'] = \Package::getByHandle(self::PKGHANDLE)->getRelativePath();
        $variables['img_url'] = $variables['base_url'] . '/images';
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