<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2020, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2020, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2020, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core;

use dFramework\core\db\query\Builder;
use dFramework\core\exception\Exception;
use dFramework\core\loader\Load;
use dFramework\libraries\Api;
use Throwable;

/**
 * Model
 *
 * A global model of application
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/Model.php
 */
class Model extends Builder
{
    /**
     * Charge un model
     *
     * @param string|array $model
     * @param string|null $alias
     * @since 3.2
     * @throws \ReflectionException
     */
    final protected function loadModel($model, ?string $alias = null)
    {
        if (is_array($model))
        {
            foreach ($model As $k => $v)
            {
                if (is_string($k))
                {
                    $mod = $k;
                    $alias = $v;
                }
                else
                {
                    $mod = $v;
                    $alias = $v;
                }
                $this->loadModel($mod, $alias);
            }
        }
        else
        {
            $mod = explode('/', $model);
            $mod = end($mod);
            $property = strtolower((!empty($alias) AND is_string($alias)) ? $alias : $mod);

            $this->{$property} = Load::model($model);
        }
    }

    /**
     * Charge une api externe
     *
     * @param string $base_url
     * @param string $var
     * @since 3.2
     * @return void
     */
    final protected function useApi(string $base_url, string $var = 'api')
    {
        if (empty($this->{$var}) OR !$this->{$var} instanceof Api)
        {
            $this->{$var} = new Api;
        }
        $this->{$var}->baseUrl($base_url);
    }
    /**
     * Injecte un objet d'Api au model
     *
     * @param Api $api
     * @param string $var
     * @since 3.2
     * @return void
     */
    final public function initApi(Api $api, string $var = 'api')
    {
        $this->{$var} = $api;
    }

    /**
     * Execute un bloc de requete dans une transaction
     *
     * @param callable $function
     * @return mixed Result of the callback function
     */
    final public function transaction(callable $function)
    {
        try {
            $this->beginTransaction();

            $response = call_user_func($function, $this);

            $this->commit();

            return $response;
        }
        catch (Throwable $th) {
            $this->rollback();

            throw $th;
        }
    }



    /**
     * Verifie s'il existe un champ avec une donnee specifique dans une table de la base de donnee
     *
     * @param string|array $key Le nom du champ de la table
     * @param mixed $value La valeur recherchee
     * @param string $table La table dans laquelle on veut faire la recherche
     * @throws Exception
     * @return bool
     */
    final public function exist($key, $value, string $table = null) : bool
    {
        $process = false;
        if (empty($table) AND is_array($key) AND is_string($value))
        {
            $process = true;
            $data  = $key;
            $table = $value;
        }
        if (!empty($table) AND is_string($key))
        {
            $process = true;
            $data = [$key => $value];
        }

        if (true === $process)
        {
            return $this->from($table)->where($data)->count() > 0;
        }
        throw new Exception("Mauvaise utilisation de la methode exist(). Consultez la doc pour plus d'informations", 1);
    }

    /**
     * Verifie si une valeur n'existe pas deja pour une cle donnee
     *
     * @param array $dif
     * @param array $eq
     * @param string $table
     * @return bool
     */
    final public function existOther(array $dif, array $eq, string $table) : bool
    {
        $this->from($table);

        foreach ($dif As $key => $value)
        {
            $this->where($key, $value);
        }
        foreach ($eq As $key => $value)
        {
            $this->where($key . ' !=', $value);
        }

        return $this->count() > 0;
    }
}
