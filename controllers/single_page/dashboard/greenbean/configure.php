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
        $fc = Package::getByHandle(self::PKGHANDLE)->getFileConfig();
        $data=array_merge(
            ['displayUnit'=>true, 'host'=>'api.greenbeantech.net', 'api'=>null, 'action'=>$this->action('submit'), 'errors'=>$errors],
            $fc->get('server'),
            $fc->get('settings')
        );
        $data['api_sample']=$data['api']??'12345678-abcd-1234-abcd-123412341234';
        //$this->addAssets([['javascript', 'configure']]);
        $this->twig('dashboard/greenbean/configure.php', $data);
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