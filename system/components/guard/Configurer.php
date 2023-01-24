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

use dFramework\core\security\Password;

/**
 * Configurer
 *
 * Classe de configuration du systeme d'authentifcation
 *
 * @package		dFramework
 * @subpackage	Components
 * @category    Guard
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.4.0
 * @file        /system/components/guard/Configurer.php
 */
class Configurer
{
	/**
	 * group
	 *
	 * @var string Le groupe de connexion à la base de données à utiliser
	 */
	private $group = 'default';

	/**
	 * table
	 *
	 * @var string La table dans laquelle on doit faire la recherche
	 */
	private $table = 'users';

	/**
	 * allowed_login_fields
	 *
	 * @var string[] Les champs a utiliser pour le login
	 */
	private $allowed_login_fields = ['username'];

	/**
	 * login_field
	 *
	 * @var string Le champ de reference pour le login
	 */
	private $login_field = 'login';

	/**
	 * password_field
	 *
	 * @var string Le champ à utiliser pour le mot de passe
	 */
	private $password_field = 'password';

	/**
	 * remember_field
	 *
	 * @var string Le champ à utiliser pour savoir si on doit se souvenir de l'utisateur
	 */
	private $remember_field = 'remember';

	/**
	 * distinct_fields
	 *
	 * @var bool Specifie si on doit distinguer l'erreur au niveau des champs (login ou password incorrect)
	 */
	private $distinct_fields = false;

	/**
	 * login_label
	 *
	 * @var string Label du champ login
	 */
	private $login_label = 'Login';

	/**
	 * password_label
	 *
	 * @var string Label du champ du login
	 */
	private $password_label = 'Password';

	/**
	 * is_password
	 *
	 * @var \Closure Fonction de callback qui verifie si le mot de passe est correct ou pas
	 */
	private $is_password;

	/**
	 * failed_login_attempts
	 *
	 * @var int Nombre de tentative  de connexion avant le blocage du compte
	 *  	(Si inferieur a 1, le systeme de blocage du compte sera desactivé)
	 */
	private $failed_login_attempts = 0;

	/**
	 * show_remaining_attempts
	 *
	 * @var bool Specifie si on doit afficher le nombre de tentatives restant ou pas
	 */
	private $show_remaining_attempts = false;

	/**
	 * inactivity_timeout
	 *
	 * @var int Definit la duree d'inactivite (en minutes) conduisant a la deconnexion automatique de l'utilisateur
     *  	(Si l'utilisateur n'ouvre aucune page durant cet intervalle de temps, il sera automatiquement deconnecter)
	 *  	(Si inferieur a 1, le systeme de deconnexion automatique sera desactivé)
	 */
	private $inactivity_timeout = 0;

	/**
	 * remember_live_time
	 *
	 * @var int Specifie la durée de vie en jour du cookie du remember
	 */
	private $remember_live_time = false;




	/**
	 * Recupere ou modifie le groupe de connexion à la base de données à utiliser
	 *
	 * @param string|null  $group  Le groupe de connexion à la base de données à utiliser
	 * @return string|self
	 */
	public function group(string $group = null)
	{
		if (empty($group))
		{
			return $this->group;
		}
		$this->group = $group;

		return $this;
	}

	/**
	 * Recupere ou modifie la table dans laquelle on doit faire la recherche
	 *
	 * @param string|null  $table  La table dans laquelle on doit faire la recherche
	 * @return string|self
	 */
	public function table(string $table = null)
	{
		if (empty($table))
		{
			return $this->table;
		}
		$this->table = $table;

		return $this;
	}

	/**
	 * Recupere ou modifie les champs a utiliser pour le login
	 *
	 * @param string[]|null $allowed_login_fields  Les champs a utiliser pour le login
	 * @return string[]|self
	 */
	public function allowedLoginFields(array $allowed_login_fields = null)
	{
		if (empty($allowed_login_fields))
		{
			return $this->allowed_login_fields;
		}
		$this->allowed_login_fields = $allowed_login_fields;

		return $this;
	}

	/**
	 * Recupere ou modifie le de reference pour le login
	 *
	 * @param string|null $login_field  Le champs de reference pour le login
	 * @return string|self
	 */
	public function loginField(string $login_field = null)
	{
		if (empty($login_field))
		{
			return $this->login_field;
		}
		$this->login_field = $login_field;

		return $this;
	}

	/**
	 * Recupere ou modifie le champ à utiliser pour le mot de passe
	 *
	 * @param string|null $password_field  Le champ à utiliser pour le mot de passe
	 * @return string|self
	 */
	public function passwordField(string $password_field = null)
	{
		if (empty($password_field))
		{
			return $this->password_field;
		}
		$this->password_field = $password_field;

		return $this;
	}

