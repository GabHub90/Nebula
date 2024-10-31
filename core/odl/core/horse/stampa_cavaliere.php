<?php
$a=$_POST['param'];

require ($_SERVER['DOCUMENT_ROOT'].'/nebula/core/html2pdf/autoload.php');

use Spipu\Html2Pdf\Html2Pdf;

$html2pdf = new Html2Pdf('P','A4');

$html2pdf->setTestTdInOnePage(false);

$html='<div style="border:2px solid black;margin-top:30px;width:95%;">';
    $html.='<div style="border-bottom:1px solid black;width:90%;font-size:30pt;padding:20px;">';
        $html.='<div>';
            $html.=substr($a['intest'],0,25);
        $html.='</div>';
        $html.='<div style="font-size:15pt;">';
            $html.=substr($a['util'],0,25);
        $html.='</div>';
    $html.='</div>';
    
    $html.='<div style="font-size:24pt;padding:20px;">';
        $html.='<div>';
            $html.=substr($a['descrizione'],0,35);
        $html.='</div>';
        $html.='<div style="font-size:20pt;">';
            $html.='<b>'.$a['targa'].' - '.$a['telaio'].'</b>';
        $html.='</div>';
    $html.='</div>';
    
$html.='</div>';

$html2pdf->WriteHTML($html);

echo base64_encode($html2pdf->output('pdf','S'));
?>