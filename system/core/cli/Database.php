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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.4.0
 */

namespace dFramework\core\cli;

use dFramework\core\db\Database as DbDatabase;
use dFramework\core\db\Dumper;
use dFramework\core\db\Seeder;
use dFramework\core\loader\Filesystem;
use dFramework\core\loader\Injector;
use dFramework\core\utilities\Date;
use dFramework\core\utilities\Str;

/**
 * Database
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.3.0
 * @file        /system/core/cli/Database.php
 */
class Database extends Cli
{
    /**
     * Remplissage de base de donnees
     *
     * @return Command
     */
    protected function _seed() : Command
    {
		return (new Command('db:seed', 'Exécute le seeder spécifié pour remplir les données connues dans la base de données.'))
            ->argument('[file]', 'Nom du fichier à seeder')
            ->action(function($file) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de remplissage de base de donnees');
                    $cli->task('Demarrage du seed');

					$queue = [];
					if (!empty($file))
					{
						$seed = Str::toPascal($file);
						$file = DB_SEED_DIR . $seed.'.php';

						if (!file_exists($file))
						{
							$cli->io->error('Impossible de demarrer le remplissage car le fichier "'.$file.'" n\'existe pas', true);
							return $cli->showHelp();
						}
						$queue[$seed] = $file;
					}
					else
					{
						/**
						 * @var \Symfony\Component\Finder\SplFileInfo[]
						 */
						$files = Filesystem::files(DB_SEED_DIR);
						foreach ($files As $file)
						{
							if ($file->getExtension() == 'php')
							{
								$queue[$file->getFilenameWithoutExtension()] = $file->getPathname();
							}
						}
					}

					foreach ($queue As $seed => $file)
					{
						self::execSeed($cli, $seed, $file);
					}

					$cli->io->ok("\t => Remplissage terminé avec succès. \n");
                    sleep(1.5);
                    $cli->end();
                }
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }

    /**
     * Import/export de base de donnees
     *
     * @return Command
     */
    protected function _dump() : Command
    {
        return (new Command('db:dump', 'Demarre l\'importation ou l\'exportation de votre base de données'))
            ->option('-e --export', 'Cree une sauvegarde de la base de donnees')
            ->option('-i --import', 'Importe un script de base de donnees')
            ->argument('[ver]', 'Spécifie la version du fichier de script à utiliser pour restaurer la base de données. Si aucun fichier n\'est spécifié, une liste au choix sera proposée')
            ->argument('[database]', 'Spécifie la configuration de la base de donnees a utiliser. Par defaut il s\'agit de la configuration "default"', 'default')
            ->action(function($export, $import, $ver, $database) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    if (empty($export) AND empty($import))
                    {
                        $cli->io->warn("\n Veuillez selectionner une option pour pouvoir executer cette tache.", true);
                        return $cli->showHelp();
                    }
                    $cli->start('Service d\'import/export de base de donnees');

                    $dump = new Dumper($database);

                    if (!empty($export))
                    {
                        $cli->task('Sauvegarde de la base de données');
                        $num_ver = $cli->io->prompt("\nVeuillez entrer le numero de la version de votre base de donnee", date('Y-m-d'));

                        $filename = $dump->export($num_ver);

                        $cli->io->ok("\n\t Base de donnees sauvegardée avec succès.", true);
                        $cli->io->info("\t Fichier de sauvegarde: ".$filename);
                    }
                    else
                    {
                        if (empty($ver))
						{
							$table = [];

							/**
							 * @var \Symfony\Component\Finder\SplFileInfo[]
							 */
							$files = Filesystem::files(DB_DUMP_DIR, false, 'changedTime');
							$files = array_reverse($files);

							foreach ($files As $file)
							{
								if ($file->getExtension() == 'sql')
								{
									$filename = $file->getFilenameWithoutExtension();
									$tmp = explode('version_', $filename);

									$table[] = [
										'version'    => array_pop($tmp),
										'updated_at' => Date::createFromTimestamp($file->getMTime())->format('d M Y - H:i:s'),
										'filename'   => $file->getPathname(),
									];
								}
							}

							if (empty($table))
							{
								$cli->io->warn('Aucun fichier de sauvegarde trouvé');
								return $cli->end();
							}

							$cli->io->white('Liste des sauvegardes disponibles', true);
							$cli->io->table($table);

							$ver = $cli->io->prompt("\nVeuillez entrer le numero de la version de votre base de donnee", $table[0]['version']);
						}

						sleep(1);
						$cli->task('Importation de la base de données en cours');

                        $filename = $dump->import($ver);

                        $cli->io->ok("\n\t Base de donnees migrée avec succès.", true);
                        $cli->io->info("\t Fichier utilisé: ".$filename);
                    }

                    $cli->end();
                }
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }

	/**
	 * Creation de la base de donnees
	 *
	 * @return Command
	 */
	protected function _create() : Command
	{
		return (new Command('db:create', "Crée la base de données si elle n'existe pas encore"))
            ->argument('[dbname]', 'Spécifie un nom de base de données différent de celui défini dans le fichier de configuration')
            ->argument('[database]', 'Spécifie la configuration de la base de donnees a utiliser. Par defaut il s\'agit de la configuration "default"', 'default')
            ->action(function($dbname, $database) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de d\'import/export de base de donnees');

					if (empty($dbname))
					{
						$dbname = config('database.'.$database.'.database');
					}
					if (empty($dbname))
					{
						$cli->io->error("Aucune base de donnees n'a étée définie", true);
						return $cli->showHelp();
					}

					$cli->task('Création de la base de données en cours');
					sleep(1);

					try {
						DbDatabase::connect($database)->withoutDatabase()->createDatabase($dbname);

						$cli->io->ok("\n\t Base de données créée avec succès.", true);
                        $cli->io->info("\t Base de données: ".$dbname);
					} catch (\Throwable $th) {
						$cli->io->warn("\nNous n'avons pas pu créer la base de données. Vous pouvez essayer de le faire manuellement", true);
						$cli->io->error($th->getMessage());
						return;
					}

					$cli->end();
                }
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }

	/**
	 * Execute le seed d'un fichier
	 *
	 * @param Command $cli
	 * @param string $seed
	 * @param string $file
	 */
	private static function execSeed(Command $cli, string $seed, string $file)
	{
		require_once $file;

        if (!class_exists($seed))
		{
			$cli->io->error('Impossible de demarrer le remplissage car le fichier "'.$file.'" ne contient pas de classe "'.$seed.'"', true);
			$cli->showHelp();
			exit;
		}

		$class = Injector::get($seed);
		if (!($class instanceof Seeder))
		{
			$cli->io->error('Impossible d\'effectuer le remplissage car la classe "'.$seed.'" n\'est pas une instance de "'.Seeder::class.'"', true);
			$cli->showHelp();
			exit;
		}

		$cli->io->yellow("\n\t Remplissage en cours de traitement : Utilisation de la classe < ".$seed." > \n");
		sleep(2.5);
		Injector::call([$class, 'seed'])->run();
		sleep(2);
	}
}
