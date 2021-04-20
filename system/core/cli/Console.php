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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\cli;

use Ahc\Cli\Application;
use Ahc\Cli\Input\Command As AhcCommand;

/**
 * Console
 * Abstract class for console working
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.3.0
 * @file        /system/core/cli/Console.php
 */
final class Console extends Application
{
    /**
     * Add a command by its name desc alias etc.
     *
     * @param string $name
     * @param string $desc
     * @param string $alias
     * @param bool   $allowUnknown
     * @param bool   $default
     *
     * @return Command
     */
    public function command(
        string $name,
        string $desc = '',
        string $alias = '',
        bool $allowUnknown = false,
        bool $default = false
    ): AhcCommand {
        $command = new Command($name, $desc, $allowUnknown, $this);

        $this->add($command, $alias, $default);

        return $command;
    }
}
