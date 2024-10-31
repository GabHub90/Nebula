<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_collaboratori.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_gruppi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_macrogruppi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_collgru.php');

class galileoMaestroUtenti extends galileoOps {

    function __construct($reparti) {

        $this->tabelle['MAESTRO_collaboratori']=new maestro_collaboratori();
        $this->tabelle['MAESTRO_gruppi']=new maestro_gruppi();
        $this->tabelle['MAESTRO_macrogruppi']=new maestro_macrogruppi();
        $this->tabelle['MAESTRO_collgru']=new maestro_collgru();

        $this->link['reparti']=$reparti;
        
    }

    function getLogin() {

        return "SELECT
            concerto AS cod_utente,
            CASE 
                WHEN stato=1 THEN 'N'
                ELSE 'S'
            END AS ind_annullo,
            cognome+' '+nome AS des_utente,
            pw AS des_pwd,
            isnull(mail,'') AS mail,
            '' AS cod_gruppo,
            '' AS cod_ditta

            FROM ".$this->tabelle['MAESTRO_collaboratori']->getTabName();
    }

    function getConfigUtente($id,$d) {

        //viene passato l'id DMS dell'utente e la data di rilevazione
        //ritorna i gruppi di appartenenza dell'utente jointati con le informazioni dei reparti dei gruppi

        //inizializza la query delle operazioni sui reparti
        $this->link['reparti']->getReparti("","");

        $this->query="SELECT ";

        $this->query.="tcoll.ID AS ID_coll,
                    tcoll.nome,
                    tcoll.cognome,
                    tcoll.concerto,
                    tgru.reparto,
                    tgru.des_reparto,
                    tgru.macroreparto,
                    tgru.des_macroreparto,
                    tgru.officina,
                    tgru.ID as ID_gruppo,
                    tgru.tag AS gruppo,
                    tgru.descrizione AS des_gruppo,
                    tgru.posizione AS pos_gruppo,
                    isnull(tgru.macrogruppo,'') AS macrogruppo,
                    isnull(tgru.des_macrogruppo,'') AS des_macrogruppo,
                    isnull(tgru.pos_macrogruppo,'') AS pos_macrogruppo,
                    tgru.sede
        ";

        $this->query.="FROM ".$this->tabelle['MAESTRO_collgru']->getTabName()." AS tcg ";

        $this->query.="INNER JOIN ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS tcoll ON tcg.collaboratore=tcoll.ID ";
        $this->query.="INNER JOIN (
            SELECT
            a.ID,
            a.tag,
            a.descrizione,
            a.reparto,
            c.descrizione AS des_reparto,
            c.macroreparto,
            c.des_macroreparto,
            c.concerto AS officina,
            c.sede,
            a.posizione,
            b.tag AS macrogruppo,
            b.descrizione AS des_macrogruppo,
            b.pos AS pos_macrogruppo
            FROM ".$this->tabelle['MAESTRO_gruppi']->getTabName()." AS a
            LEFT JOIN ".$this->tabelle['MAESTRO_macrogruppi']->getTabName()." AS b ON a.macrogruppo=b.tag
            INNER JOIN (".$this->link['reparti']->getQuery().") AS c ON a.reparto=c.reparto
        ) AS tgru ON tcg.gruppo=tgru.ID ";

