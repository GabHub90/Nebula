<?php
	
	function str_to_ts($str) {
		return mktime(0,0,0,(int)substr($str,4,2),(int)substr($str,6,2),(int)substr($str,0,4));
	}
	
	function str_to_db($str) {
		return substr($str,6,4).substr($str,3,2).substr($str,0,2);
	}
	
	function db_todata($db) {
		return substr($db,6,2)."/".substr($db,4,2)."/".substr($db,0,4);
	}
	
	function format_tohtml($txt) {
		return substr($txt,6,2)."/".substr($txt,4,2)."/".substr($txt,0,4);
	}
	
	function format_todb($txt) {
		return substr($txt,6,4).substr($txt,3,2).substr($txt,0,2);
	}
	
	function calcola_delta_ore($inizio,$fine) {
		$minuti_fine=((int)substr($fine,0,2))*60+(int)substr($fine,3,2);
		$minuti_inizio=((int)substr($inizio,0,2))*60+(int)substr($inizio,3,2);
		
		$delta_minuti=$minuti_fine-$minuti_inizio;
		$temp_ore=round($delta_minuti/60,2);
		//parte decimale di ore
		$dec_ore=ceil($temp_ore)-$temp_ore;
		
		if ($dec_ore>0.75) $dec_ore=1;
		elseif ($dec_ore>0.25) $dec_ore=0.5;
		else $dec_ore=0;
		
		return floor($delta_minuti/60)+$dec_ore;
	}
	
	function stringtomin($str) {
		return ((int)substr($str,0,2)*60)+(int)substr($str,3,2);
	}
	
	function mintostring($min) {
		$h=floor($min/60);
		$m=$min-($h*60);
		
		$str="";
		
		if ($h<10) $str.='0';
		$str.=$h.':';
		if ($m<10) $str.="0";
		$str.=$m;
		
		return $str;
	}
	
	function validate_cell($arr) {
		$res=array();
		foreach ($arr as $value) {
			if(preg_match("/^3[0-9]+/", $value,$match)) {
				//$res[]=$match[0];
				if (strlen($match[0])>8 && strlen($match[0])<11) $res[]=$match[0];
			}
		}
		return $res;
	}
	
	function validate_mail($str) {
			if(filter_var($str, FILTER_VALIDATE_EMAIL)) return filter_var($str, FILTER_VALIDATE_EMAIL);
			else return "";
	}
	
?>