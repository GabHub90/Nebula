<?php

function db_todata($db) {
	return substr($db,6,2)."/".substr($db,4,2)."/".substr($db,0,4);
}

function data_todb($data) {
	return substr($data,6,4).substr($data,3,2).substr($data,0,2);
}

function convert_date_tosql($txt) {
	//formato language ITALIAN
	return substr($txt,6,2)."-".substr($txt,4,2)."-".substr($txt,0,4);
}

function db2date($txt) {
	return substr($txt,0,4)."-".substr($txt,4,2)."-".substr($txt,6,2);
}

function input2db($txt) {
	return substr($txt,0,4).substr($txt,5,2).substr($txt,8,2);
}


function delta_tempo ($data_iniziale,$data_finale,$unita) {
	//la data_iniziale è quella minore
 
	$data1 = strtotime(db2date($data_iniziale));
	$data2 = strtotime(db2date($data_finale));
	 
		switch($unita) {
			case "m": $unita = 1/60; break; 	//MINUTI
			case "h": $unita = 1; break;		//ORE
			case "g": $unita = 24; break;		//GIORNI
			case "a": $unita = 8760; break;         //ANNI
		}
	 
	$differenza = (($data2-$data1)/3600)/$unita;
	 
	return $differenza;
}


?>