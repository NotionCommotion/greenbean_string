<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage;
class DataReporter extends Greenbeanpagecontroller
{
    public function view()
    {
        $this->setAssets();
        $this->twig('datareporter.php', ['foo'=>123]);
    }
}