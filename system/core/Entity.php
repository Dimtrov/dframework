<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.1
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
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.1
 * @file		/system/core/Entity.php
 */
abstract class Entity extends Model
{
    protected $table;    

	/**
	 * Constructor
	 *
	 * @param array|null $data
	 */
	public function __construct(?array $data = [])
	{
		parent::__construct();
        if (!empty($data)) 
        {
			$this->hydrate($data);
		}
	}
	/**
     * Hydratateur d'objet
     * 
	 * @param array $data
	 */
	public function hydrate(array $data)
	{
        if (!empty($data)) 
        {
            foreach ($data as $key => $value) 
            {
				$key = self::getProperty($key);
				$method = 'set'.ucfirst($key);
                if (method_exists($this, $method)) 
                {
					$this->{$method}($value);
                } 
                else 
                {
					$this->{$key} = $value;
				}
			}
		}
	}

    public function save()
    {

    }

    public function get($hydrate = true)
    {
        $this->db()->select()->from($this->table);
        foreach ($this->pk As $k) 
        {
            $this->where($k . ' = ?')->params([$this->{self::getProperty($k)}]);
        }

        if ($hydrate) 
        {
            return $this->one(DF_FCLA, static::class);
        }
        return $this->one();
    }

    public function findAll($hydrate = true)
    {
        $this->db()->select()->from($this->table);

        if ($hydrate)
        {
            return $this->all(DF_FCLA, static::class);
        }
        return $this->all();
    }


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
