<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chekko/chekko.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_anagrafiche.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_anagrafiche.php');

class nebulaAnagrafica extends chekko {
    //gestisce tutte le necessità riferite all'elemento anagrafica
    //viene chiamata per ogni operazione ma non viene sempre instanziata allo stesso modo
    //dipende dalle funzioni che servono al momento

    //identifica il dms primario per la gestione delle anagrafiche
    protected $map=array(
        "dms"=>"concerto"
    );

    //sono gli elementi di base di una anagrafica
    protected $info=array(
        'cod_anagra'=>'',
        'ragsoc'=>'',
        'cfisc'=>'',
        'piva'=>'',
        'email'=>'',
        'loc_nasc'=>'',
        'cap_nasc'=>'',
        'prov_nasc'=>'',
        'd_nasc'=>'',
        'indir_res'=>'',
        'loc_res'=>'',
        'cap_res'=>'',
        'prov_res'=>'',
        'tel1'=>'',
        'tel2'=>'',
        'des_patente_numero'=>'',
        'des_patente_rilasciata_da'=>'',
        'd_patente'=>'',
        'd_scadenza_patente'=>'',
        'cognome'=>'',
        'nome'=>'',
        'cod_naz_nasc'=>'',
        'cod_naz_res'=>'',
        'cod_localita_res'=>'',
        'cod_localita_nasc'=>'',
        'ind_sesso'=>'',
        'pec_fe'=>'',
        'cod_des_fe'=>'',
        'pr_a'=>'',
        'pr_b'=>'',
        'pr_c'=>'',
        'pr_d'=>'',
        'pr_e'=>'',
        'vgi_tipo_cli'=>'',
        'vgi_1'=>'',
        'vgi_2'=>'',
        'vgi_3'=>'',
        'vgi_critico'=>'',
        'vgi_critico_comm'=>'',
        'vgi_d_cri'=>'',
        'vgi_utente_cri'=>'',
        'pfis'=>'',
        'fazi'=>'',
        'fdin'=>'',
        'fepu'=>'',
        'mail_flag'=>'',
        'note'=>array()
    );

    protected $galileo;

    function __construct($dms,$galileo) {

        parent::__construct('nebulaAnagrafica');

        $this->galileo=clone($galileo);

        if ($dms!="") {
            $this->map['dms']=$dms;
        }

        $this->switchDms($this->map['dms']);

        $this->chk_fields=array(
            "cod_anagra"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "tipo"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cognome"=>array(
                "js_chk_ana_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"tipo_flag","op"=>"==","val"=>"noco")
            ),
            "nome"=>array(
                "js_chk_ana_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"tipo_flag","op"=>"==","val"=>"noco")
            ),
            "ragsoc"=>array(
                "js_chk_ana_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"tipo_flag","op"=>"==","val"=>"ragsoc")
            ),
            "cod_localita_res"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "indir_res"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "naz_res"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cap_res"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "prov_res"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "localita_res"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "tel1"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"tel2","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "tel2"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"tel1","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "mail"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>"mail_flag"),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "mail_flag"=>array(
                "js_chk_ana_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>"mail"),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cap_nas"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "localita_nas"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "prov_nas"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "data_nas"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "sesso"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "naz_nas"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cod_localita_nas"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cfisc"=>array(
                "js_chk_ana_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"tipo_flag","op"=>"==","val"=>"noco")
            ),
            "piva"=>array(
                "js_chk_ana_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"piva_flag","op"=>"==","val"=>"1")
            ),
            "fe_cod_des"=>array(
                "js_chk_ana_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>"fe_pec"),
                "js_chk_ana_ifreq"=>array("campo"=>"piva_flag","op"=>"==","val"=>"1")
            ),
            "fe_pec"=>array(
                "js_chk_ana_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>"fe_cod_des"),
                "js_chk_ana_ifreq"=>array("campo"=>"piva_flag","op"=>"==","val"=>"1")
            ),
            "pat_num"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "pat_rilda"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "pat_ril"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "pat_scad"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "tipo_flag"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "piva_flag"=>array(
                "js_chk_ana_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ana_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->tipi = array(
            "cod_anagra"=>"none",
            "tipo"=>"none",
            "nome"=>"text",
            "cognome"=>"text",
            "ragsoc"=>"text",
            "indir_res"=>"text",
            "cap_res"=>"cap",
            "localita_res"=>"text",
            "prov_res"=>"prov",
            "naz_res"=>"naz_res",
            "cod_localita_res"=>"none",
            "tel1"=>"phone",
            "tel2"=>"phone",
            "mail"=>"mail",
            "mail_flag"=>"digit",
            "cap_nas"=>"cap",
            "localita_nas"=>"text",
            "prov_nas"=>"prov",
            "data_nas"=>"data",
            "sesso"=>"none",
            "naz_nas"=>"naz_nas",
            "cfisc"=>"cfisc",
            "piva"=>"piva",
            "cod_localita_nas"=>"none",
            "pat_num"=>"patente",
            "pat_rilda"=>"text",
            "pat_ril"=>"data",
            "pat_scad"=>"scad",
            "fe_cod_des"=>"fe_cod_des",
            "fe_pec"=>"mail",
            "tipo_flag"=>"none",
            "piva_flag"=>"none"
        );

        //dall'esterno occorre caricare EXPORT e CONV in base al DMS

        //09.11.2021 provo a non utilizzare "mappa" considerato che mi gioco in casa la scrittura del form
        
    }

