<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_periodi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_piani.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_varianti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_moduli.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_parametri.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_link.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_esterni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_rettifiche.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CENTAVOS_freezed.php');

class galileoCentavos extends galileoOps {

    function __construct() {

        $this->tabelle['CENTAVOS_periodi']=new centavos_periodi();
        $this->tabelle['CENTAVOS_piani']=new centavos_piani();
        $this->tabelle['CENTAVOS_varianti']=new centavos_varianti();
        $this->tabelle['CENTAVOS_moduli']=new centavos_moduli();
        $this->tabelle['CENTAVOS_parametri']=new centavos_parametri();
        $this->tabelle['CENTAVOS_link']=new centavos_link();
        $this->tabelle['CENTAVOS_esterni']=new centavos_esterni();
        $this->tabelle['CENTAVOS_rettifiche']=new centavos_rettifiche();
        $this->tabelle['CENTAVOS_freezed']=new centavos_freezed();
    }

    function maxIdx($arr) {
        
        $this->query="SELECT
            isnull(max(a.ID),0) AS piani,
            isnull(max(b.ID),0) AS moduli,
            isnull(max(c.ID),0) AS varianti,
            isnull(max(d.ID),0) AS parametri
        ";

        $this->query.=" FROM ".$this->tabelle['CENTAVOS_piani']->getTabName()." AS a, ".$this->tabelle['CENTAVOS_moduli']->getTabName()." AS b, ".$this->tabelle['CENTAVOS_varianti']->getTabName()." AS c, ".$this->tabelle['CENTAVOS_parametri']->getTabName()." AS d";

        return true;
    }

    function addModulo($arr) {

        $this->arrQuery=array();
        $this->errorFlag=true;
        //indica la tabella temporanea
        $this->incsetTab='#resvar';

        $incSet="ID_modulo";

        ///////////////////////////////////////////    
        $res=$this->doTransactionHead("CENTAVOS_moduli",$this->incsetTab);

        if (!$res) {
            $this->errorFlag=false;
            return $this->errorFlag;
        }

        //inserimento modulo:
        $this->query="";

        //doInsert($tabella,$arr,$wclause,$incset)
        $res=$this->doInsert("CENTAVOS_moduli",$arr,$incSet,"query");

        if (!$res) {
            $this->errorFlag=false;
            return $this->errorFlag;
        }

        $this->arrQuery[]=$this->query;
    
        $this->arrQuery[]=$this->tabelle['CENTAVOS_moduli']->getResvar();

        return $this->errorFlag;

    }

    function addParametro($arr) {

        $this->arrQuery=array();
        $this->errorFlag=true;
        //indica la tabella temporanea
        $this->incsetTab='#resvar';

        $incSet="ID_parametro";

        ///////////////////////////////////////////    
        $res=$this->doTransactionHead("CENTAVOS_parametri",$this->incsetTab);

        if (!$res) {
            $this->errorFlag=false;
            return $this->errorFlag;
        }

        //inserimento modulo:
        $this->query="";

        $a=array(
            "param"=>'{"titolo":"'.$arr['titolo'].'","classe":"individuale","tipo":"'.($arr['tipo']=="principali"?"lineare":"griglia").'","paletto":"0","rettifica":"0"}',
            "funzione"=>'{"min":0,"max":100,"fattore":1}',
            "griglia"=>'{"0":[0,0],"1":["",""],"2":["",""],"3":["",""],"4":["",""],"5":["",""]}'
        );

        //doInsert($tabella,$arr,$wclause,$incset)
        $res=$this->doInsert("CENTAVOS_parametri",$a,$incSet,"query");

        if (!$res) {
            $this->errorFlag=false;
            return $this->errorFlag;
        }

        $this->arrQuery[]=$this->query;
    
        $this->arrQuery[]=$this->tabelle['CENTAVOS_parametri']->getResvar();

        return $this->errorFlag;

    }

    function getCollaboratori($arr) {

        $wclause="rep.reparto IN (".$arr['reparti'].")";

        if ( isset($arr['coll']) && $arr['coll']!="" ) {
            $wclause.=" AND cog.collaboratore='".$arr['coll']."'";
        }

        $this->link['maestro']->getCollaboratori($wclause,$arr['data_i'],$arr['data_f']);

        $this->query="SELECT
            coll.*,
            isnull(link.variante,'') AS variante,
            isnull(link.ID,0) AS ID_link,
            isnull(link.piano,0) AS ID_piano,
            link.data_i AS dlink_i,
            link.data_f AS dlink_f,
            link.periodo_inizio AS periodo_i,
            link.periodo_fine AS periodo_f,
            link.grado
        ";

        $this->query.=" FROM (".$this->link['maestro']->getQuery().') AS coll';

        $this->query.=" LEFT JOIN ".$this->tabelle['CENTAVOS_link']->getTabName()." AS link ON link.piano='".$arr['piano']."' AND link.coll=coll.ID_coll AND link.data_i<='".$arr['data_f']."' AND link.data_f>='".$arr['data_i']."'";

        $this->query.=" ORDER BY coll.cognome,coll.nome,link.data_i";

        return true;

    }

