<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_grent.php");

$param=$_POST['param'];

$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////

/*prenotazione | noleggio
nol_id
rif_id          veicolo
targa   
d_ins           data inserimento
ute_ins         utente inserimento
d_mod           data ultima modifica
ute_mod         utente ultima modifica
stato           prenotazione | noleggio | chiuso (noleggio) | annullato (prenotazione)
lista           lista per la quale è stato fatto il noleggio (determina la scadenza della prenotazione)
pren_i          riferimenti prenotazione
ora_pren_i
pren_f
ora_pren_f
note_interne
data_i          riferimenti effettivi
ora_i
data_f
ora_f
km_i
km_f
ute_i
ute_f
carb_i          JSON
carb_f          JSON
danni_i         JSON - OGGETTO DANNI
danni_f         JSON - OGGETTO DANNI - REVISIONE del primo oggetto
note_danni_i
note_danni_f
guida_1
guida_2
guida_3
fatt_a          cliente a cui fatturare (default guida 1)
d_fatt          data ultima fattura
tel_fatt        telefono di riferimento (default guida 1)
mail_fatt       mail di riferimento (default guida 1) - mail usata per spedire i contratti firmati
note_nol        JSON array di note (ID - utente - data/ora - testo)
note_fatt       JSON array di note (ID - utente - data/ora - testo)
info            JSON del noleggio con le caratteristiche
versione        Versione contratto di noleggio x.y (x=tipo di noleggio , y=versione)
dms
*/

/*
var param={
    "logged":window._nebulaMain.getMainLogged(),
    "veicolo":this.veicolo,
    "cliente":this.cliente,
    "noleggio":this.noleggio,
    "franchigia":this.franchigia
}
*/

$arr=array(
    "rif_id"=>$param['veicolo']['info']['rif'],
    "targa"=>$param['veicolo']['info']['targa'],
    "d_ins"=>date('Ymd'),
    "ute_ins"=>$param['logged'],
    "d_mod"=>date('Ymd'),
    "ute_mod"=>$param['logged'],
    "stato"=>'prenotazione',
    "lista"=>$param['lista'],
    "pren_i"=>$param['noleggio']['pren_i'],
    "pren_f"=>$param['noleggio']['pren_f'],
    "info"=>json_encode($param['noleggio']),
    "dms"=>"infinity"
);

$galileo->executeInsert('grent','GRENT_pratiche',$arr);


?>