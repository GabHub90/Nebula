<?php
class grentVeicoli {

    protected $tipoRent="";
    protected $veicoli=array();
    protected $fasce=array();
    protected $franchigie=array();

    protected $coefficienti=array(
        "kasko"=>0.2,
        "sva"=>0.05
    );

    //sono le informazioni relative alle caratteristiche del noleggio
    //non sono le coordinate temporali o del cliente
    protected $noleggio=array(
        "nol_id"=>0,
        "reset_id"=>0,
        "stato"=>"",
        "tot_fisso"=>0,
        "tot_var"=>0,
        "actual_coeff"=>0,
        "actual_coeffkm"=>0,
        "tariffa"=>0,
        "costo"=>0,
        "incent"=>0,
        "eccedenza"=>0,
        "misura"=>"g",
        "durata"=>0,
        "km"=>0,
        "limite"=>0,
        "flag_limite"=>0,
        "franchigia"=>0,
        "flag_franchigia"=>0,
        "tot_franchigia"=>0,
        "extra"=>array(),
        "pren_i"=>"",
        "ora_pren_i"=>"",
        "pren_f"=>"",
        "ora_pren_f"=>"",
        "note_pren"=>""
    );

    protected $auth=array(
        "manage"=>false,
        "reset"=>false
    );

    protected $pratiche=array();
    protected $reset=array();

    protected $logged="";
    protected $galileo;

    function __construct($tipo,$logged,$galileo) {

        $this->tipoRent=$tipo;
        $this->logged=$logged;
        $this->galileo=$galileo;

        //tutte le anagrafiche di vetture che abbiamo in casa sono su Infinity

        /*
        nell'esempio i dischi e pastiglie post. pesano come ricambi per il 30% del costo di manutenzione calcolato => coeff_km= 0.5 * (kmprevisti/sva_km) => per noleggi a breve = 150 km al giorno
        (clausola senza limite kilometrico = 100% del coefficiente) ma comunque MAI SUPERIORE A SVA KM, eventualmente occorre resettare i parametri.
        il coefficiente "coeff" di fatto è considerato sul grado di inutilizzo su 365 giorni e determina il costo giornaliero (4=inutilizzo del 75%).
        il coefficiente viene abbassato in base al reale utilizzo (1 - giorni/365) dove giorni/365=cg non può essere maggiore di 70 e quindi il coefficiente non può scendere sotto al 30% del suo valore.
        cg viene calcolato con scala logaritmica dove la differenza tra 1 e 2 è maggiore che tra 20 e 21 e molto maggiore che tra 87 e 88
        */

        $this->galileo->executeSelect('grent','GRENT_fasce',"lista='".$this->tipoRent."'","");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('grent');
            while($row=$this->galileo->getFetch('grent',$fid)) {

                $row['kasko']=$row['kasko']*(1+$this->coefficienti['kasko']);
                $this->fasce[$row['codice']]=$row;
            }
        }

