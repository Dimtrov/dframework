<?php 

namespace dFramework\core\cli;

use Ahc\Cli\Input\Command;
use Ahc\Cli\Output\Color;

class Server extends Command
{
    public function __construct()
    {
        parent::__construct('Server');

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

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($port)
    {
        $port = str_replace(['port=', 'p='], '', $port);
        $port = (empty($port) OR !is_numeric($port)) ? 3200 : intval($port);

        $io = $this->app()->io();
        $color = New Color;

        $io->write($color->info("\nServeur en cours de demarrage\n"), true);

        system('php -S localhost:'.$port);

        $io->write($color->ok("\nLe serveur a demarré. Veuillez ouvrir votre navigateur a l'adresse <http://localhost:".$port.">\n"), true);

        system('ECHO OFF');
    }
}