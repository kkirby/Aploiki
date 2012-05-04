<?php
/*
+----------------------------------------------------------+
| Aploiki is distributed under the CC-GNU GPL license.     |
| You may NOT remove or modify any 'powered by' or         |
| copyright lines in the code.                             |
| http://creativecommons.org/licenses/GPL/2.0/             |
+----------------------------------------------------------+
| Made by Kyle Kirby                                       |
+----------------------------------------------------------+
*/
class time{
	function time($unix_formatted_timestamp = null,$timezone = null){
		if ($unix_formatted_timestamp == null)$this->unix_timestamp = time();
		else $this->unix_timestamp = $unix_formatted_timestamp;
		if ($timezone != null)$this->do_timezone($timezone);
	}
	
	function do_timezone($tz){
		global $_AEYNIAS;
		$this->unix_timestamp = $this->unix_timestamp + (($tz - ($_AEYNIAS['config']['time_zone_offset'])) * 3600);
	}
	
	function convert_to_stamp($format = null){
		if ($format == null)$format = "g:i:sa n/j/Y";
		return date($format,$this->unix_timestamp);
	}
	
	function array_info(){
		$convert = date("g-i-s-a-d-m-Y",$this->unix_timestamp);
		$date_time = explode('-',$convert);
		$array = array(
		'hour' => $date_time[0],
		'min' => $date_time[1],
		'sec' => $date_time[2],
		'a' => $date_time[3],
		'day' => $date_time[4],
		'month' => $date_time[5],
		'year' => $date_time[6]);
		return $array;
	}
	
}

/*
$time = new time($time = null, $timezone = null) // automatically grab the time and append the current time zone set up in the config file.
echo $time->convert_to_stamp() // print out the time/date in stamp formate (hour:minute/second am/pm day/month/year)
print_r($time->array_info()) // print out the time in an array form, see array_info function for structure.
*/
?>