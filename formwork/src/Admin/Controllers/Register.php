<?php

namespace Formwork\Admin\Controllers;

use Formwork\Admin\Admin;
use Formwork\Admin\Security\CSRFToken;
use Formwork\Admin\Security\Password;
use Formwork\Parsers\YAML;
use Formwork\Utils\HTTPRequest;
use Formwork\Utils\Session;

class Register extends AbstractController
{
    /**
     * Register@register action
     */
    public function register(): void
    {
        CSRFToken::generate();

        switch (HTTPRequest::method()) {
            case 'GET':
                $this->view('register.register', [
                    'title' => $this->admin()->translate('admin.register.register')
                ]);

                break;

            case 'POST':
                $data = HTTPRequest::postData();

                if (!$data->hasMultiple(['username', 'fullname', 'password', 'language', 'email'])) {
                    $this->admin()->notify($this->admin()->translate('admin.users.user.cannot-create.var-missing'), 'error');
                    $this->admin()->redirectToPanel();
                }

                $userData = [
                    'username' => $data->get('username'),
                    'fullname' => $data->get('fullname'),
                    'hash'     => Password::hash($data->get('password')),
                    'email'    => $data->get('email'),
                    'language' => $data->get('language'),
                    'role'     => 'admin'
                ];

                YAML::encodeToFile($userData, Admin::ACCOUNTS_PATH . $data->get('username') . '.yml');

                Session::set('FORMWORK_USERNAME', $data->get('username'));
                $time = $this->admin()->log('access')->log($data->get('username'));
                $this->admin()->registry('lastAccess')->set($data->get('username'), $time);

                $this->admin()->redirectToPanel();

                break;
        }
    }
}
