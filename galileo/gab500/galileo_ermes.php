<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_reparti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/ERMES_ticket.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/ERMES_chat.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/DUDU_lines.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/DUDU_link.php');

class galileoErmes extends galileoOps {

    function __construct() {

        $this->tabelle['MAESTRO_reparti']=new maestro_reparti();
        $this->tabelle['ERMES_ticket']=new ermes_ticket();
        $this->tabelle['ERMES_chat']=new ermes_chat();

        $this->tabelle['DUDU_lines']=new dudu_lines();
        $this->tabelle['DUDU_link']=new dudu_link();
    }

    function newTicket($arr) {
        //è già stato impostato TRANSACTION TRUE

        $arr['ID']='###@id';

        $this->query='CREATE TABLE #temp ( k varchar(25), v varchar(25) );';

        $this->query.='DECLARE @id INT;';

        $this->query.="SELECT @id=(SELECT isnull(max(ID)+1,1) FROM ".$this->tabelle['ERMES_ticket']->getTabName().");";

        $this->query.="INSERT INTO #temp VALUES('id',@id);";

        $this->doInsert('ERMES_ticket',$arr,'','query');

        $chat=array(
            "ID"=>"###@id",
            "riga"=>1,
            "tipo"=>"Q",
            "utente"=>$arr['creatore'],
            "dataora"=>$arr['d_creazione'],
            "testo"=>$arr['msg']
        );

        $this->doInsert('ERMES_chat',$chat,'','query');

        $this->arrQuery[]=$this->query;

        $this->arrQuery[]="SELECT * FROM #temp;";

        return true;
    }

    function getPanorama($arr) {

        //{"sede":"PU","mrep":"A","reparto":"RIT"}

        $this->query="SELECT
            t.*,
            r.tipo as mrep,
            r.sede
        ";

        $this->query.=' FROM '.$this->tabelle['ERMES_ticket']->getTabName().' AS t';
        $this->query.=' INNER JOIN '.$this->tabelle['MAESTRO_reparti']->getTabName().' AS r ON t.reparto=r.tag';
        
        if (isset($arr['stato']) && $arr['stato']!='') {
            if ($arr['stato']=='chiuso') $this->query.=" WHERE t.stato='chiuso'";
            elseif ($arr['stato']=='aperto' || $arr['stato']=='nogest') {
                $this->query.=" WHERE t.stato!='chiuso'";
                if ($arr['stato']=='nogest') $arr['flagGestito']=0;
            }
            elseif ($arr['stato']=='tutti') $this->query.=" WHERE t.ID!=0";
        }
        else $this->query.=" WHERE t.stato!='chiuso'";

        if (isset($arr['reparto']) && $arr['reparto']!='') $this->query.=" AND t.reparto='".$arr['reparto']."'";

        elseif(isset($arr['mrep']) && $arr['mrep']!='') $this->query.=" AND r.tipo='".$arr['mrep']."'";

        elseif(isset($arr['sede']) && $arr['sede']!='') $this->query.=" AND r.sede='".$arr['sede']."'";

        if (isset($arr['flagGestito'])) {
            if ($arr['flagGestito']==0) $this->query.=" AND isnull(t.gestore,'')=''";
            else $this->query.=" AND isnull(t.gestore,'')!=''";
        }
        
        if(isset($arr['testo']) && $arr['testo']!='') $this->query.=" AND t.mittente LIKE '%".$arr['testo']."%'";

        if(isset($arr['monoReparto']) && $arr['monoReparto']!='') $this->query.=" AND t.reparto='".$arr['monoReparto']."'";

        if(isset($arr['monoCategoria']) && $arr['monoCategoria']!='') $this->query.=" AND t.categoria='".$arr['monoCategoria']."'";

        $this->query.=" ORDER BY t.scadenza";

        return true;
    }

    function getGestione($arr) {

        //{"sede":"PU","mrep":"A","reparto":"RIT"}

        if (!isset($arr['coll']) || $arr['coll']=='') return false;

        $this->query="SELECT
            t.*,
            r.tipo as mrep,
            r.sede
        ";

        $this->query.=' FROM '.$this->tabelle['ERMES_ticket']->getTabName().' AS t';
        $this->query.=' INNER JOIN '.$this->tabelle['MAESTRO_reparti']->getTabName().' AS r ON t.reparto=r.tag';
        
        $this->query.=" WHERE t.gestore IN (".$arr['coll'].") AND t.stato!='chiuso'";

        $this->query.=" ORDER BY t.scadenza";

        return true;
    }

