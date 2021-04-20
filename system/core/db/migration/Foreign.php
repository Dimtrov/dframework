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
 * Foreign
 * Schema foreign key definitions
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/Migration
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/migration/Foreign.php
 * 
 * 
 * @method Foreign references(string|array $columns) Specify the referenced column(s)
 * @method Foreign on(string $table) Specify the referenced table
 * @method Foreign onDelete(string $action) Add an ON DELETE action
 * @method Foreign onUpdate(string $action) Add an ON UPDATE action
 * @method Foreign deferrable(bool $value = true) Set the foreign key as deferrable (PostgreSQL)
 * @method Foreign initiallyImmediate(bool $value = true) Set the default time to check the constraint (PostgreSQL)
 */
class Foreign extends Def
{
    
}
