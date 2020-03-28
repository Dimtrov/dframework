<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


namespace dFramework\core\loader;

/**
 * ClassMapper
 *
 *  Load a file the application require
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.0
 */
class ClassMapper 
{
	/**
	 * @var array Les dossiers a scanner
	 */
	protected $paths = [SYST_DIR];
	/**
	 * @var bool specifie si on doit scanner les dossier de façon reccusive ou non
	 */
	protected $recusive = true;
	/**
	 * @var array La liste des repertoires a ignorer lors du scan
	 */
	protected $excluded_paths = [
		SYST_DIR.'core', 
		SYST_DIR.'constants'
	];

	protected $excluded_folders = [];
	/**
	 * @var array Les fichiers a exclure lors du scan
	 */
	protected $excluded_files = [
		SYST_DIR.'Autoloader.php'
	];
	protected $file_extensions = array();
	protected $excluded_file_extensions = array();
	
	protected $file_extensions_regex = '';
	/**
	 * Le tableau contenant l'ensemble  des classes mappées
	 */
	protected $classes_map = [];

	/**
	 * Classes_Mapper constructor.
	 *
	 * @param array $paths where to parse
	 *
	 * @param array $options {
	 *
	 * @type bool $recusive switches parse logic from recursive to flat fetch in the folder
	 * @type array $excluded_paths these entire paths will be excluded from parsing
	 * @type array $excluded_folders files in this folders will be excluded from parsing
	 * @type array $excluded_files file paths that will be omitted during the parsing
	 * @type array $file_extensions file with this extensions will be parsed, using this option don't forget to add 'php' - default: array( 'php' )
	 * }
	 *
	 */
	public function __construct(?array $paths = [], ?array $options = []) 
	{
		if(!empty($paths))
		{
			$this->paths = $paths;
		}
		if(!empty($options)) 
		{
			$this->set_options($options);
		}
		$this->set_file_extensions_regex();
	}

	/**
	 * @param array $options Les options de scan
	 * @return void
	 */
	protected function set_options(array $options) : void 
	{
		$options_keys = [
			'recusive',
			'excluded_paths',
			'excluded_folders',
			'excluded_files',
			'file_extensions',
		];
		foreach ($options_keys As $opt) 
		{
			if (isset($options[$opt])) 
			{
				if (is_array($options[$opt])) 
				{
					$this->$opt = array_filter($options[$opt], 'is_string');
				} 
				else 
				{
					$this->$opt = $options[$opt];
				}
			}
		}
	}

	/**
	 * @return void
	 */
	protected function set_file_extensions_regex() : void 
	{
		$extensions = 'php';
		$regex      = "/[a-z0-9_-]+\.#exts#$/i";

		if (!empty($this->file_extensions)) 
		{
			$extensions = '(' . implode('|', array_map('trim', $this->file_extensions)) . ')';
		}
		$this->file_extensions_regex = str_replace('#exts#', $extensions, $regex);
	}