    function getMiei($arr) {

        $this->query="SELECT
            t.*,
            r.tipo as mrep,
            r.sede,
            isnull(ans.ges,0) AS ges
        ";

        $this->query.=' FROM '.$this->tabelle['ERMES_ticket']->getTabName().' AS t';
        $this->query.=' INNER JOIN '.$this->tabelle['MAESTRO_reparti']->getTabName().' AS r ON t.reparto=r.tag';
        $this->query.=" LEFT JOIN (
            SELECT
            ID,
            count(*) AS ges
            FROM ".$this->tabelle['ERMES_chat']->getTabName()."
            WHERE utente='".$arr['logged']."' AND tipo='A'
            GROUP BY ID
        ) AS ans ON t.ID=ans.ID";
        
        $this->query.=" WHERE t.ID!=0";

        if (isset($arr['da']) && isset($arr['a'])) {
            $this->query.=" AND CONVERT(VARCHAR(8),t.d_creazione)>='".$arr['da']."' AND CONVERT(VARCHAR(8),t.d_creazione)<='".$arr['a']."'";
        }

        if ($arr['gestione']!='tutti') {
            if ($arr['gestione']=='creato') $this->query.=" AND t.creatore='".$arr['logged']."'";
            elseif ($arr['gestione']=='gestito') $this->query.=" AND ges>0";
        }
        else {
            $this->query.=" AND (t.creatore='".$arr['logged']."' OR ges>0)";
        }

        if ($arr['tipo']!='tutti') {
            if ($arr['tipo']=='aperto') $this->query.=" AND isnull(t.d_chiusura,'')=''";
            elseif ($arr['tipo']=='chiuso') $this->query.=" AND isnull(t.d_chiusura,'')!=''";
        }

        if(isset($arr['testo']) && $arr['testo']!='') $this->query.=" AND t.mittente LIKE '%".$arr['testo']."%'";

        if(isset($arr['monoReparto']) && $arr['monoReparto']!='') $this->query.=" AND t.reparto='".$arr['monoReparto']."'";

        if(isset($arr['monoCategoria']) && $arr['monoCategoria']!='') $this->query.=" AND t.categoria='".$arr['monoCategoria']."'";

        $this->query.=" ORDER BY t.d_creazione";

