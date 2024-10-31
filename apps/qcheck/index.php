<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('qcheck_class.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];
//contesto .......
//"ribbon":{"officina":"PV","qc_openType":"inserimento"}

//echo '<div>'.json_encode($nebulaParams).'</div>';

$qc=new qCheckApp($nebulaParams,$galileo);

$qc->draw();

//echo json_encode($galileo->getLog('query'));

?>

<!--
<script type="text/javascript">
    console.log(JSON.stringify(window._nebulaMain.contesto));
</script>
-->