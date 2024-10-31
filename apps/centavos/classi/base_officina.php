<?php
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_produttivita_S.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_fatturato_S.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_giacenza_M.php");
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/apps/qcheck/classi/qc_get_datas.php");

//require_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/concerto/concerto_odl.php");
//require_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");

require_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_croom.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');


class centavosBaseOfficina extends centavosBase {

    function __construct($panorama,$galileo) {
        
        parent::__construct($panorama,$galileo);

        $this->oggetti['interni']=array(
            "effP"=>array(
                "titolo"=>"Efficienza Pagamento",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2r",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "effI"=>array(
                "titolo"=>"Efficienza Interni",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2r",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "effG"=>array(
                "titolo"=>"Efficienza Garanzia",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2r",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "effGVWS"=>array(
                "titolo"=>"Eff Gar VW PU",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rRep",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "effGAUS"=>array(
                "titolo"=>"Eff Gar AU PU",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rRep",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "grUtil"=>array(
                "titolo"=>"Grado Utilizzo",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2r",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "rcProdP"=>array(
                "titolo"=>"Produtt. Pag RC",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2r",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "rcIncRic"=>array(
                "titolo"=>"Ricambi incentivati RC",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rfatt",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "totVWL"=>array(
                "titolo"=>"Netto VWL",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rfatt",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "passVWL"=>array(
                "titolo"=>"Netto Passaggio VWL",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rfatt",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "QC_1"=>array(
                "titolo"=>"Qcheck - controllo 1",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"qcheck",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "numQC_1"=>array(
                "titolo"=>"Esecuzione controllo 1",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"qcheck",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "fattCar"=>array(
                "titolo"=>"Fatt. Carrozzeria",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rfatt",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "manCar"=>array(
                "titolo"=>"Manod. netta Carrozz.",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rfatt",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricOffP"=>array(
                "titolo"=>"Ricambi Pagamento",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rfatt",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "obsoG1"=>array(
                "titolo"=>"Obsoles.G1(3)",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rmagar",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "garOpen"=>array(
                "titolo"=>"Gar aperte VGI",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"c2rfatt",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            )
        );

        /*
        "ricInc"=>array(
                "titolo"=>"Ricambi incentivati",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
        */

        $this->oggetti['esterni']=array(
            "qlIQSvw"=>array(
                "titolo"=>"Qualità riparazione IQS vw",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "qlIQSau"=>array(
                "titolo"=>"Qualità riparazione IQS au",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "qlIQSauc"=>array(
                "titolo"=>"Qualità riparazione IQS au Cesena",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "qlUPM"=>array(
                "titolo"=>"Feedback clienti UPM",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "citNow"=>array(
                "titolo"=>"Video Citnow",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemVW"=>array(
                "titolo"=>"Bonus CEM VW",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemVWmp"=>array(
                "titolo"=>"Mail e Privacy CEM VW",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemAUmp"=>array(
                "titolo"=>"Mail e Privacy CEM AU",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemAU"=>array(
                "titolo"=>"Bonus CEM AU",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemAUCmp"=>array(
                "titolo"=>"Mail e Privacy CEM AU Cesena",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemAUC"=>array(
                "titolo"=>"Bonus CEM AU Cesena",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricoffVGI"=>array(
                "titolo"=>"Bonus Ricambi off VGI",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricDISSvw"=>array(
                "titolo"=>"DISS Ricezione VW",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricDISSau"=>array(
                "titolo"=>"DISS Ricezione AU",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricDISSauc"=>array(
                "titolo"=>"DISS Ricezione AU Cesena",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "aggODISvw"=>array(
                "titolo"=>"Aggiornamento ODIS VW",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "aggODISau"=>array(
                "titolo"=>"Aggiornamento ODIS AU",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "aggODISauc"=>array(
                "titolo"=>"Aggiornamento ODIS AU Cesena",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "azRICvw"=>array(
                "titolo"=>"Azioni di richiamo VW",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "azRICau"=>array(
                "titolo"=>"Azioni di richiamo AU",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "azRICauc"=>array(
                "titolo"=>"Azioni di richiamo AU Cesena",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "azRICPU"=>array(
                "titolo"=>"Azioni di richiamo VGI PESARO",
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricExtPU"=>array(
                "titolo"=>"Ricambi Netti Ext. PU",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricExtAN"=>array(
                "titolo"=>"Ricambi Netti Ext. AN",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricExtFC"=>array(
                "titolo"=>"Ricambi Netti Ext. FC",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "approved"=>array(
                "titolo"=>"Vendita Approved",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"somma",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "numAppr"=>array(
                "titolo"=>"Attivazioni Approved",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"somma",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "comp"=>array(
                "titolo"=>"Competenza",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>3,
                "flag"=>false,
                "rettifica"=>false
            ),
            "prof"=>array(
                "titolo"=>"Professionalità",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>3,
                "flag"=>false,
                "rettifica"=>false
            ),
            "aff"=>array(
                "titolo"=>"Affidabilità",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>3,
                "flag"=>false,
                "rettifica"=>false
            ),
            "budgetPit"=>array(
                "titolo"=>"Budget Ricambi Pit",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "budgetVgi"=>array(
                "titolo"=>"Budget Ricambi Vgi",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "odlAperti"=>array(
                "titolo"=>"Costi Odl Aperti",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "odlDaChiu"=>array(
                "titolo"=>"Odl da chiudere",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "storniGar"=>array(
                "titolo"=>"Storni Garanzia",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "storniGarAU"=>array(
                "titolo"=>"Storni Garanzia Audi",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "storniGarAUC"=>array(
                "titolo"=>"Storni Garanzia Audi Cesena",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemSodd"=>array(
                "titolo"=>"Cem Soddisfazione",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemSoddVW"=>array(
                "titolo"=>"Soddisfazione Comp. VW",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemSoddSK"=>array(
                "titolo"=>"Soddisfazione Comp. SK",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemSpiegVW"=>array(
                "titolo"=>"Spiegazione Fattura VW",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemSpiegSK"=>array(
                "titolo"=>"Spiegazione Fattura SK",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemSoddFC"=>array(
                "titolo"=>"Cem Soddisfazione FC",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "cemAppVW"=>array(
                "titolo"=>"Facilità appuntam. CEM VW",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "graCentVGI"=>array(
                "titolo"=>"Grado di servizio Centralino VGI",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "attVen"=>array(
                "titolo"=>"Attività di vendita",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "odlNUS"=>array(
                "titolo"=>"Ordini nuovo e usato",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "consegneUPM"=>array(
                "titolo"=>"Vetture consegnate cliente",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "costiRicavi"=>array(
                "titolo"=>"Costi su ricavi",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ricScagar"=>array(
                "titolo"=>"Ricambi Gar Scaduti",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "auditVW"=>array(
                "titolo"=>"Audit VW",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "auditAU"=>array(
                "titolo"=>"Audit Audi",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "auditAltro"=>array(
                "titolo"=>"Audit Altro",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            ),
            "ggChiusura"=>array(
                "titolo"=>"GG Chiusura Gar VGI PU",
                "data_i"=>"20220101",
                "data_f"=>"21001231",
                "sorgente"=>"centext",
                "operazione"=>"ultimo",
                "default"=>0,
                "flag"=>false,
                "rettifica"=>false
            )
        );

        $this->sorgenti["c2r"]=false;
        $this->sorgenti["c2rfatt"]=false;
        $this->sorgenti["c2rmagar"]=false;
        $this->sorgenti["qcheck"]=false;

        $this->coefficienti['_pres_']['sorgente']='c2r';
        
    }

    function initSorgente($tipo) {

        //if ($tipo=='c2r') {
        if (($tipo=='c2r' || $tipo=='c2rfatt' || $tipo=='c2rRep' || $tipo=='c2rmagar') && !$this->sorgenti['c2r']) {    
            //il controllo viene fatto in BASE->setSorgente();
            //if ($this->sorgenti['c2r']) return;

            //aggiungere gli oggetti GALILEO necessari
            //$nebulaDefault=array();
            //$obj=new galileoConcertoODL();
            //$nebulaDefault['odl']=array("maestro",$obj);
            //$obj=new galileoCroom();
            //$nebulaDefault['croom']=array("gab500",$obj);
            $nebulaDefault=array();
            $obj=new galileoTempo();
            $nebulaDefault['tempo']=array("gab500",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            //$g=clone $this->galileo;

            /*ARG
            {
                "inizio":"AAAAMMDD",
                "fine":"AAAAMMDD",
                "reparti":"VWS,AUS,",
                "default":{"tipo":"standard","totali":true,"collab":true,"repcoll":true,"responsabile":false},
                "prodTipo":""
                "operaio":""
            }*/

            $a=$this->config;
            $a['reparti']="";

            //$this->galileo->clearQuery();

            $this->galileo->getReparti('S','');
            $result=$this->galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetchBase('maestro');
                while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                    $a['reparti'].="'".$row['reparto']."',";
                }
            }

            $a['default']=array(
                "tipo"=>"standard",
                "totali"=>true,
                "collab"=>true,
                "repcoll"=>true,
                "responsabile"=>false
            );

            $a['prodTipo']="";
            $a['operaio']="";

            if ($a['fine']>date('Ymd')) $a['fine']=date('Ymd');

            $this->sorgenti['c2r']=new c2rProduttivita_S($a,$this->galileo);

            if ($this->sorgenti['c2r']) {
                $this->sorgenti['c2r']->getLines();
            }
            else die('Impossibile inizializzare sorgente c2r');
        }

        ////////////////////////////////////////////
        if ($tipo=='c2rfatt' && !$this->sorgenti['c2rfatt']) {

            $nebulaDefault=array();
            //$obj=new galileoConcertoODL();
            //$nebulaDefault['odl']=array("maestro",$obj);
            $obj=new galileoCroom();
            $nebulaDefault['croom']=array("gab500",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            //$g=clone $this->galileo;

            $a=$this->config;
            if ($this->panorama['reparto']=='BRC') {
                $a['reparti']="AUS,VWS,";
            }
            else {
                $a['reparti']="'".$this->panorama['reparto']."',";
            }

            if ($a['fine']>date('Ymd')) $a['fine']=date('Ymd');

            //$this->galileo->clearQuery();

            $this->sorgenti['c2rfatt']=new c2rFatturato_S($a,$this->galileo);

            if ($this->sorgenti['c2rfatt']) {
                $this->sorgenti['c2rfatt']->setAnalisi('rc',true);
                $this->sorgenti['c2rfatt']->forzaResponsabile();
                $this->sorgenti['c2rfatt']->getLines();
            }
            else die('Impossibile inizializzare sorgente c2rfatt');

        }

        ////////////////////////////////////////////
        if ($tipo=='c2rmagar' && !$this->sorgenti['c2rmagar']) {

            $a=array(
                "reparti"=>"'G1',",
                "inizio"=>$this->config['inizio'],
                "fine"=>$this->config['fine'],
                "obso"=>"3"
            );

            $this->sorgenti['c2rmagar']=new c2rGiacenza_M($a,$this->galileo);

            if ($this->sorgenti['c2rmagar']) {
                $this->sorgenti['c2rmagar']->getLines();
            }
            else die('Impossibile inizializzare sorgente c2rmagar');
        }

        ///////////////////////////////////////////

        if ($tipo=='qcheck') {

            $nebulaDefault=array();
            $obj=new galileoQcheck();
            $nebulaDefault['qcheck']=array("gab500",$obj);
            $this->galileo->setFunzioniDefault($nebulaDefault);

            $config=array(
                "data_i"=>$this->config['inizio'],
                "data_f"=>$this->config['fine']
            );

            $this->sorgenti['qcheck']=new qcDatas($config,$this->galileo);
            if (!$this->sorgenti['qcheck']) die('Impossibile inizializzare sorgente c2r');
        }

    }

    function exportC2r() {

        if ($this->sorgenti['c2r']) {
            return $this->sorgenti['c2r']->exportRes();
        }
        else {
            return $this->sorgenti['c2r'];
        }
    }

    function getSorgente_c2r($oggetto,$arg) {

        //restituisce un Array "Team","Individuale"

        if (!$this->sorgenti['c2r']) return false;

        //if (!$this->sorgenti['c2r']) return array('individuale'=>999,"team"=>999);
        //se non esiste la sorgente inizializzala, nel caso non sia possibile segue uun DIE
        //if (!$this->sorgenti['c2r']) $this->initSorgente("c2r");

        if (substr($oggetto,0,2)=='rc') return $this->getSorgente_rc2r($oggetto,$arg);

        $res=false;

        if ($oggetto=="effP" || $oggetto=="effI" || $oggetto=="effG" ) {
            
            $res=array(
                "team"=>$this->sorgenti['c2r']->getRepParam($this->panorama['reparto'],"totale",$oggetto)*100,
                "individuale"=>$this->sorgenti['c2r']->getColParam($arg['ID_coll'],$oggetto)*100
            );
        }

        else if ($oggetto=='grUtil') {

            $res=array(
                "team"=>$this->sorgenti['c2r']->getRepParam($this->panorama['reparto'],"totale",$oggetto)*100,
                "individuale"=>$this->sorgenti['c2r']->getColParam($arg['ID_coll'],$oggetto)*100
            );
        }

        else if ($oggetto=='_pres_') {

            $res=$this->getPres($arg);
            
        }

        return $res;

    }

    function getSorgente_c2rRep($oggetto,$arg) {

        //restituisce un Array "Team","Individuale"

        if (!$this->sorgenti['c2r']) return false;

        $res=false;

        if ($oggetto=="effGVWS" ) {
            
            $res=array(
                "team"=>$this->sorgenti['c2r']->getRepParam('VWS',"totale","effG")*100,
                "individuale"=>0
            );
        }

        if ($oggetto=="effGAUS" ) {
            
            $res=array(
                "team"=>$this->sorgenti['c2r']->getRepParam('AUS',"totale","effG")*100,
                "individuale"=>0
            );
        }

        return $res;
    }

    function getPres($arg) {

        $team=array(
            "nominale"=>$this->sorgenti['c2r']->getRepParam($this->panorama['reparto'],"totale","preNom"),
            "extra"=>$this->sorgenti['c2r']->getRepParam($this->panorama['reparto'],"totale","preExt"),
            "assenza"=>$this->sorgenti['c2r']->getRepParam($this->panorama['reparto'],"totale","preAss"),
            "malattia"=>$this->sorgenti['c2r']->getRepParam($this->panorama['reparto'],"totale","preMal")
        );

        $team['actual']=$team['nominale']+$team['extra']-$team['assenza']-$team['malattia'];
        $team['coeff']=$team['nominale']==0?0:round( ($team['actual']/$team['nominale'])*100 );

        if ($arg['ctvVariante']=='RC' || $arg['ctvVariante']=='MAG') $ind=$this->getPresRC($arg);
        else {
            $ind=array(
                "nominale"=>$this->sorgenti['c2r']->getColParam($arg['ID_coll'],"preNom"),
                "extra"=>$this->sorgenti['c2r']->getColParam($arg['ID_coll'],"preExt"),
                "assenza"=>$this->sorgenti['c2r']->getColParam($arg['ID_coll'],"preAss"),
                "malattia"=>$this->sorgenti['c2r']->getColParam($arg['ID_coll'],"preMal")
            );
        }

        $ind['actual']=$ind['nominale']+$ind['extra']-$ind['assenza']-$ind['malattia'];

        if ($ind['nominale']==0) $ind['coeff']=0;
        else $ind['coeff']=round( ($ind['actual']/$ind['nominale'])*100 );
        
        $res=array(
            "team"=>$team['coeff']>70?100:$team['coeff'],
            "individuale"=>$ind['coeff']>70?100:$ind['coeff']
        );

        return $res;
    }

    function getSorgente_rc2r($oggetto,$arg) {

        $res=false;

        if ($oggetto=="rcProdP" || $oggetto=="rcProdI" || $oggetto=="rcProdG" ) {

            $p="";

            switch ($oggetto) {
                case "rcProdP": $p='prodP';
                break;
                case "rcProdI": $p='prodI';
                break;
                case "rcProdG": $p='prodG';
                break;
            }
            
            $res=array(
                "team"=>$this->sorgenti['c2r']->getRepParam($this->panorama['reparto'],"totale",$p),
                "individuale"=>$this->sorgenti['c2r']->getRcParam($arg['ID_coll'],$this->panorama['reparto'],$p)
            );
        }

        else if ($oggetto=='_pres_') {

            $res=$this->getPres($arg);
            
        }

        return $res;
    }

    function getSorgente_qcheck($oggetto,$arg) {
        
        //QC_1 (il numero è il numero del modulo)
        $e=explode('_',$oggetto);

        //$this->log[]=array($arg['concerto'],$e[1]);

        if ($e[0]=='QC') {

            $d=$this->sorgenti['qcheck']->getCollab($arg['concerto'],$e[1]);

            //$this->log[]=$d;

            if (!$d || count($d)==0) {

                $res=array(
                    "team"=>$this->oggetti['interni'][$oggetto]['default'],
                    "individuale"=>$this->oggetti['interni'][$oggetto]['default']
                );
            }
        }

        else $res=false;

        return $res;
    }

    function getPresRC($arg) {
        $ind=array(
            "nominale"=>$this->sorgenti['c2r']->getRcParam($arg['ID_coll'],$this->panorama['reparto'],"preNom"),
            "extra"=>$this->sorgenti['c2r']->getRcParam($arg['ID_coll'],$this->panorama['reparto'],"preExt"),
            "assenza"=>$this->sorgenti['c2r']->getRcParam($arg['ID_coll'],$this->panorama['reparto'],"preAss"),
            "malattia"=>$this->sorgenti['c2r']->getRcParam($arg['ID_coll'],$this->panorama['reparto'],"preMal")
        );

        return $ind;
    }

    function getSorgente_c2rfatt($oggetto,$arg) {

        //return array("individuale"=>1999);

        if (!$this->sorgenti['c2rfatt']) return false;
        //se non esiste la sorgente inizializzala, nel caso non sia possibile segue uun DIE
        //if (!$this->sorgenti['c2rfatt']) $this->initSorgente("c2rfatt");

        if ($oggetto=='rcIncRic') {
            return array("individuale"=>$this->sorgenti['c2rfatt']->getValRc($arg['ID_coll'],'ext','inc'));
        }

        if ($oggetto=="fattCar") {
            return array("team"=>$this->sorgenti['c2rfatt']->getTotStd('car','pag','man','netto')+$this->sorgenti['c2rfatt']->getTotStd('car','pag','ric','netto'));
        }

        if ($oggetto=="manCar") {
            return array("team"=>$this->sorgenti['c2rfatt']->getTotStd('car','pag','man','netto'));
        }

        if ($oggetto=="ricOffP") {
            // NETTO MINIMO = 27000+LISTINO(1-0.4) considerto uno sconto medio del 40%
            //27000 viene da 6 persone = 180000 = 45000 a trimestre di cui il 60% (27000) che deve uscire dai ricambi
            $tot=$this->sorgenti['c2rfatt']->getTotStd('mec','pag','ric','netto')+$this->sorgenti['c2rfatt']->getTotStd('car','pag','ric','netto')+$this->sorgenti['c2rfatt']->getTotStd('gom','pag','ric','netto');
            //$minimo=27000+$listino*(1-0.4);
            //return array("team"=>$this->sorgenti['c2rfatt']->getTotStd('mec','pag','ric','netto')-$minimo);
            return array("team"=>$tot);
        }

        if ($oggetto=="totVWL") {
            $temp=0;

            $u=array(
                "e.giannotti",
                "s.galli"
            );

            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','pag','man','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','pag','ric','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','pag','var','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','nac','man','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','nac','ric','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','nac','var','netto');

            return array("team"=>$temp);
        }

        if ($oggetto=="passVWL") {
            $temp=0;
            $pass=0;
            $res=0;

            $u=array(
                "e.giannotti",
                "s.galli"
            );

            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','pag','man','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','pag','ric','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','pag','var','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','nac','man','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','nac','ric','netto');
            $temp+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','nac','var','netto');

            $pass+=$this->sorgenti['c2rfatt']->getValRcs($u,'int','pag','pass','valore');
            $pass-=$this->sorgenti['c2rfatt']->getValRcs($u,'int','nac','pass','valore');

            if ($pass==0) {
                $res=0;
            }
            else {
                $res=$temp/$pass;
            }

            return array("team"=>$res);
        }

        if ($oggetto=="garOpen") {
            
            $passGar=$this->sorgenti['c2rfatt']->getTotStd('mec','gar','pass','valore')+$this->sorgenti['c2rfatt']->getTotStd('car','gar','pass','valore')+$this->sorgenti['c2rfatt']->getTotStd('gom','gar','pass','valore');

            $open=$this->sorgenti['c2rfatt']->getGarOpen();

            if ($passGar==0) $res=0;
            else $res=($open/$passGar)*100;

            return array("team"=>$res);
        }

        return false;
    }

    function getSorgente_c2rmagar($oggetto,$arg) {

        if ($oggetto=="obsoG1") {

            //echo json_encode($this->sorgenti['c2rmagar']->getAnalisi(''));

            $obso=$this->sorgenti['c2rmagar']->getVal("fine","","","","obsoleto");
            $listino=$this->sorgenti['c2rmagar']->getVal("fine","","","","listino");

            if ($listino==0) $res=0;
            else $res=($obso/$listino)*100;

            return array("team"=>$res);
        }

        return false;

    }

}

?>