<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/GDM_materiali.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/GDM_operazioni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/GDM_richieste.php');

class galileoGDM extends galileoOps {

    function __construct() {

        $this->tabelle['GDM_materiali']=new gdm_materiali();
        $this->tabelle['GDM_operazioni']=new gdm_operazioni();
        $this->tabelle['GDM_richieste']=new gdm_richieste();
    }

    function getRichiesteAperte($arg) {
        //prende richieste aperte dove NON ci sono operazioni "Pronto per Stoccaggio"
    }

    function getOperazioni($arr) {

        $this->query="select
            op.*,
            ri.statoRi,
            ri.dataRi,
            ma.tipologia,
            ma.nome,
            ma.descrizione,
            ma.compoGomme,
            ma.tipoGomme,
            CASE
                when op.dataOperazione IS NULL OR op.dataOperazione='' then isnull(CAST(ri.dataRi AS varchar),'')
	            else op.dataOperazione
            END data_rif
        ";

        $this->query.=" FROM ".$this->tabelle['GDM_operazioni']->getTabName()." as op";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_richieste']->getTabName()." as ri ON ri.id=op.idRi";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_materiali']->getTabName()." as ma on ma.id=op.idMat";

        $this->query.=" WHERE ri.idTelaio='".$arr['telaio']."'";

        $this->query.=" ORDER BY data_rif DESC,ri.id DESC";

