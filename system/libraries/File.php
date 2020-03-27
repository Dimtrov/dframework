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
 * @homepage	https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

use dFramework\core\exception\Exception;

/**
 * File
 *
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/File.html
 * @since       2.0
 */


class dF_File
{
    /**
	 * Default path to an entity
	 * @var string
	 */
	private $path	= null;
    
    
    /**
     * @return string|null
     */
    public function getPath() : ?string 
    {
        return $this->path;
    }
    /**
     * @param string|null $path
     */
    public function setPath(?string $path = null)
    {
        $this->path = $path;
    }

    /**
     * Check if a path is a directory
     * 
     * @param string|null $path
     * @return bool
     */
    public function isDir(?string $path) : bool
    {
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);

        if(true !== @is_dir($path))
        {
            $this->setError('"'.$path.'" is not a directory or not exist', __LINE__, __FILE__);
            return false;
        }
		return true;
    }
    /**
     * Check if a path is a directory
     * 
     * @param string $file
     * @return bool
     */
    public function isFile(?string $path = null) : bool
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);

        if(true !== @is_file($path))
        {
            $this->setError('"'.$path.'" is not a file or not exist', __LINE__, __FILE__);
            return false;
        }
		return true;
    }

    /**
	 * Return the list of all entity included in the path
     * 
	 * @param string $path
	 * @param bool $withroot
	 * @return array|null
	 */
	public function elements($path = null, ?bool $withroot = true) : ?array
	{
        return $this->ls($path, $withroot, null);
    }
    /**
	 * Return the list of directories included in the path
	 * @param string $path
	 * @param bool $withroot
	 * @return array|null
	 */
	public function elements_d($path = null, ?bool $withroot = true) : ?array
	{
        return $this->ls($path, $withroot, 'DIR');
    }
    /**
	 * Return the list of files included in the path
	 * @param string $path
	 * @param bool $withroot
	 * @return array|null
	 */
	public function elements_f($path = null, ?bool $withroot = true) : ?array
	{
        return $this->ls($path, $withroot, 'FILE');
	}
    
    /**
	 * Return infos of all entity included in the path
     * 
	 * @param string $path
	 * @param bool $withroot
	 * @return array|false
	 */
    public function infos($path = null, ?bool $withroot = true) : ?array
    {
        return $this->ll($path, $withroot, null);
    }
    /**
	 * Return infos of all entity included in the path
     * 
	 * @param string $path
	 * @param bool $withroot
	 * @return array|null
	 */
    public function infos_d($path = null, ?bool $withroot = true) : ?array
    {
        return $this->ll($path, $withroot, 'DIR');
    }
    /**
	 * Return infos of all entity included in the path
     * 
	 * @param string $path
	 * @param bool $withroot
	 * @return array|null
	 */
    public function infos_f($path = null, ?bool $withroot = true) : ?array
    {
        return $this->ll($path, $withroot, 'FILE');
    }

    /**
     * Change the chmod of directory
     * 
     * @param string|null $path
     * @param int $mode
     * @return bool
     */
    public function chmod(?string $path = null, $mode = 0750) : bool
    {
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);

        if (!@chmod($path, $mode))
        {
            $this->setError('Cant Change the perms of "'.$path.'"', __LINE__, __FILE__);
            return false;
        }
        return true;
    }
	/**
	 * Return the mod of a file/directory
     * 
	 * Credits goes to Ambriel_Angel (www.ambriels.net)
	 * @param string|null $path
	 * @return int
	 */
	public function mod(?string $path = null) : int
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
		// Initialisation
		$val	= 0;
		$perms	= fileperms($path);
		
		// Owner; User
		$val += (($perms & 0x0100) ? 0x0100 : 0x0000);		// Read
		$val += (($perms & 0x0080) ? 0x0080 : 0x0000);		// Write
		$val += (($perms & 0x0040) ? 0x0040 : 0x0000);		// Execute

		// Group
		$val += (($perms & 0x0020) ? 0x0020 : 0x0000);		// Read
		$val += (($perms & 0x0010) ? 0x0010 : 0x0000);		// Write
		$val += (($perms & 0x0008) ? 0x0008 : 0x0000);		// Execute

		// Global; World
		$val += (($perms & 0x0004) ? 0x0004 : 0x0000);		// Read
		$val += (($perms & 0x0002) ? 0x0002 : 0x0000);		// Write
		$val += (($perms & 0x0001) ? 0x0001 : 0x0000);		//	Execute

		// Misc
		$val += (($perms & 0x40000) ? 0x40000 : 0x0000);	// temporary file (01000000)
		$val += (($perms & 0x80000) ? 0x80000 : 0x0000); 	// compressed file (02000000)
		$val += (($perms & 0x100000) ? 0x100000 : 0x0000);	// sparse file (04000000)
		$val += (($perms & 0x0800) ? 0x0800 : 0x0000);		// Hidden file (setuid bit) (04000)
		$val += (($perms & 0x0400) ? 0x0400 : 0x0000);		// System file (setgid bit) (02000)
		$val += (($perms & 0x0200) ? 0x0200 : 0x0000);		// Archive bit (sticky bit) (01000)

		return decoct($val);
	}

    /**
	 * Create a file with or without 
     * 
	 * @param string $path
	 * @param string $content
	 * @return bool
	 */
	public function mkfile(?string $path = null, string $content = '') : bool
	{
		$this->test_var($path, $this->path);
		
		if ($handle = fopen($path, 'w+'))
		{
            if (strlen($content) != 0)
            {
                fwrite($handle, $content);
            }
			fclose($handle);
			
			return true;
        }
        return false;
	}
    /**
	 * Read the content of a file 
     * 
	 * @param string $path
	 * @param bool $byline
	 * @param int $length
	 * @return string|array|false
	 */
	public function read_file(?string $path = null, ?bool $byline = false, int $length = 1024)
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
        if(true !== $this->isFile($path))
        {
            return false;
        }
		if($byline)
		{
			if($handle = fopen($path, 'r'))
			{
                while(true !== feof($handle)) 
                {
                    $lines[] = fgets($handle, $length);
                }
				fclose($handle);
				
				return $lines;
            }
            return false;
		}
		else
		{
			return file_get_contents($path);
		}
	}

    /**
	 * Create a directory with/without chmod
     * 
	 * @param string|null $path
	 * @param int|null $chmod
	 * @return bool
	 */
	public function mkdir(?string $path = null, ?int $chmod = null) : bool
	{
		$this->test_var($path, $this->path);
        $path = rtrim($path, DS);
        
		if(@mkdir($path))
		{
            if (!is_null($chmod))
            {
                $this->chmod($path, $chmod);
            }
			return true;
        }
        return false;
    }
    
    /**
	 * Move a file or a directory
     * 
	 * @param string $path
	 * @param string $where
	 * @return bool
	 */
	public function mv(string $path, string $where) : bool
	{	
        if(true !== $this->isDir($where))
        {
            return false;
        }
		if(true === $this->isDir($path))
		{
			$tree = $this->tree($path);
			$this->cp($path, $where);
			$this->rm($tree);
		}
		else if(true === $this->isFile($path))
		{
			$this->cp($path, $where);
			$this->rm($path);
		}
		return true;
    }
    /**
	 * Remove files or/and directories
     * 
	 * @param string|string[]|null $path
	 * @return bool
	 */
	public function rm($path = null)
	{
        $this->test_var($path, $this->path);
        $path = (array) $path;
		
		foreach($path As $file)
		{
            $file = rtrim($file, DS);
			if(true === $this->isDir($file))
			{
				$tree = $this->tree($file);
				rsort($tree);
				
				foreach($tree As $f)
				{
                    $f = rtrim($f, DS);
                    if(true === $this->isDir($f))
                    {
                        rmdir($f);
                    }
                    else if(true === $this->isFile($f))
                    {
                        unlink($f);
                    }
				}
			}
			else if (true === $this->isFile($file))
			{
				unlink($file);
            }
            return false;
		}
		return true;
    }
    /**
	 * Copy files or/and directories
     * 
	 * @param string $path
	 * @param string $where
	 * @return boo
	 */
	public function cp(?string $path = null, $where) : bool
	{	
        $where = rtrim($where, DS);
        if(true !== $this->isDir($where))
        {
            return false;
        }
        $this->test_var($path, $this->path);
		$path = (array) $path;
			
		foreach($path As $file)
		{
            $file = rtrim($file, DS);
            if(true === $this->isFile($file))
            {
                copy($file, $where.DS.$file);
            }
			else if(true === $this->isDir($file))
			{
				$files = $this->tree($file);
				$this->mkdir($where.DS.$file);
				
				foreach ($files As $f)
				{
                    if(true === $this->isFile($f))
                    {
                        copy($f, $where.DS.$f);
                    }
                    else if(true === $this->isDir($f))
                    {
                        $this->mkdir($where.DS.$f);
                    }	
				}
			}
		}
		return true;
    }
    
    
	/**
	 * Return infos concerning the entity
     * 
	 * @param string $path
	 * @param bool $withroot
	 * @param bool $content
	 * @param bool $byline
	 * @param int $length
	 * @return array
	 */
	public function details(string $path = null, bool $withroot = true, bool $content = false, bool $byline = false, int $length = 1024)
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
		if(true === $this->isDir($path))
		{
			if($handle = opendir($path))
			{
				$infos['type']			= 'dir';
				$infos['path_infos']	= pathinfo($path);
				$infos['atime']			= fileatime($path);
				$infos['ctime']			= filectime($path);
				$infos['mtime']			= filemtime($path);
				$infos['chmod']			= $this->mod($path);
				$infos['owner_id']		= fileowner($path);
                $infos['group_id']		= filegroup($path);
				if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') 
                {
                    $infos['owner_infos']	= posix_getpwuid($infos['owner_id']);
    				$infos['group_infos']	= posix_getgrgid($infos['group_id']);			
                }
				$infos['size']			= $this->filesize($path);
				$infos['files_count']	= 0;
				$infos['files']			= [];
				$infos['dir_count']		= 0;
				$infos['directories']	= [];
				
				while(false !== ($file = readdir($handle)))
				{
					if(true === $this->isDir($path.DS.$file)) 
					{
						if ($file != '..' AND $file != '.' AND $file != '')
						{
                            $infos['dir_count']++;
                            $infos['directories'][] = (true === $withroot) ? $path.DS.$file : $file;
						}
					}
					else if(true === $this->isFile($path.DS.$file))
					{
						$infos['files_count']++;
						$infos['files'][] = (true === $withroot) ? $path.DS.$file : $file;
					}
				}
				$infos['files']			= array_map([$this, 'format_path'], $infos['files']);
				$infos['directories']	= array_map([$this, 'format_path'], $infos['directories']);
				
				$this->sort_results($infos['directories']);
				$this->sort_results($infos['files']);
				
				closedir($handle);
				
				return $infos;
			}
			return false;
		}
		else if(true === $this->isFile($path))
		{
			if($handle = fopen($path, 'r')) 
			{
				$infos['type']			= 'file';
				$infos['path_infos']	= pathinfo($path);
				$infos['atime']			= fileatime($path);
				$infos['ctime']			= filectime($path);
				$infos['mtime']			= filemtime($path);
				$infos['chmod']			= $this->mod($path);
                $infos['owner_id']		= fileowner($path);
                $infos['group_id']		= filegroup($path);
				if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') 
                {
                    $infos['owner_infos']	= posix_getpwuid($infos['owner_id']);
    				$infos['group_infos']	= posix_getgrgid($infos['group_id']);			
                }
				$infos['lines_count']	= 0;
				$infos['size']			= $this->filesize($path);
				$infos['md5']			= md5_file($path);
				$infos['sha1']			= sha1_file($path);
				
                if(true === $content)
                {
					$infos['content']	= (true === $byline) ? [] : file_get_contents($path);
                }
				while(true !== feof($handle)) 
				{
                    if(true === $byline AND true === $content)
                    {
                        $infos['content'][] = fgets($handle, $length);
                    }
					$infos['lines_count']++;
				}
				fclose($handle);
				
				return $infos;
			}
			return false;
		}
		return false;
	}

	/**
	 * Return tree of a directory
     * 
	 * @param string|null $path
	 * @param bool $expand2files
	 * @return array
	 */
	public function tree(?string $path = null, $expand2files = true)
	{
		$this->test_var($path, $this->path);
        $path = rtrim($path, DS);
        
		$directories = $this->elements_d($path);
		
		for($x = 0; $x < count($directories); $x++)
		{
            if(true !== $this->isDir($directories[$x]))
            {
				continue;
            }	
			if($handle = opendir($directories[$x]))
			{
				while(false !== ($file = readdir($handle)))
				{
					if(true === $this->isDir($directories[$x].DS.$file)) 
					{
						if($file != '..' AND $file != '.' AND $file != '')
						{
							$directories[] = $directories[$x].DS.$file;
						}
					}
				}
				closedir($handle);
			}
			else
			{
				$directories[] = false;
			}
		}
        $directories[]	= $path;
		$directories	= array_map([$this, 'format_path'], $directories);
			
		if(true === $expand2files)
		{		 
			foreach($directories As $dir)
			{	
				$expanded_directories[] = $dir;
				
				if($handle = opendir($dir))
				{
                    while(false !== ($file = readdir($handle)))
                    {
                        if(true === $this->isFile($dir.DS.$file))
                        {
                            $expanded_directories[] = $dir.DS.$file;
                        }
                    }
				}
				else
				{
					$expanded_directories[] = false;
				}
			}

			$expanded_directories = array_map([$this, 'format_path'], $expanded_directories);			
			$this->sort_results($expanded_directories);
		}
		else
		{
			$this->sort_results($directories);
		}
		return (true === $expand2files) ? $expanded_directories : $directories;
    }
    
    /**
	 * Return the size of an entity
     * 
	 * @param string|null $path
	 * @return int
	 */
	public function filesize(?string $path = null) : int
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
        if(true === $this->isFile($path))
        {
			return filesize($path);
        }
        else
		{
			$tree = $this->tree($path);
			$size = 0;
			
            foreach($tree As $file)
            {
                if(true === $this->isFile($file))
                {
                    $size += filesize($file);
                }
            }
			return $size;
		}
	}
	
	/**
	 * Serialize and creates a file with the serial
     * 
	 * @param mixed $var
	 * @param string $path
	 * @return bool
	 */
	public function serialize($var, ?string $path = null) : bool
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
		return ($this->mkfile(serialize($var), $path));
    }
    /**
	 * Unserialize a file
     * 
	 * @param string|null $path
	 * @return array|false
	 */
	public function unserialize(?string $path = null)
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
        if($this->isFile($path))
        {
            return unserialize($this->read_file($path));
        }
        false;
	}

    /**
	 * Parse a ini file
     * 
	 * @param string|null $path
     * @param bool $withsection
	 * @return array|false
	 */
	public function parse_ini(?string $path = null, bool $whithsection = true)
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
        if(true === $this->isFile($path))
        {
            return parse_ini_file($path, $whithsection);
        }
		return false;
	}
	/**
	 * Make ini file
	 * @param array $content
	 * @param string $path
	 * @return bool
	 */
	public function mkini(array $content, ?string $path = null) : bool
	{
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
		
		$out = '';
		
		foreach($content As $key => $ini)
		{
			if(is_array($ini))
			{
				$out .= "\n[".$key."]\n\n";
				foreach($ini As $var => $value)
				{
					$out .= $var." \t\t= ".$this->quote_ini($value)."\n";
				}
			}
			else
			{
				$out .= $key." \t\t= ".$this->quote_ini($ini)."\n";
			}
		}
		return $this->mkfile($out, $path);
	}
	

	
    /* Private section									 
	------------------------------------------------- */	

    /**
	 * Return the list of all entity included in the path
     * 
	 * @param string $path
	 * @param bool $withroot
     * @param string|null $entity
	 * @return array|null
	 */
	private function ls($path = null, $withroot = true, ?string $entity = null) : ?array
    {
        $this->test_var($path, $this->path);
        $path = rtrim($path, DS);
        
        if(true !== $this->isDir($path))
        {
            return null;
        }
		if($handle = opendir($path))
		{
			while(false !== ($file = readdir($handle)))
			{
                if($file != '..' AND $file != '.' AND $file != '')
                {
                    if(empty($entity))
                    {
                        $infos[] = (true === $withroot) ? $path.DS.$file : $file;
                    }
                    else if(strtolower($entity) == 'dir' AND true === $this->isDir($path.DS.$file)) 
                    {
                        $infos[] = (true === $withroot) ? $path.DS.$file : $file;
                    }
                    else if(strtolower($entity) == 'file' AND true === $this->isFile($path.DS.$file)) 
                    {
                        $infos[] = (true === $withroot) ? $path.DS.$file : $file;
                    }
				}
			}
			closedir($handle);
			
			return array_map([$this, 'format_path'], $infos);
		}
		return null;
    }

    /**
	 * Return infos of all entity included in the path
	 * @param string $path
	 * @param bool $withroot
     * @param string|null $entity
	 * @return array|null
	 */
	private function ll($path = null, $withroot = true, $entity = null) : ?array
	{
		$this->test_var($path, $this->path);
        $path = rtrim($path, DS);
        
        if(true !== $this->isDir($path))
        {
            return null;
        }
		if($handle = opendir($path))
		{
            $infos = [];
			while(false !== ($file = readdir($handle))) 
			{
                $ech = null;
				if($file != '..' && $file != '.' && $file != '')
				{
                    if(true === $withroot)
                    {
                        $temp = $this->format_path($path.DS.$file);
                        $ech[$temp] = $this->details($temp);
                    }
                    else
                    {
                        $temp = $this->format_path($file);
                        $ech[$temp] = $this->details($path.DS.$file);
                    }
                    
                    if(empty($entity))
                    {
                        array_push($infos, $ech);
                    }
                    else if(strtolower($entity) == 'dir' AND true === $this->isDir($path.DS.$file)) 
                    {
                        array_push($infos, $ech);
                    }
                    else if(strtolower($entity) == 'file' AND true === $this->isFile($path.DS.$file)) 
                    {
                        array_push($infos, $ech);
                    }
				}
			}
			closedir($handle);
			
			return $infos;
        }
        return null;
	}

	/**
	 * Set a variable to $default parameter if it's null
	 * @param mixed $var
	 * @param mixed $default
	 */
	private function test_var(&$var, $default)
	{
        if (is_null($var) OR strlen(trim($var)) === 0)
        {
            $var = $default;
        }
	}
	
	/**
	 * Replace '//' by '/' in paths
	 * @param string $path
	 * @return string
	 */
	private function format_path($path)
	{
        $path = preg_replace('#\/{2,}#', '/', $path);
        //$path = preg_replace('#\\{2,}#', '\\', $path);
        return $path;
	}
	
	/**
	 * Quote ini var if needed
	 * @param anything $var
	 * @return anything
	 */
	private function quote_ini($var)
	{
		return (is_string($var)) ? '"'.str_replace('"', '\"', $var).'"' : $var;
	}
	
	/**
	 * Sort results
	 * @param array $array
	 * @return array
	 */
	private function sort_results(&$array)
	{
        if (is_array($array))
        {
            array_multisort(array_map('strtolower', $array), SORT_STRING, SORT_ASC, $array);
        }
    }
    
    private function setError($error,$linea="not specified",$archivo="undefined")
    {
       /* if (!$error) $this->error = "Unknow error";
       else $this->error = $error."<br><li> Line: ".$linea."</li><li> File:".$archivo; */
    }
	

}