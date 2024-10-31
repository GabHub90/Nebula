<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT']."/nebula/apps/storico/classi/previsione.php");

require ($_SERVER['DOCUMENT_ROOT']."/nebula/core/html2pdf/autoload.php");

use Spipu\Html2Pdf\Html2Pdf;

$html2pdf = new Html2Pdf('P','A4');

$param=$_POST['param'];

$vei=json_decode(base64_decode($param['veicolo']),true);

//$prev=new nebulaStoricoPrevisione($param['km'],$param['cons'],(isset($param['delta'])?$param['delta']:''),$param['oggetti'],(isset($param['eventi'])?$param['eventi']:array()));

/*{
  "km": 50000,
  "cons": "20201230",
  "oggetti": {
    "MAN": {
      "dt": "24",
      "mint": 0,
      "maxt": 0,
      "stet": 0,
      "dkm": "30000",
      "minkm": 0,
      "maxkm": 0,
      "stekm": 0,
      "pcx": 0,
      "topt": 0,
      "topkm": 0,
      "first_t": 0,
      "first_km": 0,
      "flag_mov": "ok",
      "base": "gruppo",
      "stat": 0
    },
*/

//$html='<div>'.json_encode($vei).'</div>';
//$html='<div style="font-size:18pt;font-weight:bold;">Prossimi interventi di manutenzione:</div>';
$html='<div>Data:'.date('d/m/Y').'</div>';
$html.='<div>Veicolo:'.$vei['targa'].' - '.$vei['telaio'].'</div>';

$html.='<div style="width:100%;">';

/*$prev->calcola();

ob_start();
  $prev->draw();
$html.=ob_get_clean();*/

$html.=$param['html'];

$html.='</div>';

$html2pdf->writeHTML($html);

echo base64_encode($html2pdf->output('pdf','S'));

?>