        return true;
    }

    function getOperazioniRichiesta($arr) {

        $this->query="SELECT
            op.*,
            ma.tipologia,
            ma.nome,
            ma.descrizione,
            ma.compoGomme,
            ma.tipoGomme,
            ma.isAnnullato,
            ma.locazione
        ";

        $this->query.=" FROM ".$this->tabelle['GDM_operazioni']->getTabName()." as op";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_materiali']->getTabName()." as ma on ma.id=op.idMat";

        $this->query.=" WHERE op.idRi='".$arr['idRi']."'";

        //echo $this->query;
        //return false;

        return true;
    }

    function getOperazioniPreSto($arr) {

        $this->query="SELECT
            op.*,
            ri.statoRi,
            ri.dataRi,
            ri.idTelaio,
            ri.nomeCliente,
            ri.targa,
            ri.tipoVeicolo,
            ri.numPratica,
            ri.dms,
            ma.tipologia,
            ma.nome,
            ma.descrizione,
            ma.compoGomme,
            ma.tipoGomme,
            CASE
                when op.dataOperazione IS NULL OR op.dataOperazione='' then isnull(CAST(ri.dataRi AS varchar),'')
	            else op.dataOperazione
            END data_rif
        ";

        $this->query.=" FROM ".$this->tabelle['GDM_operazioni']->getTabName()." as op";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_richieste']->getTabName()." as ri ON ri.id=op.idRi";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_materiali']->getTabName()." as ma on ma.id=op.idMat";

        $this->query.=" WHERE op.statoOp='".$arr['statoOp']."'";

        $this->query.=" ORDER BY data_rif ASC";

        return true;
    }

    function creaRichiesta($arr) {

        //TRANSACTION ON
        /*{ "richiesta" , "operazioni" }*/

        $arr['richiesta']['id']='###@idRi';

        $this->arrQuery[]="DECLARE @idRi int;";

        $this->arrQuery[]="SELECT @idRi=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_richieste']->getTabName().");";

        if (!$this->doInsert('GDM_richieste',$arr['richiesta'],'','')) {
            echo 'Errore creazione Richiesta';
            return false;
        }

        foreach ($arr['operazioni'] as $k=>$o) {

            $o['idRi']='###@idRi';

            if (!$this->doInsert('GDM_operazioni',$o,'','')) {
                echo 'Errore creazione Operazione '.$k;
                return false;
            }

            $a=array(
                'isBusy'=>"True"
            );

            if (!$this->doUpdate('GDM_materiali',$a,"id='".$o['idMat']."'",'')) {
                echo 'Errore aggiornamento Materiale '.$k;
                return false;
            }
        }

        //racchiudo tutto in un'unica query altrimenti non funziona il settaggio della variabile @idRi
        //mantenendo l'impostazione della transaction

        $q="";

        foreach ($this->arrQuery as $k=>$a) {
            $q.=$a;
        } 

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function delRichiesta($arr) {

        //TRANSACTION ON
        $idRi="";

        foreach ($arr as $k=>$o) {

            $idRi=$o['idRi'];

            if ($idRi=='') return false;

            $this->doDelete('GDM_operazioni',"id='".$o['id']."'",'');

            $a=array(
                'isBusy'=>"False"
            );

            $this->doUpdate('GDM_materiali',$a,"id='".$o['idMat']."'",'');
        }

        $this->doDelete('GDM_richieste',"id='".$idRi."'",'');

        return true;
    }

    function getLizard($arr) {

        $this->query="SELECT
            mat.*,
            isnull(dbo.fnLAST_GDM(mat.id,mat.proprietario),'') as ultimo,
            r.dataRi as aperta
        ";

        $this->query.=" FROM ".$this->tabelle['GDM_materiali']->getTabName()." as mat";

        $this->query.=" LEFT JOIN (
            SELECT
            op.idMat,
            ric.dataRi
            FROM ".$this->tabelle['GDM_operazioni']->getTabName()." as op
            INNER JOIN ".$this->tabelle['GDM_richieste']->getTabName()." as ric ON op.idRi=ric.id AND ric.statoRi='APERTA'
        ) as r ON mat.id=r.idMat";


        $this->query.=" WHERE isnull(mat.isAnnullato,'False')!='True'";
        
        if (isset($arr['liz_tipologia'])) {
            if ($arr['liz_tipologia']!="") $this->query.=" AND mat.tipologia='".$arr['liz_tipologia']."'";
        }

        if (isset($arr['liz_locazione'])) {
            if ($arr['liz_locazione']!="") $this->query.=" AND mat.locazione LIKE '%".$arr['liz_locazione']."%'";
        }

        if (isset($arr['liz_descrizione'])) {
            if ($arr['liz_descrizione']!="") $this->query.=" AND ( mat.descrizione LIKE '%".$arr['liz_descrizione']."%' OR mat.dimeASx LIKE '%".$arr['liz_descrizione']."%' OR mat.dimeADx LIKE '%".$arr['liz_descrizione']."%' OR mat.dimePSx LIKE '%".$arr['liz_descrizione']."%' OR mat.dimePDx LIKE '%".$arr['liz_descrizione']."%')";
        }

        if (isset($arr['liz_compogomme'])) {
            if ($arr['liz_compogomme']!="") $this->query.=" AND mat.compoGomme='".$arr['liz_compogomme']."'";
        }

        if (isset($arr['liz_tipogomme'])) {
            if ($arr['liz_tipogomme']!="") $this->query.=" AND mat.tipoGomme='".$arr['liz_tipogomme']."'";
        }

        if (isset($arr['liz_proprietario'])) {
            if ($arr['liz_proprietario']!="") $this->query.=" AND mat.proprietario='".$arr['liz_proprietario']."'";
        }

        if (isset($arr['liz_telaio'])) {
            if ($arr['liz_telaio']!="") $this->query.=" AND mat.idTelaio LIKE '%".$arr['liz_telaio']."%'";
        }

        if (isset($arr['liz_busy'])) {
            if ($arr['liz_busy']!="") $this->query.=" AND isnull(mat.isBusy,'False')='".$arr['liz_busy']."'";
        }

        if (isset($arr['liz_full'])) {
            if ($arr['liz_full']!="") $this->query.=" AND isnull(mat.isFull,'True')='".$arr['liz_full']."'";
        }

        if (isset($arr['liz_usura']) && isset($arr['liz_segno'])) {
            if ($arr['liz_usura']!="") $this->query.=" AND ( (isnull(mat.usuraASx,'-1.0')<'0.0' OR isnull(mat.usuraASx,'0.0')".($arr['liz_segno']=='min'?'<=':'>=')."'".$arr['liz_usura']."') OR (isnull(mat.usuraADx,'-1.0')<'0.0' OR isnull(mat.usuraADx,'0.0')".($arr['liz_segno']=='min'?'<=':'>=')."'".$arr['liz_usura']."') OR (isnull(mat.usuraPSx,'-1.0')<'0.0' OR isnull(mat.usuraPSx,'0.0')".($arr['liz_segno']=='min'?'<=':'>=')."'".$arr['liz_usura']."') OR (isnull(mat.usuraPDx,'-1.0')<'0.0' OR isnull(mat.usuraPDx,'0.0')".($arr['liz_segno']=='min'?'<=':'>=')."'".$arr['liz_usura']."') )";
        }

        $this->query.=" ORDER BY mat.idTelaio,mat.id";

        //echo $this->query;

        return true;
    }

    function getPrelievoLizard($arg) {

        $this->query="SELECT 
            o.origine,
            o.destinazione,
            r.dataRi,
            r.targa,
            r.idTelaio,
            r.nomeCliente,
            m.dimeASx,
            m.dimeADx,
            m.dimePSx,
            m.dimePDx,
            m.compoGomme,
            m.tipoGomme,
            m.locazione
        ";

        $this->query.=" FROM ".$this->tabelle['GDM_operazioni']->getTabName()." as o";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_richieste']->getTabName()." as r ON o.idRi=r.id";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_materiali']->getTabName()." as m ON o.idMat=m.id";

        $this->query.=" WHERE r.dataRi>='".$arg['liz_da']."' AND r.dataRi<='".$arg['liz_a']."' AND o.statoOp='Richiesto'";

        $this->query.=" ORDER BY r.dataRi";

        return true;
    }

    function getStoccaggioLizard($arg) {

        $this->query="SELECT 
            o.origine,
            o.destinazione,
            r.dataRi,
            r.targa,
            r.idTelaio,
            r.nomeCliente,
            m.dimeASx,
            m.dimeADx,
            m.dimePSx,
            m.dimePDx,
            m.compoGomme,
            m.tipoGomme
        ";

        $this->query.=" FROM ".$this->tabelle['GDM_operazioni']->getTabName()." as o";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_richieste']->getTabName()." as r ON o.idRi=r.id";
        $this->query.=" INNER JOIN ".$this->tabelle['GDM_materiali']->getTabName()." as m ON o.idMat=m.id";

        $this->query.=" WHERE o.statoOp='Pronto per Stoccaggio'";

        $this->query.=" ORDER BY r.dataRi";

        return true;
    }

    function nuovoMateriale($arg) {

        //TRANSACTION TRUE

        $arg['materiale']['id']='###@idMat';

        $this->arrQuery[]="DECLARE @idMat int,@idRic int,@idOp int;";

        $this->arrQuery[]="SELECT @idMat=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_materiali']->getTabName().");";     

        //creazione materiale
        $this->doInsert('GDM_materiali',$arg['materiale'],'','');

        //creazione richiesta
        $r=array(
            "id"=>"###@idRic",
            "statoRi"=>"CHIUSA",
            "dataRi"=>date('Ymd'),
            "idTelaio"=>$arg['veicolo']['telaio'],
            "nomeCliente"=>$arg['veicolo']['nomeCliente'],
            "targa"=>$arg['veicolo']['targa'],
            "tipoVeicolo"=>$arg['veicolo']['des_veicolo'],
            "dms"=>$arg['veicolo']['dms']
        );

        $this->arrQuery[]="SELECT @idRic=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_richieste']->getTabName().");";

        $this->doInsert('GDM_richieste',$r,'','');

        //creazione operazione
        $o=array(
            "id"=>"###@idOp",
            "idRi"=>"###@idRic",
            "idMat"=>"###@idMat",
            "destinazione"=>$arg['materiale']['proprietario'],
            "statoOp"=>'Completa',
            "origine"=>'NUOVA',
            "storico"=>json_encode($arg['materiale']),
            "dataOperazione"=>date('Ymd')
        );

        $this->arrQuery[]="SELECT @idOp=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_operazioni']->getTabName().");";

        $this->doInsert('GDM_operazioni',$o,'','');

        ///////////////////////////////////////////////////////////////////////////////

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;

    }

    function editMateriale($arg) {

        //TRANSACTION TRUE 

        $this->arrQuery[]="DECLARE @idRic int,@idOp int;";

        //edit materiale
        $this->doUpdate('GDM_materiali',$arg['form'],"id='".$arg['form']['id']."'",'');

        //creazione richiesta
        $r=array(
            "id"=>"###@idRic",
            "statoRi"=>"CHIUSA",
            "dataRi"=>date('Ymd'),
            "idTelaio"=>$arg['veicolo']['telaio'],
            "nomeCliente"=>$arg['veicolo']['nomeCliente'],
            "targa"=>$arg['veicolo']['targa'],
            "tipoVeicolo"=>$arg['veicolo']['des_veicolo'],
            "dms"=>$arg['veicolo']['dms']
        );

        $this->arrQuery[]="SELECT @idRic=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_richieste']->getTabName().");";

        $this->doInsert('GDM_richieste',$r,'','');

        //creazione operazione
        $o=array(
            "id"=>"###@idOp",
            "idRi"=>"###@idRic",
            "idMat"=>$arg['form']['id'],
            "destinazione"=>$arg['form']['destinazione'],
            "statoOp"=>'Completa',
            "origine"=>'EDIT',
            "storico"=>json_encode($arg['form']),
            "dataOperazione"=>date('Ymd')
        );

        $this->arrQuery[]="SELECT @idOp=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_operazioni']->getTabName().");";

        $this->doInsert('GDM_operazioni',$o,'','');

        ///////////////////////////////////////////////////////////////////////////////

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function cambiaLocazione($arg) {
        //TRANSACTION TRUE 

        $this->arrQuery[]="DECLARE @idRic int,@idOp int;";

        //edit materiale
        $a=array(
            "locazione"=>$arg['materiale']['locazione']
        );
        $this->doUpdate('GDM_materiali',$a,"id='".$arg['materiale']['id']."'",'');

        //creazione richiesta
        $r=array(
            "id"=>"###@idRic",
            "statoRi"=>"CHIUSA",
            "dataRi"=>date('Ymd'),
            "idTelaio"=>$arg['veicolo']['telaio'],
            "nomeCliente"=>$arg['veicolo']['nomeCliente'],
            "targa"=>$arg['veicolo']['targa'],
            "tipoVeicolo"=>$arg['veicolo']['des_veicolo'],
            "dms"=>$arg['veicolo']['dms']
        );

        $this->arrQuery[]="SELECT @idRic=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_richieste']->getTabName().");";

        $this->doInsert('GDM_richieste',$r,'','');

        //creazione operazione
        $o=array(
            "id"=>"###@idOp",
            "idRi"=>"###@idRic",
            "idMat"=>$arg['materiale']['id'],
            "destinazione"=>$arg['materiale']['proprietario'],
            "statoOp"=>'Completa',
            "origine"=>'LOCA',
            "storico"=>json_encode($arg['materiale']),
            "dataOperazione"=>date('Ymd')
        );

        $this->arrQuery[]="SELECT @idOp=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_operazioni']->getTabName().");";

        $this->doInsert('GDM_operazioni',$o,'','');

        ///////////////////////////////////////////////////////////////////////////////

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function prelievo($arg) {
        //TRANSACTION ON

        //Se è smaltimento occorre comunque passare per la chiusura della richiesta perché potrebbero esserci altre operazioni

        $a=array(
            "annotazioni"=>(isset($arg['annotazioni'])?$arg['annotazioni']:''),
            "locazione"=>""
        );

        //if ($arg['destinazione']=='Smaltimento') $a['proprietario']=$arg['destinazione'];

        $this->doUpdate('GDM_materiali',$a,"id='".$arg['id']."'",'');

        $a=array(
            "statoOp"=>"Pronto",
            "destinazione"=>$arg['destinazione']
        );

        /*if ($arg['destinazione']=='Smaltimento') {
            $a['dataOperazione']=date('Ymd');
        }*/

        $this->doUpdate('GDM_operazioni',$a,"id='".$arg['operazione']."'",'');

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function smaltimento($arg) {
        //smaltimento parziale di un materiale
        //TRANSACTION TRUE
        //{"id":"7040","dimeASx":"205/60R16 92V","dimeADx":"205/60R16 92V","dimePSx":"205/60R16 92V","dimePDx":"205/60R16 92V","marcaASx":"KUMHO","marcaADx":"KUMHO","marcaPSx":"KUMHO","marcaPDx":"KUMHO","dotASx":"2823","dotADx":"2823","dotPSx":"2823","dotPDx":"2823","usuraASx":"8.0","usuraADx":"8.0","usuraPSx":"8.0","usuraPDx":"8.0","annotazioni":"","operazione":"26784","destinazione":"Deposito","origine":"Vettura"}

        //SPOSTATO SELEZIONE A FUNZIONE CHIAMANTE
        //$arg['tipologia']="###(SELECT tipologia FROM ".$this->tabelle['GDM_materiali']->getTabName()." WHERE id='".$arg['id']."')";
        //$arg['compoGomme']="###(SELECT compoGomme FROM ".$this->tabelle['GDM_materiali']->getTabName()." WHERE id='".$arg['id']."')";
        //$arg['tipoGomme']="###(SELECT tipoGomme FROM ".$this->tabelle['GDM_materiali']->getTabName()." WHERE id='".$arg['id']."')";
        $arg['id']='###@idMat';
        $arg['proprietario']='Smaltimento';
        $arg['idTelaio']="###(SELECT idTelaio FROM ".$this->tabelle['GDM_richieste']->getTabName()." WHERE id='".$arg['richiesta']."')";
        $arg['annotazioni']="";
        $arg['dataCreazione']=date('Ymd');

        $this->arrQuery[]="DECLARE @idMat int,@idOp int;";

        $this->arrQuery[]="SELECT @idMat=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_materiali']->getTabName().");";     

        //creazione materiale
        $this->doInsert('GDM_materiali',$arg,'','');

        /*
        $qry='###'.<<<TXT
            (SELECT STUFF((
                SELECT
                    ',{'
                    + '"id":'+CAST(id AS varchar(10))+','
                    + '"annotazioni":"'+isnull(annotazioni,'')+'",'
                    + '"tipoGomme":"'+isnull(tipoGomme,'')+'",'
                    + '"compoGomme":"'+isnull(compoGomme,'')+'",'
                    + '"dotASx":"'+isnull(dotASx,'')+'",'
                    + '"dotADx":"'+isnull(dotADx,'')+'",'
                    + '"dotPSx":"'+isnull(dotPSx,'')+'",'
                    + '"dotPDx":"'+isnull(dotPDx,'')+'",'
                    + '"marcaASx":"'+isnull(marcaASx,'')+'",'
                    + '"marcaADx":"'+isnull(marcaADx,'')+'",'
                    + '"marcaPSx":"'+isnull(marcaPSx,'')+'",'
                    + '"marcaPDx":"'+isnull(marcaPDx,'')+'",'
                    + '"usuraASx":"'+isnull(dotASx,'')+'",'
                    + '"usuraADx":"'+isnull(dotADx,'')+'",'
                    + '"usuraPSx":"'+isnull(dotPSx,'')+'",'
                    + '"usuraPDx":"'+isnull(dotPDx,'')+'",'
                    + '"dimeASx":"'+isnull(dotASx,'')+'",'
                    + '"dimeADx":"'+isnull(dotADx,'')+'",'
                    + '"dimePSx":"'+isnull(dotPSx,'')+'",'
                    + '"dimePDx":"'+isnull(dotPDx,'')+'"'
                    + '}'
                from gdm_materiali where id='*iddi*' FOR XML PATH(''), TYPE).value('.', 'varchar(MAX)'),1,1,'') 
            )";
TXT;
        
        $qry=str_replace('*iddi*',$arg['id'],$qry); */

        //creazione operazione
        $o=array(
            "id"=>"###@idOp",
            "idRi"=>$arg['richiesta'],
            "idMat"=>"###@idMat",
            "destinazione"=>"Smaltimento",
            "statoOp"=>'Completa',
            "origine"=>$arg['origine'],
            "storico"=>json_encode($arg),
            "dataOperazione"=>date('Ymd')
        );

        $this->arrQuery[]="SELECT @idOp=(SELECT isnull(max(id),0)+1 FROM ".$this->tabelle['GDM_operazioni']->getTabName().");";

        $this->doInsert('GDM_operazioni',$o,'','');

        ///////////////////////////////////////////////////////////////////////////////

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function confermaDaDeposito($arg) {
        // TRANSACTION TRUE
        //se origine è deposito va alla destinazione così com'è

        //{"flag":1,"chg":0,"expo":{"id":"7048","operazione":"26785","destinazione":"Vettura","origine":"Deposito","annotazioni":""}

        $a=array(
            "annotazioni"=>(isset($arg['expo']['annotazioni'])?$arg['expo']['annotazioni']:''),
            "proprietario"=>$arg['expo']['destinazione'],
            "isBusy"=>"False"
        );

        $this->doUpdate('GDM_materiali',$a,"id='".$arg['expo']['id']."'",'');

        $a=array(
            "statoOp"=>"Completa",
            "dataOperazione"=>date('Ymd'),
            "destinazione"=>$arg['expo']['destinazione']
        );

        $this->doUpdate('GDM_operazioni',$a,"id='".$arg['expo']['operazione']."'",'');

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function confermaNoDeposito($arg) {
        // TRANSACTION TRUE
    
         //{"flag":1,"chg":0,"expo":{"id":"7040","dimeASx":"205/60R16 92V","dimeADx":"205/60R16 92V","dimePSx":"205/60R16 92V","dimePDx":"205/60R16 92V","marcaASx":"KUMHO","marcaADx":"KUMHO","marcaPSx":"KUMHO","marcaPDx":"KUMHO","dotASx":"2823","dotADx":"2823","dotPSx":"2823","dotPDx":"2823","usuraASx":"8.0","usuraADx":"8.0","usuraPSx":"8.0","usuraPDx":"8.0","annotazioni":"","operazione":"26784","destinazione":"Deposito","origine":"Vettura"}
        
        $a=$arg['expo'];
        unset($a['id']);
        if ($arg['expo']['destinazione']!='Deposito') {
            $a['proprietario']=$a['destinazione'];
        }
        $a['isBusy']=($a['destinazione']=='Deposito')?'True':'False';

        $this->doUpdate('GDM_materiali',$a,"id='".$arg['expo']['id']."'",'');

        $a=array(
            "statoOp"=>($arg['expo']['destinazione']=='Deposito')?"Pronto per Stoccaggio":"Completa",
            "dataOperazione"=>($arg['expo']['destinazione']!='Deposito')?date('Ymd'):'',
            "destinazione"=>$arg['expo']['destinazione']
        );

        $this->doUpdate('GDM_operazioni',$a,"id='".$arg['expo']['operazione']."'",'');

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function confermaStoccaggio($arg) {

        //{"id":"1031","operazione":"1088","destinazione":"Deposito","origine":"Vettura","annotazioni":"","locazione":"esff"}

        $a=array(
            "proprietario"=>$arg['destinazione'],
            "locazione"=>$arg['locazione'],
            "isBusy"=>"False"
        );

        $this->doUpdate('GDM_materiali',$a,"id='".$arg['id']."'",'');

        $a=array(
            "statoOp"=>"Completa",
            "destinazione"=>$arg['destinazione']
        );

        $this->doUpdate('GDM_operazioni',$a,"id='".$arg['operazione']."'",'');

        //per sicurezza chiudiamo anche la richiesta
        $a=array(
            "statoRi"=>"CHIUSA"
        );

        $this->doUpdate('GDM_richieste',$a,"id='".$arg['richiesta']."'",'');

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

    function annullaMateriale($arg) {
        //cancella il materiale con tutti i movimenti (richieste e operazioni).
        //è possibile se non ci sono stati MAI dei movimenti confermati in officina
        //quindi in sostanza ci sarà solo una richiesta di creazione o al massimo anche di modifica
        //TRANSACTION
        //viene fornito ID materiale

        $this->arrQuery[]="DELETE FROM GDM_richieste WHERE id IN (
            SELECT idRi FROM GDM_operazioni WHERE idMat='".$arg['id']."'
        );";

        $this->doDelete('GDM_operazioni',"idMat='".$arg['id']."'",'');

        $a=array(
            "isAnnullato"=>"True"
        );

        $this->doUpdate('GDM_materiali',$a,"id='".$arg['id']."'",'');

        $q="";

        foreach ($this->arrQuery as $k=>$v) {
            $q.=$v;
        }

        $this->arrQuery=array();
        $this->arrQuery[]=$q;

        return true;
    }

} 


?>