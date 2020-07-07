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
 * @version     3.2
 */


namespace dFramework\core\cli;

use Ahc\Cli\Helper\Shell;
use Ahc\Cli\Input\Command;
use Ahc\Cli\Output\Writer;
use dFramework\core\dFramework;

/**
 * Maker
 * Launch the PHP development server
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.2
 * @file        /system/core/cli/Maker.php
 */
class Maker extends Command
{
    public function __construct()
    {
        parent::__construct('maker', 'Constructeur d\'application CRUD');

        $this
            ->option('--host', 'Hote sur lequel votre application sera lancée. "localhost" par defaut', null, 'localhost')
            ->option('--port', 'Port sur lequel vous souhaitnez demarrer le serveur. "3200" par defaut', null, 3200)
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
            $writer = new Writer;

            $this->app()->io()->write("\n ---- Serveur en cours de démarrage ----", true);
            $writer->colors("\t <blue> Le serveur a démarré avec succès. </end><eol>");
            $writer->colors("\t <white>Veuillez ouvrir votre navigateur a l'adresse</end> <boldGreen><http://".$host.":".$port."></end><eol>");
            $writer->bold->colors("\n\t<bgGreen> dFramework v".dFramework::VERSION." </end></eol>");
            
            $shell = new Shell($php . ' -S '. $host . ':' . $port . ' -t ' . escapeshellarg(\WEBROOT));
            $shell->setOptions(dirname(\WEBROOT), null, 10.5)->execute()->isRunning();
            
            $shell->stop();
            $shell->kill();
        }
        catch(\Exception $e) { }
        
        return true;
    }
}
