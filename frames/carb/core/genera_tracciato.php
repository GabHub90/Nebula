<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_func.php');

$lista=$_POST['lista'];

// variabili tracciato PHP
$file="temp/carb.txt";

//cancellazione file vecchio se esiste
unlink($file);

if ($file_handler=fopen($file,'a')) {

	$carb='';
	
	$tot=array("num"=>0,"importo"=>0);

	//scrittura del FILE
	foreach ($lista as $line) {
	
		if ($carb!=$line['tipo_carb']) {
			
			$carb=$line['tipo_carb'];
			$txt='';
			
			if ($carb=='B') $txt="\n"."Veicoli Benzina"."\n";
			if ($carb=='D') $txt="\n"."Veicoli Diesel"."\n";
			
			fwrite($file_handler,$txt);			
		}
	
		$txt=$line['ID']."-".db_todata($line['d_stampa'])."-".$line['veicolo']."-".$line['importo']."\n";
			
		fwrite($file_handler,$txt);
		
		$tot['num']++;
		$tot['importo']+=$line['importo'];
		
	}
	
	
	$txt="\n"."buoni:".$tot['num']." importo:".$tot['importo'];
	
	fwrite($file_handler,$txt);
	
	//fwrite($file_handler,print_r($param));
	
	fclose($file_handler);
}

?>