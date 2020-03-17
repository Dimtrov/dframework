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
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

use dFramework\core\db\Query;
use dFramework\core\exception\Exception;
use Faker\Factory As Faker;

/**
 * dF_Populate
 *
 * Genere du faux contenu pour remplir une base de donnees
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Populate.html
 * @since       2.1
 * @file        /system/libraries/Populate.php
 */

class dF_Populate
{
    private $locale = 'fr_FR';

    private $table = null;
    /**
     * @var string
     */
    private $use_db = 'default';
    /**
     * @var array
     */
    private $datas = [];
    /**
     * @var int
     */
    private $rows = 5;


    /**
     * Specifie la langue a utiliser pour generer des phrases aleatoire
     *
     * @param string $locale la locale a utiliser (ex: fr_FR)
     * @return dF_Populate
     */
    public function locale(string $locale) : self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Specifie la table a remplir
     *
     * @param string $table la table
     * @param string $use_db la configuration de base de donnees a utiliser
     * @return dF_Populate
     */
    public function table(string $table, $use_db = 'default') : self
    {
        $this->table = $table;
        $this->use_db = $use_db;
        return $this;
    }

    /**
     * Defini les type de donnees a generer pour chaque colone qu'on souhaite remplir dans la base de donnees
     *
     * @param array|string $field Le champ a remplir
     * @param null|string $function La fonction designant le type de donnees a generer
     * @return dF_Populate
     */
    public function generate($field, ?string $function = null) : self
    {
        if(is_array($field))
        {
            $this->datas = array_merge($this->datas, $field);
        }
        if(is_string($field) AND null !== $function)
        {
            $this->datas = array_merge($this->datas, [$field => $function]);
        }
        return $this;
    }

    /**
     * Specifie le nombre de ligne a inserer dans la base de donnees
     *
     * @param int $rows nombre de ligne
     * @return dF_Populate
     */
    public function rows(int $rows) : self
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     *  Lance la generation des donnees
     */
    public function run()
    {
        try
        {
            $query = new Query($this->use_db);
            $table = $query->db->config['prefix'].$this->table;

            for($i = 0; $i < $this->rows; $i++)
            {
                list($statement, $params) = $this->buildInsert($table);
                $query->query($statement, $params);
            }
            
        }
        catch (\Exception $e)
        {
            Exception::Throw($e);
        }
    }


    /**
     * Associe de facon aleatoire les cles de differentes tables dans une table d'association
     *
     * @param string $merge La table d'associaition
     * @param array $tables Les couples tables/cles a associer
     * @param string $use_db La configuration  de la base de donnees a utiliser
     */
    public function join(string $merge, array $tables, string $use_db = 'default')
    {
        $tab_values = [];
        $tab1 = $tab2 = null;
        $field1 = $field2 = null;
        $query = new Query($use_db ?? 'default');
        $merge = $query->db->config['prefix'].$merge;

        $i = 0;
        foreach ($tables As $key => $value)
        {
            $key = explode('.', $key);
            $table = $query->db->config['prefix'].$key[0];

            $t = $query->query('SELECT '.$key[1].' FROM '.$table)->fetchAll(PDO::FETCH_NUM);
            foreach ($t As $k => $v)
            {
                $tab_values[$key[0]][] = $v[0];
            }
            if(++$i === 1)
            {
                $tab1 = $key[0];
                $field1 = $value;
            }
            else
            {
                $tab2 = $key[0];
                $field2 = $value;
            }
        }
        $generator = Faker::create($this->locale);

        foreach ($tab_values[$tab1] As $value)
        {
            $randomKeys = $generator->randomElements($tab_values[$tab2], rand(0, count($tab_values[$tab2])));
            foreach ($randomKeys As $key)
            {
                $nbr = $query->query("SELECT COUNT(*) FROM ".$merge." WHERE {$field1} = ? AND {$field2} = ?", [$value, $key])->fetchColumn();
                if($nbr < 1) {
                    $query->query("INSERT INTO ".$merge."({$field1}, {$field2}) VALUES(?, ?)", [$value, $key]);
                }
            }
        }
    }



    /**
     * @param string $table
     * @return array
     */
    private function buildInsert(string $table)
    {
        $generator = Faker::create($this->locale);

        $parts = ['INSERT INTO '.$table];
        $columns = [];
        $values = [];
        $params = [];

        foreach ($this->datas As $key => $value)
        {
            $columns[] = $key;
            $values[] = '?';

            $value = explode('[', $value);
            $formatter = $value[0];
            $arguments = [];
            if(isset($value[1]))
            {
                $value = explode(']', $value[1])[0];
                $arguments = explode(',', $value);
            }
            $params[] = $generator->format($formatter, $arguments);
        }
        $parts[] = '(' . join(', ', $columns) . ')';
        $parts[] = 'VALUES (' . join(', ', $values) . ')';

        return [join(' ', $parts), $params];
    }
}