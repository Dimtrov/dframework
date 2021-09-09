<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.4.0
 */

namespace dFramework\components\guard;

use dFramework\core\loader\Load;
use dFramework\core\loader\Service;
use dFramework\core\security\Session;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticator
 *
 * Systeme de connexion automatique des utilisateurs
 *
 * @package		dFramework
 * @subpackage	Components
 * @category    Guard
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.4.0
 * @file        /system/components/guard/Authenticator.php
 */
class Authenticator
{
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
    protected $user = [];

    /**
     * @var string La locale a utiliser
     */
    protected $_locale;

	/**
	 * @var object Parametres de configuration
	 */
	private $params;


	/**
	 * Constructor
	 *
	 * @param Configurer $configurer
	 * @param string|null $locale
	 */
	public function __construct(Configurer $configurer, string $locale = null)
	{
        if (empty($locale) AND true === config('general.negotiate_locale'))
		{
			$locale = Service::negotiator()->language((array) config('general.supported_locales'));
		}
		$this->_locale = Service::language()->searchLocale($locale);

		$this->initParams($configurer);
		$this->loadFromSession();
	}

	/**
	 * Execute le processus de connexion
	 *
	 * @param array|Psr\Http\Message\ServerRequestInterface $credentials
	 * @return bool
	 */
	public function execute($credentials) : bool
	{
		if (true === $this->isConnect())
        {
            return true;
        }

		if ($credentials instanceof ServerRequestInterface)
		{
			$post = $credentials->getParsedBody();
			$login = $post[$this->params->login_field] ?? null;
			$password = $post[$this->params->password_field] ?? null;
			$remember = $post[$this->params->remember_field] ?? null;
		}
		else
		{
			$login = $credentials[0] ?? null;
			$password = $credentials[1] ?? null;
			$remember = $credentials[2] ?? null;
		}

		if (empty($login) OR empty($password))
        {
			if (empty($login))
            {
				$this->errors = [
					$this->params->login_field => $this->translate('entrez_le_login_mdp', ['entry' => $this->params->login_label]),
                ];
            }
            if (empty($password))
            {
				$this->errors = [
					$this->params->password_field => $this->translate('entrez_le_login_mdp', ['entry' => $this->params->password_label]),
                ];
            }
			$this->errMsg = $this->translate('remplissez_tous_les_champs');

            $this->logout();
            return false;
        }

		$user = $this->loadUser($login);

		if (empty($user))
        {
            if (true !== $this->params->distinct_fields)
            {
                $this->errors = [
                    $this->params->login_field    => $this->translate('verifiez_votre_entree', ['entry' => $this->params->login_label]),
                    $this->params->password_field => $this->translate('verifiez_votre_entree', ['entry' => $this->params->password_label]),
                ];
                $this->errMsg = $this->translate('login_mdp_incorrect', [
                    'login'    => $this->params->login_label,
                    'password' => $this->params->password_label
                ]);
            }
            else
            {
                $this->errMsg = $this->translate('utilisateur_innexistant');
                $this->errors = [
                    $this->params->login_field    => $this->translate('verifiez_votre_entree', ['entry' => $this->params->login_label])
                ];
            }
            $this->logout();
            return false;
        }

		if (true === $this->bruteForce($login))
        {
            $this->errMsg = $this->translate('nbr_tentatives_epuiser');
            $this->errors = [
                $this->params->login_field    => $this->translate('essayer_autre_compte', ['login' => $this->params->login_label]),
                $this->params->password_field => $this->translate('mdp_rater_plusieurs_fois', [
                    'password' => $this->params->password_label,
                    'nbr_fois' => $this->params->failed_login_attempts
                ]),
            ];
            $this->logout();
            return false;
        }

		if (true !== $this->checkPwd($password, $user[$this->params->password_field], $remaining, $login))
        {
            if (true !== $this->params->distinct_fields)
            {
                $this->errMsg = $this->translate('login_mdp_incorrect', [
                    'login'    => $this->params->login_label,
                    'password' => $this->params->password_label
                ]);
                $this->errors = [
                    $this->params->login_field    => $this->translate('verifiez_votre_entree', ['entry' => $this->params->login_label]),
                    $this->params->password_field => $this->translate('verifiez_votre_entree', ['entry' => $this->params->password_label]),
                ];
            }
            else
            {
				$this->errors = [
					$this->params->password_field => $this->translate('verifiez_votre_entree', ['entry' => $this->params->password_label])
                ];
				$this->errMsg = $this->translate('mdp_incorrect', ['password' => $this->params->password_label]);
            }

            if (null !== $remaining AND is_int($remaining))
            {
                $this->errMsg .= "\n" . $this->translate('nbr_tentatives_restant', ['nbr_tentatives' => '<b>'.$remaining.'</b>']);
            }
            $this->logout();
            return false;
        }

        $this->unlinkTentatives($login);
        $this->saveSession($user, (bool) $remember);

        return true;
	}


