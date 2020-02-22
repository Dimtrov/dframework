<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

use dFramework\core\db\Database;
use dFramework\core\exception\Exception;

/**
 * Paginator
 *
 * Make a pagination
 *
 * This code is not the integral work of dFramework developers. It was inspired sources found on the net
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemlibrary/validator
 * @credit      Simple PDO MySQL pagination class -  By Ademola Abisayo Paul (sayopaul) https://github.com/sayopaul
 * @credit      Generate a Pagination (with Bootstrap) V1.0.0 - By Zheness https://github.com/Zheness/Pagination/ Github Repo
 */


class dF_Paginator
{
    /**
     * @var Database
     */
    protected $db;
    /**
     * @var bool Specifie if we run query to found a pagination information
     */
    protected $run_query = true;
    /**
     * @var int The number of elements to display in the page.
     *
     * Example :
     * I would display 20 articles per pages,
     *  so I type : Paginator::init(['limit' => 20]);
     */
    protected $limit = 15;
    /**
     * @var int The number of links before and after the current page
     *
     * Example :
     * The current page is the 5, and I want 3 links after and before,
     *  so I type : Paginator::init(['inner_links' => 3]);
     *
     * In a pagination of 'V1' type, this property represents the number of button (navigation link) to display in the pagination
     * Example :
     * If I want to have 10 navigation button in the pagination
     *  so I type : Paginator::init(['inner_links' => 10]);
     */
    protected $inner_links = 5;
    /**
     * @var string The separtor between links.
     *
     * Available only in a "v2" pagination
     * Example :
     * I would display " - " between my links,
     *  so I type : Paginator::init(['separator' => ' - ']);
     */
    protected $separator = '---';
    /**
     * @var string The url in the links. You MUST include "{i}" where you want display the number of the page.
     *
     * Example :
     * I would display my articles on "articles.php?page=X" where X is the number of page.
     *  So I type : Paginator::init(['url' => 'articles.php?page={i}']);
     *
     * Why {i} ? Because the number of page can be placed everywhere.
     *  If you have your url like this "articles/month/08/page/X/sort/date-desc", you can place {i} instead of X.
     */
    protected $url = '?page={i}';
    /**
     * @var int
     */
    private $offset = 0;
    /**
     * @var int The current page
     *
     * Example :
     * The current page is the 5,
     *  so I type : Paginator::init(['current_page' => 5]);
     */
    protected $current_page;
    /**
     * @var int The maximum elements in your database. (Or other way)
     *
     * Example :
     * I have 200 articles in my database,
     *  so I type : Paginator::init(['max_item' => 200]);
     */
    protected $max_item;
    /**
     * @var bool
     */
    protected $return_data = true;
    /**
     * @var string
     */
    protected $table;
    /**
     * @var null
     */
    protected $query = null;
    /**
     * @var array
     */
    protected $query_args = [];
    /**
     * @var null
     */
    protected $query_count = null;
    /**
     * @var string
     */
    private $querystring = '';


    /**
     * dF_Paginator constructor.
     * @throws \dFramework\core\exception\DatabaseException
     */
    public function __construct()
    {
        $this->useDb('default');
    }

