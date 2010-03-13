<?php

class BeiFenSchedule
{
	var $isFrequent;

	var $nextBackup;

	var $Frequence;

	function BeiFenSchedule($details)
	{
		if($details['schedule_type'] == 'Single')
		{
			$this->scheduleBackup($details['schedule_time_hour'],
								  $details['schedule_time_minute'],
								  $details['schedule_single_date_day'],
								  $details['schedule_single_date_month'],
								  $details['schedule_single_date_year']);
			$this->isFrequent = false;
			$this->Frequence = false;
		}
		else
		{
			$this->isFrequent = true;
			$this->Frequence = $details['schedule_frequence'];
			switch($this->Frequence)
			{
				case 'Daily':
					$this->scheduleBackup($details['schedule_time_hour'],
										  $details['schedule_time_minute'],
										  date('j'),
										  date('n'),
										  date('Y'),
										  $this->Frequence);
					break;
				case 'Weekly':
					for($x=1;$x<=8;$x++)
					{
						if(date('D', mktime(0,0,0,12,$x,2009))==substr($details['schedule_frequence_weekly'], 0, 3))
						{
							$next_day = date('j', mktime(0,0,0,date('n'),$x,date('Y')));
							$next_month = date('n', mktime(0,0,0,date('n'),$x,date('Y')));
							$next_year = date('Y', mktime(0,0,0,date('n'),$x,date('Y')));
						}
					}
					$this->scheduleBackup($details['schedule_time_hour'],
										  $details['schedule_time_minute'],
										  $next_day,
										  $next_month,
										  $next_year,
										  $this->Frequence);
					break;
				case 'Monthly':
					if($details['schedule_frequence_monthly']=='first')
					{
						$x = 1;
					}
					else
					{
						$x = date('t');
					}
					$next_day = date('j', mktime(0,0,0,date('n'),$x,date('Y')));
					$next_month = date('n', mktime(0,0,0,date('n'),$x,date('Y')));
					$next_year = date('Y', mktime(0,0,0,date('n'),$x,date('Y')));
					break;
			}
			while($this->isPastDue())
			{
				$this->rescheduleBackup();
			}
		}
	}

	function scheduleBackup($hour, $minute, $day, $month, $year, $frequence = false)
	{
		$this->nextBackup = mktime($hour, $minute, 0, $month, $day, $year);
		if($frequence)
		{
			switch($frequence)
			{
				case 'Daily':
					$this->Frequence = 'Daily';
					break;
				case 'Weekly':
					$this->Frequence = 'Weekly';
					break;
				case 'Monthly':
					$this->Frequence = 'Monthly';
					break;
				default:
					trigger_error('Invalid frequence: ' . $frequence, E_USER_ERROR);
					break;
			}
			$this->isFrequent = true;
		}
		else
		{
			$this->isFrequent = false;
		}
	}

	function isPastDue()
	{
		return (time()>=$this->nextBackup);
	}

	function rescheduleBackup()
	{
			switch($this->Frequence)
			{
				case 'Daily':
					$this->nextBackup = $this->nextBackup + (24 * 60 * 60);
					break;
				case 'Weekly':
					$this->nextBackup = $this->nextBackup +  (7 * 24 * 60 * 60);
					break;
				case 'Monthly':
					$new_hour = date("G", $this->nextBackup);
					$new_minute = date("i", $this->nextBackup);
					$new_month = date("n", $this->nextBackup) + 1;
					$year = date("Y", $this->nextBackup);
					if(date("j", $this->nextBackup)==1)
					{
						$new_day = 1;
					}
					else
					{
						$temp = mktime(0, 0, 0, date("n", $this->nextBackup) + 1, 1, $year);
						$new_day = date('t', $temp);
					}
					$this->nextBackup = mktime($new_hour, $new_minute, 0, $new_month, $new_day, $year);
					break;
			}
	}
}

?>