    function getLink($arr) {

        $this->query="SELECT
            link.*,
            piano.reparto,
            piano.data_i AS piano_i,
            piano.data_f AS piano_f
        ";

        $this->query.=" FROM ".$this->tabelle['CENTAVOS_link']->getTabName()." AS link";

        $this->query.=" INNER JOIN ".$this->tabelle['CENTAVOS_piani']->getTabName()." AS piano ON link.piano=piano.ID";

        $this->query.=" WHERE link.ID='".$arr['link']."'";

        return true;
    }

    function getRettifiche($arr) {

        $this->query="SELECT
            ret.*,
            per.d_inizio AS d_inizio,
            per.d_fine AS d_fine
        ";

        $this->query.=" FROM ".$this->tabelle['CENTAVOS_rettifiche']->getTabName()." AS ret";

        $this->query.=" INNER JOIN ".$this->tabelle['CENTAVOS_periodi']->getTabName()." AS per ON ret.periodo=per.ID";

        $this->query.=" WHERE ret.piano='".$arr['piano']."'";

        return true;

    }

    function getLastFine($arr) {

        $this->query="SELECT max(d_fine) as d_fine from ".$this->tabelle['CENTAVOS_periodi']->getTabName()." where piano='".$arr['piano']."'";

        return true;
    }

    function getPianoPeriodo($arr) {

        $this->query="
            SELECT
            pi.*,
            isnull(pe.d_inizio,'') AS d_inizio,
            isnull(pe.d_fine,'') AS d_fine,
            pe.stato,
            pe.hidden,
            pe.ID as idp
        ";

        $this->query.=" FROM ".$this->tabelle['CENTAVOS_piani']->getTabName()." as pi";
        $this->query.=" LEFT JOIN ".$this->tabelle['CENTAVOS_periodi']->getTabName()." as pe ON pi.ID=pe.piano";

        $this->query.=" WHERE pi.ID='".$arr['piano']."'";

        $this->query.=" ORDER BY isnull(pe.d_fine,'') DESC";

        return true;
    }

