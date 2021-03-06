<?php 

namespace dFramework\core\db\migration;

use dFramework\core\exception\Exception;

class Creator
{
    /**
     * @var array
     */
    protected $columns = [];
    /**
     * @var array
     */
    protected $indexes = [
        'index' => [],
        'primary' => [],
        'unique' => [],
    ];
    /**
     * @var array
     */
    protected $uis = [];

    /**
     * @var string
     */
    private $sql;

    private $mapTypes = [
        'boolean' => 'TINYINT(1)',
        
        'bigInteger' => 'BIGINT',
        'integer' => 'INT',
        'mediumInteger' => 'MEDIUMINT',
        'smallInteger' => 'SMALLINT',
        'tinyInteger' => 'TINYINT',
        'decimal' => 'DECIMAL',
        'double' => 'DOUBLE',
        'float' => 'FLOAT',
        
        'date' => 'DATE',
        'dateTime' => 'DATETIME',
        'dateTimeTz' => 'DATETIME',
        'time' => 'TIME',
        'timeTz' => 'TIME',
        'timestamp' => 'TIMESTAMP',
        'timestampTz' => 'TIMESTAMP',
        'year' => 'YEAR',
        
        'binary' => 'BINARY',
        'char' => 'CHAR',
        'longText' => 'LONGTEXT',
        'mediumText' => 'MEDIUMTEXT',
        'string' => 'VARCHAR',
        'text' => 'TEXT',

        'enum' => 'ENUM',
        'set' => 'SET',

        'json' => 'JSON',
        'jsonb' => 'JSON',

        'geometry' => 'GEOMETRY',
        'point' => 'POINT',
    ];

    /**
     * Make a sql query to create table
     *
     * @param string $table
     * @return string
     */
    public function createTable(string $table) : string 
    {
        $this->columns = array_map(function($v){
            return preg_replace('#AFTER ([a-zA-Z0-9_-]+)#', '', $v);
        }, $this->columns);

        $sql = "DROP TABLE IF EXISTS ".$table.";\n";
        $sql .= "CREATE TABLE ".$table." (";
        $sql .= "\n\t".join(", \n\t", $this->columns);
          
        if (!empty($this->indexes['primary'])) 
        {
            $sql .= ",\n\tPRIMARY KEY(".join(',', $this->indexes['primary']).")";
        }
        foreach ($this->indexes['unique'] As $index) 
        {
            $sql .= ",\n\tUNIQUE INDEX ".$index."(".$index.")";
        }
        foreach ($this->indexes['index'] As $index) 
        {
            $sql .= ",\n\tINDEX ".$index."(".$index.")";
        }

        $sql .= "\n);";

        return $sql;
    }

    /**
     * Make a sql query to create table
     *
     * @param string $table
     * @param bool $ifExist
     * @return string
     */
    public function dropTable(string $table, bool $ifExist) : string 
    {
        return 'DROP TABLE '.($ifExist ? 'IF EXISTS ' : '').$table;
    }

