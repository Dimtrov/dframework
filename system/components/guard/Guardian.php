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

use dFramework\core\loader\Injector;

/**
 * Guardian
 *
 * Gestionnaire principale d'authentification.
 * Cette classe sert de facade pour charger le bon element (Authenticator, Registrator, Resetor, Authorizer)
 *
 * @package		dFramework
 * @subpackage	Components
 * @category    Guard
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.4.0
 * @file        /system/components/guard/Guardian.php
 */
class Guardian
{
	/**
	 * @var Configurer mixed
	 */
	protected $configurer;

	/**
	 * @var array Les erreurs survenus lors du processus
	 */
	private $errors = [];

	/**
	 * @var object
	 */
	private $user;

	/**
	 * Construct
	 *
	 * @param Configurer $configurer
	 */
	public function __construct(Configurer $configurer)
	{
		$this->configurer = $configurer;
	}

	/**
	 * Set the value of configurer
	 *
	 * @param Configurer $configurer
	 * @return  self
	 */
	public function setConfigurer(Configurer $configurer)
	{
		$this->configurer = $configurer;

		return $this;
	}

	/**
	 * Recupere les erreurs du processus en cours
	 *
	 * @return array
	 */
	public function getErrors() : array
	{
		return $this->errors;
	}

	/**
	 * Recupere l'utilisateur issu du processus en cours
	 *
	 * @param string|null $process
	 * @return object
	 */
	public function getUser(?string $process = null) : object
	{
		if (empty($this->user))
		{
			if ($process === 'authentication')
			{
				$this->user = Injector::make(Authenticator::class, [$this->configurer])->getUser();
			}
		}
		return $this->user ?? new \stdClass;
	}


	/**
	 * Authentifie un utilisateur
	 *
	 * @param array|Psr\Http\Message\ServerRequestInterface $credentials
	 * @return bool
	 */
	public function authenticate($credentials) : bool
	{
		/** @var Authenticator $authenticator  */
		$authenticator = Injector::make(Authenticator::class, [$this->configurer]);

		$is_authenticate = $authenticator->execute($credentials);

		if (true !== $is_authenticate)
		{
			$this->setErrors($authenticator->getErrors());
		}
		else
		{
			$this->setUser($authenticator->getUser());
		}

		return $is_authenticate;
	}

	/**
	 * Verifie s'il y'a une session d'utilisateur active
	 *
	 * @return bool
	 */
	public function isAuthenticate() : bool
	{
		return Injector::make(Authenticator::class, [$this->configurer])->isConnect();
	}

	/**
	 * Deconnecter l'utilisateur de la session active
	 *
	 * @param callable|null $callback
	 * @return void
	 */
	public function logout(?callable $callback)
	{
		return Injector::make(Authenticator::class, [$this->configurer])->logout($callback);
	}


	/**
	 * Defini les erreurs d'un processus en cours
	 *
	 * @param array $errors
	 * @return void
	 */
	protected function setErrors(array $errors)
	{
		$this->errors = $errors;
	}

	/**
	 * Defini l'utilisateur issu du processus en cours
	 *
	 * @param object $user
	 * @return void
	 */
	protected function setUser(object $user)
	{
		$this->user = $user;
	}
}
