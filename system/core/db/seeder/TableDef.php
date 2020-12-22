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
 * @copyright	Copyright (c) 2019 - 2020, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2020, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */

namespace dFramework\core\db\seeder;

/**
 * TableDef
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Seeder.html
 * @since       3.2.3
 * @file        /system/core/db/seeder/Table.php
 */
class TableDef
{
    /**
     * @var Table
     */
    private $table;
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Defini les type de donnees a generer pour chaque colone qu'on souhaite remplir dans la base de donnees
     *
     * @param array $columns
     * @return self
     */
    public function columns(array $columns) : self
    {
        $columns = $this->preprocess($columns);
        $this->table->setColumns($columns);
        
        return $this;
    }

    /**
     * Specifie le nombre de ligne a inserer dans la table
     *
     * @param integer $rows
     * @return self
     */
    public function rows(int $rows = 30) : self
    {
        $this->table->setRowQuantity($rows);
    
        return $this;
    }


    public function data(array $rawData, array $columnNames=[]) : self 
    {
        $this->table->setRawData($rawData, $columnNames);
    
        return $this;
    }



    private function preprocess(array $columns) : array 
    {
        foreach ($columns As $key => $value) 
        {
            if (is_numeric($key)) 
            {
                if (!is_scalar($value)) 
                {
                    throw new \Exception("If the column is lazy configured, it's value should be scalar - either id, or foreign key, i.e. status_id");
                }
                $config = explode('_', $value);
                
                if ($config[0]==='id') 
                {
                    $newColumns[$value]=[Generator::PK];
                }
                elseif (count($config) === 2 OR $config[1] === 'id') 
                {
                    $newColumns[$value] = [Generator::RELATION, $config[0], 'id'];
                }
                else 
                {
                    throw new \Exception("Column ".$value." is badly lazy-configured");
                }
            }
            else 
            {
                $newColumns[$key]=$value;
            }
        }

        return $newColumns;
    }
}
