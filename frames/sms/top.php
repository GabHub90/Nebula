<?php 
	function top() { ?>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" >
				<style type="text/css">
					@import url(stile.css);
				</style>
				<title>Invio SMS</title>

<?php
	}
	function stringaData() {
		$now=getdate();
		if ($now['wday']==0) $str="Dom ";
		elseif ($now['wday']==1) $str="Lun ";
		elseif ($now['wday']==2) $str="Mar ";
		elseif ($now['wday']==3) $str="Mer ";
		elseif ($now['wday']==4) $str="Gio ";
		elseif ($now['wday']==5) $str="Ven ";
		elseif ($now['wday']==6) $str="Sab ";
		$str=$str.date("d/m");
	return $str;		
	}
?>