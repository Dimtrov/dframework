<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package     dFramework
 * @author      Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright   Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright   Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license     https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link        https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.4
 */

namespace dFramework\core\generator;

use RuntimeException;
use dFramework\core\dFramework;
use Nette\PhpGenerator\ClassType;
use dFramework\core\utilities\Str;
use Nette\PhpGenerator\PhpNamespace;
use dFramework\core\db\Database;
use dFramework\core\Entity as CoreEntity;
use dFramework\core\support\traits\CliMessage;

/**
 * generator\Entity
 *
 * Generate a file for entity class
 *
 * @package     dFramework
 * @subpackage  Core
 * @category    Generator
 * @author      Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link        https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.1
 * @file        /system/core/generator/Entity.php
 */
final class Entity
{
    use CliMessage;

    /**
     * @var bool Specifie si le fichier doit etre vide ou pas
     */
    private $empty = false;
    /**
     * @var string Classe a generer
     */
    private $class = '';
    /**
     * @var string Nom complet de la classe généré (namespace y compris)
     */
    private $full_class_name = '';
    /**
     * @var string Dossier de sauvegarde
     */
    private $dir = '';


    /**
     * Constructor
     *
     * @param mixed $empty
     */
    public function __construct($empty = null)
    {
        if (!empty($empty))
        {
            $this->empty = true;
        }
        helper('inflector');
    }


    /**
     * Demarre la generation
     *
     * @param string $class
     * @param string $dir
     * @return string
     */
    public function generate(string $class, ?string $dir = \ENTITY_DIR) : string
    {
        $dir = empty($dir) ? \ENTITY_DIR : $dir;
        $this->class = preg_replace('#Entity$#i', '', $class);

        $this->writeProperties($dir, $render);
        $this->createFile($render);

        return $this->full_class_name;
    }

    /**
     * Ecrit les proprietes de la classe, les getters et les setters
     *
     * @param string $class
     * @param array $properties
     * @param $render
     */
    protected function writeProperties($dir, &$render)
    {
        $dir = str_replace(\ENTITY_DIR, '', $dir);
        $dir = trim($dir, '/\\');
        $dir = str_replace(['/', '\\'], '/', $dir);
        $dir = rtrim($dir, '/');

        if (!empty($dir))
        {
            $namespace = new PhpNamespace(str_replace('/', '\\', $dir));
        }
        $this->dir = $dir . (empty($dir) ? '' : '/');

        $class_name = Str::toPascalCase(singularize($this->class)).'Entity';
        $generator = (new ClassType($class_name, $namespace ?? null))
		->addComment($class_name."\n")
		->addComment('Generated by dFramework v'.dFramework::VERSION)
		->addComment('Date : '.date('r'))
		->addComment('PHP Version : '.phpversion())
		->addComment('Entity : '.preg_replace("#Entity$#", '', $class_name))
		->setExtends(CoreEntity::class);

        if (false === $this->empty)
        {
			$table = Str::toSnake($this->class);

			if (Database::tableExist($table))
            {
				$this->makeProperties($generator, $table);
            }
            else if (Database::tableExist(pluralize($table)))
            {
				$this->makeProperties($generator, pluralize($table));
            }
        }
        $this->full_class_name = implode('\\', [$namespace ?? '', $class_name]);

        $render = (string) $generator;
    }

	/**
	 * Ajoute les differentes proprietés de la classe
	 *
	 * @param ClassType $generator
	 * @param string $table
	 */
    private function makeProperties(ClassType &$generator, string $table)
    {
        $generator->addProperty('table', $table)->setProtected()->addComment('@var string Table a utiliser');

        $pk = Database::indexes($table, 'PRIMARY');
        $generator->addProperty('primaryKey', $pk->fields[0] ?? singular('id_'.$table))->setProtected()->addComment('@var string Cle primaire de la table');

        $generator->addProperty('columns', Database::columnsName($table))->setProtected()->addComment('@var array colonnes de l\'entité');
    }

    /**
     * Enregistre le code de la classe dans le fichiers
     *
     * @param $render
     */
    protected function createFile($render)
    {
        $class_name = Str::toPascal(singularize($this->class)).'Entity';

        $dir = str_replace(\ENTITY_DIR, '', $this->dir);
        $dir = ENTITY_DIR.trim($dir, '/\\');
        $dir = str_replace(['/', '\\'], DS, $dir);
        $dir = rtrim($dir, DS).DS;

        $this->dir = $dir;
        if (!is_dir($this->dir))
        {
            mkdir($this->dir);
        }
        $filename  = $this->dir.$class_name.'.php';
        $f = fopen($filename, 'w');
        if (!is_resource($f))
        {
            throw new RuntimeException('impossible de generer la classe d\'entité: '.$this->class);
        }

        fwrite($f, "<?php \n".$render);
        fclose($f);
    }
}
