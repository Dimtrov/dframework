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

use dFramework\core\db\Database;
use dFramework\core\generator\Controller;
use dFramework\core\generator\Entity;
use dFramework\core\generator\Model;

/**
 * Maker
 * Generate many files to create a minimal CRUD application
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/
 * @since       3.2.1
 * @file        /system/core/cli/Maker.php
 */
class Maker extends Cli
{
   /**
     * Genere des controleurs
     *
     * @return Command
     */
    protected function _controller() : Command
    {
        return (new Command('make:controller', 'Crée un nouveau controleur'))
            ->argument('<name>', 'Definie le nom du contrôleur à créer.')
            ->option('--resource', 'Spécifie si on souhaite créer un contrôleur REST ou pas')
            ->option('--presenter', 'Spécifie si on souhaite créer un contrôleur de présentation ou pas')
            ->option('--path', 'Défini le répertoire de stockage du contrôleur créé')
            ->action(function($name, $resource, $presenter, $path) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de construction d\'application');
                    $cli->task('Création de contrôleur');
                    sleep(1);

                    $generator = new Controller($resource, $presenter);
                    $class_name = $generator->generate($name, $path);

                    $cli->io->ok('Contrôleur créé avec succès: '.$class_name);
                    $cli->end();
                }
                catch(\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }

    /**
     * Genere des classes d'entites
     *
     * @return Command
     */
    protected function _entity() : Command
    {
        return (new Command('make:entity', 'Crée une nouvelle classe d\'entité'))
            ->argument('<name>', 'Definie le nom de l\'entité à créer.')
            ->option('--path', 'Défini le répertoire de stockage de l\'entité créée')
            ->option('--empty', 'Spécifie qu\'on veut avoir un fichier vide')
            ->action(function($name, $path, $empty) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de construction d\'application');
                    $cli->task('Création d\'entité');
                    sleep(1);

                    $generator = new Entity($empty);
                    $class_name = $generator->generate($name, $path);

                    $cli->io->ok('Entité créée avec succès: '.$class_name);
                    $cli->end();
                }
                catch(\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }

    /**
     * Genere des modeles
     *
     * @return Command
     */
    protected function _model() : Command
    {
        return (new Command('make:model', 'Crée un nouveau modèle'))
            ->argument('<name>', 'Definie le nom du modèle à créer.')
            ->option('--path', 'Défini le répertoire de stockage du modèle créé')
            ->option('--empty', 'Spécifie qu\'on veut avoir un fichier vide')
            ->action(function($name, $path, $empty) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de construction d\'application');
                    $cli->task('Création des modèle');
                    sleep(1);

                    $generator = new Model($empty);
                    $class_name = $generator->generate($name, $path);

                    $cli->io->ok('Modèle créé avec succès: ' . $class_name);
                    $cli->end();
                }
                catch(\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }


