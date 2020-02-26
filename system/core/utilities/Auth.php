<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


/**
 * Auth
 *
 * Systeme d'authentification automatique des utilisateurs
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Form.html
 * @since       2.2
 * @file        /system/core/utilities/Auth.php
 */

namespace dFramework\core\utilities;

use dFramework\core\db\Query;
use dFramework\core\security\Session;

class Auth
{   
    /**
     * @var array les parametres d'authentification
     */
    private $login_params = [
        /**
         * La table dans laquelle on doit faire la recherche
         */
        'table'             => 'default.users',
        'fields'            => [
            /**
             * Le champ a utiliser comme login 
             *  (doit etre le meme dans la base de donnees et l'attribut name du input)
             */
            'login',
            /**
             * Le champ a utiliser comme mot de passe 
             *  (doit etre le meme dans la base de donnees et l'attribut name du input)
             */
            'password'
        ],
        /**
         * Specifie si on doit distinguer l'erreur au niveau des champs (login ou password incorrect)
         */
        'distinct_fields'   => false,
        /**
         * Nombre de tentative  de connxion avant le blocage du compte
         *  (Si inferieur a 1, le systeme de blocage du compte sera desactivé)
         */
        'nbr_login_failed'    => 0,
    ];

    /**
     * @var string Le message d'erreur global
     */
    public $errMsg = '';

    /**
     * @var array Les message d'erreurs personnalisés en fonction des champs
     */
    public $errors = [];

    /**
     * @var array les informations de l'utilisateur de la session
     */
    protected $_user = [];


    public function __construct()
    {
        $this->load_from_session();
    }


    private static $_instance = null;
    public static function instance()
    {
        if(null === self::$_instance)
        {
            self::$_instance = new Auth;
        }
        return self::$_instance;
    }



    /**
     * Definit les parametres de connexion
     * @param array $params
     * @return Auth
     */
    public function setLoginParams(array $params) : self 
    {
        $this->login_params = array_merge($this->login_params, $params);
        return $this;
    }

    /**
     * Verifie si l'utilisateur est connecté
     * @return bool
     */
    public function isConnect() : bool
    {
        return !empty($this->_user);
    }

    public function checkin(string $url = '')
    {
        if(true === $this->isConnect())
        {
            redirect($url);
        }
    }


    public function checkout(string $url = '')
    {
        if(true !== $this->isConnect())
        {
            redirect($url);
        }
    }


    /**
     * Tente de connexter un utilisateur a partir des donnees poster dans le formulaire
     * @param array $datas
     */
    public function login(array $datas) : bool
    {
        $login = $this->login_params['fields'][0] ?? 'login';
        $password = $this->login_params['fields'][1] ?? 'password';

        if(empty($datas[$login]) OR empty($datas[$password]))
        {
            $this->errMsg = 'Please complete all the fields of form';
            $this->errors = [
                $login    => 'Please enter the "'.$login.'"',
                $password => 'Please enter the "'.$password.'"'
            ];
            $this->logout();
            return false;
        }
        $user = $this->load_user($datas[$login]);

        if(empty($user))
        {
            if(true !== $this->login_params['distinct_fields'])
            {
                $this->errMsg = 'incorrect '.$login.' or '.$password;
                $this->errors = [
                    $login    => 'Check your "'.$login.'"',
                    $password => 'Check your "'.$paswword.'"'
                ];
            }
            else 
            {
                $this->errMsg = 'Unknow user in our database';
                $this->errors = [$login => 'Check your "'.$login.'"'];
            }
            $this->logout();
            return false;
        }
        if(true === $this->bruteForce($datas[$login], $this->login_params['nbr_login_failed']))
        {
            $this->errMsg = 'You have {atteint} the maximal number of trying connection. Retry tomorrow';
            $this->errors = [$login => 'Try with an another "'.$login.'"'];
        }
        if(true !== password_verify(sha1($datas[$password]), $user[$password]))
        {
            if(true !== $this->login_params['distinct_fields'])
            {
                $this->errMsg = 'incorrect '.$login.' or '.$password;
                $this->errors = [
                    $login    => 'Check your "'.$login.'"',
                    $password => 'Check your "'.$paswword.'"'
                ];
            }
            else 
            {
                $this->errMsg = 'The "'.$password.'" that you have enter is incorrect';
                $this->errors = [$password => 'Check your "'.$password.'"'];
            }
            $this->logout();
            return false;
        }
        $this->unlinkTentatives($datas[$login]);
        $this->save_session($user[$login], $user[$password]);
        return true;
    }


