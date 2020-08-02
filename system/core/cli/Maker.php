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
            ->option('--db', 'Spécifie la configuration de la base de données à utiliser. Par defaut il s\'agit de la configuration "default"', null, 'default')
            
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
                '<bold>  dbot make app rest</end> <comment> => Crée une REST-application de base en se servant des table de votre base de données</end><eol/>' 
            );
    }

    public function execute($element, $value, $db)
    {
        try {
            $this->_startMsg();
            
            $element = strtolower($element);
            if (!in_array($element, ['model', 'view', 'controller', 'entity', 'app']))
            {
                $this->_io->warn('Argument non pris en compte. Veuillez consulter la documentation pour plus de détails', true); 
                $this->showHelp();
            }
            else 
            {
                $element = '_'.$element;
                
                // $this->{$element}($value, $db);
                call_user_func_array([$this, $element], [$value, $db]);
            }
            
            $this->_endMsg();
        }
        catch(\Exception $e) { 

        }
        finally {
            return true;
        }
    }





    /**
     * Creation des modeles
     *
     * @param string|null $value
     * @param string|null $db
     * @return void
     */
    private function _model(?string $value = null, ?string $db = null)
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

        var_dump($tables);
    }
    /**
     * Creation des vues
     *
     * @param string|null $value
     * @param string|null $db
     * @return void
     */
    private function _view(?string $value = null, ?string $db = null)
    {

    }
    /**
     * Creation des controleurs
     *
     * @param string|null $value
     * @param string|null $db
     * @return void
     */
    private function _controller(?string $value = null, ?string $db = 'default')
    {

    }
    /**
     * Creation des entites
     *
     * @param string|null $value
     * @param string|null $db
     * @return void
     */
    private function _entity(?string $value = null, ?string $db = 'default')
    {

    }
    /**
     * Creation des application CRUD
     *
     * @param string|null $value
     * @param string|null $db
     * @return void
     */
    private function _app(?string $value = null, string $db = 'default')
    {

    }

    /**
     * Recupere toutes les tables de la base de donnees
     *
     * @param string|null $db
     * @return void
     */
    private function getAllTables(string $db = 'default')
    {
        return (new Query)->use($db)->query('SHOW TABLES')->fetchAll(\PDO::FETCH_NUM);     
    }
}
