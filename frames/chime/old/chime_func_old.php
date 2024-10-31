<?php
	function get_report_menu_lines($reparto) {
		echo '<div>'.$param.'</div>';
		$query='SELECT ID,tag,descrizione FROM report WHERE reparto="'.$reparto.'" AND attivo=1';
		if($result=mysqli_query($query)) {
			while ($row=mysqli_fetch_array($result)) {
				echo'<div class="menuline" onclick="chg_report('.$row[ID].',\''.addslashes($row[descrizione]).'\',\''.addslashes($row[tag]).'\');">'.$row[tag].'</div>';
			}
		}
	}
	
	function get_sms_menu_lines($indice) {
		$obj=array();
		$query='SELECT t2.ID, t2.testo, t2.tag, t1.def FROM rep_sms AS t1 INNER JOIN sms AS t2 ON t1.sms=t2.ID WHERE t1.report='.$indice.' AND t2.attivo=1';
		if($result=mysqli_query($query)) {
			while ($row=mysqli_fetch_assoc($result)) {
				$obj[]=$row;
			}
		}
		//$str='{';
		//$x=0;
		//foreach ($obj as $obj2) {
			//if ($x!=0) $str.=',';
			//$str.='"'.$x.'":'.json_encode($obj2);
			//$x++;
		//}
		//$str.='}';
		$str=json_encode($obj);
		echo 'var _sms='.$str.';';
	}
	
	function get_mail_menu_lines($indice) {
		$obj=array();
		$query='SELECT t2.ID, t2.codice, t2.tag, t2.descrizione, t1.def FROM rep_mail AS t1 INNER JOIN mail AS t2 ON t1.mail=t2.ID WHERE t1.report='.$indice.' AND t2.attivo=1';
		if($result=mysqli_query($query)) {
			while ($row=mysqli_fetch_assoc($result)) {
				$obj[]=$row;
			}
		}
		//$str='{';
		//$x=0;
		//foreach ($obj as $obj2) {
			//if ($x!=0) $str.=',';
			//$str.='"'.$x.'":'.json_encode($obj2);
			//$x++;
		//}
		//$str.='}';
		$str=json_encode($obj);
		echo 'var _mail='.$str.';';
	}
	
	function get_link_menu_lines($indice) {
		$obj=array();
		$query='SELECT t2.ID, t2.tag, t2.descrizione, t1.def FROM rep_link AS t1 INNER JOIN link AS t2 ON t1.link=t2.ID WHERE t1.report='.$indice.' AND t2.attivo=1';
		if($result=mysqli_query($query)) {
			while ($row=mysqli_fetch_assoc($result)) {
				$obj[]=$row;
			}
		}
		//$str='{';
		//$x=0;
		//foreach ($obj as $obj2) {
			//if ($x!=0) $str.=',';
			//$str.='"'.$x.'":'.json_encode($obj2);
			//$x++;
		//}
		//$str.='}';
		$str=json_encode($obj);
		echo 'var _link='.$str.';';
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