    /**
     * Make a sql query to modify table
     *
     * @param string $table
     * @param array $commands
     * @return string
     */
    public function modifyTable(string $table, array $commands = []) : string 
    {
        $alters = array_merge([], array_map(function($v){ return 'ADD COLUMN ' .$v; }, $this->columns));
        
        foreach ($commands As $command) 
        {
            if ($command->name === 'dropColumn')
            {
                $alters = array_merge($alters, array_map(function($v){ return 'DROP COLUMN ' . $v; }, $command->columns));
            }
            if ($command->name === 'dropIndex')
            {
                $alters = array_merge($alters, array_map(function($v){ return 'DROP INDEX ' . $v; }, $command->columns));
            }
            if ($command->name === 'dropUnique')
            {
                $alters = array_merge($alters, array_map(function($v){ return 'DROP UNIQUE INDEX ' . $v; }, $command->columns));
            }
            if ($command->name === 'dropPrimary')
            {
                $alters = array_merge($alters, ['DROP PRIMARY KEY']);
            }
            if ($command->name === 'primary')
            {
                $alters = array_merge($alters, ['DROP PRIMARY KEY, ADD PRIMARY KEY('.join(',', $command->columns).')']);
            }
            if ($command->name === 'unique')
            {
                $alters = array_merge($alters, ['DROP UNIQUE INDEX '.($command->index ?? '').', ADD UNIQUE INDEX '.($command->index ?? '').'('.join(',', $command->columns).')']);
            }
            if ($command->name === 'index')
            {
                $alters = array_merge($alters, ['DROP INDEX '.($command->index ?? '').', ADD INDEX '.($command->index ?? '').'('.join(',', $command->columns).')']);
            }
            foreach ($this->indexes['unique'] As $index) 
            {
                $alters = array_merge($alters, ['ADD UNIQUE INDEX '.$index.'('.$index.')']);
            }
            foreach ($this->indexes['index'] As $index) 
            {
                $alters = array_merge($alters, ['ADD INDEX '.$index.'('.$index.')']);
            }
        }
        
        $sql = "ALTER TABLE ".$table;
        $sql .= "\n\t";
        $sql .= join(", \n\t", $alters);    
        $sql .= "\n;";

        return $sql;
    }

    public function getSql() : string 
    {
        return trim(trim($this->sql, "\n"), ',');
    }


    /**
     * Generate a sql column from object definition
     *
     * @param object $column
     * @return self
     */
    public function makeColumn(object $column) : self 
    {
        if (empty($column->name) OR empty($column->type)) 
        {
            throw new Exception("Error Processing Request", 1);
        }
        $code = [];

        $code[] = $column->name;
        
        $code[] = $this->makeType($column);
        
        if (!$this->isNullable($column)) 
        {
            $code[] = 'NOT NULL';
        }
        
        $code[] = $this->addDefault($column);

       
        if ($this->isAutoincrement($column)) 
        {
            $code[] = 'AUTO_INCREMENT';
        }
        if (!empty($column->comment)) 
        {
            $code[] = 'COMMENT "'.htmlspecialchars($column->comment).'"';
        }
        if (!empty($column->collation)) 
        {
            $code[] = 'COLLATE "'.htmlspecialchars($column->collation).'"';
        }
        if (!empty($column->after)) 
        {
            $code[] = 'AFTER '.$column->after;
        }
        else if (!empty($column->first)) {
            $code[] = 'FIRST';
        }

        $this->columns[] = join(' ', array_filter($code, function ($v) { return !empty($v); }));

        $this->addIndexes($column);

        return $this;
    }

    /**
     * Make a column type
     *
     * @param object $column
     * @return string
     */
    private function makeType(object $column) : string 
    {
        $type  = $this->mapTypes[$column->type];
        if ($this->isNumeric($column) AND $this->isUnsigned($column)) 
        {
            $type .= ' UNSIGNED';
        } 
        if ($this->isChar($column)) 
        {
            $type .= '('.($column->length ?? 255).')';
        }
        if ($this->isReal($column))
        {
            $type .= '('.($column->total ?? 8).', '.($column->places ?? 2).')';
        }
        if ($this->isEnum($column)) 
        {
            if (empty($column->allowed)) 
            {
                throw new Exception("Undefined ENUM/SET values");
            }
            $type .= '('.join(',', array_map(function($v) { return is_string($v) ? "'".$v."'" : $v; }, (array) $column->allowed)).')';
        }

        return $type;
    }
    /**
     * Set a default value
     *
     * @param object $column
     * @return string|null
     */
    private function addDefault(object $column) : ?string
    {
        if ($this->isUseCurrent($column))
        {
             return 'DEFAULT CURRENT_TIMESTAMP';
        }
        if ($this->isDefault($column)) 
        {
            if ($column->type === 'boolean') 
            {
                return 'DEFAULT '. (int) $column->default;
            }
            return 'DEFAULT '. $column->default;
        }
        return null;
    }
    /**
     * Add indexes (pk, uk, k) keys
     *
     * @param object $column
     * @return void
     */
    private function addIndexes(object $column) 
    {
        if ($this->isPk($column)) 
        {
            $this->indexes['primary'][] = $column->name;
        }
        if ($this->isUnique($column)) 
        {
            $this->indexes['unique'][] = $column->name;
        }
        if ($this->isIndex($column)) 
        {
            $this->indexes['index'][] = $column->name;
        }
    }