	/**
     * Verifie si l'utilisateur est connecté
     *
     * @return bool
     */
    public function isConnect() : bool
    {
        return !empty($this->user);
    }

	/**
     * Deconnecte l'utilisateur
     *
     * @param callable|null $callback
     */
    public function logout(?callable $callback = null)
    {
        $this->clearData();
        $this->clearSession();
		if (null !== $callback AND is_callable($callback))
        {
            return call_user_func($callback);
        }
    }

	 /**
     * Verifie si on n'essaie pas une attaque par brute force avec un login precis
     *
     * @param string $login
     * @return bool
     */
    public function bruteForce(string $login) : bool
    {
        if (!is_int($this->params->failed_login_attempts) OR $this->params->failed_login_attempts < 1)
        {
            return false;
        }
        list($existence_ft, $nbr_tentatives) = $this->getLoginTentatives($login);

        if (($nbr_tentatives + 1) >= $this->params->failed_login_attempts)
        {
            return true;
        }
        $this->setLoginTentatives($login, $existence_ft, $nbr_tentatives);

        return false;
    }

	/**
	 * Renvoi les erreurs rencontrées lors de l'operation
	 *
	 * @return array
	 */
	public function getErrors() : array
	{
		return [
			'message' => $this->errMsg,
			'errors' => $this->errors
		];
	}

	/**
	 * Renvoi l'utilisateur trouvé
	 *
	 * @return object|null
	 */
	public function getUser() : ?object
	{
		return empty($this->user) ? null : (object) $this->user;
	}


    /**
     * Recupere les information d'un utilisateur en base de donnees
     *
     * @param string $login
	 * @return array|null
     */
    protected function loadUser(string $login) : ?array
    {
       $builder = Service::builder($this->params->group)->from($this->params->table);
		foreach ($this->params->allowed_login_fields As $value)
		{
			$builder->orWhere($value, $login);
		}

		return $builder->first(\PDO::FETCH_ASSOC);
    }

	/**
     * Recupere les information d'un utilisateur a partir de la session
     */
    protected function loadFromSession()
    {
        $this->checkSession(function($auth_session) {
			if (1 < $this->params->inactivity_timeout)
            {
                Session::set('auth.expire_on', time() + (60 * $this->params->inactivity_timeout));
            }
            if (empty($auth_session) OR !is_array($auth_session))
            {
                $auth_session = Session::get('auth');
            }

			if (!empty($auth_session['login']))
			{
				$this->user = $this->loadUser($auth_session['login']);
			}
        });
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

        if (!$this->isPassword($pass, $hash))
        {
            if (true === $this->params->show_remaining_attempts AND is_int($this->params->failed_login_attempts) AND 1 < $this->params->failed_login_attempts)
            {
                list($existence_ft, $nbr_tentatives) = $this->getLoginTentatives($login);
                $remaining = $this->params->failed_login_attempts - (int) $nbr_tentatives;
            }
            return false;
        }
        return true;
    }

	/**
     * Sauvegarde les information de l'utilsateur courant en session
     *
     * @param array $user
     * @param bool $remember
     */
    protected function saveSession(array $user, bool $remember)
    {
		$password = $user[$this->params->password_field];
		$login = '';
		foreach ($this->params->allowed_login_fields As $value)
		{
			if (!empty($user[$value]))
			{
				$login = $user[$value];
				break;
			}
		}

        $this->user = $user;
        $auth = [
            'login'    => $login,
            'password' => $password,

            'uid'      => hash('sha512', uniqid('', true) . '_' . mt_rand()),
            'uua'      => hash('sha512', $_SERVER['HTTP_USER_AGENT']),
            'ure'      => hash('sha512', parse_url($_SERVER['HTTP_HOST'] ?? '', PHP_URL_HOST)),
            'uip'      => hash('sha512', ip_address()),
        ];
        if (1 < $this->params->inactivity_timeout)
        {
            $auth['expire_on'] = time() + (60 * $this->params->inactivity_timeout);
        }
        Session::set(compact('auth'));

		if (true === $remember)
		{
			cookie('_df_auth_', [
				'secure' => true,
				'httponly' => true,
				'expire' => 60 * 60 * 24 * $this->params->remember_live_time,
				'value' =>  Load::library('Crypto')->encrypt(json_encode($auth)),
			]);
		}
    }

	/**
     * Reinitialise l'utilisateur
     */
    protected function clearData()
    {
        $this->user = null;
    }

    /**
     * Supprime les information de l'utilisateur de la session
     */
    protected function clearSession()
    {
		cookie('_df_auth_', [
			'value' => '',
			'expire' => -1
		]);
		Session::destroy('auth');
    }

