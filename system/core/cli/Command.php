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
use Ahc\Cli\IO\Interactor;
use dFramework\core\dFramework;
use Ahc\Cli\Input\Command As AhcCommand;

/**
 * Command
 * Abstract class for console commands
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.3.0
 * @file        /system/core/cli/Command.php
 */
class Command extends AhcCommand
{
    protected $_name = '';
    protected $_desc = '';
    protected $_role = '';

    /**
     * @var Interactor
     */
    public $_io;
    public $io;

    public function __construct(string $name = '', string $desc = '', bool $allowUnknow = false, ?Application $app = null)
    {
        $name = empty($name) ? $this->_name : $name;
        $desc = empty($desc) ? (empty($this->_role) ? $this->_desc : $this->_role) : $desc;

        parent::__construct($name, $desc, $allowUnknow, $app);
        $this->_io = $this->io = new Interactor();
    }


    /**
     * Set command description
     *
     * @param string $desc
     * @return self
     */
    public function description(string $desc) : self 
    {
        $this->_desc = $desc;

        return $this;
    }

    public function task(string $msg)
    {
        $this->io->write(' ******************* '.$msg.' *******************', true);
    }

    /**
     * Message d'entete commun a tous les services de la console
     *
     * @return void
     */
    public function start(string $desc = '')
    {
        $desc = empty($desc) ? $this->_desc : $desc;

        $eq_str = str_repeat('=', strlen($desc));

        $this->io->write("====================================".$eq_str, true);
        $this->io->write("dFramework Console Line Interface | ".$desc, true);
        $this->io->write("====================================".$eq_str, true);
        $this->io->write('', true);
    }
    /**
     * Message de pied commun a tous les services de la console
     *
     * @return void
     */
    public function end()
    {
        $info = 'dFramework v'.dFramework::VERSION.' * dbot v1.2 * '.date('Y-m-d H:i:s');
        $this->io->write("\n\n".str_repeat('-', strlen($info))."\n");
        $this->io->writer()->bold->info($info, true);
    }


    /**
	 * A simple method to display an error with line/file,
	 * in child commands.
	 *
	 * @param \Throwable $e
	 */
	public function showError(\Throwable $e)
	{
        $this->io->error("\n".$e->getMessage(), true);
		$this->io->write($e->getFile() . ' - ' . $e->getLine(), true);
	}

    public function colorize(array $message)
    {
        $this->io->colors("<".$message['color'].">".$message['message']."</end><eol>");
    }
}