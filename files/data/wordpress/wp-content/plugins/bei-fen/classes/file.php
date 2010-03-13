<?php

class XinitBackupFileHelper
{
	function writeTextToFile($file, $text, $mode = 'w')
	{
		$fh = fopen($file, $mode);
		fwrite($fh, $text);
		fclose($fh);
	}
		
	function readTextFromFile($file)
	{
		$file_string = '';
		$handle = @fopen($file, "r");
		if ($handle)
		{
			while (!feof($handle)) {
				$file_string .= fgetss($handle, 4096);
			}
			fclose($handle);
		}
		return $file_string;
	}
	
	function deleteFolder($folder)
	{
		if(file_exists($folder))
		{
			$folderhandle = opendir($folder);
			if($folderhandle)
			{
				while (($entry = readdir($folderhandle)) !== false)
				{
					if(!$entry)
					{
						break;
					}
					if($entry != '.' && $entry != '..')
					{
						if(is_dir($folder . $entry . DS))
						{
							$this->deleteFolder($folder . $entry . DS);
						}
						elseif(is_file($folder . $entry))
						{
							unlink($folder . $entry);
						}
					}
				}
				closedir($folderhandle);
			}
			rmdir($folder);
		}
	}
	
	function destination_writable($destination, $error_list = array())
	{
		// folder exists?
		if(!file_exists($destination) && !is_dir($destination))
		{
			return $error_list;
		}
		// ???
		// add trailing slash, if not existing, Win/Unix????
		// ???
		if($destination[strlen($destination)-1] != DS)
		{
			$destination .= DS;
		}
		$folderhandle = opendir($destination);
		if($folderhandle)
		{
			while (($entry = readdir($folderhandle)) !== false)
			{				
				if(($entry == true) && ($entry != '.') && ($entry != '..'))
				{
					if(is_writable($destination . $entry))
					{
						if(is_dir($destination . $entry))
						{
							$error_list = $this->destination_writable($destination . $entry, $error_list);
						}
					}
					else
					{
						$error_list[] = 'File nw: ' . $destination . $entry . '<br/>';
					}
				}
			}
			closedir($folderhandle);
		}
		return $error_list;
	}
	
	function copyFolderRec($source, $destination, $exclude = false)
	{
		if($exclude!==false)
		{
			if(is_array($exclude))
			{
				foreach($exclude as $no_copy)
				{
					if($source==$no_copy)
						return;
				}
			}
			else
			{
				if($source==$exclude)
				{
					return;
				}
			}
		}
		if(!file_exists($destination))
		{
			mkdir($destination);
		}
		$folderhandle = opendir($source);
		if($folderhandle)
		{
			// add trailing slash, if not existing
			if($source[strlen($source)-1] != DS)
			{
				$source .= DS;
			}
			while (($entry = readdir($folderhandle)) !== false)
			{
				if(!$entry)
				{
					break;
				}
				if($entry != '.' && $entry != '..' && $source . $entry . DS != $destination)
				{
					if(is_dir($source.$entry))
					{
						$this->copyFolderRec($source . $entry . DS, $destination . $entry . DS, $exclude);
					}
					if(is_file($source . $entry))
					{
						copy($source . $entry, $destination . $entry);
					}
				}
			}
			closedir($folderhandle);
		}
	}
}
?>