        return true;
    }

    function newBubble($arr) {

        //TRANSACTION ATTIVO

        /*
        $arr=array(
            "ID"=>$this->id,
            "tipo"=>$tipo,
            "utente"=>$logged,
            "comp"=>$comp,
            "msg"=>""
        );
        */


        //Evento cambio gestione se necessario
        if ($arr['tipo']=='A' && $arr['utente']!=$arr['comp']) {

            $bubble=array(
                "ID"=>$arr['ID'],
                "riga"=>"###(SELECT isnull(max(riga),0)+1 FROM ".$this->tabelle['ERMES_chat']->getTabName()." WHERE ID='".$arr['ID']."')",
                "tipo"=>"E",
                "utente"=>"",
                "dataora"=>date('Ymd:H:i'),
                "testo"=>"Passaggio di gestione a: ".$arr['utente'].' - '.date('d/m/Y').' '.date('H:i'),
                "stato"=>1
            );

            $this->doInsert('ERMES_chat',$bubble,'','query');
        }

        $bubble=array(
            "ID"=>$arr['ID'],
            "riga"=>"###(SELECT isnull(max(riga),0)+1 FROM ".$this->tabelle['ERMES_chat']->getTabName()." WHERE ID='".$arr['ID']."')",
            "tipo"=>$arr['tipo'],
            "utente"=>$arr['utente'],
            "dataora"=>date('Ymd:H:i'),
            "testo"=>$arr['msg'],
            "stato"=>1
        );

        $this->doInsert('ERMES_chat',$bubble,'','query');

        $ticket=array(
            "stato"=>$arr['stato'],
            "scadenza"=>""
        );

        if ($arr['tipo']=='A') $ticket["gestore"]=$arr['utente'];

        if (isset($arr['react'])) $ticket['react']=$arr['react'];

        if (isset($arr['scadenza'])) $ticket['scadenza']=$arr['scadenza'];

        $this->doUpdate('ERMES_ticket',$ticket,"ID='".$arr['ID']."'",'query');

        $this->arrQuery[]=$this->query;

        return true;
    }

    function concludi($arr) {

        $bubble=array(
            "ID"=>$arr['ID'],
            "riga"=>"###(SELECT isnull(max(riga),0)+1 FROM ".$this->tabelle['ERMES_chat']->getTabName()." WHERE ID='".$arr['ID']."')",
            "tipo"=>"E",
            "utente"=>$arr['utente'],
            "dataora"=>date('Ymd:H:i'),
            "testo"=>"Chiuso Ticket da: ".$arr['utente'].' - '.date('d/m/Y').' '.date('H:i'),
            "stato"=>1
        );

        $this->doInsert('ERMES_chat',$bubble,'','query');

        $ticket=array(
            "stato"=>"chiuso",
            "scadenza"=>"",
            "d_chiusura"=>date('Ymd:H:i')
        );

        $this->doUpdate('ERMES_ticket',$ticket,"ID='".$arr['ID']."'",'query');

        $this->arrQuery[]=$this->query;

        return true;
    }

    function concludiInoltra($arr) {

        $bubble=array(
            "ID"=>$arr['ID'],
            "riga"=>"###(SELECT isnull(max(riga),0)+1 FROM ".$this->tabelle['ERMES_chat']->getTabName()." WHERE ID='".$arr['ID']."')",
            "tipo"=>"E",
            "utente"=>$arr['utente'],
            "dataora"=>date('Ymd:H:i'),
            "testo"=>"Ticket inotrato da: ".$arr['utente'].' a: '.$arr['reparto'].' - '.date('d/m/Y').' '.date('H:i'),
            "stato"=>1
        );

        $this->doInsert('ERMES_chat',$bubble,'','query');

        $ticket=array(
            "reparto"=>$arr['reparto'],
            "categoria"=>$arr['categoria'],
            "gestore"=>$arr['gestore'],
            "des_reparto"=>"###(SELECT descrizione FROM ".$this->tabelle['MAESTRO_reparti']->getTabName()." WHERE tag='".$arr['reparto']."')"
        );

        $this->doUpdate('ERMES_ticket',$ticket,"ID='".$arr['ID']."'",'query');

        $this->arrQuery[]=$this->query;

        return true;
    }

    function concludiForzaGestione($arr) {

        $bubble=array(
            "ID"=>$arr['ID'],
            "riga"=>"###(SELECT isnull(max(riga),0)+1 FROM ".$this->tabelle['ERMES_chat']->getTabName()." WHERE ID='".$arr['ID']."')",
            "tipo"=>"E",
            "utente"=>$arr['utente'],
            "dataora"=>date('Ymd:H:i'),
            "testo"=>"Ticket assegnato da: ".$arr['utente'].' a: '.$arr['gestore'].' - '.date('d/m/Y').' '.date('H:i'),
            "stato"=>1
        );

        $this->doInsert('ERMES_chat',$bubble,'','query');

        $ticket=array(
            "gestore"=>$arr['gestore']
        );

        $this->doUpdate('ERMES_ticket',$ticket,"ID='".$arr['ID']."'",'query');

        $this->arrQuery[]=$this->query;

        return true;
    }

    function getTodoByGestore($arr) {

        $this->query="SELECT
            lin.ID AS todo_ID,
            lin.riga AS todo_riga,
            lin.testo AS todo_testo,
            isnull(lin.d_creazione,'') AS todo_d_creazione,
            isnull(lin.d_scadenza,'') AS todo_d_scadenza,
            isnull(lin.d_chiusura,'') AS todo_d_chiusura,
            t.*
        ";
        
        $this->query.=" FROM ".$this->tabelle['DUDU_link']->getTabName()." as l";
        $this->query.=" INNER JOIN ".$this->tabelle['DUDU_lines']->getTabName()." as lin ON l.dudu=lin.ID";
        $this->query.=" INNER JOIN ".$this->tabelle['ERMES_ticket']->getTabName()." as t ON l.rif=t.ID AND t.stato!='chiuso' AND t.gestore='".$arr['gestore']."'";

        $this->query.=" WHERE l.app='ermes'";
        $this->query.=" ORDER BY lin.ID,lin.riga";

        return true;
    }

} 


?>