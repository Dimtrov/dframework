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
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.4.0
 */

namespace dFramework\core\db;

use dFramework\core\db\query\Builder;
use dFramework\core\db\seeder\Faker;
use dFramework\core\db\seeder\Generator;
use dFramework\core\db\seeder\Table;
use dFramework\core\db\seeder\TableDef;
use dFramework\core\loader\Service;

/**
 * Seeder
 *
 * Genere du faux contenu pour remplir une base de donnees
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Seeder.html
 * @since       3.2.3
 * @file        /system/core/db/Seeder.php
 */
abstract class Seeder
{
    /**
     * @var array Table
     */
    private $tables = [];

    /**
     * @var Generator
     */
    private $generator;
    /**
     * @var Builder
     */
    private $builder;
    /**
     * @var array
     */
    private $filledTablesNames = [];

    /**
     * @param string $locale
     * @param string $group
     */
    public function __construct(string $locale = 'fr_FR', string $group = null)
    {
        $this->generator = new Generator($locale);
        $this->builder = Service::builder($group);
    }

    /**
     * execute de la generation et du seed
     *
     * @return void
     */
    public function run()
    {
        return $this->execute();
    }
    /**
     * Definition des seeds
     *
     * @param Faker $faker
     * @return self
     */
    public abstract function seed(Faker $faker) : self;


    /**
     * Specifie la table a remplir
     *
     * @param string $name
     * @param bool $truncate
     * @return TableDef
     */
    protected function table(string $name, bool $truncate = false) : TableDef
    {
        if (!isset($this->tables[$name]))
        {
            $this->tables[$name] = new Table($name, $this->generator, $this->builder, $truncate);
        }

        return new TableDef($this->tables[$name]);
    }

    /**
     *  Lance la generation des donnees
     */
    private function execute()
    {
        $this->checkCrossDependentTables();
        $tableNames = array_keys($this->tables);
        sort($tableNames);
        $foolProofCounter = 0;
        $tableNamesIntersection = [];

        while ($tableNamesIntersection !== $tableNames)
        {
            if ($foolProofCounter++ > 500)
            {
                throw new \Exception("Something unexpected happened: some tables possibly cannot be filled");
            }
            foreach ($this->tables As $tableName => $table) {

                if (!$table->getIsFilled() AND $table->canBeFilled($this->filledTablesNames))
                {
                    $table->fill();
                    $this->generator->setColumns($tableName, $table->getColumns());

                    if (!in_array($tableName, $this->filledTablesNames))
                    {
                         // because some tables are filled twice
                        $this->filledTablesNames[] = $tableName;
                    }
                }
            }

            $tableNamesIntersection = array_intersect($this->filledTablesNames, $tableNames);
            sort($tableNamesIntersection);
        }
    }

    private function checkCrossDependentTables()
    {
        $dependencyMap = [];
        foreach ($this->tables As $tableName => $table)
        {
            $dependencyMap[$tableName] = $table->getDependsOn();
        }
        foreach ($dependencyMap As $tableName => $tableDependencies)
        {
            foreach ($tableDependencies As $dependencyTableName)
            {
                if (in_array($tableName, $dependencyMap[$dependencyTableName]))
                {
                    throw new \InvalidArgumentException("You cannot pass tables that are dependent on each other");
                }
            }
        }
    }
}