    public function bruteForce($login, int $nbr_login_failed) : bool
    {
        if(!is_int($nbr_login_failed) OR $nbr_login_failed < 1)
        {
            return false;
        }
        list($existence_ft, $nbr_tentatives) = $this->getLoginTentatives($login);
        
        if(++$nbr_tentatives > $nbr_login_failed) 
        {
            return true;
        }
        $this->setLoginTentatives($login, $existence_ft, $nbr_tentatives);

        return false;
    }

    /**
     * Deconnecte l'utilisateur
     */
    public function logout()
    {
        $this->clear_data();
        $this->clear_session();
    }



    /**
     * Recupere les information d'un utilisateur a partir de la session
     */
    protected function load_from_session()
    {
        if(Session::exist('auth'))
        {
            $this->_user = [
                'login' => Session::get('auth.login'),
                'password' => Session::get('auth.password')
            ];
        }
        else 
        {
            $this->clear_data();
        }
    }

    /**
     * Recupere les information d'un utilisateur en base de donnees
     * @param string $login
     */
    protected function load_user($login)
    {
        $table = explode('.', $this->login_params['table']);
        
        $query = (new Query($table[0] ?? 'default'));
        $request = $query
            ->query('SELECT * FROM '.($query->db->config['prefix']).($table[1] ?? 'users').' WHERE '.($this->login_params['fields'][0] ?? 'login').' = ?', [$login]);
        
        return $request->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Sauvegarde les information de l'utilsateur courant en session
     * @param string $login
     * @param string $password
     */
    protected function save_session($login, $password)
    {
        $this->_user = [
            'login' => $login,
            'password' => $password
        ];
        Session::set([
            'auth.login'    => $login,
            'auth.password' => $password
        ]);
    }

    /**
     * Reinitialise l'utilisateur
     */
    protected function clear_data()
    {
        $this->_user = null;
    }
    /**
     * Supprime les information de l'utilisateur de la session
     */
    protected function clear_session()
    {
        Session::destroy('auth');
    }




    /**
     * @param mixed $login
     * @return array 
     */
    private function getLoginTentatives($login) : array
    {
        $tentatives = 0; 
        $existence_ft = 0;

        $fichier = RESOURCE_DIR . '_antibruteforce_'. DS . sha1($login) . '.df';
        if(file_exists($fichier))
        {
            $fichier_tentatives = fopen($fichier, 'r');
            $contenu_tentatives = fgets($fichier_tentatives);
            $infos_tentatives = explode(';', $contenu_tentatives);

            if($infos_tentatives[0] == date('d/m/Y'))
            {
                $tentatives = $infos_tentatives[1];
            }
            else 
            {
                $existence_ft = 2;
            }
        }
        else
        {
            $existence_ft = 1;
        }
        return [$existence_ft, $tentatives];
    }
    private function setLoginTentatives($login, $existence_ft, $nbr_tentatives)
    {
        $fichier = RESOURCE_DIR . '_antibruteforce_'. DS . sha1($login) . '.df';
        if(file_exists($fichier)) {
            unlink($fichier);
        }
        $nb = ($existence_ft == 1 OR $existence_ft == 2) ? 1 : ($nbr_tentatives + 1);

        $fichier_tentatives = fopen($fichier, 'a+');
        fputs($fichier_tentatives, date('d/m/Y').';'.$nb);
        fclose($fichier_tentatives);
        return;
    }
    private function unlinkTentatives($login)
    {
        @unlink(RESOURCE_DIR . '_antibruteforce_'. DS . sha1($login) . '.df');
        return;
    }


}