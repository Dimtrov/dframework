<?php
use \dFramework\components\auth\Login;
use \dFramework\core\Controller;

class HomeController extends Controller
{

    public function index()
    {
        $this->loadLibrary('Captcha');

        $this->captcha->use(dF_Captcha::IMAGE_SECURIMAGE);
        $captcha = $this->captcha->get();

        echo '<img src="'.$captcha.'" >';
    }

    public function a()
    {
        $this->view('/test')->render();

    }

    /**
     * @throws ReflectionException
     * @throws \dFramework\core\exception\Exception
     */
    public function contact()
    {
		$this->useObject(self::REQUEST_OBJECT);

        if($this->request->is('post')) {
            $this->_submitContact($this->data->post());
        }
        $this->layout
            ->add('contact')
            ->launch();
    }


    private function _submitContact(array $datas)
    {
        if(!empty($datas['anythings'])) {
            exit('<error>');
        }
        $this->loadLibrary('Validator', 'v');
        $this->v->useInputField(true, $datas);

        if(true !== $this->v->inField('name', 'email', 'subject', 'content')) {
            exit('<error>Veuillez remplir tous les champs du formulaire');
        }
        if(true !== $this->v->length_between('name', 3, 20)) {
            exit('<error>Votre nom doit avoir entre 3 et 20 caracteres');
        }
        if(true !== $this->v->is_email('email')) {
            exit('<error>L\'adresse email que vous avez entrer est invalide');
        }
        if(true !== $this->v->length_between('subject', 5, 50)) {
            exit('<error>Le sujet de votre message doit avoir entre 5 et 50 caracteres');
        }
        if(true !== $this->v->min_length('content', 10)) {
            exit('<error>Votre message doit avoir au moins 10 caracteres');
        }

        if(empty($datas['service']) OR !in_array(strtolower($datas['service']), ['sales', 'technical', 'manager'])) {
            $datas['service'] = 'contact';
        }
        $datas['service'] = strtolower($datas['service']);

        $this->loadLibrary('Mail');
        $this->mail->use('default');

        try {

            $this->mail
                ->from($datas['service'].'@dimtrov.com', 'Dimtrov :: '.ucfirst($datas['service']))
                ->to('dimitri@dimtrov.local', 'Dimitric Sitchet Tomkeu')
                ->reply($datas['email'], $datas['name'])
                ->subject($datas['subject'])
                ->message($datas['content']);
            if($this->mail->send()) {
                exit('<ok>Message envoyé avec succès. <br>Nous vous repondrons dans les brefs delais');
            }
            else {
                exit('<error>Echec d\'envoi');
            }
        }
        catch (Exception $e) {
            exit('<error>Une erreur interne s\'est produite. Veuillez reessayer');
        }
    }
}