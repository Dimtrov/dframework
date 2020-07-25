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

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use dFramework\core\dFramework;

/**
 * Cli
 * Abstract class for console working
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.2.1
 * @file        /system/core/cli/Cli.php
 */
abstract class Cli extends Command
{
    /**
     * @var string
     */
    protected $_description;
    /**
     * @var string
     */
    protected $_name;
    /**
     * @var Interactor
     */
    protected $_io;

    public function __construct()
    {
        parent::__construct($this->_name, $this->_description);

        $this->_io = new Interactor();
    }
    /**
     * Message d'entete commun a tous les services de la console
     *
     * @return void
     */
    protected function __startMsg()
    {
        $this->_io->write('', true);
        $eq_str = str_repeat('=', strlen($this->_description));

        $this->_io->write("====================================".$eq_str, true);
        $this->_io->write("dFramework Console Line Interface | ".$this->_description, true);
        $this->_io->write("====================================".$eq_str, true);
    }
    /**
     * Message de pied commun a tous les services de la console
     *
     * @return void
     */
    protected function __endMsg()
    {
        $info = 'dFramework v'.dFramework::VERSION.' * dbot v1.1 * '.date('Y-m-d H:i:s');
        $this->_io->write("\n".str_repeat('-', strlen($info))."\n");
        $this->_io->writer()->bold->info($info, true);
    }
}
