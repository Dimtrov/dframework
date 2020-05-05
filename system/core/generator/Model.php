<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.1
 */

 
namespace dFramework\core\generator;

use dFramework\core\dFramework;
use dFramework\core\loader\Load;
use dFramework\core\Model as CoreModel;
use dFramework\core\utilities\Chaine;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;

/**
 * generator\Model
 *
 * Generate a file for CRUD models class
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Generator
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.1
 * @file		/system/core/generator/Model.php
 */

final class Model extends Generator
{
    public function __construct()
    {
        parent::__construct();
        Load::helper('inflector');    
    }

    public function generate(string $class, string $dir = \MODEL_DIR) 
    {
        $dir = empty($dir) ? \MODEL_DIR : $dir;
        $class = preg_replace('#Model$#i', '', $class);

        $this->writeProperties($class, $render);
        $this->createFile($render, $class, $dir);
    }


      /**
     * Ecrit les proprietes de la classe, les getters et les setters
     *
     * @param string $class
     * @param array $properties
     * @param $render
     */
    protected function writeProperties($class, &$render)
    {
        $class_name = Chaine::toPascalCase($class.'Model');
        $generator = new ClassType($class_name);

        $generator 
            ->setExtends(CoreModel::class)
            ->addComment($class_name."\n")
            ->addComment('Generated by dFramework v'.dFramework::VERSION)
            ->addComment('Date : '.date('r'))
            ->addComment('PHP Version : '.phpversion())
            ->addComment('Model : '.preg_replace("#Model$#", '', $class_name));

        $pks = $this->manager->getKeys($class, 'PRI');

        /* Ajout de la methode de creation (C)*/
        $this->addCreateMethod($generator, compact('class'));
        
        /* Ajout de la methode de selection (R)*/
        $this->addReadMethod($generator, compact('class', 'pks'));

        /* Ajout de la methode de modification (U)*/
        $this->addUpdateMethod($generator, compact('class', 'pks'));

        /* Ajout de la methode de supression (D)*/
        $this->addDeleteMethod($generator, compact('class', 'pks'));

        /* Ajout de la methode pour compter le nombre d'elements */
        $this->addCountMethod($generator, compact('class'));

        $render = (string) $generator;
    }

    /**
     * Enregistre le code de la classe dans le fichiers
     *
     * @param $render
     * @param string $class
     * @param string $file
     */
    protected function createFile($render, $class, $dir)
    {
        $class_name = ucfirst(Chaine::toCamelCase($class)).'Model';

        $dir = str_replace(\MODEL_DIR, '', $dir);
        $dir = MODEL_DIR.trim($dir, '/\\');
        $dir = str_replace(['/', '\\'], DS, $dir);
        $dir = rtrim($dir, DS).DS;

        if (!is_dir($dir))
        {
            mkdir($dir);
        }
        $filename  = $dir.$class_name.'.php';
    
        $f = fopen($filename, 'w');
        if (!is_resource($f))
        {
            return false;
            exit("impossible de generer le model: ".$class);
        }
        fwrite($f, "<?php \n".$render);
        fclose($f);
    }


    private function addCreateMethod(ClassType &$generator, array $variables)
    {
        extract($variables);

        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->insert(\$$class) \n";
        $hydrate .= "\t->into('$class'); \n";
        $hydrate .= "\nreturn \$this->lastId();";

        $m = (new Method('create'))
            ->setPublic()
            ->addComment("Ajoute 1 ".\singular($class)." dans la base de donnés \n")
            ->addComment('@param array $'.$class)
            ->addComment('@return int|null')
            ->setReturnNullable()
            ->setReturnType('int')
            ->setParameters([
                (new Parameter($class))
                    ->setType('array')
            ])
            ->setBody($hydrate);
        $generator->addMember($m);
    }

