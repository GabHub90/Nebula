<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/carb/classi/buono_pdf.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_carb.php');

$param=$_POST['param'];

$obj=new galileoCarb();
$nebulaDefault['carb']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

//{"ID":"0","dms":"infinity","veicolo":"9287","importo":"20","reparto":"RIT","id_rich":"1","id_esec":"1","nota":"prova","gestione":"NEBULA","causale":"VAR","pieno":"","flag_ris":"0","tipo_carb":"D","telaio":"WVWZZZ9NZ2D030404","targa":"BX725MF","des_veicolo":"POLO 1.4 TDI COMFORT","stato":""}

if ($param['d_creazione']=="") $param['d_creazione']=date('Ymd');
$param['d_stampa']=date('Ymd');
$param['importo']=$param['importo']==""?"0.00":number_format($param['importo'],2,'.','');

$param['mov_open']=0;

if ($param['stato']=='creato' || $param['stato']=='daris' || $param['stato']=='dacompletare') $param['mov_open']=1;

$json=array(
    "stato"=>0,
    "query"=>"",
    "errori"=>"",
    "pdf"=>""
);

$res=false;

if ($param['ID']!="0") {
    $galileo->executeUpdate('carb','CARB_buoni',$param,"ID='".$param['ID']."'");
    $res=$galileo->getResult();
}

else {
    //trova ID nuovo
    //valorizza $param['ID']
    $param['ID']=$galileo->executeNext('carb','CARB_buoni');

    if (!$param['ID'] || $param['ID']=="") die ("Errore ID database");

    //ESCLUDI "INCREMENT" ==> azzera increment ed aggiungi il campo a DEFAULT ed alla clausola NOTNULL
    if (!$galileo->disableIncrement('carb','CARB_buoni')) die ("Errore Disable Increment");

    $param['d_creazione']=date('Ymd');

    $galileo->executeInsert('carb','CARB_buoni',$param);

    //verifica l'inserimento (la tabella BUONI ha l'ID UNIQUE)
    $galileo->executeSelect('carb','CARB_buoni',"ID='".$param['ID']."'","");

    $fid=$galileo->preFetch('carb');

    while ($row=$galileo->getFetch('carb',$fid)) {
        $res=$row;
    }

}
/////////////////////////////////////////////////////////////////////////////////////

if ($res) {

    $causali=array();
    $collaboratori=array();
    $carburanti=array(
        "B"=>"Benzina",
        "D"=>"Diesel",
        "M"=>"Metano"
    );

    $galileo->executeSelect('carb','CARB_causali',"","");
    if ($galileo->getResult()) {
        $fid=$galileo->preFetch('carb');
        while ($row=$galileo->getFetch('carb',$fid)) {   
            $causali[$row['codice']]=$row;
        }
    }

    $galileo->getMaestroCollab('');
    if ($galileo->getResult()) {
        $fid=$galileo->preFetchBase('maestro');
        while ($row=$galileo->getFetchBase('maestro',$fid)) {   
            $collaboratori[$row['ID']]=$row;
        }
    }

    $pdf=new carbBuonoPDF('P','mm','A4');

    $pdf->AddPage();

    $pdf->nebulaBuono($param,$collaboratori,$causali,$carburanti);

    $json['pdf']=base64_encode($pdf->Output('pdf','S'));

    //echo $pdf->Output('pdf','S');
}

$json['stato']=$res?1:0;

//$json['query']=$galileo->getLog('query');
//$json['errori']=$galileo->getLog('errori');

echo json_encode($json);



?>