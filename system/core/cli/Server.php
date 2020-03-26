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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


namespace dFramework\core\cli;

use Ahc\Cli\Helper\Shell;
use Ahc\Cli\Input\Command;
use Ahc\Cli\Output\Writer;


/**
 * Server
 *
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.0
 * @file        /system/core/cli/Server.php
 */
class Server extends Command
{
    public function __construct()
    {
        parent::__construct('server', 'Service de lancement du serveur de developpemt');

        $this
            ->argument('[port]', 'Le port sur lequel vous souhaitez demarrer le serveur. 3200 par defaut', 3200)
            // Usage examples:
            ->usage(
                '<bold>  dbot Server</end> <comment>--Lance le serveur sur le port 3200 et y heberge votre application</end><eol/>' .
                '<bold>  dbot Server 8080</end> <comment>--Lance le serveur sur le port 8080 et y heberge votre application</end><eol/>' . 
                '<bold>  dbot Server port=8080</end> <comment>--Lance le serveur sur le port 8080 et y heberge votre application</end><eol/>' . 
                '<bold>  dbot Server p=8080</end> <comment>--Lance le serveur sur le port 8080 et y heberge votre application</end><eol/>'
            );
    }

    public function execute($port)
    {
        $port = str_replace(['port=', 'p='], '', $port);
        $port = (empty($port) OR !is_numeric($port)) ? 3200 : intval($port);
        try{
            $io = $this->app()->io();
            $writer = new Writer;

            $io->write("\n ---- Serveur en cours de démarrage ----", true);
            $writer->colors("\t <blue> Le serveur a démarré avec succès. </end><eol>\t  <white>Veuillez ouvrir votre navigateur a l'adresse</end> <boldGreen><http://localhost:".$port."></end><eol>");
            $writer->bold->colors("\n\t<bgGreen> dFramework v3.0.0 </end></eol>");
            
            $shell = new Shell('php -S localhost:'.$port.' -t public');
            $shell->setOptions(dirname(\WEBROOT), null, 10.5)->execute()->isRunning();
            
            $shell->stop();
            $shell->kill();
        }
        catch(\Exception $e) { }
        
        return true;
    }
}