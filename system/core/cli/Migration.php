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
        return (new Command('migration:run', 'Localise et exécute toutes les nouvelles migrations dans la base de données.'))
            ->usage('<bold>  dbot migration:run</end> <comment> => Exécute les méthodes up() des fichiers de migrations situés dans le repertoire "/app/resources/database/migrations/"</end><eol/>')
            ->action(function() {
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    $cli->start('Service de gestion de migrations de bases de données');
                    $cli->task('Execution des migration');
                    sleep(1);
                    
                    $runner = Runner::instance();
                    if (! $runner->latest())
                    {
                        return $cli->io->error('Migration failed!');
                    }

                    $messages = $runner->getMessages();
                    foreach ($messages as $message)
                    {
                        $cli->colorize($message);
                    }
                    $cli->io->ok('Done');
                    $cli->end();
                } 
                catch (\Throwable $th) {
                    $cli->showError($th);
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
        return (new Command('migration:rollback', 'Exécute la méthode « down » pour toutes les migrations du dernier lot.'))
            ->option('-b', 'Spécifie un lot à restaurer ; par exemple, « 3 » pour retourner au lot #3 ou « -2 » pour retourner deux fois')
            ->option('-f', 'Cette option vous permet de contourner la question de confirmation lors de l\'exécution de cette commande dans un environnement de production')
            ->usage('<bold>  dbot migration:rollback</end> <comment> => Exécute un rollback (méthodes down()) des fichiers de migrations situés dans le repertoire "/app/resources/database/migrations/"</end><eol/>')
            ->action(function($b, $f) {
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    $cli->start('Service de gestion de migrations de bases de données');
                    
                    if (empty($f) AND  $cli->io->choice('Are you sure you want to rollback?', ['y', 'n']) === 'n')
                    {
                        return;
                    }
                    $cli->task('Rollback des migrations');
                    sleep(1);

                    $runner = Runner::instance();
                    $batch = !empty($b) ? $b : $runner->getLastBatch() - 1;

                    $cli->io->warn('Rolling back migrations to batch: ' . $batch, true);

                    if (! $runner->regress($batch))
                    {
                        return $cli->io->error('Migration failed!');
                    }

                    $messages = $runner->getMessages();
                    foreach ($messages as $message)
                    {
                        $cli->colorize($message);
                    }
                    $cli->io->ok('Done');
                    $cli->end();
                } 
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }
}