    /**
     * Check if a column is a primary key
     *
     * @param object $column
     * @return boolean
     */
    private function isPk(object $column) : bool 
    {
        return property_exists($column, 'primary') AND $column->primary === true;
    }
    /**
     * Check if a column is an unique index
     *
     * @param object $column
     * @return boolean
     */
    private function isUnique(object $column) : bool 
    {
        return property_exists($column, 'unique') AND $column->unique === true;
    }
    /**
     * Check if a column is an index
     *
     * @param object $column
     * @return boolean
     */
    private function isIndex(object $column) : bool 
    {
        return property_exists($column, 'index') AND $column->index === true;
    }
    
    
    /**
     * Check if a column is unsigned numerical value
     *
     * @param object $column
     * @return boolean
     */
    private function isUnsigned(object $column) : bool
    {
        return property_exists($column, 'unsigned') AND $column->unsigned === true;
    }
    /**
     * Check if a column accept a nullable value
     *
     * @param object $column
     * @return boolean
     */
    private function isNullable(object $column) : bool
    {
        return property_exists($column, 'nullable') AND $column->nullable === true;
    }
    /**
     * check if a default value of column is a current_timestamp
     *
     * @param object $column
     * @return boolean
     */
    private function isUseCurrent(object $column) : bool 
    {
        return $this->isTimestamp($column) AND property_exists($column, 'useCurrent') AND $column->useCurrent === true;
    }
    /**
     * Check if a column has a default value
     *
     * @param object $column
     * @return boolean
     */
    private function isDefault(object $column)
    {
        return property_exists($column, 'default');
    }
    /**
     * Check if a column is autoincrementable
     *
     * @param object $column
     * @return boolean
     */
    private function isAutoincrement(object $column) : bool
    {
        return $this->isInteger($column) AND property_exists($column, 'autoIncrement') AND $column->autoIncrement === true;
    }
    

    /**
     * Check if a column is a character type
     *
     * @param object $column
     * @return boolean
     */
    private function isChar(object $column) : bool 
    {
        return in_array($column->type, ['char', 'string']);
    }
    /**
     * Check if a column is an enum type
     *
     * @param object $column
     * @return boolean
     */
    private function isEnum(object $column) : bool 
    {
        return in_array($column->type, ['enum', 'set']);
    }
    /**
     * Check if a column is an integer type
     *
     * @param object $column
     * @return boolean
     */
    private function isInteger(object $column) : bool 
    {
        return in_array($column->type, ['integer', 'int', 'bigInteger', 'mediumInteger', 'smallInteger', 'tinyInteger']);
    }
    /**
     * Check if a column is a numeric type
     *
     * @param object $column
     * @return boolean
     */
    private function isNumeric(object $column) : bool 
    {
        return $this->isInteger($column) OR $this->isReal($column);
    }
    /**
     * Check if a column is a real type
     *
     * @param object $column
     * @return boolean
     */
    private function isReal(object $column) : bool 
    {
        return in_array($column->type, ['decimal', 'double', 'float']);
    }
    /**
     * Check if a column is a timestamp type
     *
     * @param object $column
     * @return boolean
     */
    private function isTimestamp(object $column) : bool 
    {
        return in_array($column->type, ['timestamp', 'timestampTz']);
    }
}
