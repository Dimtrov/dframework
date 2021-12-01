<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core\models\cast;

use DateTime;
use dFramework\core\utilities\Date;

/**
 * DatetimeCast
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Models/Cast
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.4.0
 * @credit		CodeIngiter 4 - https://github.com/codeigniter4/framework/tree/master/system/Entity/Cast/DatetimeCast.php
 * @file		/system/core/models/cast/DatetimeCast.php
 */
class DatetimeCast extends BaseCast
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public static function get($value, array $params = [])
    {
        if ($value instanceof Date)
		{
            return $value;
        }
        if ($value instanceof DateTime)
		{
            return Date::createFromInstance($value);
        }
        if (is_numeric($value))
		{
            return Date::createFromTimestamp($value);
        }
        if (is_string($value))
		{
            return Date::make($value);
        }
        return $value;
    }
}
