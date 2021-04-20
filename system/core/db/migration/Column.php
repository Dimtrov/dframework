<?php 
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core\db\migration;

/**
 * Column
 * Schema columns definitions
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/Migration
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/migration/Column.php
 * 
 * @method Column after(string $column) Place the column "after" another column (MySQL)
 * @method Column always() Used as a modifier for generatedAs() (PostgreSQL)
 * @method Column autoIncrement() Set INTEGER columns as auto-increment (primary key)
 * @method Column change() Change the column
 * @method Column charset(string $charset) Specify a character set for the column (MySQL)
 * @method Column collation(string $collation) Specify a collation for the column (MySQL/PostgreSQL/SQL Server)
 * @method Column comment(string $comment) Add a comment to the column (MySQL)
 * @method Column default(mixed $value) Specify a "default" value for the column
 * @method Column first() Place the column "first" in the table (MySQL)
 * @method Column generatedAs(string|Expression $expression = null) Create a SQL compliant identity column (PostgreSQL)
 * @method Column index(string $indexName = null) Add an index
 * @method Column nullable(bool $value = true) Allow NULL values to be inserted into the column
 * @method Column primary() Add a primary index
 * @method Column spatialIndex() Add a spatial index
 * @method Column storedAs(string $expression) Create a stored generated column (MySQL)
 * @method Column unique() Add a unique index
 * @method Column unsigned() Set the INTEGER column as UNSIGNED (MySQL)
 * @method Column useCurrent() Set the TIMESTAMP column to use CURRENT_TIMESTAMP as default value
 * @method Column virtualAs(string $expression) Create a virtual generated column (MySQL)
 * @method Column persisted() Mark the computed generated column as persistent (SQL Server)
 */
class Column extends Def
{
    
}
