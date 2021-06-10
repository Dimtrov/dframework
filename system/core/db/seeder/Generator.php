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

namespace dFramework\core\db\seeder;

use \Faker\Factory As TrueFaker;

/**
 * Generator
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Seeder.html
 * @since       3.2.3
 * @credit      tebazil\dbseeder
 * @file        /system/core/db/seeder/Generator.php
 */
class Generator
{
    const PK = 'pk';
    const FAKER = 'faker';
    const RELATION = 'relation';

    /**
     * @var TrueFaker
     */
    private $faker;

    private $reset = false;
    /**
     * @var integer
     */
    private $pkValue = 1;

    private $tables;

    public function __construct($locale = 'fr_FR')
    {
        $this->faker = $this->getNewFakerInstance($locale);
    }

    public function getValue($config)
    {
        if (!is_array($config))
        {
            $config = [$config];
        }

        $value = null;
        switch ($config[0])
        {
            case self::PK:
                $value = $this->pk();
                break;
            case self::FAKER:
                $faker = $this->faker;
                if (isset($config[3]))
                {
                    if (isset($config[3][Faker::UNIQUE]) AND is_array($config[3][Faker::UNIQUE]))
                    {
                        $faker = call_user_func_array([$faker, 'unique'], $config[3][Faker::UNIQUE]);
                    }
                    if (isset($config[3][Faker::OPTIONAL]) AND is_array($config[3][Faker::OPTIONAL]))
                    {
                        $faker = call_user_func_array([$faker, 'optional'], $config[3][Faker::OPTIONAL]);
                    }
                }
                if (isset($config[2])) {
                    $value = $faker->format($config[1], $config[2]);
                }
                else
                {
                    $value = $faker->format($config[1]);
                }
                break;
            case self::RELATION:
                if (!$this->isColumnSet($config[1], $config[2]))
                {
                    throw new \InvalidArgumentException("Table data for table $config[1] column $config[2] is not found in class instance. Probably this is a bug.");
                }
                $value = $this->getRandomColumnValue($config[1], $config[2]);
                break;
            default:
                if (is_callable($config[0])) {
                    return call_user_func($config[0]);
                }
                else
                {
                    return $config[0];
                }

        }
        return $value;
    }

    public function reset()
    {
        $this->reset = true;
    }

    private function pk() : int
    {
        if ($this->reset)
        {
            $this->pkValue = 1;
            $this->reset = false;
        }

        return $this->pkValue++;
    }

    private function getNewFakerInstance(string $locale)
    {
        return TrueFaker::create($locale);
    }

    private function isColumnSet(string $table, $column) : bool
    {
        return isset($this->tables[$table]) AND isset($this->tables[$table][$column]);
    }

    public function setColumns(string $table, $columns)
    {
        $this->tables[$table] = $columns;
    }

    public function getRandomColumnValue(string $table, $column)
    {
        if (isset($this->tables[$table][$column]))
        {
            return $this->tables[$table][$column][array_rand($this->tables[$table][$column])];
        }
        else
        {
            throw new \InvalidArgumentException("Table $table , column $column is not filled");
        }
    }
}
