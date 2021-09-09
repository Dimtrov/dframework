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

use dFramework\components\guard\Configurer;
use dFramework\components\guard\Guardian;

/**
 * dFramework Guard Service
 *
 * Cette classe permet de configurer le gestionnaire d'authentification et de bénéficier de ses fonctionnalités simplement
 *
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @since       3.4.0
 * @file        /app/services/GuardService.php
 */
class GuardService
{
	/**
	 * @var Guardian
	 */
	protected $guardian;

	/**
	 * Constructor
	 *
	 * @param Guardian $guardian
	 * @param Configurer $configurer
	 */
	public function __construct(Guardian $guardian, Configurer $configurer)
	{
		$this->guardian = $guardian->setConfigurer($configurer);
	}

	/**
	 * Recupere les erreurs survenues lors du processus
	 *
	 * @return array
	 */
	public function getErrors() : array
	{
		return $this->guardian->getErrors();
	}

	/**
	 * Renvoi l'utilisateur issue du processus
	 *
	 * @param string|null $process
	 * @return object|null
	 */
	public function getUser(?string $process = null) : ?object
	{
		return $this->guardian->getUser($process);
	}


	/**
	 * Authentifie un utilisateur
	 *
	 * @param array|\Psr\Http\Message\ServerRequestInterface $credentials
	 * @return bool
	 */
	public function authenticate($credentials) : bool
	{
		return $this->guardian->authenticate($credentials);
	}

	/**
	 * Verifie si un utilisateur est authentifié
	 *
	 * @return bool
	 */
	public function isAuthenticate() : bool
	{
		return $this->guardian->isAuthenticate();
	}

	/**
     * Redirige un utilisateur si il est connecté
     *
     * @param string $url
     */
    public function connectIn(string $url = '')
    {
        if (true === $this->isAuthenticate())
        {
            redirect($url);
			exit;
        }
    }

    /**
     * Redirige un utilisateur si il n'est pas connecter
     *
     * @param string $url
     */
    public function connectOut(string $url = '')
    {
        if (true !== $this->isAuthenticate())
        {
            redirect($url);
			exit;
        }
    }

	/**
	 * Deconnecte l'utilisateur
	 *
	 * @param callable|null $callback
	 * @return void
	 */
	public function logout(?callable $callback = null)
	{
		return $this->guardian->logout($callback);
	}
}
