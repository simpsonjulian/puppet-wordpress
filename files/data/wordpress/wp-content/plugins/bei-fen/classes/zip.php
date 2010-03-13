<?php

class XinitBackupZipHelper
{
	function addFolderToZip($folder, $zip)
	{
		$folderhandle = opendir($folder);
		if($folderhandle)
		{
			while (($entry = readdir($folderhandle)) !== false)
			{
				if($entry!==false && $entry != '.' && $entry != '..')
				{
					if(is_dir($folder . $entry))
					{
						$this->addFolderToZip($folder . $entry . DS, $zip);
					}
					if(is_file($folder . $entry))
					{
						$zip->addFile($folder . $entry, str_replace(WP_BEIFEN_CURRENT_BACKUP,'',$folder . $entry));
					}
				}
			}
		}
	}

	function createZip($name,$folder)
	{
		$zip = new ZipArchive;
		$res = $zip->open($name, ZipArchive::CREATE);
		if ($res === TRUE) {
			$this->addFolderToZip($folder,$zip);
			$zip->close();
		}
	}
}
?>