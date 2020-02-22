<?php
use dFramework\core\Controller;
use dFramework\core\utilities\Auth;

class AccountController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index() {

        $a = new Envms\FluentPDO\Query();
    }

    public function login()
    {
        $auth = (new Auth)->setLoginParams([
            'table' => 'default.membres',
            'fields' => ['login', 'mdp'],
            'distinct_fields' => true,
        ]);
        $auth->checkin();

        $datas = [];
        $this->loadLibrary('Form', null, $datas['form']);
        
        if($this->request->is('post')) {
            if (false === $auth->login($this->request->data)) {
                $datas['error'] = $auth->errMsg;
                $datas['form']->error($auth->errors);
            }
            else {
                redirect();
            }
        }
        $this->view('/login', $datas)->render();
    }

    public function logout()
    {
        Auth::instance()->logout();
        redirect('account/login');
    }

    public function register()
    {
        $auth = (new Auth)->setRegisterParams([
            'table' => 'default.membres',
            'fields' => ['login', 'mdp'],
            'required' => ['login', 'mdp'],
            'save'  => ['login', 'mdp']
        ]);
        $auth->checkin();

        $datas = [];
        $this->loadLibrary('Form', null, $datas['form']);
        
        if($this->request->is('post')) {
            if (false === $auth->login($this->request->data)) {
                $datas['error'] = $auth->errMsg;
                $datas['form']->error($auth->errors);
            }
            else {
                redirect();
            }
        }
        $this->view('/login', $datas)->render();
    }
}