	public function process() : self 
	{
		foreach ($this->paths As $path) 
		{
			if (true === $this->recusive)
			{
				$iterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
				$iterator = new \RecursiveIteratorIterator($iterator);
			} 
			else 
			{
				$iterator = new \DirectoryIterator($path);
			}

			/**
			 * @var $file_info \SplFileInfo
			 */
			foreach ($iterator as $file_info) 
			{
				$realpath = $file_info->getRealPath();

				if (true !== $this->file_has_valid_extension($realpath) OR
				    true === $this->in_excluded_path($realpath) OR
				    true === $this->in_excluded_folder($realpath) OR
				    true === $this->is_excluded_file($realpath)
				) 
				{
					continue;
				}
				$file_data = file_get_contents($realpath);

				if (empty($file_data)) 
				{
					continue;
				}
				foreach($this->paths As $path)
				{
					if($path === SYST_DIR)
					{
						$realpath = '{SYST_DIR}'.str_replace(SYST_DIR, '', $realpath);
					}
				}
				$tokens            = token_get_all($file_data, TOKEN_PARSE);
				$entities          = $this->parse_tokens($tokens, $file_data);
				$classes_map       = array_combine($entities, array_fill(0, count($entities), $realpath));
				$this->classes_map = array_merge($this->classes_map, $classes_map);
			}
		}
		return $this;
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	protected function file_has_valid_extension(string $file) : bool 
	{
		return (bool) preg_match($this->file_extensions_regex, $file);
	}
	/**
	 * @param string $file
	 * @return bool
	 */
	protected function in_excluded_path(string $file) : bool 
	{
		$dirname = pathinfo($file, PATHINFO_DIRNAME);
		foreach ($this->excluded_paths As $excluded_path) 
		{
			if (strpos($dirname, $excluded_path) !== false) 
			{
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $file
	 * @return bool
	 */
	protected function in_excluded_folder(string $file) : bool 
	{
		$dirname = pathinfo($file, PATHINFO_DIRNAME);
		foreach ($this->excluded_folders As $excluded_path) 
		{
			if ($dirname === rtrim( $excluded_path, DIRECTORY_SEPARATOR))
			{
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $file
	 * @return bool
	 */
	protected function is_excluded_file(string $file) : bool 
	{
		foreach ( $this->excluded_files as $excluded_path) 
		{
			if (rtrim($file, DIRECTORY_SEPARATOR) === rtrim($excluded_path, DIRECTORY_SEPARATOR)) 
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * @var array $tokens 
	 * @var string $file_data
	 * @return array
	 */
	protected function parse_tokens(array $tokens, string $file_data) : array 
	{
		$data              = [];
		$current_namespace = '';

		for ( $i = 0; $i < count( $tokens ); $i ++ ) {
			$value = null;
			$token = $tokens[$i];
			if (is_array($token)) 
			{
				$token_type = $token[0];

				switch ( $token_type )
				{
					case T_NAMESPACE:
						$i++;
						$current_namespace = $this->parse_token($tokens, $i, 'parse_namespace');
						break;
					case T_CLASS:
					case T_INTERFACE:
					case T_TRAIT:
						$i++;
						$value = $this->parse_token($tokens, $i, 'parse_entity');
						break;
					case T_FUNCTION:
						$i++;
						$value = $this->parse_token($tokens, $i, 'parse_entity');
						if($this->is_function_method($value, $file_data)) 
						{
							$value = null;
						}
						break;
					case T_CONSTANT_ENCAPSED_STRING:
						$i ++;
						$value = $this->parse_token($tokens, $i, 'parse_constant');
						break;
					default;

						break;
				}
				if (!empty($value)) 
				{
					$data[] = $this->concat_namespace_with_entity($current_namespace, $value);
				}
			}
		}
		return $data;
	}
	/**
	 * @var array $tokens
	 * @var int $i
	 * @var callable $callback
	 * @return string
	 */
	protected function parse_token(array $tokens, &$i, $callback) : string 
	{
		$value = '';
		for ( ; $i < count($tokens); $i++) 
		{
			$result = call_user_func([$this, $callback], $tokens[$i][0]);
			if($result === true) 
			{
				$value .= $tokens[$i][1];
			} 
			else if($result === false) 
			{
				break;
			} 
			else if($result === null) 
			{
				continue;
			}
		}
		return $value;
	}

	/**
	 * @var string $namespace
	 * @var string $entity
	 * @return string
	 */
	protected function concat_namespace_with_entity(string $namespace, string $entity) : string 
	{
		return ltrim($namespace.'\\'.$entity, '\\');
	}
	/**
	 * @var $token_type
	 * @return bool|null
	 */
	protected function parse_namespace($token_type) : ?bool 
	{
		if($token_type === ';') 
		{
			return false;
		}
		if(in_array($token_type, [T_STRING, T_NS_SEPARATOR], true)) 
		{
			return true;
		}
		return null;
	}
	/**
	 * @var $token_type
	 * @return bool|null
	 */
	protected function parse_entity($token_type) : ?bool 
	{
		if($token_type === T_WHITESPACE) 
		{
			return null;
		}
		if($token_type === T_STRING) 
		{
			return true;
		}
		return false;
	}
	/**
	 * @var $token_type
	 * @return bool|null
	 */
	protected function parse_constant($token_type) : ?bool 
	{
		if($token_type === T_WHITESPACE OR $token_type == '=') 
		{
			return null;
		}
		if (in_array($token_type, [T_STRING, T_DNUMBER, T_LNUMBER])) 
		{
			return true;
		}
		return false;
	}

	/**
	 * @var string $func_name
	 * @var string $file_data
	 * @return bool
	 */
	protected function is_function_method(string $func_name, string $file_data) : bool 
	{
		return (bool) preg_match("/(?:class|interface|trait).*?\{.*?{$func_name}.*?\}.*}/ms", $file_data);
	}

	/**
	 * @return array
	 */
	public function get_result_as_array() : array 
	{
		return $this->classes_map;
	}
	/**
	 * @return string
	 */
	public function get_result_as_json() : string 
	{
		return json_encode($this->get_result_as_array());
	}

	/**
	 * @var string $file_path
	 * @return bool
	 */
	public function export_result_in_file(string $file_path ) : bool 
	{
		try 
		{
			$contentToWrite  = "<?php\n";
        	$contentToWrite .= "/**\n";
        	$contentToWrite .= " * Do not edit\n";
        	$contentToWrite .= " * Generated by ".__CLASS__." at ".date("Y-m-d H:i:s")."\n";
        	$contentToWrite .= " */\n";
        	$contentToWrite .= "return ".var_export($this->get_result_as_array(), true).";";
			
			$fp = fopen($file_path, 'w');
        	fwrite($fp, $contentToWrite);
        	fclose($fp);
        	chmod($file_path, 0775);
			return true;
		} 
		catch ( \Exception $e )
		{
			return false;
		}
	}
	/**
	 * @var string $file_path
	 * @return bool
	 */
	public function export_result_in_json_file(string $file_path ) : bool 
	{
		try 
		{
			file_put_contents($file_path, $this->get_result_as_json());

			return true;
		} 
		catch ( \Exception $e ) 
		{
			return false;
		}
	}
}