	/**
	 * Recupere ou modifie le champ à utiliser pour savoir si on doit se souvenir de l'utisateur
	 *
	 * @param string|null  $remember_field  Le champ à utiliser pour savoir si on doit se souvenir de l'utisateur
	 * @return string|self
	 */
	public function rememberField(string $remember_field = null)
	{
		if (empty($remember_field))
		{
			return $this->remember_field;
		}
		$this->remember_field = $remember_field;

		return $this;
	}

	/**
	 * Recupere ou modifie le fait qu'on doit distinguer l'erreur au niveau des champs (login ou password incorrect)
	 *
	 * @param bool|null  $distinct_fields  Specifie si on doit distinguer l'erreur au niveau des champs (login ou password incorrect)
	 * @return bool|self
	 */
	public function distinctFields(bool $distinct_fields = null)
	{
		if (!is_bool($distinct_fields))
		{
			return $this->distinct_fields;
		}
		$this->distinct_fields = $distinct_fields;

		return $this;
	}

	/**
	 * Recupere ou modifie le label à utiliser pour le champ login
	 *
	 * @param string|null  $login_label  Label du champ login
	 * @return string|self
	 */
	public function loginLabel(string $login_label = null)
	{
		if (empty($login_label))
		{
			return $this->login_label;
		}
		$this->login_label = $login_label;

		return $this;
	}

	/**
	 * Recupere ou modifie le label à utiliser pour le champ password
	 *
	 * @param string|null  $password_label  Label du champ du password
	 * @return self
	 */
	public function passwordLabel(string $password_label = null)
	{
		if (empty($password_label))
		{
			return $this->password_label;
		}
		$this->password_label = $password_label;

		return $this;
	}

	/**
	 * Recupere ou modifie la fonction de callback qui verifie si le mot de passe est correct ou pas
	 * Cette fonction doit prendre en entré un mot de passe en claire et un hash puis renvoi un booleen issue de la comparaison des deux parametres
	 *
	 * @param \Closure|null  $is_password  Fonction de callback qui verifie si le mot de passe est correct ou pas
	 * @return \Closure|self
	 */
	public function isPassword(\Closure $is_password = null)
	{
		if (empty($is_password))
		{
			if (empty($this->is_password))
			{
				return function(string $pass, string $hash) : bool {
					return Password::compare($pass, $hash);
				};
			}
			return $this->is_password;
		}
		$this->is_password = $is_password;

		return $this;
	}

	/**
	 * Recupere ou modifie le nombre de tentative  de connexion avant le blocage du compte
	 *
	 * @param int|null  $failed_login_attempts  Nombre de tentative  de connexion avant le blocage du compte (Si inferieur a 1, le systeme de blocage du compte sera desactivé)
	 * @return int|self
	 */
	public function failedLoginAttempts(int $failed_login_attempts = null)
	{
		if (empty($failed_login_attempts))
		{
			return $this->failed_login_attempts;
		}
		$this->failed_login_attempts = $failed_login_attempts;

		return $this;
	}

	/**
	 * Recupere ou modifie le fait qu'on doit afficher le nombre de tentatives restant ou pas
	 *
	 * @param bool|null $show_remaining_attempts  Specifie si on doit afficher le nombre de tentatives restant ou pas
	 * @return bool|self
	 */
	public function showRemainingAttempts(bool $show_remaining_attempts = null)
	{
		if (!is_bool($show_remaining_attempts))
		{
			return $this->show_remaining_attempts;
		}
		$this->show_remaining_attempts = $show_remaining_attempts;

		return $this;
	}

	/**
	 * Recupere ou modifie la duree d'inactivite (en minutes) conduisant a la deconnexion automatique de l'utilisateur
	 *
	 * @param int|null $inactivity_timeout Duree d'inactivite (en minutes) conduisant a la deconnexion automatique de l'utilisateur
	 * @return int|self
	 */
	public function inactivityTimeout(int $inactivity_timeout = null)
	{
		if (!is_int($inactivity_timeout))
		{
			return $this->inactivity_timeout;
		}
		$this->inactivity_timeout = $inactivity_timeout;

		return $this;
	}

	/**
	 * Recupere ou modifie la durée de vie en jour du cookie du remember
	 *
	 * @param int|null  $remember_live_time  Specifie la durée de vie en jour du cookie du remember
	 * @return int|self
	 */
	public function rememberLiveTime(int $remember_live_time = null)
	{
		if (!is_int($remember_live_time))
		{
			return $this->remember_live_time;
		}
		$this->remember_live_time = $remember_live_time;

		return $this;
	}
}
