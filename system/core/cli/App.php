<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\cli;

use Ahc\Cli\Helper\Shell;

/**
 * App
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.3.0
 * @file        /system/core/cli/App.php
 */
class App extends Cli
{

    /**
     * Serveur de developpement
     *
     * @return Command
     */
    protected function _serve() : Command
    {
        return (new Command('serve', 'Demarre le serveur de developpement.'))
            ->option('--host', 'Hote sur lequel votre application sera lancée. "localhost" par defaut', null, 'localhost')
            ->option('--port', 'Port sur lequel vous souhaitez demarrer le serveur. "3200" par defaut', null, 3200)
            ->option('--php', 'Chemin vers l\'executable php à utiliser pour démarrer le serveur.', null, PHP_BINARY)
            ->action(function($host, $port, $php) {
                /**
                 * @var Command
                 */
				$cli = $this;

                self::runServe($cli, $host, $port, $php);
            });
    }
	private static function runServe(Command $cli, string $host = 'localhost', int $port = 3200, string $php = PHP_BINARY)
	{
		try {
			$cli->start('Service de lancement du serveur de developpement');
			$cli->task('Demarrage du serveur de developpement');

			sleep(2);
			$cli->io->ok("\t => Le serveur a démarré avec succès.", true);

			sleep(2);
			$cli->io->writer()->colors("\t => <white>Ouvrez votre navigateur a l'adresse</end> <boldGreen><http://".$host.":".$port."></end>");

			sleep(1);
			$cli->end();

			$shell = new Shell($php . ' -S '. $host . ':' . $port . ' -t ' . escapeshellarg(\WEBROOT));
			$shell->setOptions(ROOTPATH, null, 2.5)->execute()->isRunning();

			$shell->stop();
			$shell->kill();
		}
		catch (\Throwable $th) {
			$cli->showError($th);
		}
	}

    /**
     * Liste des routes
     *
     * @return Command
     */
    protected function _routes() : Command
    {
        return (new Command('routes:list', 'Affiche tous les itinéraires définis par l\'utilisateur.'))
            ->action(function() {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de gestion de l\'application');
                    $cli->task("Recherche des routes");
                    $cli->io->write("\n");

                    sleep(1.75);

                    require_once APP_DIR . 'config' . DS . 'routes.php';
                    $collection = $routes;
                    $methods    = [
                        'get',
                        'head',
                        'post',
                        'patch',
                        'put',
                        'delete',
                        'options',
                        'trace',
                        'connect',
                        'cli',
                    ];
					$nbr_routes = [
                        'get'     => 0,
                        'head'    => 0,
                        'post'    => 0,
                        'patch'   => 0,
                        'put'     => 0,
                        'delete'  => 0,
                        'options' => 0,
                        'trace'   => 0,
                        'connect' => 0,
                        'cli'     => 0,
                    ];
                    $table = [];

					foreach ($methods as $method)
                    {
                        $routes = $collection->getRoutes($method, true);

                        foreach ($routes as $route => $handler)
                        {
                            $tab = [
                                'Method' => strtoupper($method),
                                'Route' => $route,
                                'Name' => '',
                                'Handler' => ''
                            ];
                            if (is_string($handler))
                            {
                                $tab['Handler'] = $handler;
                            }
                            if (is_array($handler))
                            {
                                $tab['Handler'] = is_string($handler['handler']) ? $handler['handler'] : 'Closure';
                                $tab['Name'] = $handler['name'];
                            }

                            $table[] = $tab;

							$nbr_routes[$method]++;
                        }
                    }

					$total_routes = array_sum($nbr_routes);

					$cli->io->writer()->write("\t ==> ")->boldGreen($total_routes)->write(" routes trouvées <== \n", true);
                    $cli->io->table($table);

					$cli->io->writer()->write("\n ==> ")->boldGreen($total_routes)->write(" routes trouvées <== \n", true);
					foreach ($nbr_routes As $key => $value)
					{
						$cli->io->writer()->write("======> " . strtoupper($key) . ': ')->boldGreen($value)->write(" routes", true);
					}

					$cli->end();
                }
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }

	/**
	 * Initialise l'application
	 *
	 * @return Command
	 */
	protected function _initialize() : Command
	{
		return (new Command('initialize', 'Initialise les paramètres de l\'application.'))
		->action(function() {
			/**
			 * @var Command
			 */
			$cli = $this;
			try {
				$cli->start('Service de gestion de l\'application');
				$cli->task("Initialisation de l'application");

				sleep(2);
				$cli->io->write("\nMerci d'avoir choisir dFramework pour la réalisation de votre projet. Nous alons à présent initialiser votre application", true);

				$data = [
					'ENVIRONMENT' => 'dev',
					'app.encryptionKey' => md5(uniqid())
				];

				$data['app.appName'] = $cli->io->prompt("\nEntrez le nom de votre application", 'My dFramework App');
				$data['app.baseUrl'] = $cli->io->prompt("\nEntrez l'URL de base", 'http://localhost:3200');

				if ($cli->io->confirm("\nSouhaitez-vous ajouter une base de données ?", 'y'))
				{
					$dirvers = ['a' => 'PDO MySQL', 'b' => 'MySQLi', 'c' => 'PDO PgSQL', 'd' => 'PDO SQLite'];

					$data['db.connection'] = 'default';
					$data['db.default.driver'] = strtolower(str_replace(' ', '', $dirvers[$cli->io->choice("\nQuel pilote utilisez vous pour communiquer avec la base de données", $dirvers, 'a')]));
					$data['db.default.hostname'] = $cli->io->prompt("\nEntrez l'hôte de votre base de données", 'localhost');
					$data['db.default.database'] = $cli->io->prompt("\nEntrez le nom de votre base de données", 'test');
					$data['db.default.username'] = $cli->io->prompt("\nEntrez votre nom d'utilisateur", 'root');
					$data['db.default.password'] = $cli->io->prompt("\nEntrez votre mot de passe", 'root');
				}

				$values = '';
				foreach ($data As $k => $v)
				{
					$values .= $k . " = '" .$v . "'\n\n";
				}

				file_put_contents(ROOTPATH . '.env', $values);

				$cli->io->ok("\n\n ************** Initialisation effectuée avec succès ****************** \n");

				if ($cli->io->confirm("\nSouhaitez-vous lancer le serveur de développement maintenant ?", 'y'))
				{
					$cli->io->write("\n-------------------------------------------------------------------------\n");
					self::runServe($cli);
				}
				else
				{
					$cli->end();
				}
			}
			catch (\Throwable $th) {
				$cli->showError($th);
			}
		});
	}
}
