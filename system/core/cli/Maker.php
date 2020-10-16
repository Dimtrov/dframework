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

use dFramework\core\db\Query;
use dFramework\core\generator\Controller;
use dFramework\core\generator\Entity;
use dFramework\core\generator\Model;
use Exception;
use Throwable;

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
    protected $_name = 'Maker';
    protected $_description = 'Service de construction d\'application CRUD';
     
    public function __construct()
    {
        parent::__construct();

        $this
            ->argument('<element>', 'Element à générer')
            ->argument('[value]', 'Valeur de l\'element')
            
            // Options
            ->option('--database | -db', 'Spécifie la configuration de la base de données à utiliser. Par defaut il s\'agit de la configuration "default"', null, 'default')
            ->option('--empty | -e', 'Precise que le contenu de éléments générés (implémentation des méthodes) devra être vide')
            ->option('--rest', 'Spécifie qu\'on souhaite créer une application REST de base')
            
            // Usage examples:
            ->usage(
                '<bold>  dbot make model</end> <comment> => Génère tous les modèles issus des tables de votre bases de données</end><eol/>' .
                '<bold>  dbot make model users</end> <comment> => Génère le modèle "UsersModel" à partir de la table "users" de votre base de données</end><eol/>' .
                
                '<bold>  dbot make view</end> <comment> => Génère les vues correspondantes au CRUD des tables de votre bases de données</end><eol/>' .
                '<bold>  dbot make view users</end> <comment> => Génère les vues de CRUD de la table "users" de votre base de données</end><eol/>' .
                
                '<bold>  dbot make controller</end> <comment> => Génère les contrôleurs à partir des tables de votre bases de données</end><eol/>' .
                '<bold>  dbot make controller users</end> <comment> => Génère le contrôleur "UsersController" à partir de la table "users" de votre base de données</end><eol/>' .
                
                '<bold>  dbot make entity</end> <comment> => Génère les fichiers d\'entité de toutes les tables de la base de données dans le dossier "/app/entities/"</end><eol/>' .
                '<bold>  dbot make entity users</end> <comment> => Génère le fichier d\'entité "/app/entities/UsersEntity.php" faisant référence à la table "users" de votre base de données</end><eol/>' .
                
                '<eol/>'.
                '<bold>  dbot make app</end> <comment> => Crée une application CRUD basique en générant les modèles, les vues, les contrôleurs et les entités correspondant aux différentes table de votre base de données</end><eol/>' .
                '<bold>  dbot make app users</end> <comment> => Crée le CRUD de base faisant référence à la table "users" de votre base de données</end><eol/>' 
            );
    }

    public function execute($element, $value, $database, $empty, $rest)
    {
        try {
            $this->_startMsg();
            
            $element = strtolower($element);
            if (!in_array($element, ['model', 'view', 'controller', 'entity', 'app']))
            {
                $this->_io->warn('Argument non pris en compte. Veuillez consulter la documentation pour plus de détails', true); 
                $this->showHelp();
            }
            $params = [$value, $database, !empty($empty), !empty($rest)];
            $element = '_'.$element;
            
            $this->{$element}(...$params);
            
            $this->_endMsg();
        }
        catch(\Exception | Throwable $e) { 
            throw $e;
        }
        finally {
            return true;
        }
    }


    /**
     * Recherche des modeles a creer
     *
     * @param string|null $value
     * @param string|null $database
     * @param bool $empty
     * @return void
     */
    private function _model(?string $value = null, ?string $database = null, bool $empty = false)
    {
        try {
            ['tables' => $tables, 'message' => $message] = $this->getTables($value, $database);
            
            $this->_makeModels($tables, $empty);
            
            if ($this->_io->confirm($message['entity']['confirm']))
            {
                $this->_makeEntities($tables, $empty);
            }
            
            if ($this->_io->confirm($message['controller']['confirm']))
            {
                $this->_makeControllers($tables, $empty);
            }
            
            if ($this->_io->confirm($message['view']['confirm']))
            {
                $this->_makeViews($tables, $empty);
            }
        }
        catch(Throwable | Exception $e) { throw $e; }
    }
    /**
     * Creation des modeles, $empty
     *
     * @param array $tables
     * @param bool $empty
     * @return void
     */
    private function _makeModels(array $tables, bool $empty = false)
    {
        $this->_io->write("\n Création des modèles en cours de réalisation \n");
        
        foreach ($tables As $table) 
        {
            $this->_associateModel($table, $empty);
        }

        $this->_io->writer()->colors("\t => <blue>Traitement terminé avec succès. </end><eol>");
        sleep(1.5);
        $this->_io->writer()->colors("\t => <boldGreen>".count($tables)."</end> <white>modèles générés avec succès</end><eol>"); 
    }
    /**
     * Generation d'un modele
     *
     * @param string $table
     * @param bool $empty
     * @return void
     */
    private function _associateModel(string $table, bool $empty = false)
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
            (new Model)->generate($table, $dirname);
        }
        catch(Throwable | Exception $e) { throw $e; }
    }
    
    /**
     * Recherche des vues a creer
     *
     * @param string|null $value
     * @param string|null $db
     * @param bool $empty
     * @return void
     */
    private function _view(?string $value = null, ?string $db = null, bool $empty = false)
    {
        try {
            ['tables' => $tables, 'message' => $message] = $this->getTables($value, $db);
            
            $this->_makeViews($tables, $empty);
            
            if ($this->_io->confirm($message['entity']['confirm']))
            {
                $this->_makeEntities($tables, $empty);
            }
            
            if ($this->_io->confirm($message['controller']['confirm']))
            {
                $this->_makeControllers($tables, $empty);
            }
            
            if ($this->_io->confirm($message['model']['confirm']))
            {
                $this->_makeModels($tables, $empty);
            }
        }
        catch(Throwable | Exception $e) { throw $e; }
    }
    /**
     * Creation des vues
     *
     * @param array $tables
     * @param bool $empty
     * @return void
     */
    private function _makeViews(array $tables, bool $empty = false)
    {
        $this->_io->write("\n Création des vues en cours de réalisation \n");
        
        foreach ($tables As $table) 
        {
            $this->_associateView($table, $empty);
        }

        $this->_io->writer()->colors("\t => <blue>Traitement terminé avec succès. </end><eol>");
        sleep(1.5);
        $this->_io->writer()->colors("\t => <boldGreen>".count($tables)."</end> <white>vues générées avec succès</end><eol>"); 
    }
    /**
     * Generation d'une vue
     *
     * @param string $table
     * @param bool $empty
     * @return void
     */
    private function _associateView(string $table, bool $empty = false)
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
            (new Model)->generate($table, $dirname);
        }
        catch(Throwable | Exception $e) { throw $e; }
    }
    
    /**
     * Recherche des controleurs a creer
     *
     * @param string|null $value
     * @param string|null $db
     * @param bool $empty
     * @return void
     */
    private function _controller(?string $value = null, ?string $db = null, bool $empty = false)
    {
        try {
            ['tables' => $tables, 'message' => $message] = $this->getTables($value, $db);
            
            $this->_makeControllers($tables, $empty);
            
            if ($this->_io->confirm($message['model']['confirm']))
            {
                $this->_makeModels($tables, $empty);
            }

            if ($this->_io->confirm($message['entity']['confirm']))
            {
                $this->_makeEntities($tables, $empty);
            }
            
            if ($this->_io->confirm($message['view']['confirm']))
            {
                $this->_makeViews($tables, $empty);
            }
        }
        catch(Throwable | Exception $e) { throw $e; }
    }
    /**
     * Creation des controleurs
     *
     * @param array $tables
     * @param bool $empty
     * @return void
     */
    private function _makeControllers(array $tables, bool $empty = false)
    {
        $controller_type = [
            Controller::SIMPLE_CONTROLLER => 'Contrôleurs classic', 
            Controller::REST_CONTROLLER => 'Contrôleurs REST'
        ];
        $choice = $this->_io->choice('Selectionnez le type de contrôleur à générer', $controller_type, Controller::SIMPLE_CONTROLLER);
        
        $this->_io->write("\n Création des ".strtolower($controller_type[$choice])." en cours de réalisation \n");
        
        foreach ($tables As $table) 
        {
            $this->_associateController($table, $choice, $empty);
        }

        $this->_io->writer()->colors("\t => <blue>Traitement terminé avec succès. </end><eol>");
        sleep(1.5);
        $this->_io->writer()->colors("\t => <boldGreen>".count($tables)."</end> <white>".strtolower($controller_type[$choice])." générés avec succès</end><eol>"); 
    }
    /**
     * Generation d'un controleur
     *
     * @param string $table
     * @param int $type
     * @param bool $empty
     * @return void
     */
    private function _associateController(string $table, int $type, bool $empty = false)
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
            (new Controller)->generate($table, $type, $dirname);
        }
        catch(Throwable | Exception $e) { throw $e; }
    }

    /**
     * Recherche des entites a creer
     *
     * @param string|null $value
     * @param string|null $db
     * @param bool $empty
     * @return void
     */
    private function _entity(?string $value = null, ?string $db = null, bool $empty = false)
    {
        try {
            ['tables' => $tables, 'message' => $message] = $this->getTables($value, $db);
            
            $this->_makeEntities($tables, $empty);
            
            if ($this->_io->confirm($message['model']['confirm']))
            {
                $this->_makeModels($tables, $empty);
            }
            
            if ($this->_io->confirm($message['controller']['confirm']))
            {
                $this->_makeControllers($tables, $empty);
            }
            
            if ($this->_io->confirm($message['view']['confirm']))
            {
                $this->_makeViews($tables, $empty);
            }
        }
        catch(Throwable | Exception $e) { die($e); }
    }
    /**
     * Creation des entites
     *
     * @param array $tables
     * @param bool $empty
     * @return void
     */
    private function _makeEntities(array $tables, bool $empty = false)
    {
        $this->_io->write("\n Création des entités en cours de réalisation \n");
        
        foreach ($tables As $table) 
        {
            $this->_associateEntity($table, $empty);
        }

        $this->_io->writer()->colors("\t => <blue>Traitement terminé avec succès. </end><eol>");
        sleep(1.5);
        $this->_io->writer()->colors("\t => <boldGreen>".count($tables)."</end> <white>entités générées avec succès</end><eol>"); 
    }
    /**
     * Generation d'une entite
     *
     * @param string $table
     * @param bool $empty
     * @return void
     */
    private function _associateEntity(string $table, bool $empty = false)
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
            (new Entity)->generate($table, $dirname);
        }
        catch(Throwable | Exception $e) { throw $e; }
    }


    /**
     * Creation des application CRUD
     *
     * @param string|null $value
     * @param string|null $db
     * @param bool $empty
     * @param bool $rest
     * @return void
     */
    private function _app(?string $value = null, string $db = 'default', bool $empty = false, bool $rest = false)
    {
        try {
            ['tables' => $tables] = $this->getTables($value, $db);
                    
            $this->_io->write("\n Génération de l'application CRUD en cours de réalisation \n");
    
            $nbr_fichiers = 0;
            $nbr_tables = count($tables);

            $this->_io->write("-> Modèles en cours de création \n");
            foreach ($tables As $table) 
            {
                $this->_associateModel($table, $empty);
                $nbr_fichiers++;
            }
            $this->_io->writer()->colors("-> <boldGreen>".$nbr_tables."</end> <white>modèles créés avec succès\n </end><eol>"); 
    
            $this->_io->write("-> Vues en cours de création \n");
            foreach ($tables As $table) 
            {
                $this->_associateView($table, $empty);
                $nbr_fichiers += 4;
            }
            $this->_io->writer()->colors("-> <boldGreen>".$nbr_tables."</end> <white>vues créées avec succès\n </end><eol>"); 
            
            $this->_io->write("-> Contrôleurs en cours de création \n");
            foreach ($tables As $table) 
            {
                $this->_associateController($table, ($rest ? Controller::REST_CONTROLLER : Controller::SIMPLE_CONTROLLER), $empty);
                $nbr_fichiers++;
            }
            $this->_io->writer()->colors("-> <boldGreen>".$nbr_tables."</end> <white>contrôleurs ".($rest ? 'REST' : '')." crées avec succès\n </end><eol>"); 
            
            
            $this->_io->write("-> Entités en cours de création \n");
            foreach ($tables As $table) 
            {
                $this->_associateEntity($table, $empty);
                $nbr_fichiers++;
            }
            $this->_io->writer()->colors("-> <boldGreen>".$nbr_tables."</end> <white>entités créées avec succès\n </end><eol>"); 
           
            
            $this->_io->writer()->colors("\t => <blue>Traitement terminé avec succès. </end><eol>");
            sleep(1.5);
            $this->_io->writer()->colors("\t => <boldGreen>".$nbr_fichiers."</end> <white> fichiers de modèles, vues, contrôleurs et entités générés avec succès</end><eol>"); 
        
        }
        catch(Throwable | Exception $e) { throw $e; }
    }


    /**
     * Undocumented function
     *
     * @param string|null $value
     * @param string $db
     * @return array
     */
    private function getTables(?string $value, ?string $db = 'default') : array
    {
        $make_all = empty($value);
        
        if (!$make_all)
        {
            $tables = explode('|', $value);
        }
        else 
        {
            $tables = $this->getAllTables($db);
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
    /**
     * Recupere toutes les tables de la base de donnees
     *
     * @param string|null $db
     * @return array
     */
    private function getAllTables(?string $db = 'default') : array
    {
        $tables = (new Query)->use(empty($db) ? 'default' : strtolower($db))->query('SHOW TABLES')->fetchAll(\PDO::FETCH_NUM); 
        
        foreach ($tables As $key => $value) 
        {
            $tables[$key] = $value[0];
        }    

        return $tables;
    }
}
