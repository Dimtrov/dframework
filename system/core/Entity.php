<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.1
 */


namespace dFramework\core;

use dFramework\core\utilities\Chaine;

/**
 * Entity
 *
 * A global Entity system of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.1
 * @file		/system/core/Entity.php
 */

class Entity extends Model
{
        
    /**
     * getProperty
     *
     * @param string $fieldName
     * @return string
     */
    public static function getProperty(string $fieldName) : string
    {
        $case = Config::get('data.hydrator.case');
        $case = \strtolower($case);
        if (\in_array($case, ['camel', 'pascal', 'snake', 'ada', 'macro']))
        {
            $case = 'to'.$case;
            return Chaine::{$case}($fieldName);
        }        
        return $fieldName;
    }
}
