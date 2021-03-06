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

use dFramework\core\db\migration\Runner;
use dFramework\core\generator\Migration as GeneratorMigration;

/**
 * Migration
 * Database migration service
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.3.0
 * @file        /system/core/cli/Migration.php
 */
class Migration extends Cli
{
    /**
     * Creation of migration
     *
     * @return Command
     */
    protected function _make() : Command
    {
        return (new Command('migration:make', 'Créé un nouveau fichier de migration'))
            ->argument('<name>', 'Specifie lle nom du fichier de migration à créer.')
            ->option('--create', 'Nom de la table que l\'on souhaite créer')
            ->option('--modify', 'Nom de la table que l\'on souhaite migrer')
            ->usage('<bold>  dbot migration:make create_posts_table</end> <comment> => Cree un fichier de migration de la base de donnees dans le repertoire "/app/resources/database/migrations/" sous le nom "[timestamp]_create_posts_table.php"</end><eol/>')
            ->action(function($name, $create, $modify) {
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    $cli->start('Service de gestion de migrations de bases de données');
                    $cli->task('Création de migration en cours');

                    $generator = new GeneratorMigration();
                    if (!empty($modify)) 
                    {
                        $generator->doModify($modify);
                    }
                    else if (!empty($create)) 
                    {
                        $generator->doCreate($create);
                    }
                    $migration = $generator->generate($name);

                    sleep(1);
                    $cli->_io->ok("\tMigration créée: ".$migration);

                    $cli->end();
                } 
                catch (\Throwable $th) {
                    die($th->getMessage());
                }

                return true;
            });
    }

    /**
     * Launch migrations
     *
     * @return Command
     */
    protected function _up() : Command
    {
        return (new Command('migration:run', 'Demarre l\'execution des migrations'))
            ->usage('<bold>  dbot migration:run</end> <comment> => Exécute les méthodes up() des fichiers de migrations situés dans le repertoire "/app/resources/database/migrations/"</end><eol/>')
            ->action(function() {
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    $cli->start('Service de gestion de migrations de bases de données');
                    $cli->task('Execution des migration');

                    $cli->_io->write("\t=> Recherche de migrations en cours", true);
                    sleep(1);

                    $runner = Runner::instance();
                    $migrations = $runner->up();
                    if (empty($migrations)) 
                    {
                        $cli->_io->warn("\t=> Aucune migration trouvée");
                    }
                    else 
                    {
                        foreach ($migrations As $migration) 
                        {
                            $runner->launch($migration, 'up');
                            $cli->_io->ok("\t + Migration: ".$migration->name, true);
                        }
                    }

                    $cli->end();
                } 
                catch (\Throwable $th) {
                    die($th->getMessage());
                }
            });
    }

    /**
     * Rollback migrations
     *
     * @return Command
     */
    protected function _down() : Command
    {
        return (new Command('migration:rollback', 'Annulle les migrations'))
            ->usage('<bold>  dbot migration:rollback</end> <comment> => Exécute un rollback (méthodes down()) des fichiers de migrations situés dans le repertoire "/app/resources/database/migrations/"</end><eol/>')
            ->action(function() {
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    $cli->start('Service de gestion de migrations de bases de données');
                    $cli->task('Rollback des migrations');

                    $cli->_io->write("\t=> Recherche de migrations en cours", true);
                    sleep(1);

                    $runner = Runner::instance();
                    $migrations = $runner->down();
                    if (empty($migrations)) 
                    {
                        $cli->_io->warn("\t=> Aucune migration trouvée");
                    }
                    else 
                    {
                        foreach ($migrations As $migration) 
                        {
                            $runner->launch($migration, 'down');
                            $cli->_io->ok("\t + Migration: ".$migration->name, true);
                        }
                    }
                    
                    $cli->end();
                } 
                catch (\Throwable $th) {
                    die($th->getMessage());
                }
            });
    }
}
