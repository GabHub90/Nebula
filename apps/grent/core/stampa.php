<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/utf8graffa.php');

$param=$_POST['param'];

include($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/init.php');

$pdf->SetFont($fontName,'B',15);

$pdf->write(10,'Proposta di noleggio:');
$pdf->ln(8);

$pdf->SetFont($fontName,'',15);

$pdf->write(10,'Utente: '.$param['logged']);
$pdf->ln(8);
$pdf->write(10,'Data: '.date('d/m/Y'));
$pdf->ln(10);

$pdf->SetFont($fontName,'B',15);
$pdf->write(10,'Veicolo:');
$pdf->ln(8);

$pdf->SetFont($fontName,'',15);
$pdf->write(10,$param['veicolo']['info']['des_veicolo']);
$pdf->ln(8);
$pdf->write(10,'Targa: '.$param['veicolo']['info']['targa'].' Telaio: '.$param['veicolo']['info']['telaio']);
$pdf->ln(8);
$pdf->write(10,'Km attuali: '.$param['veicolo']['actual_km']);
$pdf->ln(10);

$pdf->SetFont($fontName,'B',15);
$pdf->write(10,'Condizioni:');
$pdf->ln(8);

$pdf->SetFont($fontName,'',15);

$pdf->write(10,'Dal: '.mainFunc::gab_todata($param['noleggio']['pren_i']).' Al: '.mainFunc::gab_todata($param['noleggio']['pren_f']));
$pdf->ln(8);
$pdf->write(10,'Km: '.($param['noleggio']['flag_limite']==1?'Illimitati':$param['noleggio']['km']. ' costo eccedenza '.$param['noleggio']['eccedenza'].' €/km'));
$pdf->ln(8);
$pdf->write(10,'Tariffa giornaliera: '.number_format($param['noleggio']['tariffa'],2,',','.').'+iva');
$pdf->ln(8);
$pdf->write(10,'Tariffa mensile: '.($param['noleggio']['durata']>=28?number_format($param['noleggio']['tariffa']*30,2,',','.').'+iva':''));
$pdf->ln(8);
$pdf->write(10,'Totale ('.$param['noleggio']['durata'].' giorni): '.number_format($param['noleggio']['tariffa']*$param['noleggio']['durata'],2,',','.').'+iva ('.number_format($param['noleggio']['tariffa']*$param['noleggio']['durata']*1.22,2,',','.').')');
$pdf->ln(10);

$pdf->SetFont($fontName,'B',15);
$pdf->write(10,'Servizi:');
$pdf->ln(8);

$pdf->SetFont($fontName,'',15);
$pdf->write(10,'Compreso');
$pdf->ln(8);
$pdf->SetFont($fontName,'',12);
$pdf->write(10,'Fornitura pneumatici estivi ed invernali in funzione della stagione');
$pdf->ln(8);

/*{"fr_id":"1","importo":"1500","perc":"10","limite":"15000","flag_impo
    rto":"300","flag_perc":"10","flag_limite":"15000","calc_max":"500","c
    alc_min":"50","calc_indice":".9"}*/

$pdf->SetFont($fontName,'',15);
$pdf->write(10,'Compreso');
$pdf->ln(8);
$pdf->SetFont($fontName,'',12);
$pdf->write(10,'Assicurazione KASKO,Furto e Incendio,Cristalli,Atti vandalici');
$pdf->ln(8);
if ($param['noleggio']['flag_franchigia']==1) {
    $pdf->write(10,'RIDUZIONE Franchigia a: '.number_format($param['franchigia']['flag_importo'],2,',','.').'€ o '.$param['franchigia']['flag_perc'].'% per danni oltre '.number_format($param['franchigia']['flag_limite'],2,',','.').'€');
}
else {
    $pdf->write(10,'Franchigia: '.number_format($param['franchigia']['importo'],2,',','.').'€ o '.$param['franchigia']['perc'].'% per danni oltre '.number_format($param['franchigia']['limite'],2,',','.').'€');
}
$pdf->ln(10);

$pdf->SetFont($fontName,'B',15);
$pdf->write(10,'Note:');
$pdf->ln(8);

$pdf->SetFont($fontName,'',15);
$pdf->write(10,$param['noleggio']['note_pren']);
$pdf->ln(10);

echo base64_encode($pdf->Output('pdf','S'));


?>