<?php

class BeiFenBackup
{
	var $backup_name;
	var $backup_type;
	var $backup_timeout;
	var $compress_backup;
	var $Schedule;

	function BeiFenBackup($details)
	{
		$this->backup_name = $details['backup_name'];
		$this->backup_type = $details['backup_type'];
		$this->backup_timeout = $details['backup_timeout'];
		$this->compress_backup = $details['compress_backup'];

		if(array_key_exists('schedule_type', $details))
		{
			$this->Schedule = new BeiFenSchedule($details);
		}
		else
		{
			$this->Schedule = false;
		}
	}
}

?>