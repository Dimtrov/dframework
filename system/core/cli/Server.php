<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.1
 */

namespace dFramework\core\cli;

use Ahc\Cli\Helper\Shell;

/**
 * Server
 * Launch the PHP development server
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.0
 * @file        /system/core/cli/Server.php
 */
class Server extends Cli
{
    protected $_description = 'Service de lancement du serveur de developpement';
    protected $_name = 'Server';

    public function __construct()
    {
        parent::__construct();

        $this
            ->option('--host', 'Hote sur lequel votre application sera lancée. "localhost" par defaut', null, 'localhost')
            ->option('--port', 'Port sur lequel vous souhaitez demarrer le serveur. "3200" par defaut', null, 3200)
            ->option('--php', 'Chemin vers l\'executable php à utiliser pour démarrer le serveur.', null, PHP_BINARY)
 
            // Usage examples:
            ->usage(
                '<bold>  dbot serve</end> <comment> ==> Lance le serveur sur l\'hote "http://localhost:3200" et y heberge votre application</end><eol/>' .
                '<bold>  dbot serve --port=8080</end> <comment> ==> Utilise le port "8080" pour lancer le serveur et heberger votre application</end><eol/>' . 
                '<bold>  dbot serve --host=local.dev --port=3000</end> <comment> ==> Heberge votre application sur l\'hote virtuel "local.dev" et utilise le port 3000 pour lancer le serveur</end><eol/>'
            );
    }

    public function execute($host, $port, $php)
    {
        try {
            $this->_startMsg();
            
            $this->_io->write("\n Serveur de développement en cours de démarrage \n");
            sleep(2.5);
            $this->_io->ok("\t => Le serveur a démarré avec succès. \n");
            sleep(2.5);
            $this->_io->writer()->colors("\t => <white>Ouvrez votre navigateur a l'adresse</end> <boldGreen><http://".$host.":".$port."></end><eol>");
            sleep(1.5);
            $this->_endMsg();

            $shell = new Shell($php . ' -S '. $host . ':' . $port . ' -t ' . escapeshellarg(dirname(\WEBROOT)));
            $shell->setOptions(dirname(\WEBROOT), null, 2.5)->execute()->isRunning();
            
            $shell->stop();
            $shell->kill();
        }
        catch(\Exception $e) { 

        }
        finally {
            return true;
        }
    }
}
