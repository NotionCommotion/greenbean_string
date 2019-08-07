<?php
namespace Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\Dashboard\Greenbean;
use Concrete\Package\GreenbeanDataIntegrator\Controller\SinglePage\dashboard\GreenbeanDashboardPageController;
use Package;
class Configure extends GreenbeanDashboardPageController
{
    public function view()
    {
        $this->_view();
    }

    private function _view(array $errors=[])
    {
        syslog(LOG_INFO, 'errors: '.json_encode($errors));
        //$this->addAssets([['javascript', 'configure']]);
        $this->twig('dashboard/greenbean/configure.php', ['action'=>$this->action('submit'), 'errors'=>$errors]);
    }

    public function submit()
    {
        //What is the correct way to do this?  Use validation helper, not directly POST, etc.
        $errors=[];
        if($missing=array_diff_key(array_flip(['host','api','displayUnit']), array_filter($this->post()))) {
            $errors[]=implode(', ', array_keys($missing)).' must be provided';
        }
        else {
            if(!filter_var($this->post('host'), FILTER_VALIDATE_DOMAIN)) $errors[]='Host name is not valid';
            if(!preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $this->post('api'))) $errors[]='API key is not valid';
        }
        if(!$errors) {
            syslog(LOG_ERR, 'check server');
        }
        if($errors) {
            $this->_view($errors);
        }
        else {
            $fc = Package::getByHandle(self::PKGHANDLE)->getFileConfig();
            $fc->save('server.host', $this->post('host'));
            $fc->save('server.api', $this->post('api'));
            $fc->save('settings.displayUnit', (int)$this->post('displayUnit'));
            $this->redirect('/dashboard/greenbean');
        }
    }
}