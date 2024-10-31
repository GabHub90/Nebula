<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT']."/nebula/apps/storico/classi/previsione.php");

$param=$_POST['param'];

$prev=new nebulaStoricoPrevisione($param['km'],$param['cons'],(isset($param['delta'])?$param['delta']:''),$param['oggetti'],(isset($param['eventi'])?$param['eventi']:array()));

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

$prev->calcola();

$prev->draw();


?>