    /**
     * @param array $params
     * @throws \dFramework\core\exception\DatabaseException
     */
    public function init(array $params = [])
    {
        if(isset($params['run_query']))
        {
            if(!is_bool($params['run_query']))
            {
                Exception::show('The parameter "run_query" must be a boolean');
            }
            $this->run_query = $params['run_query'];
        }
        if(!empty($params['db']))
        {
            if(!is_string($params['db']))
            {
                Exception::show('The parameter "db" must be a string');
            }
            $this->useDb($params['db']);
        }
        if(!empty($params['limit']))
        {
            if(!is_int($params['limit']) OR $params['limit'] < 1)
            {
                Exception::show('The parameter "limit" must be a integer greater than 0');
            }
            $this->limit = $params['limit'];
        }
        if(!empty($params['inner_links']))
        {
            if(!is_int($params['inner_links']) OR $params['inner_links'] < 3)
            {
                Exception::show('The parameter "inner_links" must be a integer greater than 2');
            }
            $this->inner_links = $params['inner_links'];
        }
        if(!empty($params['separator']))
        {
            if(!is_string($params['separator']))
            {
                Exception::show('The parameter "separator" must be a string');
            }
            $this->separator = $params['separator'];
        }
        if(!empty($params['url']))
        {
            if(!is_string($params['url']))
            {
                Exception::show('The parameter "url" must be a string');
            }
            $this->url = $params['url'];
        }
        if(!empty($params['current_page']))
        {
            if(!is_int($params['current_page']) OR $params['current_page'] < 1)
            {
                Exception::show('The parameter "current_page" must be a integer greater than 0');
            }
            $this->current_page = $params['current_page'];
        }
        if(!empty($params['max_item']))
        {
            if(!is_int($params['max_item']) OR $params['max_item'] < 1)
            {
                Exception::show('The parameter "max_item" must be a integer greater than 0');
            }
            $this->max_item = $params['max_item'];
        }
        if(isset($params['return_data']))
        {
            if(!is_bool($params['return_data']))
            {
                Exception::show('The parameter "return_url" must be a boolean');
            }
            $this->return_data = $params['return_data'];
        }
        if(!empty($params['table']))
        {
            if(!is_string($params['table']))
            {
                Exception::show('The parameter "table" must be a string');
            }
            $this->table = $params['table'];
        }
        if(!empty($params['query']))
        {
            if(!is_string($params['query']))
            {
                Exception::show('The parameter "query" must be a string');
            }
            $this->query = $params['query'];
        }
        if(!empty($params['query_args']))
        {
            if(!is_array($params['query_args']))
            {
                Exception::show('The parameter "query_args" must be a array');
            }
            $this->query_args = $params['query_args'];
        }
        if(!empty($params['query_count']))
        {
            if(!is_string($params['query_count']))
            {
                Exception::show('The parameter "query_count" must be a string');
            }
            $this->query_count = $params['query_count'];
        }
    }

    /**
     * Render the final pagination
     *
     * @param string $version
     * @return array
     */
    public function pagine($version = 'v1') : array
    {
        $datas = [];
        $this->compile();
        $this->runQuery($datas);

        $version = strtolower($version);
        if($version == 'v2')
        {
            $datas['pagination'] = $this->makeV2();
        }
        else
        {
            $datas['pagination'] = $this->makeV1();
        }
        return $datas;
    }


    /**
     * @param string $db_name
     * @throws \dFramework\core\exception\DatabaseException
     */
    public function useDb(string $db_name = 'default')
    {
        $this->db = new Database($db_name);
    }

    /**
     * Initialise required parameters for the pagination
     */
    private function compile()
    {
        if(!empty($_GET))
        {
            $args = explode("&", $_SERVER['QUERY_STRING']);
            foreach($args as $arg)
            {
                $keyval = explode("=", $arg);
                if($keyval[0] != "page" And $keyval[0] != "url")
                {
                    $this->querystring .= "&" . $arg;
                }
            }
        }
        if(empty($this->current_page))
        {
            $this->current_page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1 ;
        }

        $this->offset = (($this->current_page - 1) * $this->limit);

        if(empty($this->query_count))
        {
            $this->query_count = 'SELECT COUNT(*) FROM '.($this->db->config['prefix'] ?? '').$this->table;
        }
        if(empty($this->query))
        {
            $this->query = 'SELECT * FROM '.($this->db->config['prefix'] ?? '').$this->table.' LIMIT '.$this->limit.' OFFSET '.$this->offset;
        }
    }

    /**
     * @param array $data
     */
    private function runQuery(array &$data)
    {
		if(true === $this->run_query)
		{
			if(empty($this->max_item))
			{
				$this->max_item = $this->db->pdo()->query($this->query_count)->fetchColumn();
			}
			if(true === $this->return_data)
			{
				$request = $this->db->pdo()->prepare($this->query);
				if(!empty($this->query_args) AND is_array($this->query_args))
				{
					foreach ($this->query_args As $key => $value)
					{
						$request->bindValue(
							is_int($key) ? $key + 1 : $key,
							$value,
							is_int($value) || is_bool($value) ? PDO::PARAM_INT : PDO::PARAM_STR
						);
					}
				}
				$request->execute();
				$data['data'] = $request->fetchAll();
				$request->closeCursor();
			}
		}
    }


