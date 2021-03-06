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
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\db;

use dFramework\core\db\migration\Schema;

/**
 * Migration
 *
 * Classe abstraite de gestion de migrations de base de donnees
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Seeder.html
 * @since       3.3.0
 * @file        /system/core/db/Migration.php
 */
abstract class Migration
{
	private $schemas = [];

	//--------------------------------------------------------------------

	/**
	 * Perform a migration step.
	 */
	abstract public function up();

	//--------------------------------------------------------------------

	/**
	 * Revert a migration step.
	 */
	abstract public function down();
	

	/**
	 * Renvoi la liste des executions soue
	 *
	 * @return array
	 */
	public function getSchemas() : array 
	{
		return $this->schemas;
	}

	/**
     * Create a new table on the schema.
     *
     * @param  string    $table
     * @param  callable  $callback
	 * @return void
     */
    protected function create(string $table, callable $callback)
    {
		$schema = $this->build($table, $callback);
		$schema->create();

		$this->schemas[] = $schema;
    }
	
	/**
     * Modify a table on the schema.
     *
     * @param  string    $table
     * @param  callable  $callback
     * @return void
     */
    protected function modify(string $table, callable $callback)
    {
		$schema = $this->build($table, $callback);
		$schema->modify();

		$this->schemas[] = $schema;
    }

    /**
     * Drop a table from the schema.
     *
     * @param  string  $table
     * @return void
     */
    protected function drop(string $table)
    {
		$schema = $this->createSchema($table);
		$schema->drop();

		$this->schemas[] = $schema;
    }

    /**
     * Drop a table from the schema if it exists.
     *
     * @param  string  $table
     * @return void
     */
    protected function dropIfExists(string $table)
    {
        $schema = $this->createSchema($table);
		$schema->dropIfExists();

		$this->schemas[] = $schema;
    }

    /**
     * Rename a table on the schema.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function rename(string $from, string $to)
    {
		$schema = $this->createSchema($from);
		$schema->rename($to);
    }


	/**
     * @param string $table
     * @param callable $callback
     * @return schema
     */
    private function build(string $table, callable $callback) : schema
    {        
        return $callback($this->createSchema($table));
    }
	private function createSchema(string $table) : schema 
    {
        return new Schema($table);
    }
}