        $this->query.="WHERE tcoll.concerto='".$id."' AND tcg.data_i<='".$d."' AND tcg.data_f>='".$d."' ";

    }

    function getCollaboratori($wclause,$i,$f) {
        
        $this->link['reparti']->setOrderBy("");
        $this->link['reparti']->getReparti("","");

        $this->query="SELECT
                    cog.collaboratore AS ID_coll,
                    cog.data_i,
                    cog.data_f,
                    gru.ID AS ID_gruppo,
                    gru.tag AS gruppo,
                    gru.descrizione As des_gruppo,
                    gru.posizione,
                    gru.macrogruppo,
                    mgru.descrizione AS des_macrogruppo,
                    mgru.pos AS posizione_macrogruppo,
                    rep.sede,
                    rep.reparto,
                    rep.macroreparto,
                    rep.descrizione AS des_reparto,
                    rep.concerto AS rep_concerto,
                    rep.des_macroreparto,
                    coll.nome,
                    coll.cognome,
                    isnull(coll.concerto,'') AS concerto,
                    isnull(coll.cod_operaio,'') AS cod_operaio,
                    isnull(coll.tel_interno,'') AS tel_interno,
                    isnull(coll.cellulare,'') AS cellulare,
                    isnull(coll.mail,'') AS mail,
                    isnull(coll.IDDIP,'') AS IDDIP,
                    isnull(coll.IDMAT,'') AS IDMAT
        ";

        $this->query.=" FROM ".$this->tabelle['MAESTRO_collgru']->getTabName()." AS cog";

        $this->query.=" INNER JOIN ".$this->tabelle['MAESTRO_gruppi']->getTabName()." AS gru ON cog.gruppo=gru.ID";
        $this->query.=" LEFT JOIN ".$this->tabelle['MAESTRO_macrogruppi']->getTabName()." AS mgru ON gru.macrogruppo=mgru.tag";
        $this->query.=" INNER JOIN (".$this->link['reparti']->getQuery().") AS rep ON gru.reparto=rep.reparto";
        $this->query.=" INNER JOIN ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS coll ON cog.collaboratore=coll.ID";

        $this->query.=" WHERE cog.data_i<='".$f."' AND cog.data_f>='".$i."'";

        if ($wclause!="") $this->query.=" AND ".$wclause;

        if ($this->orderBy!="") $this->query.=' ORDER BY '.$this->orderBy;

    }

    function getGruppi($wclause) {

        $this->query="SELECT
            gru.ID AS ID_gruppo,
            gru.tag AS gruppo,
            gru.descrizione As des_gruppo,
            gru.posizione,
            isnull(gru.macrogruppo,'') AS macrogruppo,
            mgru.descrizione AS des_macrogruppo,
            mgru.pos AS posizione_macrogruppo
        ";

        $this->query.=" FROM ".$this->tabelle['MAESTRO_gruppi']->getTabName()." AS gru";

        $this->query.=" LEFT JOIN ".$this->tabelle['MAESTRO_macrogruppi']->getTabName()." AS mgru ON gru.macrogruppo=mgru.tag";

        if ($wclause!="") {
            $this->query.=" WHERE ".$wclause;
        }

        $this->query.=' ORDER BY posizione';

    }

    function getAvalaibleColl($reparto,$today) {

        $this->query="SELECT
            coll.ID,
            coll.cognome,
            coll.nome,
            coll.concerto
        ";

        $this->query.=" FROM (
            SELECT
            coll.ID
            FROM ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." as coll
            
            LEFT JOIN ".$this->tabelle['MAESTRO_collgru']->getTabName()." as cg ON cg.collaboratore=coll.ID

            LEFT JOIN (
                SELECT
                collaboratore
                FROM ".$this->tabelle['MAESTRO_collgru']->getTabName()." AS a
                INNER JOIN ".$this->tabelle['MAESTRO_gruppi']->getTabName()." AS b ON a.gruppo=b.ID
                WHERE b.reparto='".$reparto."' AND (a.data_i>'".$today."' OR a.data_f>'".$today."')
                GROUP BY a.collaboratore
            ) AS blacklist ON blacklist.collaboratore=cg.collaboratore

            WHERE isnull(coll.cognome,'')!='' AND isnull(blacklist.collaboratore,0)=0 AND coll.stato='1'
            GROUP BY coll.ID
        ) AS a

        INNER JOIN ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." as coll ON coll.ID=a.ID

        ORDER BY coll.cognome,coll.nome,coll.ID
        ";

        return true;
    }

} 


?>