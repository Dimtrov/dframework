<?php
/**
 * Created by PhpStorm.
 * User: Dimitri Sitchet
 * Date: 17/06/2019
 * Time: 07:47
 */

namespace dFramework\core\db;

use dFramework\core\{Config, exception\Exception, exception\HydratorException};

class Hydrator
{

    /**
     * @param array $array
     * @param string $class
     * @param string $dir
     * @return mixed
     * @throws Exception
     */
    public static function hydrate(array $array, string $class, string $dir = ENTITY_DIR)
    {
        $class = preg_replace('#Entity#isU', '', $class) . 'Entity';
        $dir = str_replace(['/', '\\'], DS, $dir);
        $dir = rtrim($dir, DS).DS;

        $file = $dir . ucfirst($class) . '.php';

        if(true !== is_file($file))
        {
            fopen($file, 'w');
        }
        require_once $file;

        if(true !== class_exists($class))
        {
            self::makeEntityClass($class, $file);
        }
        $instance = new $class();

        foreach ($array As $key => $value)
        {
            $property = lcfirst(self::getProperty($key));
            //$instance->$property = $value;
            $method = self::getSetter($key);
            if(method_exists($instance, $method)) {
               $instance->$method($value);
            }
            else {
                $instance->$property = $value;
            }
        }
        return $instance;
    }


    public static function Exception($message = '')
    {
        die($message);
    }


    /**
     * @param string $fieldName
     * @return string
     */
    private static function getSetter(string $fieldName) : string
    {
        return 'set'. self::getProperty($fieldName);
    }

    /**
     * @param string $fieldName
     * @return string
     */
    private static function getProperty(string $fieldName) : string
    {
        $case = Config::get('data.hydrator.case');
        if(strtolower($case) === 'camel')
        {
            $str=$fieldName;
            $i = array("-","_");
            $str = preg_replace('/([a-z])([A-Z])/', "\\1 \\2", $str);
            $str = preg_replace('@[^a-zA-Z0-9\-_ ]+@', '', $str);
            $str = str_replace($i, ' ', $str);
            $str = str_replace(' ', '', ucwords(strtolower($str)));
            $str = strtolower(substr($str,0,1)).substr($str,1);
            return $str;
        }
        else if(strtolower($case) === 'pascal')
        {
            return join('', array_map('ucfirst', explode('_', $fieldName)));
        }
        else
        {
            return $fieldName;
        }
    }


    /**
     * @param string $class
     * @param string $file
     * @param string $db_setting
     * @throws HydratorException
     */
    public static function makeEntityClass(string $class, string $file, string $db_setting = 'default')
    {
        $file = preg_replace('#'.ucfirst($class).'\.php$#i', '', $file);
        $file = rtrim($file, DS).DS.ucfirst($class).'.php';

        $class = preg_replace('#Entity$#i', '', $class);

        try {
            $columns = (new Query($db_setting))->db->pdo()->query('DESCRIBE '.$class)->fetchAll(\PDO::FETCH_OBJ);
        }
        catch (\PDOException $e) {
            throw new HydratorException('
                Impossible d\'hydrater l\'entite <b>'.$class.'</b>. 
                Vous pouvez resoudre ce probleme en creant manuellement la classe '.$class.'Entity 
                <br>
                <i>&laquo; '.$e->getMessage().' &raquo;</i>
            ');
        }

        self::getProperties($columns, $properties);
        self::writeProperties($properties, $render, $class);
        self::createFile($render, $class, $file);
    }

    /**
     * Permet de creer les proprietes de la classe a partir des champs de la base de donnees
     *
     * @param array $columns
     * @return array
     */
    private static function getProperties(array $columns, &$properties)
    {
        $properties = (array) $properties; $properties = []; $i = 0;

        foreach ($columns As $column)
        {
            if(!($column instanceof \stdClass))
            {
                continue;
            }

            $properties[$i]['name'] = self::getProperty($column->field);

            if(preg_match('#^(int|longint|smallint)#i', $column->type))
            {
                $properties[$i]['type'] = 'int';
            }
            else if(preg_match('#^(varchar|text|char)#i', $column->type))
            {
                $properties[$i]['type'] = 'string';
            }
            else if(preg_match('#^(decimal|float)#i', $column->type))
            {
                $properties[$i]['type'] = 'float';
            }
            else if(preg_match('#^(boolean|tinyint)#i', $column->type))
            {
                $properties[$i]['type'] = 'bool';
            }
            else
            {
                $properties[$i]['type'] = 'mixed';
            }

            if(!empty($column->default))
            {
                $properties[$i]['default'] = $column->default;
            }
            $i++;
        }
        return $properties;
    }

    /**
     * Ecrit les proprietes de la classe, les getters et les setters
     *
     * @param array $properties
     * @param $render
     */
    private static function writeProperties(array $properties, &$render, $class)
    {
        foreach ($properties As $property)
        {
            /* Generation des proprietes */
            $render .= "\n\t /** \n \t * @var ".$property['type'];
            $render .= "\n\t */\n";
            $render .= "\t private $".$property['name'];
            if(!empty($property['default']))
            {
                $render .= ' = '.$property['default'];
            }
            $render .= ";\n";

            /* Generation des getters */
            $render .= "\n\t /** \n \t *@return ".$property['type'];
            $render .= "\n\t */\n";
            $render .= "\t public function get".ucfirst($property['name'])."()";
            if($property['type'] !== 'mixed')
            {
                $render .= " : " . $property['type'];
            }
            $render .= "\n\t {";
            $render .= "\n\t\t return \$this->".$property['name'].";";
            $render .= "\n\t }";
            $render .= "\n";

            /* Generation des setters */
            $render .= "\n\t /** \n \t * @param ".$property['type']." $".$property['name'];
            $render .= "\n\t * @return ".$class."Entity";
            $render .= "\n\t */\n";
            $render .= "\t public function set".ucfirst($property['name'])."(".$property['type']." $".$property['name'].") : self";
            $render .= "\n\t {";
            $render .= "\n\t\t \$this->".$property['name']." = $".$property['name'].";";
            $render .= "\n\t\t return \$this;";
            $render .= "\n\t }";
            $render .= "\n";

            $render .= "\n";
        }
    }

    /**
     * Enregistre le code de la classe dans le fichiers
     *
     * @param $render
     * @param $class
     * @param $file
     */
    private static function createFile($render, $class, $file)
    {
        $class = $class.'Entity';
        $return = '';

        $return .= "<?php \n";
        $return .= "/** \n * Created by dFramework. \n * Date: ".date('d/m/Y - H:i:s')." \n * Entity: ".$class." \n*/";

        $return .= "\n\n";
        $return .= "class ".$class."\n{".$render."\n}";

        $fp = fopen($file, 'w');
        fwrite($fp, $return);
        fclose($fp);
    }
}