    /**
     * Create the pagination (Version 1)
     *
     * @return string Pagination
     */
    private function makeV1() : string
    {
        $inner_links = ceil( $this->current_page + $this->inner_links);

        $html = '<nav><ul class="pagination df_custom_pagination">';
        $html .= '<li class="page-item"><a class="page-link" href="'.str_replace('{i}', 1, $this->url).$this->querystring.'"> First </a></li>';

        if($this->offset > 0 AND $this->current_page <= ceil($this->max_item/$this->limit))
        {
            $html .= '<li class="page-item"><a class="page-link" href="'.str_replace('{i}', ($this->current_page - 1), $this->url).$this->querystring.'"> << </a> </li>';
        }
        for($i= $this->current_page; $i<$inner_links;  $i++)
        {
            $active = ((($i-1) * $this->limit) == $this->offset) ? 'active' : '';

            if ($i <= ceil($this->max_item/$this->limit))
            {
                $html.= '<li class="page-item '.$active.'"><a class="page-link" href="'.str_replace('{i}', $i, $this->url).$this->querystring.'">'.$i.'</a></li>';
            }
        }
        if(($this->offset + $this->inner_links) < $this->max_item)
        {
            $html .= '<li class="page-item"><a class="page-link" href="'.str_replace('{i}', ($this->current_page + 1), $this->url).$this->querystring.'"> >> </a></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="'.str_replace('{i}', ceil($this->max_item/$this->limit), $this->url).$this->querystring.'"> Last </a></li>';
        $html .= '</ul></nav>';
        
        return $html;
    }

    /**
     * Generate the HTML pagination
     *
     * @uses $this->generateArrayPagination()
     * @return string Pagination in HTML. Use Bootstrap
     */
    private function makeV2() : string
    {
        $array_pagination = $this->generateArrayPagination();

        $html = '<nav><ul class="pagination df_custom_pagination">';
        if ($this->limit)
        {
            foreach ($array_pagination as $v)
            {
                if ($v == $this->separator)
                {
                    $html .= '<li class="page-item disabled"><a class="page-link">' . $this->separator . '</a></li>';
                }
                else if (preg_match("/<b>(.*)<\/b>/i", $v))
                {
                    $html .= '<li class="page-item active"><a class="page-link">' . strip_tags($v) . '</a></li>';
                }
                else
                {
                    $html .= '<li>' . $v . '</li>';
                }
            }
        }
        else
        {
            $html .= '<li class="page-item active"><span>1</span></li>';
        }
        $html .= '</ul></nav>';

        return $html;
    }

    /**
     * Generate the Pagination in array.
     *
     * @return array Each value is the link to display.
     */
    private function generateArrayPagination()
    {
        $array_pagination = [];
        $keyArray = 0;

        $subLinks = $this->current_page - $this->inner_links;
        $nbLastLink = ceil($this->max_item / $this->limit);

        if ($this->current_page > 1)
        {
            $array_pagination[$keyArray++] = '<a class="page-link" href="' . str_replace('{i}', 1, $this->url).$this->querystring.'">1</a>';
        }
        if ($subLinks > 2)
        {
            $array_pagination[$keyArray++] = $this->separator;
        }
        for ($i = $subLinks; $i < $this->current_page; $i++)
        {
            if ($i >= 2)
            {
                $array_pagination[$keyArray++] = '<a class="page-link" href="' . str_replace('{i}', $i, $this->url).$this->querystring.'">'.$i.'</a>';
            }
        }
        $array_pagination[$keyArray++] = '<b>' . $this->current_page . '</b>';

        for ($i = ($this->current_page + 1); $i <= ($this->current_page + $this->inner_links); $i++)
        {
            if ($i < $nbLastLink)
            {
                $array_pagination[$keyArray++] = '<a class="page-link" href="' . str_replace('{i}', $i, $this->url).$this->querystring.'">'.$i.'</a>';
            }
        }
        if (($this->current_page + $this->inner_links) < ($nbLastLink - 1))
        {
            $array_pagination[$keyArray++] = $this->separator;
        }
        if ($this->current_page != $nbLastLink)
        {
            $array_pagination[$keyArray++] = '<a class="page-link" href="'.str_replace('{i}', $nbLastLink, $this->url).$this->querystring.'">' . $nbLastLink . '</a>';
        }
        return $array_pagination;
    }
}