    function switchDms($dms) {

        $this->map['dms']=$dms;
        
        if ($this->map['dms']=='concerto') {

            $obj=new galileoConcertoAnagrafiche();
            $nebulaDefault['anagra']=array("maestro",$obj);
            $this->galileo->setFunzioniDefault($nebulaDefault);

            return true;
        }

        else if ($this->map['dms']=='infinity') {

            $obj=new galileoInfinityAnagrafiche();
            $nebulaDefault['anagra']=array("rocket",$obj);
            $this->galileo->setFunzioniDefault($nebulaDefault);

            return true;
        }

        return false;

    }

    function loadAnagra($tipo,$rif) {

        $this->galileo->executeGeneric('anagra','anaSelect',array("rif"=>$rif,"tipo"=>$tipo),'');

        $fetID=$this->galileo->preFetch('anagra');

        while($row=$this->galileo->getFetch('anagra',$fetID)) {

            foreach ($this->info as $k=>$V) {
                if (array_key_exists($k,$row)) {
                    $this->info[$k]=$row[$k];
                }
            }
        }

    }

    function draw_js() {

        /*echo 'window._js_chk_'.$this->form_tag.'.kind_tt=function(val,id) {';
            echo <<<JS
                val = val.toUpperCase();
                val.trim();
                this.chg_val(id,val);
                //se TRUE significa che sono stati trovati caratteri non validi
                var pattern=/[^A-Z0-9]/;
                return this.pattern.test(val);
JS;
        echo '};';*/
    }

    function draw_css() {}

    //////////////////////////////////////////////////////////////////////////////////

    function getStato() {
        //ritorna 'V' se l'anagrafica è corretta
        //ritorna 'G' se mancano dei dati importanti

        return 'V';
    }

    function draw() {

    }

    function drawMain($edit) {

        $txt='<div style="position:relative;text-align:left;" >';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;height:15px;vertical-align:top;font-size:1em;">';

                    $txt.='<div style="position:relative;display:inline-block;width:20%;height:15px;vertical-align:top;font-size:0.8em;overflow:hidden;">';
                        $txt.='<span style="margin-left:3px;">'.number_format((int)$this->info['cod_anagra'],0,'','').'</span>';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;display:inline-block;width:80%;height:15px;vertical-align:top;font-size:1em;overflow:hidden;">';
                        $txt.='<span style="font-weight:bold;">'.utf8_encode($this->info['ragsoc']).'</span>';
                    $txt.='</div>';

                $txt.='</div>';

            $txt.='</div>';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;height:15px;vertical-align:top;font-size:1em;">';

                    $txt.='<div style="position:relative;display:inline-block;width:50%;height:15px;vertical-align:top;font-size:0.8em;overflow:hidden;">';
                        $txt.='<span style="margin-left:3px;">'.$this->info['indir_res'].'</span>';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;display:inline-block;width:49%;height:15px;vertical-align:top;font-size:0.8em;margin-left:1%;overflow:hidden;">';
                        $txt.='<span style="">'.$this->info['loc_res'].'</span>';
                    $txt.='</div>';

                $txt.='</div>';

            $txt.='</div>';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;height:17px;vertical-align:top;font-size:1em;">';

                    $txt.='<div style="position:relative;display:inline-block;width:48%;height:17px;line-height:17px;vertical-align:top;font-size:0.8em;overflow:hidden;border: 1px solid gray;box-sizing: border-box;">';
                        $txt.='<span style="margin-left:3px;">'.$this->info['email'].'</span>';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;display:inline-block;width:25%;height:17px;line-height:17px;vertical-align:top;font-size:0.8em;margin-left:1%;overflow:hidden;border: 1px solid gray;box-sizing: border-box;text-align:center;">';
                        $txt.='<span style="">'.$this->info['tel1'].'</span>';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;display:inline-block;width:25%;height:17px;line-height:17px;vertical-align:top;font-size:0.8em;margin-left:1%;overflow:hidden;border: 1px solid gray;box-sizing: border-box;text-align:center;">';
                        $txt.='<span style="">'.$this->info['tel2'].'</span>';
                    $txt.='</div>';

                $txt.='</div>';

            $txt.='</div>';

        $txt.='</div>';

        return $txt;

    }

