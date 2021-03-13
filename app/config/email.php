<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */


/*
| -------------------------------------------------------------------
| EMAILS SETTINGS OF APPLICATION
| -------------------------------------------------------------------
| This file will contain the datas settings for the mail sending of your application.
|
| For complete instructions please consult the 'Mail Library' in User Guide.
|
*/



/*
|--------------------------------------------------------------------------
| Datas of connexion in mail server
|--------------------------------------------------------------------------
*/
$email['default']['connect'] = [
    /**
     * HOST
     *  Adresse du serveur de messagerie
     * 
     * @var string
     */
    'host'      => 'localhost',

    /**
     * USERNAME
     *  Nom de l'utilisateur qui se connecte au serveur de messagerie
     * 
     * @var string
     */
    'username'  => 'admin@localhost',

    /**
     * PASSWORD
     *  Mot de passe de l'utilisateur du serveur
     * 
     * @var string
     */
    'password'  => 'admin',

    /**
     * PORT
     *  Port de communication du serveur de messagerie
     * 
     * @var int
     */
    'port' 		=> 25,
    
    /**
     * DEBUG
     *  Specifie si on doit afficher ou non les erreurs en cas d'echec d'envoi d'email
     * 
     * @var bool
     */
	'debug'		=> true,
];


/*
|--------------------------------------------------------------------------
| Parameters of sending mail process
|--------------------------------------------------------------------------
*/
$email['default']['set'] = [
    /**
     * METHOD
     *  Protocole a utiliser pour l'envoi d'emails
     * 
     * @var string
     */
    'method'     => 'SMTP',

    /**
     * TIMEOUT
     *  Delai d'attente SMTP en seconde
     * 
     * @var int
     */
    'timeout'    => 300,

    /**
     * CHARSET
     *  Jeu de caracteres utilises dans le message
     * 
     * @var string
     */
    'charset'    => 'utf-8',

    /**
     * PRIORITY
     *  Priorité du couriel
     * 
     * @var int|null
     */
    'priority'   => null,

    /**
     * ENCRYPTION
     *  Cryptage SMTP
     * 
     * @var string
     */
    'encryption' => 'tls',
];


/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return compact('email');