    private function addReadMethod(ClassType &$generator, array $variables)
    {
        extract($variables);

        $class_name = Chaine::toPascalCase($class.'Entity');

    // Liste de tous les enregistrements
        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->select() \n";
        $hydrate .= "\t->from('$class'); \n";
        $hydrate .= "if (\$hydrate) { \n";
            $hydrate .= "\treturn \$this->result(DF_FCLA, ".$class_name."::class); \n";
        $hydrate .= "} \n";
        $hydrate .= "return \$this->result(); \n";
        $m = (new Method('read'))
            ->setPublic()
            ->addComment("Selectionne toutes les données de la table ".$class." \n")
            ->addComment('@param bool $hydrate')
            ->addComment('@return stdClass[]|'.$class_name.'[]')
            ->setParameters([
                (new Parameter('hydrate'))
                    ->setType('bool')
                    ->setDefaultValue(false)
            ])
            ->setBody($hydrate);
        $generator->addMember($m);
        

    // Enregistrement unique
        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->select() \n";
        $hydrate .= "\t->from('$class')";
        foreach ($pks As $pk) 
        {
            $hydrate .= "\n\t->where('".$pk." = ?')->params([$".$pk."])";
        }
        $hydrate .= "; \nif (\$hydrate) { \n";
            $hydrate .= "\treturn \$this->first(DF_FCLA, ".$class_name."::class); \n";
        $hydrate .= "} \n";
        $hydrate .= "return \$this->first(); \n";
        
        $m = (new Method('read_pk'))
            ->setPublic()
            ->addComment("Recupère les informations rélative à 1 ".\singular($class)." \n");
        $params = [];
        foreach ($pks As $pk) 
        {
            $m->addComment('@param mixed $'.$pk);
            $params[] = new Parameter($pk);
        }
        $m->addComment('@param bool $hydrate')
            ->addComment('@return stdClass|'.$class_name)
            ->setParameters(array_merge($params, [
                (new Parameter('hydrate'))->setType('bool')->setDefaultValue(false),
            ]))
            ->setBody($hydrate);
        $generator->addMember($m);


    // Enregistrements limitees
         $hydrate  = "\$this->free_db() \n";
         $hydrate .= "\t->select() \n";
         $hydrate .= "\t->from('$class') \n";
         $hydrate .= "\t->limit(\$limit, \$offset); \n";
         $hydrate .= "if (\$hydrate) { \n";
             $hydrate .= "\treturn \$this->result(DF_FCLA, ".$class_name."::class); \n";
         $hydrate .= "} \n";
         $hydrate .= "return \$this->result(); \n";
         $m = (new Method('read_limit'))
             ->setPublic()
             ->addComment("Selectionne les données de la table ".$class." par lots \n")
             ->addComment('@param int $limit')
             ->addComment('@param int $offset')
             ->addComment('@param bool $hydrate')
             ->addComment('@return stdClass[]|'.$class_name.'[]')
             ->setParameters([
                 (new Parameter('limit'))->setType('int'),
                 (new Parameter('offset'))->setType('int')->setDefaultValue(0),
                 (new Parameter('hydrate'))->setType('bool')->setDefaultValue(false),
             ])
             ->setBody($hydrate);
         $generator->addMember($m);

        
    // Enregistrements avec jointures de table
        $fks = $this->manager->getFks($class);
        if (count($fks) > 0)
        {
            $hydrate  = "\$one = false; \n";
            $hydrate .= "\$this->free_db() \n";
            $hydrate .= "\t->select() \n";
            $hydrate .= "\t->from('$class')";
            foreach ($fks As $fk) 
            {
                $hydrate .= "\n\t->join('".$fk->referenced_table_name."', '".$class.".".$fk->column_name." = ".$fk->referenced_table_name.".".$fk->referenced_column_name."', 'inner')";
            }
            $hydrate .= ";";
            foreach ($pks As $pk)
            {
                $hydrate .= "\nif (\$$pk !== null) {\n";
                    $hydrate .= "\t\$this->where('$pk = ?')->params([\$$pk]); \n";
                    $hydrate .= "\t\$one = true; \n";
                $hydrate .= "}";
            }
            $hydrate .= "\nif (\$hydrate) { \n";
                $hydrate .= "\tif (\$one) { \n";
                    $hydrate .= "\t\treturn \$this->first(DF_FCLA, ".$class_name."::class); \n";
                $hydrate .= "\t} \n";
                $hydrate .= "\treturn \$this->result(DF_FCLA, ".$class_name."::class); \n";
            $hydrate .= "} \n";
            $hydrate .= "if (\$one) { \n";
                $hydrate .= "\treturn \$this->first(); \n";
            $hydrate .= "} \n";
            $hydrate .= "return \$this->result(); \n";
            
            $m = (new Method('read_join'))
                ->setPublic();
            $params = [];
            foreach ($pks As $pk) 
            {
                $m->addComment('@param mixed|null $'.$pk);
                $params[] = (new Parameter($pk))->setDefaultValue(null)->setNullable();
            }
            $m->addComment('@param bool $hydrate')
                ->addComment('@return stdClass[]|'.$class_name.'[]|stdClass|'.$class_name)
                ->setParameters(array_merge($params, [
                    (new Parameter('hydrate'))->setType('bool')->setDefaultValue(false),
                ]))
                ->setBody($hydrate);
            $generator->addMember($m);
    
        }   
    }