    function drawLinkerHead($row) {

        $txt='<div style="position:relative;text-align:left;background-color:#eeeeee;" >';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;height:15px;vertical-align:top;font-size:1em;">';

                    $txt.='<div style="position:relative;display:inline-block;width:20%;height:15px;vertical-align:top;font-size:0.8em;">';
                        $txt.='<img style="position:relative;width:13px;margin-left:2px;top:1px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/grabo.png" onclick="window._nebulaOdl.linker.setAnagra(\''.$row['cod_anagra'].'\');" />';
                        $txt.='<span style="margin-left:3px;">'.number_format($row['cod_anagra'],0,'','').'</span>';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;display:inline-block;width:80%;height:15px;vertical-align:top;font-size:1em;">';
                        $txt.='<span style="margin-left:3px;font-weight:bold;">'.substr(utf8_encode($row['ragsoc']),0,30).'</span>';
                    $txt.='</div>';

                $txt.='</div>';

            $txt.='</div>';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;display:inline-block;width:34%;height:15px;vertical-align:top;font-size:0.9em;padding-left:2px;box-sizing:border-box;">'.substr($row['loc_res'],0,15).'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:33%;height:15px;vertical-align:top;font-size:0.9em;text-align:center;">'.$row['tel1'].'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:33%;height:15px;vertical-align:top;font-size:0.9em;text-align:center;">'.$row['tel2'].'</div>';

            $txt.='</div>';

        $txt.='</div>';

        return $txt;
    }

    function cercaLinker($param) {

        if (!$this->switchDms($param['dms'])) return;

        $ret=array(
            "records"=>0,
            "html"=>""
        );

        $first=true;
        $ana="";
        $link=array();

        $this->galileo->executeGeneric('anagra','getLinker',$param,"");

        $fetID=$this->galileo->preFetch('anagra');

        while($row=$this->galileo->getFetch('anagra',$fetID)) {

            if ($ana!=$row['cod_anagra']) {

                $ret['records']++;

                if ($ret['records']>25) break;

                if (!$first) {
                    //$ret['html'].='<div id="linkerAnagraLink_'.$row['cod_anagra'].'" data-info="'.base64_encode(json_encode($link)).'"></div>';
                    $ret['html'].='</div>';
                }

                //$ret['html'].='<div id="linkerAnagraDiv_'.$row['cod_anagra'].'" style="position:relative;width:93%;padding:2px;box-sizing:border-box;border-bottom:2px solid black;margin-top:4px;margin-bottom:4px;" data-info="'.base64_encode(json_encode($row)).'">';
                $ret['html'].='<div id="linkerAnagraDiv_'.$row['cod_anagra'].'" style="position:relative;width:93%;padding:2px;box-sizing:border-box;border-bottom:2px solid black;margin-top:4px;margin-bottom:4px;" data-info="">';

                //scrittura del DIV dei dati della vettura in questo contesto
                $ret['html'].=$this->drawLinkerHead($row);

                $ana=$row['cod_anagra'];

                $link=array();
            }

            //scrittura del div dell'abbinamento se esiste
            if ($row['progressivo']!='999') {

                $link[$row['num_rif_veicolo']][$row['progressivo']]=$row;

                if (count($link[$row['num_rif_veicolo']])==1) {

                    $ret['html'].='<div style="position:relative;margin-top:3px;border:1px solid black;padding:2px;box-sizing:border-box;background-color:beige;text-align:left;">';
                    
                        $ret['html'].='<div style="position:relative;display:inline-block;font-weight:bold;width:25%;vertical-align:top:">'.$row['targa'].'</div>';

                        $ret['html'].='<div style="position:relative;display:inline-block;width:60%;vertical-align:top;font-size:0.9em;">'.substr(strtolower($row['des_veicolo']),0,30).'</div>';

                        $ret['html'].='<div style="position:relative;display:inline-block;width:15%;height:15px;vertical-align:top;text-align:center;">';
                            if ($row['num_rif_veicolo']!='') $ret['html'].='<img style="position:relative;top:50%;width:20px;height:15px;cursor:pointer;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/link.png" />';
                        $ret['html'].='</div>';
                        
                    $ret['html'].='</div>';
                }
            }

            $first=false;
        }

        if (!$first) {
            //$ret['html'].='<div id="linkerAnagraLink_'.$ana.'" data-info="'.base64_encode(json_encode($link)).'"></div>';
            $ret['html'].='</div>';
        }

        //$ret['html']=json_encode($this->galileo->getLog('query'));

        return $ret;
    }

}

?>