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

use dFramework\core\db\query\Builder;

/**
 * Table
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Seeder.html
 * @since       3.2.3
 * @credit      tebazil\dbseeder
 * @file        /system/core/db/seeder/Table.php
 */
class Table
{
    const DEFAULT_ROW_QUANTITY=30;
    /**
     * @var Faker
     */
    private $generator;
    /**
     * @var Builder
     */
    private $builder;
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $columns;
    /**
     * @var int
     */
    private $rowQuantity = self::DEFAULT_ROW_QUANTITY;
    /**
     * @var array
     */
    private $rows;
    /**
     * @var boolean
     */
    private $truncateTable = false;


    private $rawData;
    private $isFilled=false;
    private $isPartiallyFilled=false;
    private $dependsOn=[];
    private $selfDependentColumns=[];
    private $columnConfig=[];

    
    /**
     * @param string $name
     * @param Generator $generator
     * @param Builder $builder
     */
    public function __construct(string $name, Generator $generator, Builder $builder, bool $truncateTable) 
    {
        $this->name = $name;
        $this->generator = $generator;
        $this->builder = $builder;
        $this->truncateTable = $truncateTable;
    }

    /**
     * Definit les colonnes où les insertions se passeront
     * 
     * @param array $columns
     * @return self
     */
    public function setColumns(array $columns) : self
    {
        $columnNames = array_keys($columns);
        foreach ($columnNames As $columnName) 
        {
            $this->columns[$columnName] = [];
        }
        
        $this->columnConfig = $columns;
        $this->calcDependsOn();
        $this->calcSelfDependentColumns();
       
        return $this;
    }
    /**
     * Definit le nombre d'element a generer
     *
     * @param integer $rows
     * @return self
     */
    public function setRowQuantity(int $rows = 30) : self 
    {
        if (!is_numeric($rows)) 
        {
            throw new \Exception('$rows parameter should be numeric');
        }
        $this->rowQuantity = $rows;
        
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param array $rawData
     * @param array $columnNames
     * @return self
     */
    public function setRawData(array $rawData, array $columnNames=[]) : self
    {
        if (!$rawData) 
        {
            throw new \InvalidArgumentException('$rawData cannot be empty array');
        }
        if (!is_array($firstRow = reset($rawData))) 
        {
            throw new \InvalidArgumentException('$rawData should be an array of arrays (2d array)');
        }
        if (is_numeric(key($firstRow)) AND !$columnNames) 
        {
            throw new \InvalidArgumentException('Either provide $rawData line arrays with corresponding column name keys, or provide column names in $columnNames');
        }

        $this->rawData = $rawData;
        $columnNames = $columnNames ?: array_keys(reset($this->rawData));
        $this->columnConfig = []; //just in case
        foreach ($columnNames As $columnName) 
        {
            if ($columnName) 
            {
                $this->columns[$columnName] = []; //we skip false columns and empty columns
            }
            $this->columnConfig[]=$columnName;
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param boolean $writeDatabase
     * @return void
     */
    public function fill(bool $writeDatabase = true) 
    {
        is_null($this->rawData) ? $this->fillFromGenerators($this->columnConfig): $this->fillFromRawData($this->columnConfig, $this->rawData);

        if ($this->selfDependentColumns) 
        {
            if ($this->isPartiallyFilled) 
            {
                $this->isFilled = true; //second run
            }
            else 
            {
                $this->isPartiallyFilled = true; //first run
            }
        }
        else 
        {
            $this->isFilled = true; //no self-dependent columns
        }

        if ($this->isFilled AND $writeDatabase) 
        {
            $this->insertData();
        }
    }

    /**
     * @return boolean
     */
    public function getIsFilled() : bool
    {
        return $this->isFilled;
    }

    /**
     * Undocumented function
     *
     * @param array $filledTableNames
     * @return boolean
     */
    public function canBeFilled(array $filledTableNames) : bool 
    {
        $intersection = array_intersect($filledTableNames, $this->dependsOn);
        sort($intersection);
    
        return $intersection === $this->dependsOn;
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }



    /**
     * Undocumented function
     *
     * @param array $columnConfig
     * @param array $data
     * @return void
     */
    private function fillFromRawData(array $columnConfig, array $data) 
    {
        $sizeofColumns = count($columnConfig);
        $data = array_values($data);
        $sizeofData = count($data);

        for ($rowNo = 0; $rowNo < $this->rowQuantity; $rowNo++) 
        {
            $dataKey = ($rowNo < $sizeofData) ? $rowNo : ($rowNo % $sizeofData);
            $rowData = array_values($data[$dataKey]);

            for ($i = 0; $i < $sizeofColumns; $i++) 
            {
                if (!$columnConfig[$i]) 
                {
                    continue;
                }

                $this->rows[$rowNo][$columnConfig[$i]] = $rowData[$i];
                $this->columns[$columnConfig[$i]][$rowNo] = $rowData[$i];
            }
        }
    }
    /**
     * Undocumented function
     *
     * @param array $columnConfig
     * @return void
     */
    private function fillFromGenerators(array $columnConfig) 
    {
        $this->generator->reset();
        for ($rowNo = 0; $rowNo < $this->rowQuantity; $rowNo++) 
        {
            foreach ($columnConfig as $column => $config) 
            {
                //first and second run separation
                if ($this->selfDependentColumns) 
                {
                    $columnIsSelfDependent = in_array($column, $this->selfDependentColumns);
                    if (!$this->isPartiallyFilled) 
                    {
                        if ($columnIsSelfDependent) 
                        {
                            continue;
                        }
                    }
                    else 
                    {
                        if (!$columnIsSelfDependent) 
                        {
                            continue;
                        }
                    }
                }
                $value = $this->generator->getValue($config);
                $this->rows[$rowNo][$column] = $value;
                $this->columns[$column][$rowNo] = $value;
            }
        }
    }

    private function calcDependsOn() 
    {
        if ($this->rawData) 
        {
            return false;
        }
        foreach ($this->columnConfig As $name => $config) 
        {
            if (!is_callable($config)) 
            {
                if (is_array($config) AND ($config[0] === Generator::RELATION) AND ($this->name !== $config[1])) 
                {
                    $this->dependsOn[] = $config[1];
                }
            }
        }
        sort($this->dependsOn);
    }

    private function calcSelfDependentColumns() 
    {
        if ($this->rawData) 
        {
            return false;
        }
        foreach ($this->columnConfig as $name => $config) 
        {
            if (!is_callable($config)) 
            {
                if (is_array($config) AND ($config[0] === Generator::RELATION) AND ($config[1] === $this->name)) 
                {
                    $this->selfDependentColumns[]=$name;
                }
            }
        }
    }


    private function insertData()
    {
        if (true === $this->truncateTable)
        {
            $this->builder->disableFk();
            $this->builder->truncate($this->name);
        } 
        foreach ($this->rows As $row) 
        {
            $this->builder->from($this->name)->insert($row);
        }
    }
}