        $this->galileo->executeSelect('grent','GRENT_franchigie',"","");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('grent');
            while($row=$this->galileo->getFetch('grent',$fid)) {

                $this->franchigie[$row['fr_id']]=$row;
            }
        }
    }

    function setAuth($a) {
        foreach ($this->auth as $k=>$v) {
            if (array_key_exists($k,$a)) {
                $this->auth[$k]=$a[$k];
            }
        }
    }

    //è un metodo cghe può essere riscritto dalle classi EXTENDS
    function build() {

        //call_user_func_array(array($this, 'getPratiche_'.$this->tipoRent), array() );
        $this->getPratiche();

        /*TEST
        $this->veicoli=array(

            "A"=>array(
                "5056590"=>array(
                    "data_i"=>'20220627',
                    "data_f"=>"",
                    "stato"=>'regolare',
                    "km_i"=>24473,
                    "km_f"=>0,
                    "sva_tot"=>0,
                    "reset"=>'',
                    "km_reset"=>0,
                    "fascia"=>"",
                    "coeff"=>0,
                    "coeff_km"=>0,
                    "sva"=>0,
                    "sva_km"=>0,
                    "sva_tempo"=>0,
                    "pratica_id"=>0,
                    "pratica_da"=>"",
                    "pratica_a"=>"",
                    "actual_km"=>24473,
                    "note"=>"Prova",
                    "incent"=>0,
                    "cop_fisso"=>0,
                    "info"=>false
                )
            )
        );*/

        //pratica si riferisce alla pratica attuale se c'è
        $this->galileo->executeSelect('grent','GRENT_veicoli',"lista='".$this->tipoRent."' AND stato!='chiuso'","marca");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('grent');
            while($row=$this->galileo->getFetch('grent',$fid)) {
                $this->veicoli[$row['marca']][$row['rif_id']]=$row;
                $this->veicoli[$row['marca']][$row['rif_id']]['info']=false;
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_id']=0;
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_da']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_a']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_stato']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_utente']="";
                //$this->veicoli[$row['marca']][$row['rif_id']]['note']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['actual_km']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['sva']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['valore_i']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['valore_f']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['km_i']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['km_f']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['chiusura']=0;
                $this->veicoli[$row['marca']][$row['rif_id']]['reset_valore_i']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['reset_valore_f']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['fascia']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['franchigia']='';


                //call_user_func_array(array($this, 'elaboraPratiche_'.$this->tipoRent), array($row) );
                $this->elaboraPratiche($row);

                //collega i RESET
                $this->getReset($row);
            }
        }

        /*
        capitale assicurato 30.000 => KASKO + furto e incendio + cristalli + RCA =>1650,00
        Tutti i filtri + olio + liq. freni + pastiglie e dischi anteriori con 3 ore di manodopera => 787,00 => -40% => 470,00 + iva
        Gomme => preventivo con 10,00 => 480,00 => 394,00 + iva
        IPT => 388,00
        $this->fasce=array(
            "A3"=>array(
                "codice"=>"A3",
                "lista"=>"UM",
                "testo"=>"12 mesi 30.000 km",
                "kasko"=>1650*(1+$this->coefficienti['kasko']),
                "manut"=>900,
                "gomme"=>400,
                "ipt"=>400,
                "bollo"=>300
            )
        );*/  

    }

    function getPratiche() {

        $this->galileo->executeSelect('grent','GRENT_pratiche',"lista='".$this->tipoRent."' AND stato IN ('noleggio','prenotazione')","");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('grent');
            while($row=$this->galileo->getFetch('grent',$fid)) {

                $this->pratiche[$row['rif_id']]=$row;
            }
        }
    }

    function elaboraPratiche($row) {

        //##################################################
        //ricerca noleggio attivo
        //popolazione campi PRATICA
        if (array_key_exists($row['rif_id'],$this->pratiche)) {

            $this->pratiche[$row['rif_id']]['info']=json_decode($this->pratiche[$row['rif_id']]['info'],true);
            if ($this->pratiche[$row['rif_id']]['info']) {
                $this->pratiche[$row['rif_id']]['info']['nol_id']=$this->pratiche[$row['rif_id']]['nol_id'];
            }

            $this->veicoli[$row['marca']][$row['rif_id']]['pratica_id']=$this->pratiche[$row['rif_id']]['nol_id'];
            $this->veicoli[$row['marca']][$row['rif_id']]['pratica_da']=$this->pratiche[$row['rif_id']]['pren_i'];
            $this->veicoli[$row['marca']][$row['rif_id']]['pratica_a']=$this->pratiche[$row['rif_id']]['pren_f'];
            $this->veicoli[$row['marca']][$row['rif_id']]['pratica_stato']=$this->pratiche[$row['rif_id']]['stato'];
            $this->veicoli[$row['marca']][$row['rif_id']]['pratica_utente']=$this->pratiche[$row['rif_id']]['ute_mod'];
        }
        //##################################################
    }

    function getReset($a) {

        //TEST
        //RESET riferiti a quella pratica di vettura ordinati dal più vecchio al più giovane
        /*$r=array(
            array(
                "grent_id"=>1,
                "reset_id"=>1,
                "d"=>'20220721',
                "km_reset"=>3129,
                "fascia"=>'A3',
                "franchigia"=>1,
                "valore_i"=>24000,
                "valore_f"=>18500,
                "sva_km"=>30000,
                "sva_tempo"=>18,
                "coeff"=>4,
                "coeff_km"=>0.3,
                "incent"=>0.03,
                "cop_fisso"=>0.5,
                "chiusura"=>0
            )
        );*/
        //$r=array();
        //END TEST

        $this->galileo->executeSelect('grent','GRENT_reset',"grent_id='".$a['grent_id']."'","d");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('grent');
            
            while($row=$this->galileo->getFetch('grent',$fid)) {

                $this->elaboraReset($a,$row);            

                if (isset($this->pratiche[$a['rif_id']]['info']) && $this->pratiche[$a['rif_id']]['info']) {
                    //se il reset ID corrisponde a quello del noleggio in atto allora prendilo per buono e fermati
                    if ($this->pratiche[$a['rif_id']]['info']['reset_id']==$row['reset_id']) break;
                }
            }
        }
    }

    function elaboraReset($a,$row) {

        $this->veicoli[$a['marca']][$a['rif_id']]['reset']=$row['d'];
        $this->veicoli[$a['marca']][$a['rif_id']]['km_reset']=$row['km_reset'];
        $this->veicoli[$a['marca']][$a['rif_id']]['reset_id']=$row['reset_id'];
        $this->veicoli[$a['marca']][$a['rif_id']]['fascia']=$row['fascia'];
        $this->veicoli[$a['marca']][$a['rif_id']]['franchigia']=$row['franchigia'];
        $this->veicoli[$a['marca']][$a['rif_id']]['coeff']=$row['coeff'];
        $this->veicoli[$a['marca']][$a['rif_id']]['coeff_km']=$row['coeff_km'];
        $this->veicoli[$a['marca']][$a['rif_id']]['sva_km']=$row['sva_km'];
        $this->veicoli[$a['marca']][$a['rif_id']]['sva_tempo']=$row['sva_tempo'];
        $this->veicoli[$a['marca']][$a['rif_id']]['incent']=$row['incent'];
        $this->veicoli[$a['marca']][$a['rif_id']]['cop_fisso']=$row['cop_fisso'];
        $this->veicoli[$a['marca']][$a['rif_id']]['chiusura']=$row['chiusura'];
        $this->veicoli[$a['marca']][$a['rif_id']]['reset_valore_i']=$row['valore_i'];
        $this->veicoli[$a['marca']][$a['rif_id']]['reset_valore_f']=$row['valore_f'];

        if ($this->veicoli[$a['marca']][$a['rif_id']]['valore_i']=='') {
            $this->veicoli[$a['marca']][$a['rif_id']]['valore_i']=$row['valore_i'];
            $this->veicoli[$a['marca']][$a['rif_id']]['km_i']=$row['km_reset'];
        }
        $this->veicoli[$a['marca']][$a['rif_id']]['valore_f']=$row['valore_f'];
        $this->veicoli[$a['marca']][$a['rif_id']]['sva']=$row['valore_i']-$row['valore_f'];
        if ($row['chiusura']==1) {
            $this->veicoli[$a['marca']][$a['rif_id']]['km_f']=$row['km_reset'];
            //se per caso ci fosse stato un errore
            $this->veicoli[$a['marca']][$a['rif_id']]['stato']='chiuso';
        }
        else {
            $this->veicoli[$a['marca']][$a['rif_id']]['km_f']=$row['km_reset']+$row['sva_km'];
            $this->veicoli[$a['marca']][$a['rif_id']]['data_f']=date('Ymd',strtotime('+'.$row['sva_tempo'].'month',mainFunc::gab_tots($row['d'])));
        }
    }

    function popolaInfo($marca,$rif) {

        $this->galileo->executeGeneric('veicoli','veiSelect',array("rif"=>$rif),'');
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('veicoli');

            while ($row=$this->galileo->getFetch('veicoli',$fid)) {
                $this->veicoli[$marca][$rif]['info']=$row;
                $this->veicoli[$marca][$rif]['actual_km']=$row['km'];
            }
        }

        //leggi ultimo reset
        /*TEST
            $this->veicoli[$marca][$rif]['reset']='20220627';
            $this->veicoli[$marca][$rif]['km_reset']=24473;
            $this->veicoli[$marca][$rif]['fascia']='A3';
            $this->veicoli[$marca][$rif]['coeff']=4;
            $this->veicoli[$marca][$rif]['coeff_km']=0.3;
            $this->veicoli[$marca][$rif]['sva']=4000*(1+$this->coefficienti['sva']);
            $this->veicoli[$marca][$rif]['sva_km']=30000;
            $this->veicoli[$marca][$rif]['sva_tempo']=18;
            $this->veicoli[$marca][$rif]['incent']=0.03;
            $this->veicoli[$marca][$rif]['cop_fisso']=0.5;
        //END TEST*/

    }

    function calcolaStato($v) {

        //se non c'è un reset
        if (!isset($v['reset'])) {
            $v['lim']=0;
            $v['stato']='limiti';
        }
        else {
            $v['lim']=strtotime("+".$v['sva_tempo']." month",mainFunc::gab_tots($v['data_i']));

            if ( ($v['actual_km']-$v['km_i'])>=$v['sva_km']) $v['stato']='km';
            if (time()>=$v['lim']) {
                $v['stato']=$v['stato']=='regolare'?'tempo':'limiti';
            }
        }

        $v['color']='#93e993';
        if ($v['stato']=='km' || $v['stato']=='tempo' || $v['stato']=='limiti') $v['color']='#fc8888';
        else if ($v['pratica_da']!="") $v['color']='#e9e593';

        return $v;
    }

    function drawLista() {

        foreach ($this->veicoli as $marca=>$m) {

            foreach ($m as $rif=>$v) {

               $this->popolaInfo($marca,$rif);
            }
        }

        if ($this->tipoRent=='UM') $this->draw_UM();

        //echo '<div>'.json_encode($this->pratiche).'</div>';
    }

    function draw_UM() {

        foreach ($this->veicoli as $marca=>$m) {

            foreach ($m as $rif=>$v) {

                //scrivi blocchi veicoli
                echo '<div style="position:relative;width:90%;min-height:50px;margin-top:15px;margin-bottom:15px;padding:3px;border-top:2px solid #999999;border-bottom:2px solid #999999;box-sizing:border-box;" ';
                    //if ($v['info']) echo 'onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selVei(this.id,\''.$this->tipoRent.'\',\''.$marca.'\',\''.$rif.'\');"'; 
                echo '>';
                    if (!$v['info']) echo 'Veicolo '.$rif.' non trovato in Infinity';
                    else if (!isset($this->veicoli[$marca][$rif]['reset'])) {
                        $this->drawVeiInfo($rif,$v);
                        echo '<div id="grent_noresblock_'.$rif.'" style="position:relative;margin-top:10px;font-weight:bold;color:red;">';
                            echo 'Nessun Reset trovato';
                            if ($this->auth['manage']) {
                                echo '<img style="position:absolute;right:0px;top:0px;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/edit.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selManage(\'grent_noresblock_'.$rif.'\',\''.$this->tipoRent.'\',\''.$marca.'\',\''.$rif.'\',\''.$v['grent_id'].'\');" />';
                            }
                        echo '</div>';
                    }
                    else {
                        //echo json_encode($v['info']);
                        /*{"rif":5056590,"dms":"infinity","targa":"FR217RT","telaio":"WAUZZZ8V4JA129871","cod_marca":"A","des_marca":"AUDI",
                            "modello":"NODEF","des_veicolo":"NODEF","cod_interno":"","cod_esterno":"","des_interno":"","des_esterno":"","cod_alim":"","mat_motore":"","anno_modello":null,
                            "cod_vw_tipo_veicolo":"","des_vw_tipo_veicolo":"","cod_po_tipo_veicolo":"","des_po_tipo_veicolo":"","cod_ente_venditore":"",
                            "d_imm":"20180621","d_cons":"20180621","d_fine_gar":"20200620","d_rev":"","des_note_off":"","cod_infocar":"","cod_infocar_anno":"","cod_infocar_mese":"","cod_marca_infocar":""}
                        */
                        
                        $v=$this->calcolaStato($v);

                        echo '<div style="position:relative;height:15px;font-size:0.9em;font-weight:bold;background-color:'.$v['color'].';">';
                            if ($v['pratica_da']!="") {
                                if ($v['pratica_stato']=='noleggio') echo 'Noleggiata '.mainFunc::gab_todata($v['pratica_da']).' - '.mainFunc::gab_todata($v['pratica_a']).' - '.$v['pratica_utente'];
                                if ($v['pratica_stato']=='prenotazione') echo 'PRENOT. '.mainFunc::gab_todata($v['pratica_da']).' - '.mainFunc::gab_todata($v['pratica_a']).' - '.$v['pratica_utente'];
                            }
                            elseif ($v['stato']=='regolare') echo 'Regolare';
                            else echo 'Limiti superati';

                            if ($this->auth['manage']) {
                                echo '<img style="position:absolute;right:0px;top:0px;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/edit.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selManage(\'grent_veiblock_'.$rif.'\',\''.$this->tipoRent.'\',\''.$marca.'\',\''.$rif.'\',\''.$v['grent_id'].'\');"/>';
                            }
                            
                        echo '</div>';

                        echo '<div id="grent_veiblock_'.$rif.'" style="position:relative;cursor:pointer;" ';
                            if ($v['info']) echo 'onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selVei(this.id,\''.$this->tipoRent.'\',\''.$marca.'\',\''.$rif.'\');"';
                        echo ' >';

                           $this->drawVeiInfo($rif,$v);

                            echo '<div style="margin-top:5px;">';
                                echo '<table style="width:100%;border-collapse:collapse;font-size:0.9em;text-align:center;border:1px solid black;" >';
                                    echo '<tr>';
                                        echo '<th style="width:25%;">Reset</th>';
                                        echo '<th style="width:25%;">Km</th>';
                                        echo '<th style="width:25%;">Lim T</th>';
                                        echo '<th style="width:25%;">Lim Km</th>';
                                    echo '</tr>';
                                    echo '<tr>';
                                        echo '<td style="width:25%;">'.mainFunc::gab_todata($v['reset']).'</td>';
                                        echo '<td style="width:25%;">'.$v['actual_km'].'</td>';
                                        echo '<td style="width:25%;'.(($v['stato']=='tempo' || $v['stato']=='limiti')?"background-color:#fc8888;":"").'">'.mainFunc::gab_todata(date('Ymd',$v['lim'])).'</td>';
                                        echo '<td style="width:25%;'.(($v['stato']=='km' || $v['stato']=='limiti')?"background-color:#fc8888;":"").'">'.($v['km_reset']+$v['sva_km']).'</td>';
                                    echo '</tr>';
                                echo '</table>';
                            echo '</div>';

                            echo '<div style="height:15px;font-size:0.9em;margin-top:5px;">';
                                echo 'Note: '.$v['note'];
                            echo '</div>';
                        
                        echo '</div>';
                    }
                echo '</div>';
            }
        }

        /*echo '<div>';
            echo json_encode($this->galileo->getLog('query'));
        echo '</div>';*/
    }

    function drawVeiInfo($rif,$v) {

        echo '<div style="height:15px;margin-top:5px;">';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;font-size:0.9em;" >'.$rif.'</div>';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:17%;font-weight:bold;" >'.$v['info']['targa'].'</div>';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;font-weight:bold;" >'.$v['info']['telaio'].'</div>';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;font-size:0.9em;" >'.mainFunc::gab_todata($v['info']['d_cons']).'</div>';
        echo '</div>';

        echo '<div style="height:15px;font-size:0.9em;">';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;" >'.substr($v['info']['des_marca'],0,7).'</div>';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:17%;" >'.$v['info']['modello'].'</div>';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:65%;" >'.substr($v['info']['des_veicolo'],0,35).'</div>';
        echo '</div>';
    }

    function drawVei($marca,$vei) {

        $this->popolaInfo($marca,$vei);
        $v=$this->calcolaStato($this->veicoli[$marca][$vei]);

        echo '<div>';

            echo '<div style="height:10px;font-size:0.9em;background-color:'.$v['color'].';text-align:right;">';
                echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].chiudiVei();" />';
            echo '</div>';

            //veicolo

            echo '<div style="height:15px;margin-top:5px;">';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;font-size:0.9em;" >'.$vei.'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:17%;font-weight:bold;" >'.$v['info']['targa'].'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;font-weight:bold;" >'.$v['info']['telaio'].'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;font-size:0.9em;" >'.mainFunc::gab_todata($v['info']['d_cons']).'</div>';
            echo '</div>';

            echo '<div style="height:15px;font-size:0.9em;">';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;" >'.substr($v['info']['des_marca'],0,10).'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:17%;" >'.$v['info']['modello'].'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:65%;" >'.substr($v['info']['des_veicolo'],0,35).'</div>';
            echo '</div>';

            //reset

            echo '<div style="margin-top:5px;">';
                echo '<table style="width:100%;border-collapse:collapse;font-size:1em;text-align:center;border:1px solid black;" >';
                    echo '<tr>';
                        echo '<th style="width:20%;">Inizio</th>';
                        echo '<th style="width:20%;">Reset</th>';
                        echo '<th style="width:20%;">Km</th>';
                        echo '<th style="width:20%;">Lim T</th>';
                        echo '<th style="width:20%;">Lim Km</th>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td style="width:20%;">'.mainFunc::gab_todata($v['data_i']).'</td>';
                        echo '<td style="width:20%;">'.mainFunc::gab_todata($v['reset']).'</td>';
                        echo '<td style="width:20%;">'.$v['actual_km'].'</td>';
                        echo '<td style="width:20%;'.(($v['stato']=='tempo' || $v['stato']=='limiti')?"background-color:#fc8888;":"").'">'.mainFunc::gab_todata(date('Ymd',$v['lim'])).'</td>';
                        echo '<td style="width:20%;'.(($v['stato']=='km' || $v['stato']=='limiti')?"background-color:#fc8888;":"").'">'.($v['km_reset']+$v['sva_km']).'</td>';
                    echo '</tr>';
                echo '</table>';
            echo '</div>';

            //fascia

            echo '<div style="margin-top:10px;font-size:1em;">';

                $tf=$v['sva']+$this->fasce[$v['fascia']]['ipt']+$this->fasce[$v['fascia']]['bollo']+$this->fasce[$v['fascia']]['kasko'];
                $tv=$this->fasce[$v['fascia']]['manut']+$this->fasce[$v['fascia']]['gomme'];

                echo '<div>';
                    echo 'Fascia: '.$v['fascia'].' - '.$this->fasce[$v['fascia']]['testo'];
                echo '</div>';

                echo '<div>';
                    echo '<table style="width:100%;border-collapse:collapse;font-size:1em;text-align:center;border:1px solid black;" >';
                        echo '<tr>';
                            echo '<th style="width:14%;">SVALUT.</th>';
                            echo '<th style="width:15%;">IPT/BOLLO</th>';
                            echo '<th style="width:17%;">KASKO</th>';
                            echo '<th style="width:17%;">MANUT.</th>';
                            echo '<th style="width:17%;">TOT</th>';
                            echo '<th style="width:10%;">COEF</th>';
                            echo '<th style="width:10%;">C.KM</th>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<td style="width:14%;">'.number_format($v['sva'],2,',','').'</td>';
                            echo '<td style="width:15%;">'.number_format($this->fasce[$v['fascia']]['ipt']+$this->fasce[$v['fascia']]['bollo'],2,',','.').'</td>';
                            echo '<td style="width:17%;">'.number_format($this->fasce[$v['fascia']]['kasko'],2,',','.').'</td>';
                            echo '<td style="width:17%;">'.number_format($this->fasce[$v['fascia']]['manut']+$this->fasce[$v['fascia']]['gomme'],2,',','.').'</td>';
                            echo '<td style="width:17%;font-weight:bold;">'.number_format($tf+$tv,2,',','.').'</td>';
                            echo '<td style="width:10%;">'.number_format($v['coeff'],1,',','').'</td>';
                            echo '<td style="width:10%;">'.number_format($v['coeff_km'],2,',','').'</td>';
                        echo '</tr>';
                    echo '</table>';
                echo '</div>';

            echo '</div>';

            echo '<div style="height:15px;font-size:1em;margin-top:5px;">';
                echo 'Note: '.$v['note'];
            echo '</div>';

            echo '<div style="font-size:1em;margin-top:15px;text-align:center;">';
                echo '<hr style="width:80%;height:3px;background-color:teal;" />';
            echo '</div>';

            $this->noleggio['tot_fisso']=$tf;
            $this->noleggio['tot_var']=$tv;

            /*echo '<div style="font-size:1em;margin-top:5px;">';
                echo '<div style="font-weight:bold;font-size:1.1em;">Noleggio:</div>';
            echo '</div>';*/

            $this->drawForm($marca,$vei);

        echo '</div>';

    }

    function drawForm($marca,$vei) {

        $this->noleggio['reset_id']=$this->veicoli[$marca][$vei]['reset_id'];

        if (array_key_exists($vei,$this->pratiche)) {

            if ($this->pratiche[$vei]['info']) {
                foreach ($this->noleggio as $k=>$v) {
                    if (array_key_exists($k,$this->pratiche[$vei]['info'])) {
                        $this->noleggio[$k]=$this->pratiche[$vei]['info'][$k];
                    }
                }
            }
            else die('Dati noleggio corrotti!!!');
        }
        else {
            //se non c'è NESSUN noleggio/prenotazione aperta
            //setta al massimo i km (==illimitati)
            $this->noleggio['km']=$this->veicoli[$marca][$vei]['sva_km']-($this->veicoli[$marca][$vei]['actual_km']-$this->veicoli[$marca][$vei]['km_reset']);
            $this->noleggio['flag_limite']=1;
        }

        echo '<div style="position:relative;margin-top:10px;">';
            echo '<div style="position:relative;display:inline-block;width:150px;vertical-align:top;font-weight:bold;">Durata</div>';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;">';
                echo '<input id="grent_form_durata" style="width:120px;text-align:center;font-weight:bold;font-size:1.2em;" type="text" value="'.($this->noleggio['durata']!=0?$this->noleggio['durata']:"").'" disabled />';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:100px;vertical-align:top;">';
                echo '<input name="grent_form_misura" type="radio" value="g" checked />';
                //echo '<input name="grent_form_misura" type="radio" value="g" onclick="window._grentForm.setDurata();" '.($this->noleggio['misura']=='g'?"checked":"").' '.($this->noleggio['nol_id']!=0?"disabled":"").' />';
                echo '<span style="margin-left:5px;">Giorni</span>';
                //echo '<input name="grent_form_misura" type="radio" style="margin-left:20px;" value="m" onclick="window._grentForm.setDurata();" '.($this->noleggio['misura']=='m'?"checked":"").' '.($this->noleggio['nol_id']!=0?"disabled":"").' />';
                //echo '<span style="margin-left:5px;">Mesi</span>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:220px;vertical-align:top;text-align:right;">';

                if ($this->veicoli[$marca][$vei]['pratica_stato']=='prenotazione') {
                    echo '<button class="grent_form_allowbutton" style="position:relative;margin-right:20px;vertical-align:top;background-color:aquamarine;" onclick="window._grentForm.noleggia(\''.$this->noleggio['nol_id'].'\');">Noleggia</button>';
                }
                elseif ($this->veicoli[$marca][$vei]['pratica_stato']==''){
                    echo '<button class="grent_form_allowbutton" style="position:relative;margin-right:20px;vertical-align:top;background-color:yellow;" onclick="window._grentForm.prenota(\''.$this->tipoRent.'\');">Prenota</button>';
                }

                if ($this->veicoli[$marca][$vei]['pratica_stato']=='prenotazione' && ($this->auth['manage'] || $this->veicoli[$marca][$vei]['pratica_utente']==$this->logged) ) {
                    echo '<img style="position:relative;width:20px;height:20px;margin-right:20px;vertical-align:top;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/trash.png" onclick="window._grentForm.annulla(\''.$this->noleggio['nol_id'].'\');" />';
                }
                echo '<img id="grent_form_printicon" class="grent_form_allowbutton" style="width:20px;height:20px;position:relative;margin-right:5px;vertical-align:top;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/print.png" onclick="window._grentForm.stampa();" />';
            echo '</div>';
        echo '</div>';

        echo '<div style="position:relative;margin-top:10px;">';
            echo '<div style="position:relative;display:inline-block;width:150px;vertical-align:top;font-weight:bold;">';
                echo '<div>Km</div>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;">';
                echo '<input id="grent_form_km" style="width:120px;text-align:center;font-weight:bold;font-size:1.2em;" type="text" data-flag="'.($this->noleggio['nol_id']!=0 || $this->noleggio['flag_limite']==1?'1':'0').'" data-tempkm="'.$this->noleggio['km'].'" value="'.($this->noleggio['km']!=0?$this->noleggio['km']:"").'" onchange="window._grentForm.setKm(true);" '.($this->noleggio['nol_id']!=0 || $this->noleggio['flag_limite']==1?"disabled":"").' />';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:250px;vertical-align:top;">';
                echo '<span style="">Max:</span>';
                echo '<input id="grent_form_flag_limite" type="checkbox" style="margin-left:5px;" onclick="window._grentForm.setLimite(this.checked);" '.($this->noleggio['flag_limite']==1?"checked":"").' '.($this->noleggio['nol_id']!=0?"disabled":"").' />';
                echo '<span style="margin-left:5px;">Limite:</span>';
                echo '<span id="grent_form_flag_limite_txt" style="margin-left:5px;"></span>';
            echo '</div>';
        echo '</div>';

        /*echo '<div id="grent_form_calcolo_div" style="position:relative;margin-top:5px;'.($this->noleggio['misura']=='m'?"visibility:visible;":"visibility:hidden;").'">';
            echo '<div style="position:relative;display:inline-block;width:150px;vertical-align:top;text-align:right;padding-right:10px;box-sizing:border-box;">Km Anno:</div>';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;">';
                echo '<input id="grent_form_calcolo_km" style="width:120px;text-align:center;font-size:0.8em;" type="text" />';
                echo '<button style="margin-left:10px;font-size:0.7em;">Calcola</button>';
            echo '</div>';
        echo '</div>';*/

        echo '<div style="position:relative;margin-top:10px;">';
            echo '<div style="position:relative;display:inline-block;width:150px;vertical-align:top;font-weight:bold;">';
                echo '<div>Coeff. Km</div>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;">';
                echo '<input id="grent_form_ckm" style="width:80px;text-align:center;" type="text" value="'.($this->noleggio['actual_coeffkm']!=0?$this->noleggio['actual_coeffkm']:"").'" disabled />';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:250px;vertical-align:top;">';
                echo '<input id="grent_form_eccedenza" type="text" style="width:80px;text-align:center;" value="'.($this->noleggio['eccedenza']!=0?$this->noleggio['eccedenza']:"").'" disabled />';
                echo '<span id=""grent_form_flag_eccedenza_txt" style="margin-left:5px;">x km in eccedenza</span>';
            echo '</div>';
        echo '</div>';

        echo '<div style="position:relative;margin-top:10px;">';
            echo '<div style="position:relative;display:inline-block;width:150px;vertical-align:top;font-weight:bold;">';
                echo '<div>Giornaliero</div>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;text-align:left;">';
                $temp=($this->noleggio['actual_coeff']!=0?$this->noleggio['actual_coeff']:$this->veicoli[$marca][$vei]['coeff']+1);
                echo '<button id="grent_form_pm_m" onclick="window._grentForm.setCoeff(-1);" style="'.($temp==$this->veicoli[$marca][$vei]['coeff'] || $this->noleggio['nol_id']!=0?'visibility:hidden;':'visibility:visible;').'">-</button>';
                echo '<input id="grent_form_c" style="width:80px;text-align:center;margin-left:10px;" type="text" data-default="'.$this->veicoli[$marca][$vei]['coeff'].'" value="'.number_format($temp,1,'.','').'" disabled />';
                echo '<button id="grent_form_pm_p" style="margin-left:10px;'.($this->noleggio['nol_id']!=0?'visibility:hidden;':"").'" onclick="window._grentForm.setCoeff(1);" >+</button>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:160px;vertical-align:top;font-weight:bold;font-size:1.2em;">';
                echo '<div id="grent_form_giornaliero_txt" style="position:relative;display:inline-block;width:60px;vertical-align:top;text-align:right;" ></div>';
                echo '<div style="position:relative;display:inline-block;width:90px;vertical-align:top;margin-left:5px;">+ iva</div>';
            echo '</div>';
            echo '<div id="grent_form_incent" style="position:relative;display:inline-block;width:100px;vertical-align:top;border:2px solid green;height:25px;color:green;padding:5px;box-sizing:border-box;text-align:center;">';
            echo '</div>';
        echo '</div>';

        echo '<div style="position:relative;margin-top:10px;">';
            echo '<div style="position:relative;display:inline-block;width:150px;vertical-align:top;font-weight:bold;">';
                echo '<div>Franchigia</div>';
                echo '<div style="font-weight:normal;">'.$this->franchigie[$this->veicoli[$marca][$vei]['franchigia']]['importo'].'€ o '.$this->franchigie[$this->veicoli[$marca][$vei]['franchigia']]['perc'].'%</div>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;text-align:left;">';
                echo '<div>';
                    echo '<input id="grent_form_flag_franchigia" type="checkbox" style="margin-left:5px;" onclick="window._grentForm.setDurata();" '.($this->noleggio['flag_franchigia']==1?"checked":"").' '.($this->noleggio['nol_id']!=0?"disabled":"").' />';
                    echo '<span style="margin-left:5px;">Riduzione</span>';
                echo '</div>';
                echo '<div style="font-size:0.9em;" >';
                    echo $this->franchigie[$this->veicoli[$marca][$vei]['franchigia']]['flag_importo'].'€ ('.$this->franchigie[$this->veicoli[$marca][$vei]['franchigia']]['flag_perc'].'% dai '.$this->franchigie[$this->veicoli[$marca][$vei]['franchigia']]['flag_limite'].'€)';
                echo '</div>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:250px;vertical-align:top;font-weight:bold;font-size:1.2em;">';
                echo '<div id="grent_form_franchigia_txt" style="position:relative;display:inline-block;width:60px;vertical-align:top;text-align:right;" ></div>';
                echo '<div style="position:relative;display:inline-block;width:50px;vertical-align:top;margin-left:5px;">+ iva</div>';
                echo '<div id="grent_form_franchigia_txt_tot" style="position:relative;display:inline-block;width:120px;vertical-align:top;text-align:right;font-weight:normal;" ></div>';
            echo '</div>';
        echo '</div>';

        echo '<div style="position:relative;margin-top:10px;">';
            //echo '<div>Importi Iva ESCLUSA:</div>';
            echo '<table style="width:100%;border-collapse:collapse;font-size:1em;text-align:center;height:50px;" >';
                echo '<tr style="height:25px;">';
                    echo '<th style="width:2%;">1 anno</th>';
                    echo '<th style="width:20%;">6 Mesi (182 gg)</th>';
                    echo '<th style="width:20%;">2 Mesi (60 gg)</th>';
                    echo '<th style="width:20%;">Mese (30 gg)</th>';
                    echo '<th style="width:20%;">Giornata (1 gg)</th>';
                echo '</tr>';
                echo '<tr style="height:25px;">';
                    echo '<td id="grent_tariffa_txt_5" style="width:20%;"></td>';
                    echo '<td id="grent_tariffa_txt_4" style="width:20%;"></td>';
                    echo '<td id="grent_tariffa_txt_3" style="width:20%;"></td>';
                    echo '<td id="grent_tariffa_txt_2" style="width:20%;"></td>';
                    echo '<td id="grent_tariffa_txt_1"style="width:20%;"></td>';
                echo '</tr>';
            echo '</table>';
        echo '</div>';

        echo '<div style="position:relative;margin-top:5px;">';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;text-align:center;">';
                echo '<div style="font-weight:bold;">Inizio</div>';
                echo '<div>';
                    echo '<input id="grent_form_inizio" style="width:150px;" type="date" value="'.($this->noleggio['nol_id']!=0?mainFunc::gab_toinput($this->noleggio['pren_i']):"").'" onchange="window._grentForm.setDurata();" '.($this->noleggio['nol_id']!=0?"disabled":"").' />';
                echo '</div>';
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;width:200px;vertical-align:top;text-align:center;">';
                echo '<div style="font-weight:bold;">Fine</div>';
                echo '<div>';
                    echo '<input id="grent_form_fine" style="width:150px;" type="date" value="'.($this->noleggio['nol_id']!=0?mainFunc::gab_toinput($this->noleggio['pren_f']):"").'" onchange="window._grentForm.setDurata();" '.($this->noleggio['nol_id']!=0?"disabled":"").' />';
                echo '</div>';
            echo '</div>';   

            echo '<div style="position:relative;display:inline-block;width:270px;height:60px;vertical-align:top;text-align:center;border:1px solid black;box-sizing:border-box;">';
                echo '<div style="position:relative;display:inline-block;width:118px;vertical-align:top;text-align:center;">';
                    echo '<div style="font-weight:bold;">Tariffa</div>';
                    echo '<div id="grent_form_tariffa_txt" style="font-weight:bold;background-color:bisque;"></div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;width:149px;vertical-align:top;text-align:center;">';
                    echo '<div style="font-weight:bold;">Totale</div>';
                    echo '<div id="grent_form_totale_txt" style="background-color:bisque;"></div>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        echo '<div style="font-size:1em;margin-top:5px;box-sizing:border-box;">';
            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;" >Note Prenot.:</div>';
            echo '<div style="position:relative;display:inline-block;width:90%;vertical-align:top;" >';
                echo '<textarea id="grent_form_note" style="width:100%;resize:none;" row="2" >'.addslashes($this->noleggio['note_pren']).'</textarea>';
            echo '</div>';
        echo '</div>';

        echo '<input id="grent_form_veicolo" type="hidden" value="'.base64_encode(json_encode($this->veicoli[$marca][$vei])).'"/>';
        echo '<input id="grent_form_noleggio" type="hidden" value="'.base64_encode(json_encode($this->noleggio)).'"/>';


        //echo '<div>'.json_encode($this->noleggio).'</div>';

        echo '<script type="text/javascript">';

            echo 'window._grentForm=new grentFormClass();';
            echo 'var temp='.json_encode($this->franchigie[$this->veicoli[$marca][$vei]['franchigia']]).';';
            echo 'window._grentForm.loadFranchigia(temp);';
            echo 'window._grentForm.setDurata();';
            
        echo '</script>';
    }


}
?>