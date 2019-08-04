<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage;
use Concrete\Core\Page\Controller\PageController;
class DataReporter extends PageController
{
    public function view()
    {
        $this->addAssets();
        $this->twig('datareporter.php', ['foo'=>123]);
    }
}