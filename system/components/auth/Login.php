<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

 
namespace dFramework\components\auth;

use dFramework\core\db\Query;
use dFramework\core\security\Session;
use dFramework\core\security\Csrf;
use dFramework\core\utilities\Utils;
use dFramework\core\data\Request;
use dFramework\core\Helpers;
use dFramework\core\loader\Load;

/**
 * Login
 *
 * Systeme de connexion automatique des utilisateurs
 *
 * @package		dFramework
 * @subpackage	Components
 * @category    Auth
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.2
 * @file        /system/components/auth/Login.php
 */

class Login
{   
    /**
     * @var array les parametres d'authentification
     */
    private $_params = [
        /**
         * La table dans laquelle on doit faire la recherche
         */
        'table'             => 'default.users',
        'fields'            => [
            /**
             * Le champ a utiliser comme login sous forme de cle:valeur
             *  (la cle doit etre le meme dans la base de donnees et l'attribut name du input)
             *  (la valeur represente le texte qui sera affiché a l'utilisateur)
             *  (si la valeur est omise, elle sera remplacee par la cle)
             */
            'login:login',
            /**
             * Le champ a utiliser comme mot de passe sous forme de cle:valeur
             *  (la cle doit etre le meme dans la base de donnees et l'attribut name du input)
             *  (la valeur represente le texte qui sera affiché a l'utilisateur)
             *  (si la valeur est omise, elle sera remplacee par la cle)
             */
            'password:password'
        ],
        /**
         * Specifie si on doit valider le token csrf ou pas
         */
        'check_token'           => true,
        /**
         * Specifie si on doit distinguer l'erreur au niveau des champs (login ou password incorrect)
         */
        'distinct_fields'       => false,
        /**
         * Nombre de tentative  de connexion avant le blocage du compte
         *  (Si inferieur a 1, le systeme de blocage du compte sera desactivé)
         */
        'failed_login_attempts' => 0,
        /**
         * Specifie si on doit afficher le nombre de tentatives restant ou pas
         */
        'show_remaining_attempts' => true,
        /**
         * Definit la duree d'inactivite (en minutes) conduisant a la deconnexion automatique de l'utilisateur
         *  (Si l'utilisateur n'ouvre aucune page durant cet intervalle de temps, il sera automatiquement deconnecter)
         *  (Si inferieur a 1, le systeme de deconnexion automatique sera desactivé)
         */
        'inactivity_timeout'    => 0,
        /**
         * Specifie si on doit egalement stocker les informations de session dans les cookies ou pas
         */
        'save_in_cookie'        => false,
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

    /**
     * @var object Les langues issues du fichier json
     */
    protected $_lang;


    public function __construct(string $locale = null)
    {
        $this->load_from_session();

        Load::lang('component.login', $this->_lang, $locale, false);
        $this->_lang = (array) $this->_lang;
    }


    private static $_instance = null;
    public static function instance(string $locale = null)
    {
        if(null === self::$_instance)
        {
            $class = __CLASS__;
            self::$_instance = new $class($locale);
        }
        return self::$_instance;
    }



    /**
     * Definit les parametres de connexion
     * 
     * @param string|array $param
     * @param mixed $value
     * @return Login
     */
    public function set($param, $value = null) : self 
    {
        if(is_string($param) AND null !== $value)
        {
            $this->set([$param => $value]);
        }
        else if(is_array($param))
        {
            $this->_params = array_merge($this->_params, $param);
        }
        return $this;
    }

    /**
     * Verifie si l'utilisateur est connecté
     * 
     * @return bool
     */
    public function isConnect() : bool
    {
        return !empty($this->_user);
    }

    /**
     * Redirige un utilisateur si il est connecter
     * 
     * @param string $url
     */
    public function checkin(string $url = '')
    {
        if(true === $this->isConnect())
        {
            redirect($url);
        }
    }
    /**
     * Redirige un utilisateur si il n'est pas connecter
     * 
     * @param string $url
     */
    public function checkout(string $url = '')
    {
        if(true !== $this->isConnect())
        {
            redirect($url);
        }
    }


    /**
     * Tente de connecter un utilisateur a partir des donnees poster dans le formulaire
     * 
     * @param array|null $datas
     */
    public function login(array $datas = []) : bool
    {
        if(true === $this->isConnect())
        {
            $this->errMsg = $this->_lang['deja_connecter'];
            return false;
        }
        if(empty($datas))
        {
            $datas = (new Request)->data;
        }
        if(true === $this->_params['check_token'])
        {
            if(empty($datas['formcsrftoken']))
            {
                $this->errMsg = $this->_lang['token_innexistant'];
                return false;
            }
            if(true !== Csrf::instance()->verify($datas['formcsrftoken']))
            {
                $this->errMsg = $this->_lang['token_invalide'];
                return false;
            }
        }
        $login = explode(':', $this->_params['fields'][0] ?? 'login:login');
        $password = explode(':', $this->_params['fields'][1] ?? 'password:password');

        $login_k = $login[0]; $login_v = ucfirst($login[1] ?? $login[0]);
        $password_k = $password[0]; $password_v = ucfirst($password[1] ?? $password[0]);

        if(empty($datas[$login_k]) OR empty($datas[$password_k]))
        {
            $this->errMsg = $this->_lang['remplissez_tous_les_champs'];
            $this->errors = [
                $login_k    => str_replace('{login}', '"'.$login_v.'"', $this->_lang['entrez_le_login']),
                $password_k => str_replace('{password}', '"'.$password_v.'"', $this->_lang['entrez_le_mdp']),
            ];
            $this->logout();
            return false;
        }
        $user = $this->load_user($datas[$login_k]);

        if(empty($user))
        {
            if(true !== $this->_params['distinct_fields'])
            {
                $this->errMsg = str_replace(['{login}', '{password}'], [$login_v, $password_v], $this->_lang['login_mdp_incorrect']);
                $this->errors = [
                    $login_k    => str_replace('{entry}', '"'.$login_v.'"', $this->_lang['verifiez_votre_entree']),
                    $password_k => str_replace('{entry}', '"'.$password_v.'"', $this->_lang['verifiez_votre_entree']),
                ];
            }
            else 
            {
                $this->errMsg = $this->_lang['utilisateur_innexistant'];
                $this->errors = [$login_k => str_replace('{entry}', '"'.$login_v.'"', $this->_lang['verifiez_votre_entree'])];
            }
            $this->logout();
            return false;
        }
        if(true === $this->bruteForce($datas[$login_k]))
        {
            $this->errMsg = $this->_lang['nbr_tentatives_epuiser'];
            $this->errors = [
                $login_k => str_replace('{login}', '"'.$login_v.'"', $this->_lang['essayer_autre_compte']),
                $password_k => str_replace(['{password}', '{nbr_fois}'], ['"'.$password_v.'"', '"'.$this->_params['failed_login_attempts'].'"'], $this->_lang['mdp_rater_plusieurs_fois']), 
            ];
            $this->logout();
            return false;
        }
        if(true !== $this->checkPwd($datas[$password_k], $user[$password_k], $remaining, $datas[$login_k]))
        {
            if(true !== $this->_params['distinct_fields'])
            {
                $this->errMsg = str_replace(['{login}', '{password}'], [$login_v, $password_v], $this->_lang['login_mdp_incorrect']);
                $this->errors = [
                    $login_k    => str_replace('{entry}', '"'.$login_v.'"', $this->_lang['verifiez_votre_entree']),
                    $password_k => str_replace('{entry}', '"'.$password_v.'"', $this->_lang['verifiez_votre_entree']),
                ];
            }
            else 
            {
                $this->errMsg = str_replace('{password}', '"'.$password_v.'"', $this->_lang['mdp_incorrect']);
                $this->errors = [$password_k => str_replace('{entry}', '"'.$password_v.'"', $this->_lang['verifiez_votre_entree'])];
            }
            if(null !== $remaining AND is_int($remaining))
            {
                $this->errMsg .= "\n" . str_replace('{nbr_tentatives}', '<b>'.$remaining.'</b>', $this->_lang['nbr_tentatives_restant']);
            }
            $this->logout();
            return false;
        }
        $this->unlinkTentatives($datas[$login_k]);
        $this->save_session($user[$login_k], $user[$password_k]);
        return true;
    }

    /**
     * Verifie si on n'essaie pas une attaque par brute force avec un login precis
     * 
     * @param string $login
     * @return bool
     */
    public function bruteForce($login) : bool
    {
        if(!is_int($this->_params['failed_login_attempts']) OR $this->_params['failed_login_attempts'] < 1)
        {
            return false;
        }
        list($existence_ft, $nbr_tentatives) = $this->getLoginTentatives($login);
     
        if(($nbr_tentatives + 1) >= $this->_params['failed_login_attempts']) 
        {
            return true;
        }
        $this->setLoginTentatives($login, $existence_ft, $nbr_tentatives);

        return false;
    }

    /**
     * Deconnecte l'utilisateur
     * 
     * @param callable|null $callback
     */
    public function logout(?callable $callback = null)
    {
        $this->clear_data();
        $this->clear_session();
        if(null !== $callback AND is_callable($callback))
        {
            call_user_func($callback);
        }
    }


    /**
     * Recupere les information d'un utilisateur en base de donnees
     * 
     * @param string $login
     */
    protected function load_user($login)
    {
        $table = explode('.', $this->_params['table']);
        
        $query = (new Query($table[0] ?? 'default'));
        $request = $query
            ->query('SELECT * FROM '.($query->db->config['prefix']).($table[1] ?? 'users').' WHERE '.($this->_params['fields'][0] ?? 'login').' = ?', [$login]);
        
        $response = $request->fetch(\PDO::FETCH_ASSOC);
        $request->closeCursor();
        return $response;
    }

    /**
     * Recupere les information d'un utilisateur a partir de la session
     */
    protected function load_from_session()
    {
        $this->checkSession(function($auth_session) {
            if (1 < $this->_params['inactivity_timeout'])
            {
                Session::set('auth.expire_on', time() + (60 * $this->_params['inactivity_timeout']));
            }
            if (empty($auth_session) OR !is_array($auth_session)) 
            {
                $auth_session = Session::get('auth');
            }
            $this->_user = [
                'login'    => $auth_session['login'],
                'password' => $auth_session['password'],
            ];
        });
    }

    /**
     * Sauvegarde les information de l'utilsateur courant en session
     * 
     * @param string $login
     * @param string $password
     */
    protected function save_session($login, $password)
    {
        $this->_user = [
            'login'    => $login,
            'password' => $password
        ];
        $auth = [
            'login'    => $login,
            'password' => $password,

            'uid'      => sha1(uniqid('', true) . '_' . mt_rand()),
            'uua'      => sha1($_SERVER['HTTP_USER_AGENT']),
            'ure'      => sha1(parse_url($_SERVER['HTTP_REFERER'] ?? null, PHP_URL_HOST)),
            'uip'      => Helpers::instance()->ip_address(),
        ];
        if(1 < $this->_params['inactivity_timeout'])
        {
            $auth['expire_on'] = time() + (60 * $this->_params['inactivity_timeout']);
        }
        Session::set(compact('auth'));
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
     * Verifie si le mot de passe est correct et met a jour le nombre d'essai restant
     * 
     * @param string $pass
     * @param string $hash
     * @param mixed $remaining
     * @param string $login
     * @return bool
     */
    protected function checkPwd(string $pass, string $hash, &$remaining, string $login) : bool
    {
        $remaining = null;
        if(Utils::hashpass(trim($pass)) !== trim($hash))
        {
            if(true === $this->_params['show_remaining_attempts'] AND is_int($this->_params['failed_login_attempts']) AND 1 < $this->_params['failed_login_attempts'])
            {
                list($existence_ft, $nbr_tentatives) = $this->getLoginTentatives($login);
                $remaining = $this->_params['failed_login_attempts'] - (int) $nbr_tentatives;
            }
            return false;
        }
        return true;
    }


    private function checkSession(callable $callback)
    {
        $auth_session = Session::get('auth');
        if(empty($auth_session))
        {
            $this->logout();
        }
        else if(empty($auth_session['login']) OR empty($auth_session['password']))
        {
            $this->logout();
        }
        else if(empty($auth_session['uid']))
        {
            $this->logout();
        }
        else if(empty($auth_session['uip']) OR $auth_session['uip'] !== Helpers::instance()->ip_address())
        {
            $this->logout();
        }
        else if(empty($auth_session['uua']) OR $auth_session['uua'] !== sha1($_SERVER['HTTP_USER_AGENT']))
        {
            $this->logout();
        }
        else if(empty($auth_session['ure']) OR $auth_session['ure'] !== sha1(parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST)))
        {
            $this->logout();
        }
        else if(1 < $this->_params['inactivity_timeout'] AND (empty($auth_session['expire_on']) OR time() >= $auth_session['expire_on']))
        {
            $this->logout();
        }
        else 
        {
            call_user_func_array($callback, $auth_session);
        }        
    }

    /**
     * @param string $login
     * @return array 
     */
    private function getLoginTentatives(string $login) : array
    {
        $tentatives = 0; 
        $existence_ft = 0;

        $fichier = RESOURCE_DIR . '_antibruteforce'. DS . sha1($login) . '.df';
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
        $fichier = RESOURCE_DIR . '_antibruteforce'. DS . sha1($login) . '.df';
        if(file_exists($fichier)) {
            unlink($fichier);
        }
        $nb = ($existence_ft == 1 OR $existence_ft == 2) ? 1 : ($nbr_tentatives + 1);

        $fichier_tentatives = fopen($fichier, 'w+');
        fputs($fichier_tentatives, date('d/m/Y').';'.$nb);
        fclose($fichier_tentatives);
        return;
    }
    private function unlinkTentatives($login)
    {
        @unlink(RESOURCE_DIR . '_antibruteforce'. DS . sha1($login) . '.df');
        return;
    }

}