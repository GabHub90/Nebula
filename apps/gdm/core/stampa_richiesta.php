<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
define('FPDF_FONTPATH',$_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/fonts/');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/utf8graffa.php');
require($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/gdm/classi/richiesta.php');

//inizializza l'oggetto $pdf
//include($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/init.php');
$pdf = new utf8graffaPDF();
$fontName = 'DejaVu';
$pdf->AddFont($fontName,'','DejaVuSerif.ttf',true);
$pdf->AddFont($fontName,'B','DejaVuSans-Bold.ttf',true);

$pdf->AddPage('P','A4');

$pdf->SetFont($fontName,'B',15);

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////
//VALIDO PER PNEUMATICI

$r=false;

$galileo->executeSelect('gdm','GDM_richieste',"id='".$param['id']."'",'');

if ($galileo->getResult()) {

    $fid=$galileo->preFetch('gdm');

    while ($row=$galileo->getFetch('gdm',$fid)) {
        $r=new gdmRichiesta('',$row,$galileo);
    }
}

$r->printStorico($pdf,$fontName);

echo base64_encode($pdf->Output('pdf','S'));

?>