	/**
	 * Verifie les données de session pour evider les falsification
	 *
	 * @param callable $callback
	 * @return void
	 */
	private function checkSession(callable $callback)
    {
        $auth_session = Session::get('auth');
		$from_cookie = false;

		if (empty($auth_session))
		{
			$cookie = cookie('_df_auth_');
			if (!empty($cookie))
			{
				$cookie = Load::library('Crypto')->decrypt($cookie);
				$auth_session = json_decode($cookie, true);

				$from_cookie = true;
				Session::set(['auth' => $auth_session]);
			}
		}

        if (empty($auth_session))
        {
            $this->logout();
        }
        else if (empty($auth_session['login']) OR empty($auth_session['password']))
        {
            $this->logout();
        }
        else if (empty($auth_session['uid']))
        {
            $this->logout();
        }
        else if (empty($auth_session['uip']) OR $auth_session['uip'] !== hash('sha512', ip_address()))
        {
            $this->logout();
        }
        else if (empty($auth_session['uua']) OR $auth_session['uua'] !== hash('sha512', $_SERVER['HTTP_USER_AGENT']))
        {
            $this->logout();
        }
        else if (empty($auth_session['ure']) OR $auth_session['ure'] !== hash('sha512', parse_url($_SERVER['HTTP_HOST'] ?? '', PHP_URL_HOST)))
        {
            $this->logout();
        }
        else if (false === $from_cookie AND 1 < $this->params->inactivity_timeout AND (empty($auth_session['expire_on']) OR time() >= $auth_session['expire_on']))
        {
            $this->logout();
        }
        else
        {
            call_user_func($callback, $auth_session);
        }
    }

	/**
	 * Verifie que le mot de passe entré est egale à celui present en bd
	 *
	 * @param string $pass
	 * @param string $hash
	 * @return bool
	 */
	private function isPassword(string $pass, string $hash) : bool
	{
		return call_user_func_array($this->params->is_password, [$pass, $hash]);
	}

	/**
	 * Traduit et renvoi les message d'erreur
	 *
	 * @param string $key
	 * @param array $arguments
	 * @return string
	 */
	private function translate(string $key, array $arguments = []) : string
	{
		return lang('login.'.$key, $arguments, $this->_locale);
	}

	/**
	 * Recupere les informations sur les tentatives d'un login
	 *
     * @param string $login
     * @return array
     */
    private function getLoginTentatives(string $login) : array
    {
        $tentatives = 0;
        $existence_ft = 0;

        $fichier = $this->getFileTentative($login);
        if (file_exists($fichier))
        {
            $fichier_tentatives = fopen($fichier, 'r');
            $contenu_tentatives = fgets($fichier_tentatives);
            $infos_tentatives = explode(';', $contenu_tentatives);

            if ($infos_tentatives[0] == date('dmY'))
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

	/**
	 * Modifie le nombre de tentatives d'un login
	 *
	 * @param string $login
	 * @param int $existence_ft
	 * @param int $nbr_tentatives
	 * @return void
	 */
    private function setLoginTentatives(string $login, $existence_ft, int $nbr_tentatives)
    {
        $fichier = $this->getFileTentative($login);
        if (file_exists($fichier))
        {
            unlink($fichier);
        }
        $nb = ($existence_ft == 1 OR $existence_ft == 2) ? 1 : ($nbr_tentatives + 1);

        $fichier_tentatives = fopen($fichier, 'w+');
        fputs($fichier_tentatives, date('dmY').';'.$nb);
        fclose($fichier_tentatives);

        return;
    }

	/**
	 * Supprime les tentatives d'un login
	 *
	 * @param string $login
	 * @return void
	 */
    private function unlinkTentatives(string $login)
    {
        @unlink($this->getFileTentative($login));

        return;
    }

	/**
	 * Genere et renvoi le chemin approprié pour le fichier de tentatives
	 *
	 * @param string $login
	 * @return string
	 */
	private function getFileTentative(string $login) : string
	{
		return STORAGE_DIR . 'guard'. DS .'abf.' . sha1(strtolower($login)) . '.df';
	}

	/**
	 * Initialise les parametres de configuration
	 *
	 * @return void
	 */
	private function initParams(Configurer $configurer)
	{
		$this->params = (object) [
			'group'                   => $configurer->group(),
			'table'                   => $configurer->table(),
			'distinct_fields'         => $configurer->distinctFields(),
			'failed_login_attempts'   => $configurer->failedLoginAttempts(),
			'show_remaining_attempts' => $configurer->showRemainingAttempts(),
			'inactivity_timeout'      => $configurer->inactivityTimeout(),
			'login_label'             => $configurer->loginLabel(),
			'login_field'             => $configurer->loginField(),
			'password_label'          => $configurer->passwordLabel(),
			'password_field'          => $configurer->passwordField(),
			'remember_field'          => $configurer->rememberField(),
			'remember_live_time'      => $configurer->rememberLiveTime(),
			'allowed_login_fields'    => $configurer->allowedLoginFields(),
			'is_password'             => $configurer->isPassword()
		];
	}
}
