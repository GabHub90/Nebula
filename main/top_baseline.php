<?php

include("baseline.php");
include_once("logged.php");

//la funzione js openApp per sicurezza slasha tutti i valori
foreach ($_REQUEST as $k=>$v) {
    $nebulaParams[$k]=stripslashes($v);
}

//////////////////////////////////////////////////////////
//CONTESTO LOG IN
//////////////////////////////////////////////////////////

//se è stato specificato un utente ed una applicazione acquisirne il valore
$nebulaContesto=array(
    "cookieLogged"=>(isset($_COOKIE['nebulaLogged']))?$_COOKIE['nebulaLogged']:"",
    "mainLogged"=>(array_key_exists("mainLogged",$nebulaParams))?$nebulaParams['mainLogged']:"",
    "mainApp"=>(array_key_exists("mainApp",$nebulaParams))?$nebulaParams['mainApp']:""
);

$pw=(array_key_exists("mainPassword",$nebulaParams))?$nebulaParams['mainPassword']:"";

//$nebulaLogged=new nebulaLogged($nebulaContesto['cookieLogged'],$nebulaContesto['mainLogged'],$pw,$nebulaContesto['mainApp'],$galileo);
$nebulaLogged=new nebulaLogged($pw,$nebulaContesto,$galileo);

//lettura dei blocchi di codice specifici per l'applicazione
$nebulaLogged->initApp();

//////////////////////////////////////////////////////////
//FUNZIONI SPECIFICHE APPLICAZIONE
//////////////////////////////////////////////////////////
//memorizza le funzioni abilitate in GALILEO
//$nebulaFunzioni=$galileo->getFunzioniDefault();
//setta gli oggetti per le funzioni richieste dall'applicazione che viene aperta (se viene aperta)
?>

<html>
<head>
    <?php
        $nebulaLogged->drawHead();
	?>
</head>

<!--quando si clicca o si preme un tasto sul sito viene verificato il tempo di inattività-->
<!--body onclick="window._nebulaMain.checkTime();" onkeypress="window._nebulaMain.checkTime();"-->
<body>

    <!-- visualizzazione demandata all'oggetto nebulaLogged -->

    <div class="nebulaTopBaseline" style="background-image: url('http://<?php echo $_SERVER['SERVER_ADDR']; ?>/nebula/main/img/barra_galassie.png');">
        <?php
            $nebulaLogged->drawTopLine();
        ?>

        <div>
            <?php
                //echo json_encode($nebulaLogged->getLog());
            ?>
        </div>
    </div>

    <div id="nebulaTopDiv" class="nebulaTopDiv">

        <?php
            if (isset($nebulaParams['linkFunk'])) $link=$nebulaParams['linkFunk'];
            else $link="";
            $nebulaLogged->startApp($link);
        ?>
        
    </div>

</body>

</html>