	/**
	 * Genere une application CRUD
	 *
	 * @return Command
	 */
	protected function _app(): Command
	{
		$self = $this;

		return (new Command('make:app', 'Crée une application skelete avec les table de la base de donnees'))
			->option('--only', 'Défini le répertoire de stockage du modèle créé')
			->option('--except', 'Défini le répertoire de stockage du modèle créé')
			->option('--tables', 'Spécifie qu\'on veut avoir un fichier vide')
			->option('--resource', 'Spécifie si on souhaite créer un contrôleur REST ou pas')
			->action(function($only, $except, $tables, $resource) use($self) {

				$generate = !empty($only)
					? explode(',', (string)$only) :
					['controllers', 'models', 'views', 'entities'];

				if (!empty($except))
				{
					$except = explode(',', $except);
					$c = count($generate);
					for ($i = 0; $i < $c; $i ++)
					{
						if (in_array($generate[$i], $except))
						{
							unset($generate[$i]);
						}
					}
				}

				/**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de construction d\'application');
                    $cli->task('Génération de l\'application CRUD en cours');
                    sleep(1);

					['tables' => $tables] = $self->getTables($tables);


					$models = [];
					if (in_array('models', $generate))
					{
						$cli->io->writer()->write("\n -> Modèles en cours de création \n");

						$generator = new Model();

						foreach ($tables As $table)
						{
							$models[] = $generator->generate($table);
						}
					}

					$views = [];
					if (in_array('views', $generate))
					{
						$cli->io->writer()->write("\n -> Vues en cours de création \n");

						/* $generator = new Model();

						foreach ($tables As $table)
						{
							$views[] = $generator->generate($table);
						} */
					}

					$entities = [];
					if (in_array('entities', $generate))
					{
						$cli->io->writer()->write("\n -> Entites en cours de création \n");

						$generator = new Entity();

						foreach ($tables As $table)
						{
							$entities[] = $generator->generate($table);
						}
					}

					$controllers = [];
					if (in_array('controllers', $generate))
					{
						$cli->io->writer()->write("\n -> Controleurs en cours de création \n");

						$generator = new Controller($resource, 'presenter');

						foreach ($tables As $table)
						{
							$controllers[] = $generator->generate($table);
						}
					}

					$cli->io->writer()->write("\n ******************************************** \n");
                    $cli->io->ok('Creation terminée avec succès:', true);

                    $cli->io->yellow(count($controllers) . ' Controleurs:', true);
					foreach ($controllers as $item) {
						$cli->io->writer()->write(" -->" . $item, true);
					}

                    $cli->io->yellow(count($models) . ' Modeles:', true);
					foreach ($models as $item) {
						$cli->io->writer()->write(" --> " . $item, true);
					}

                    $cli->io->yellow(count($entities) . ' Entites:', true);
					foreach ($entities as $item) {
						$cli->io->writer()->write(" --> " . $item, true);
					}

                    $cli->io->yellow(count($views) . ' Vues:', true);
					foreach ($views as $item) {
						$cli->io->writer()->write(" --> " . $item, true);
					}

					$stats = [
						'views'       => count($views),
						'models'      => count($models),
						'entities'    => count($entities),
						'controllers' => count($controllers),
					];
					$cli->io->table([
						array_merge($stats, [
							'total' => array_sum(array_values($stats))
						])
					]);

                    $cli->end();
                }
                catch(\Throwable $th) {
                    $cli->showError($th);
                }

			});
	}


	/**
     * Recupere les tables avec lesquelles on va travailler
     *
     * @param string|null $value
     * @return array
     */
    private function getTables(?string $value) : array
    {
        $make_all = empty($value);

        if (!$make_all)
        {
            $tables = explode('|', $value);
        }
        else
        {
            $tables = Database::instance()->connection()->listTables();
        }

        $nbr_tables = count($tables);

        $message = [];
        if ($nbr_tables > 1)
        {
            $message['model'] = [
                'confirm' => 'Souhaitez-vous générer les modèles associés ?',
                'success' => $nbr_tables.' modèles générés avec succès',
            ];
            $message['view'] = [
                'confirm' => 'Souhaitez-vous générer les vues associées ?',
                'success' => $nbr_tables.' vues générées avec succès',
            ];
            $message['controller'] = [
                'confirm' => 'Souhaitez-vous générer les contrôleurs associés ?',
                'success' => $nbr_tables.' contrôlleurs générés avec succès',
            ];
            $message['entity'] = [
                'confirm' => 'Souhaitez-vous générer les entités associées ?',
                'success' => $nbr_tables.' entités créées avec succès',
            ];
        }
        else
        {
            $message['model'] = [
                'confirm' => 'Souhaitez-vous générer son modèle ?',
                'success' => 'Modèle généré avec succès',
            ];
            $message['view'] = [
                'confirm' => 'Souhaitez-vous générer sa vue ?',
                'success' => 'Vue générée avec succès',
            ];
            $message['controller'] = [
                'confirm' => 'Souhaitez-vous générer son contrôleur ?',
                'success' => 'Contrôlleur généré avec succès',
            ];
            $message['entity'] = [
                'confirm' => 'Souhaitez-vous générer sa classe d\'entité',
                'success' => 'Entité créée avec succès',
            ];
        }

        return compact('tables', 'message');
    }
}