    private function addUpdateMethod(ClassType &$generator, array $variables)
    {
        \extract($variables);

        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->set(\$$class) ";
        foreach ($pks As $pk) 
        {
            $hydrate .= "\n\t->where('".$pk." = ?')->params([$".$pk."])";
        }
        $hydrate .= "\n\t->update('$class'); \n";
        $m = (new Method('edit'))
            ->setPublic()
            ->addComment("Modifie les données rélatives à 1 ".\singular($class)." précise \n");
        $params = [];
        foreach ($pks As $pk) 
        {
            $m->addComment('@param mixed $'.$pk);
            $params[] = new Parameter($pk);
        }
        $m->addComment('@param array $'.$class)
            ->addComment('@return void')
            ->setParameters(array_merge($params, [
                (new Parameter($class))->setType('array'),
            ]))
            ->setBody($hydrate);
        $generator->addMember($m);

        
        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->set(\$$class) \n";
        $hydrate .= "\t->update('$class'); \n";
        $m = (new Method('refactor'))
            ->setPublic()
            ->addComment("Modifie toutes les données de la table ".$class." \n")
            ->addComment('@param array $'.$class)
            ->addComment('@return void')
            ->setParameters([
                (new Parameter($class))->setType('array'),
            ])
            ->setBody($hydrate);
        $generator->addMember($m);
    }

    private function addDeleteMethod(ClassType &$generator, array $variables)
    {
        \extract($variables);

        $hydrate  = "\$this->free_db() ";
        foreach ($pks As $pk) 
        {
            $hydrate .= "\n\t->where('".$pk." = ?')->params([$".$pk."])";
        }
        $hydrate .= "\n\t->delete('$class'); \n";
        $m = (new Method('remove'))
            ->setPublic()
            ->addComment("Supprime 1 ".\singular($class)." de la base de données \n");
        foreach ($pks As $pk) 
        {
            $m->addComment('@param mixed $'.$pk)
                ->addParameter($pk);
        }
        $m->addComment('@return void')
            ->setBody($hydrate);
        $generator->addMember($m);
        

        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->delete('$class'); \n";
        $m = (new Method('clear'))
            ->setPublic()
            ->addComment("Supprime tous les éléments de la table ".$class." \n")
            ->addComment('@return void')
            ->setBody($hydrate);
        $generator->addMember($m);
        

        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->truncate('$class'); \n";
        $m = (new Method('clean'))
            ->setPublic()
            ->addComment("Vide la table ".$class." \n")
            ->addComment('@return void')
            ->setBody($hydrate);
        $generator->addMember($m);
    }

    private function addCountMethod(ClassType &$generator, array $variables)
    {
        \extract($variables);

        $hydrate  = "\$this->free_db() \n";
        $hydrate .= "\t->from('$class'); \n";
        $hydrate .= "foreach (\$enreg as \$key => \$value) { \n";
            $hydrate .= "\t\$this->where(\$key.' = ?')->params([\$value]); \n";
        $hydrate .= "}\n";
        $hydrate .= "return \$this->count(); \n";
        $m = (new Method('count_'.$class))
            ->setPublic()
            ->addComment("Compte tous les enregistrements de la table ".$class." \n")
            ->addComment('@param array $enreg')
            ->addComment('@return int')
            ->setReturnType('int')
            ->setParameters([
                (new Parameter('enreg'))->setType('array')->setDefaultValue([]),
            ])
            ->setBody($hydrate);
        $generator->addMember($m);

    }
}
