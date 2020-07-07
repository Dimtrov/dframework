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
 * @version     3.1
 */


namespace dFramework\core\cli;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use dFramework\core\db\Hydrator;
use dFramework\core\db\Query;
use dFramework\core\dFramework;
use dFramework\core\generator\Controller;
use dFramework\core\generator\Model;
use Exception;

/**
 * Entity
 *
 * A simple ORM service
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.1
 * @file        /system/core/cli/Entity.php
 */
class Entity extends Command
{
    private 
        $color,
        $io,
        $writer;


    public function __construct()
    {
        parent::__construct('entity', 'Service d\'hydratation des entites et de remplissage de base de donnees');

        $this->color = new Color;
        $this->io = new Interactor();
        $this->writer = new Writer();

        $this
            ->option('-c --create', 'Cree un ou plusieurs fichier d\'entité')
            ->option('-g --generate', 'Genere les fichiers d\'entité de toutes les tables de la base de donnees')
            ->option('-p --populate', 'Remplit une table de la base de donnees')
            ->argument('[database]', 'Specifie la configuration de la base de donnees a utiliser. Par defaut il s\'agit de la configuration "default"')
            // Usage examples:
            ->usage(
                '<bold>  dbot entity -c users</end> <comment> => Genere le fichier d\'entite "/app/entities/UsersEntity.php" faisant reference a la table users</end><eol/>' .
                '<bold>  dbot entity -c customers/bills</end> <comment> => Genere le fichier d\'entite "/app/entities/customers/BillsEntity.php" faisant reference a la table bills</end><eol/>' .
                '<bold>  dbot entity -c users+customers/bills</end> <comment> => Genere les fichiers d\'entite "/app/entities/UsersEntity.php" et "/app/entities/customers/BillsEntity.php" faisant reference aux tables users et bills respectivement</end><eol/>' .
                '<eol/>'.
                '<bold>  dbot entity -g</end> <comment> => Genere les fichiers d\'entite de toutes les tables de la base de donnees dans le dossier "/app/entities/"</end><eol/>' .
                '<eol/>'.
                '<bold>  dbot entity -p users</end> <comment> => Crée 5 enregistrements dans la table users</end><eol/>' .
                '<bold>  dbot entity -p bills:30</end> <comment> => Crée 30 enregistrements dans la table bills</end><eol/>' .
                '<bold>  dbot entity -p users+bills:30</end> <comment> => Crée 5 enregistrements dans la table users et 30 enregistrements dans la table bills</end><eol/>' 
            );
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io)
    {
        if (!$this->create AND !$this->generate AND !$this->populate)
        {
            echo $this->color->warn("Veuillez selectionner une option pour pouvoir executer cette tache. \n");
            $this->showHelp();
        }
    }
    public function execute()
    {
        try{
            if (!$this->generate)
            {
                $entry = trim($this->create ?? $this->populate);
                if (!empty($entry) AND $entry != '1')
                {
                    if($this->create) 
                    {
                        $this->create($entry);
                    }
                    else 
                    {
                        $this->populate($entry);
                    }
                }
                else 
                {
                    echo $this->color->warn("Syntaxe incorrect. Veuillez consulter la documentation. \n");
                    $this->showHelp();
                }
            }
            else 
            {
                $this->generate();
            }
        }
        catch(\Exception $e) { }
        
        return true;
    }


