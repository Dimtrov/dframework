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
| DATAS SETTINGS OF APPLICATION
| -------------------------------------------------------------------
| This file will contain the datas settings of your application.
|
| For complete instructions please consult the 'Data Configuration' in User Guide.
|
*/


/*
|--------------------------------------------------------------------------
| Encryption Key
|--------------------------------------------------------------------------
*/
$data['encryption'] = [
    /**
     * KEY
     *  La cle de chiffrement des donnees de l'application (cookie, et donnees chiffrées avec la librairie Crypto)
     *
     * @var string
     */
    'key'       => env('app.encryptionKey', 't568hujkjdfghjudv45rt6y7u3edf3eq'),

    /**
     * ALGO
     *  Specifie l'algorithme à utiliser pour le chiffrement des donnees
     *
     * @var string
     */
    'algo'      => 'CAST5-CBC',

    /**
     * ADD_HMAC
     *  Specifie si on doit ajouter un hmac a la fin d'un chiffrement (utilisé dans la librairie Crypto)
     *
     * @var bool
     */
    'add_hmac'  => true,

    /**
     * SALT
     *  Definit la cle a utiliser comme salt dans le processus d'haschage de mot de passe
     *
     * @var string
     */
    'salt'      => '',
];


/*
| -------------------------------------------------------------------
| SESSION SETTINGS OF APPLICATION
| -------------------------------------------------------------------
| This section will contain the sessions settings of your application.
*/
$data['session'] = [
    /**
     * CACHE_LIMITER
     */
    'cache_limiter' => 'private',

    /**
     * LIFETIME
     *  Temps d'expirara du cache de session en minute
     *
     * @var int
     */
    'lifetime' => 60,

    /**
     * EXPIRE
     *  The number of SECONDS you want the session to last.
     *  Setting to 0 (zero) means expire when the browser is closed.
     *
     * @var int
     */
    'expire' => 7200,
];


/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
*/
$data['cookies'] = [
    /**
     * PREFIX
     *  Set a cookie name prefix if you need to avoid collisions
     *
     * @var string
     */
    'prefix'   => env('app.cookie.prefix', ''),
    /**
     * DOMAIN
     *  Set to .your-domain.com for site-wide cookies
     *
     * @var string
     */
    'domain'   => env('app.cookie.domain', ''),
    /**
     * PATH
     *  Typically will be a forward slash
     *
     * @var string
     */
    'path'     => env('app.cookie.path', '/'),
    /**
     * SECURE
     *  Cookie will only be set if a secure HTTPS connection exists.
     *  Whether to only transfer cookies via SSL
     *
     * @var bool
     */
    'secure'   => env('app.cookie.secure', false),
    /**
     * HTTPONLY
     *  Cookie will only be accessible via HTTP(S) (no javascript)
     *  Whether to only makes the cookie accessible via HTTP (no javascript)
     *
     * @var bool
     */
    'httponly' => env('app.cookie.HTTPOnly', true),
];


/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
*/
$data['csrf'] = [
    /**
     * @var bool
     */
    'protection'    => env('app.CSRF.protection', false),

    /**
     * @var string The token name
     */
    'token_name'    => env('app.CSRF.tokenName', '_csrfToken'),

    /**
     * @var string The cookie name
     */
    'cookie_name'   => env('app.CSRF.cookieName', 'csrfToken'),

    /**
     * @var int The number in seconds the token should expire.
     */
    'expire'        => env('app.CSRF.expire', 7200),

    /**
     * @var bool
     */
    'samesite'       => env('app.CSRF.samesite', null),

    /**
     * @var bool Regenerate token on every submission
     */
    'regenerate'    => env('app.CSRF.regenerate', false),

    /**
     * @var array Array of URIs which ignore CSRF checks
     */
    'exclude_uris'  => env('app.CSRF.excludeURIs', [])
];


/*
|--------------------------------------------------------------------------
| Hydrator
|--------------------------------------------------------------------------
| Set a configuration of sql entities hydratator
*/
$data['hydrator'] = [
    /**
     * CASE
     *  Specifie si le nom des colones issues de la bd doivent etre convertie
     *  Les valeurs admissible sont camel (camelcase), pascal(pascalcase), null (rien)
     *
     * @var string|null
     */
    'case'    => 'camel'
];


/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return compact('data');
