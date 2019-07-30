<?php
/**
Or should this class be located in the package's controller directory?
If so, change path to __DIR__.'/../../single_pages' assuming namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage.
*/
namespace Greenbean\Concrete5\GreenbeanDataIntegrator;
trait CommonTrait
{
    private $twig;

    //Future.  Inject path to templates, etc

    protected function twig(string $template, array $variables=[], bool $render=true):string {
        if(!$this->twig) {
            $this->twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__.'/../single_pages'), [
                //'cache' => 'path/to/cache'    // Add when complete.  See auto_reload option
                'debug'=>true,
                //'strict_variables'=>true
            ]);
        }
        $html = $this->twig->render($template, $variables);
        if($render) {
            $this->set('html', $html);
            $this->render('/twig');
        }
        return $html;
    }

    public function on_start()
    {
        //Why bother doing this in on_start() instead of just the view?
        if($assets=$this->getAssets()) {
            $assetList = \Concrete\Core\Asset\AssetList::getInstance();
            foreach($assets as $asset) {
                $asset=array_merge($asset, count($asset)===3?[[],'greenbean_data_integrator']:['greenbean_data_integrator']);
                $assetList->register(...$asset);    //returns JavascriptAsset if javascript, etc
            }
        }
    }

    //See Greenbeandashboardpagecontroller
    protected function getAssets(array $assets=[])
    {
        return [];
    }

    //How should this be accompished?
    //Pass array to override with given assets or null to override and include no assets.
    protected function setAssets(?array $assets=[])
    {
        //$this->requireAsset('jquery/ui'); //v1.11.4
        if($assets) {
            foreach($assets as $asset) {
                $this->requireAsset($asset[0], $asset[1]);
            }
        }
        elseif($assets && $assets=$this->getAssets()) {
            foreach($assets as $asset) {
                $this->requireAsset($asset[0], $asset[1]);
            }
        }
    }
}