    function copyPiano($arr) {
        //TRANSACTION ON

        /*$arr=array(
            "tag"=>$nebulaParams['desc'],
            "piano"=>$old,
            "varianti"=>$var,
            "moduli"=>$mod,
            "parametri"=>$par,
            "idx"=>$idx
        );*/

        //{"piani":14,"moduli":97,"varianti":126,"parametri":168}
        $idPiano=$arr['idx']['piani']+1;

        $this->arrQuery[]="
            INSERT INTO  ".$this->tabelle['CENTAVOS_piani']->getTabName()." (ID,reparto,macroreparto,descrizione,base_dati,parametri,cadenza,data_i,data_f,varianti,stato)
            SELECT '".$idPiano."',reparto,macroreparto,'".$arr['tag']."',base_dati,parametri,cadenza,data_i,data_f,varianti,stato
            FROM ".$this->tabelle['CENTAVOS_piani']->getTabName()."
            WHERE ID='".(int)$arr['piano']['ID']."';
        ";

        //#######################################################

        $idPar=$arr['idx']['parametri']+1;

        //{"94":"","95":"","109":"","96":"","97":"","98":"","99":"","93":"","112":"","10":"","11":"","13":"","77":"","108":"","17":"","19":"","20":"","110":"","114":"","115":"","113":"","8":"","18":"","155":""}

        foreach ($arr['parametri'] as $k=>$p) {

            $this->arrQuery[]="
                INSERT INTO  ".$this->tabelle['CENTAVOS_parametri']->getTabName()." (ID,param,griglia,funzione)
                SELECT '".$idPar."',param,griglia,funzione
                FROM ".$this->tabelle['CENTAVOS_parametri']->getTabName()."
                WHERE ID='".(int)$k."';
            ";

            $arr['parametri'][$k]=$idPar;
            $idPar++;
        }

        //#######################################################

        $this->disableIncrement('CENTAVOS_moduli');
        $idMod=$arr['idx']['moduli']+1;

        //{"51":{"newID":"","map":{"p":"[\"94\"]","m":"[\"95\"]"}}, ... }

        foreach ($arr['moduli'] as $k=>$m) {

            $t=array(
                "ID"=>$idMod,
                "titolo"=>$m['titolo'],
                "principali"=>array(),
                "modificatori"=>array()
            );

            foreach ($m['map']['p'] as $kp=>$p) {
                $t['principali'][]="".$arr['parametri'][$p];
            }
            foreach ($m['map']['m'] as $kp=>$p) {
                $t['modificatori'][]="".$arr['parametri'][$p];
            }

            $t['principali']=json_encode($t['principali']);
            $t['modificatori']=json_encode($t['modificatori']);

            $this->query="";
            $this->doInsert("CENTAVOS_moduli",$t,'','query');

            $this->arrQuery[]=$this->query;

            $arr['moduli'][$k]['newID']=$idMod;
            $idMod++;
        }

        //#######################################################

        $this->disableIncrement('CENTAVOS_varianti');
        $idVar=$arr['idx']['varianti']+1;

        /*{"60":{
            "row":{"ID":60,"variante":"TEC","piano":7,"titolo":"Citnow","eccedenza":1,"moduli":"[\"51\"]","budget":150,"flag_gradi":1,"limite":"{\"51\":120}","peso":"{\"51\":100}","coefficienti":"{\"_pres_\":true,\"_redd_\":true,\"_rett_\":true}","gradi":"{\"0\":{\"51\":100},\"1\":{\"51\":100},\"2\":{\"51\":100},\"3\":{\"51\":100},\"4\":{\"51\":100}}","livello":"0"},"moduli":{"51":{"ID":51,"titolo":"Video Citnow","principali":"[\"94\"]","modificatori":"[\"95\"]"}}},"75":{"row":{"ID":75,"variante":"RT","piano":7,"titolo":"Budget Ricambi Vgi","eccedenza":0,"moduli":"[\"65\"]","budget":150,"flag_gradi":1,"limite":"{\"65\":100}","peso":"{\"65\":100}","coefficienti":"{\"_pres_\":false,\"_redd_\":false,\"_rett_\":false}","gradi":"{\"0\":{\"65\":100},\"1\":{\"65\":100},\"2\":{\"65\":100},\"3\":{\"65\":100},\"4\":{\"65\":100}}","livello":"0"},
            "moduli":{"65":{"ID":65,"titolo":"Budget Ricambi Vgi","principali":"[\"109\"]","modificatori":"[]"}}
        }*/

        foreach ($arr['varianti'] as $k=>$v) {

            //echo '<div>'.$k.'</div>';
            //echo '<div>'.json_encode($v).'</div>';

            $t=$v['row'];

            $t['ID']=$idVar;
            $t['piano']=$idPiano;

            $tres=array();
            $tm=json_decode($t['moduli'],true);
            foreach ($tm as $km=>$kv) {
                $tres[]="".$arr['moduli'][(int)$kv]['newID'];
            }
            $t['moduli']=json_encode($tres);

            $tres=array();
            $tm=json_decode($t['limite'],true);
            foreach ($tm as $km=>$kv) {
                $tres["".$arr['moduli'][(int)$km]['newID']]=$kv;
            }
            $t['limite']=json_encode($tres);

            $tres=array();
            $tm=json_decode($t['peso'],true);
            foreach ($tm as $km=>$kv) {
                $tres["".$arr['moduli'][(int)$km]['newID']]=$kv;
            }
            $t['peso']=json_encode($tres);

            //$tres=array('0'=>array(),'1'=>array(),'2'=>array(),'3'=>array(),'4'=>array());
            $tres=array();
            $tm=json_decode($t['gradi'],true);
            foreach ($tm as $km=>$kv) {

                foreach ($kv as $a=>$b) {
                    $tres[''.$km][''.$arr['moduli'][(int)$a]['newID']]=$b;
                }
            }
            $t['gradi']=json_encode($tres,JSON_FORCE_OBJECT);

            //echo '<div>'.json_encode($t).'</div>';

            $this->query="";
            //$this->doInsert("CENTAVOS_varianti",$t,'','query');
            $this->query.="INSERT INTO ".$this->tabelle['CENTAVOS_varianti']->getTabName()." VALUES ('".$t['ID']."','".$t['variante']."','".$t['piano']."','".$t['titolo']."','".$t['eccedenza']."','".$t['moduli']."','".$t['budget']."','".$t['flag_gradi']."','".$t['limite']."','".$t['peso']."','".$t['coefficienti']."','".$t['gradi']."','".$t['livello']."');";

            $this->arrQuery[]=$this->query;

            //echo '<div>'.$this->query.'</div>';

            $idVar++;
        }

        $temp="";
        foreach ($this->arrQuery as $k=>$v) {
            $temp.=$v;
        }
        $this->arrQuery=array();
        $this->arrQuery[]=$temp;

        //echo '<div>'.$temp.'</div>';

        return true;

    }

} 


?>