<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");

include('comest_liste.php');

$param=$_POST['param'];

if (  (!isset($param['ambito']) || $param['ambito']!='odl') && (!isset($param['ribbon']['comest_tt']) || $param['ribbon']['comest_tt']=="") && (!isset($param['ribbon']['comest_fornitore']) || $param['ribbon']['comest_fornitore']=="") && (!isset($param['ribbon']['comest_da']) || $param['ribbon']['comest_da']=="") && (!isset($param['ribbon']['comest_a']) || $param['ribbon']['comest_a']=="") ) die ('Nessun parametro di ricerca impostato!');

if (isset($param['ribbon']['comest_da']) && $param['ribbon']['comest_da']!="" && (!isset($param['ribbon']['comest_a']) || $param['ribbon']['comest_a']=="") ) die ('Non è stata impostata una data di fine!');
if (isset($param['ribbon']['comest_a']) && $param['ribbon']['comest_a']!="" && (!isset($param['ribbon']['comest_da']) || $param['ribbon']['comest_da']=="") ) die ('Non è stata impostata una data di inizio!');

$param['ribbon']['tipo']='archivio';

if (isset($param['ribbon']['comest_tt']) && $param['ribbon']['comest_tt']!="") $param['ribbon']['targa']=$param['ribbon']['comest_tt'];
if (isset($param['ribbon']['comest_tt']) && $param['ribbon']['comest_tt']!="") $param['ribbon']['telaio']=$param['ribbon']['comest_tt'];
if (isset($param['ribbon']['odl']) && $param['ribbon']['odl']!="" && isset($param['ribbon']['dms']) && $param['ribbon']['dms']!="") {
    $param['ribbon']['dms']!="";
    $param['ribbon']['odl']!="";
}
if (isset($param['ribbon']['comest_fornitore']) && $param['ribbon']['comest_fornitore']!="") $param['ribbon']['fornitore']=base64_decode($param['ribbon']['comest_fornitore']);
if (isset($param['ribbon']['comest_da']) && $param['ribbon']['comest_da']!="") $param['ribbon']['da']=mainFunc::gab_input_to_db($param['ribbon']['comest_da']);
if (isset($param['ribbon']['comest_a']) && $param['ribbon']['comest_a']!="") $param['ribbon']['a']=mainFunc::gab_input_to_db($param['ribbon']['comest_a']);


//echo json_encode($param);

$l=new comestListeApp($param,$galileo);

if (isset($param['ambito']) && $param['ambito']=='odl') {
    $l->drawListaOdl($param['new']);
}
else $l->draw();

?>