    private function create($entry)
    {
        $tables = explode('+', $entry);   
        $nbr_tables = count($tables);
        
        if ($nbr_tables > 1) 
        {
            $msg_entity = [
                'confirm' => '',
                'success' => $nbr_tables.' Entités créées avec succès',
            ];
            $msg_model = [
                'confirm' => 'Souhaitez-vous générer leurs modèles ?',
                'success' => $nbr_tables.' modèles générés avec succès',
            ];
            $msg_controller = [
                'confirm' => 'Souhaitez-vous générer les contrôlleurs associés ?',
                'success' => $nbr_tables.' contrôlleurs générés avec succès',
            ];
          //  echo $this->color->ok("\t\t ". \n");
        }
        else  
        {
            $msg_entity = [
                'confirm' => '',
                'success' => 'Entité créée avec succès',
            ];
            $msg_model = [
                'confirm' => 'Souhaitez-vous générer son modèle ?',
                'success' => 'Modèle généré avec succès',
            ];
            $msg_controller = [
                'confirm' => 'Souhaitez-vous générer le contrôlleurs associé ?',
                'success' => 'Contrôlleur généré avec succès',
            ];
        }
        
        foreach ($tables As $table) 
        {
            $this->associateEntity($table);
        }
        $this->io->write("\t --- Traitement terminé", true);
        echo $this->color->ok("\t\t ".$msg_entity['success']." \n");  
            
        if ($this->io->confirm($msg_model['confirm']))
        {
            foreach ($tables As $table)
            {
                $this->associateModel($table);
            }
            $this->io->write("\t --- Traitement terminé", true);
            echo $this->color->ok("\t\t ".$msg_model['success']." \n");
        }
                
        if ($this->io->confirm($msg_controller['confirm']))
        {
            $controller_type = [
                Controller::SIMPLE_CONTROLLER => 'Contrôlleur classic', 
                Controller::REST_CONTROLLER => 'Contrôlleur REST'
            ];
            $choice = $this->io->choice('Selectionnez le type de contrôlleur à générer', $controller_type, Controller::SIMPLE_CONTROLLER);
            foreach ($tables As $table)
            {
                $this->associateController($table, $choice);
            }
            $this->io->write("\t --- Traitement terminé", true);
            echo $this->color->ok("\t\t ".$msg_controller['success']." \n");
        }

        $this->writer->bold->colors("\n\t<bgGreen> dFramework v".dFramework::VERSION." </end></eol>");
    }

    private function associateEntity($table)
    {
        $table = explode('/', $table);
        if (count($table) == 1) 
        {
            $table = $table[0];
            $dirname = '';
        }
        else 
        {
            $tmp = array_pop($table);
            $dirname = \implode(\DS, $table).\DS;
            $table = $tmp;
        }
        try {
            Hydrator::makeEntityClass($table, $dirname);
        }
        catch(Exception $e) {}
    }
    private function associateModel($table)
    {
        $table = explode('/', $table);
        if (count($table) == 1) 
        {
            $table = $table[0];
            $dirname = '';
        }
        else 
        {
            $tmp = array_pop($table);
            $dirname = \implode(\DS, $table).\DS;
            $table = $tmp;
        }
        try {
            $model = new Model();
            $model->generate($table, $dirname);
        }
        catch(Exception $e) {}
    }
    private function associateController($table, $controller_type)
    {
        $table = explode('/', $table);
        if (count($table) == 1) 
        {
            $table = $table[0];
            $dirname = '';
        }
        else 
        {
            $tmp = array_pop($table);
            $dirname = \implode(\DS, $table).\DS;
            $table = $tmp;
        }
        try {
            $controller = new Controller();
            $controller->generate($table, $controller_type, $dirname);
        }
        catch(Exception $e) {}
    }



    private function populate($table)
    {
        echo $this->color->info("Fonctionnalite indisponible pour le moment \n");

        $this->writer->bold->colors("\n\t<bgGreen> dFramework v".dFramework::VERSION." </end></eol>");
    }

    private function generate()
    {
        try {
            $tables = (new Query)->use('default')->query('SHOW TABLES')->fetchAll(\PDO::FETCH_NUM);
            foreach ($tables As $key => $value) 
            {
                $tables[$key] = ['tables' => $value[0]];
            }
            echo $this->color->info("Votre base de données compte < ".count($tables)." > tables \n");
            $this->writer->table($tables);

            if ($this->io->confirm('Voullez-vous générer toutes les classes d\'entités correspondantes ?')) 
            {
                foreach ($tables As $key => $value) 
                {
                    $tables[$key] = $value['tables'];
                }
                $this->create(join('+', $tables));
            }
        }
        catch(Exception $e) {}
    }
}