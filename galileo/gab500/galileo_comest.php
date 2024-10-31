<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/COMEST_commesse.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/COMEST_revisioni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/COMEST_lavorazioni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/COMEST_versioni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/COMEST_fornitori.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/COMEST_logmail.php');

class galileoComest extends galileoOps {

    function __construct() {

        $this->tabelle['COMEST_commesse']=new comest_commesse();
        $this->tabelle['COMEST_revisioni']=new comest_revisioni();
        $this->tabelle['COMEST_lavorazioni']=new comest_lavorazioni();
        $this->tabelle['COMEST_versioni']=new comest_versioni();
        $this->tabelle['COMEST_fornitori']=new comest_fornitori();
        $this->tabelle['COMEST_logmail']=new comest_logmail();
    }

    function getVersione($arr) {

        $this->query="SELECT top 1 * FROM ".$this->tabelle['COMEST_versioni']->getTabName()." ORDER BY versione DESC";

        return true;
    }

    function insertCommessa($arr) {
        //è già stata impostata TRANSACTION TRUE

        $this->doTransactionHead('COMEST_commesse','#tind');

        $res=$this->doInsert('COMEST_commesse',$arr,'indice','');

        $this->arrQuery[]='SELECT * from #tind';

        return $res;
    }

    function getTT($arr) {
        //trova le commesse già aperte per una vettura quando se ne vuole creare un'altra

        $this->query="SELECT * FROM ".$this->tabelle['COMEST_commesse']->getTabName();

        $this->query.=" WHERE ISNULL(d_controllo,'')=''";

        if (isset($arr['txt']) && $arr['txt']!="") $this->query.=" AND ( targa LIKE '%".$arr['txt']."%' OR telaio LIKE '%".$arr['txt']."%' )";

        if (isset($arr['odl']) && isset($arr['dms']) && $arr['odl']!="") $this->query.=" AND dms='".$arr['dms']."' && odl='".$arr['odl']."'";

        return true;
    }

    function baseCommesse() {

        $this->query="SELECT
            com.*,
            isnull(rev.nota,'') AS nota,
            isnull(rev.riconsegna,'') AS riconsegna,
            rev.preventivo
        ";

        $this->query.=" FROM ".$this->tabelle['COMEST_revisioni']->getTabName()." AS rev";

        $this->query.=" INNER JOIN ".$this->tabelle['COMEST_commesse']->getTabName()." AS com ON rev.commessa=com.rif";

        $this->query.=" INNER JOIN (
            SELECT
            commessa,
            max(revisione) AS revisione
            FROM ".$this->tabelle['COMEST_revisioni']->getTabName()."
            GROUP BY commessa
        ) AS mr ON mr.commessa=com.rif AND mr.revisione=rev.revisione";
    }

    function getCommesse($arr) {

        $this->baseCommesse();

        if (isset($arr['tipo']) && $arr['tipo']!="") {

            if ($arr['tipo']=='salvate') {
                $this->query.=" WHERE isnull(rev.d_chiusura,'')='' AND isnull(com.d_annullo,'')=''";
            }
            elseif ($arr['tipo']=='aperte') {
                $this->query.=" WHERE isnull(com.d_apertura,'')!='' AND isnull(com.d_controllo,'')='' AND isnull(rev.d_chiusura,'')!='' AND isnull(com.d_annullo,'')=''";
            }
            elseif ($arr['tipo']=='archivio') {
                $this->query.=" WHERE com.rif>0";

                if ( (isset($arr['targa']) && $arr['targa']!="") && (isset($arr['telaio']) && $arr['telaio']!="") ) {
                    $this->query.=" AND (com.targa LIKE '%".$arr['targa']."%' OR com.telaio LIKE '%".$arr['telaio']."%')";
                }
                elseif ( (isset($arr['targa']) && $arr['targa']!="") && (!isset($arr['telaio']) || $arr['telaio']=="") ) {
                    $this->query.=" AND (com.targa LIKE '%".$arr['targa']."%')";
                }
                elseif ( (!isset($arr['targa']) || $arr['targa']=="") && (isset($arr['telaio']) && $arr['telaio']!="") ) {
                    $this->query.=" AND (com.telaio LIKE '%".$arr['telaio']."%')";
                }

                //if (isset($arr['telaio']) && $arr['telaio']!="") $this->query.=" AND com.telaio LIKE '%".$arr['telaio']."%'";

                /*if (isset($arr['odl']) && $arr['odl']!="" && isset($arr['dms']) && $arr['dms']!="") {
                    $this->query.=" AND (com.dms ='".$arr['dms']."' AND com.odl='".$arr['odl']."')";
                }*/

                if (isset($arr['fornitore']) && $arr['fornitore']!="") $this->query.=" AND com.fornitore LIKE '%".$arr['fornitore']."%'";

                if (isset($arr['da']) && $arr['da']!="" && isset($arr['a']) && $arr['a']!="") {
                    $this->query.=" AND (com.d_apertura <='".$arr['a']."' AND CASE WHEN isnull(com.d_controllo,'')!='' THEN com.d_controllo ELSE rev.riconsegna END>='".$arr['da']."')";
                }
            }
        }

        $this->query.=" ORDER BY com.rif";

        return true;
    }

    function getBudget($arr) {

        $this->baseCommesse();

        $this->query.=" WHERE convert(varchar(8),rev.riconsegna,112)>='".$arr['da']."' AND convert(varchar(8),rev.riconsegna,112)<='".$arr['a']."'";

        return true;

    }

    function getAllinea($arr) {

        $this->baseCommesse();

        $this->query.=" WHERE isnull(com.d_apertura,'')!='' AND isnull(com.d_controllo,'')!=''";

        return true;
    }

    function newRevisione($arr) {
        //GALILEO è settato in transaction

        $query="DECLARE @idx int; SET @idx=(SELECT max(revisione)+1 FROM ".$this->tabelle['COMEST_revisioni']->getTabName()." WHERE commessa='".$arr['commessa']."');";

        $query.="INSERT INTO ".$this->tabelle['COMEST_revisioni']->getTabName()." (commessa,revisione,d_creazione,utente_creazione,d_chiusura,utente_chiusura,preventivo,riconsegna,nota)";
        $query.=" SELECT a.commessa,@idx,'".date('Ymd')."','".$arr['utente']."','','',a.preventivo,a.riconsegna,a.nota FROM ".$this->tabelle['COMEST_revisioni']->getTabName()." AS a WHERE a.commessa='".$arr['commessa']."' AND a.revisione='".$arr['revisione']."';";

        $query.="INSERT INTO ".$this->tabelle['COMEST_lavorazioni']->getTabName()." (commessa,revisione,ID,zona,titolo,descrizione)";
        $query.=" SELECT b.commessa,@idx,b.ID,b.zona,b.titolo,b.descrizione FROM  ".$this->tabelle['COMEST_lavorazioni']->getTabName()." AS b WHERE b.commessa='".$arr['commessa']."' AND b.revisione='".$arr['revisione']."';";

        $this->arrQuery[]=$query;

        return true;
    }

    function cancellaCommessa($arg) {
        //GALILEO è settato in transaction
        $this->doDelete('COMEST_commesse',"rif='".$arg['rif']."'",'');
        $this->doDelete('COMEST_revisioni',"commessa='".$arg['rif']."'",'');
        $this->doDelete('COMEST_lavorazioni',"commessa='".$arg['rif']."'",'');

        return true;
    }

    function getStorico($arr) {

        $this->query="SELECT
            a.*,
            isnull(a.d_controllo,'') AS chiusura,
            b.revisione,
            b.titolo,
            b.descrizione
        ";

        $this->query.=" FROM ".$this->tabelle['COMEST_commesse']->getTabName()." AS a";
        $this->query.=" INNER JOIN ".$this->tabelle['COMEST_lavorazioni']->getTabName()." AS b on a.rif=b.commessa";
        
        $this->query.=" WHERE isnull(a.d_apertura,'')!='' AND b.revisione=(SELECT max(c.revisione) FROM ".$this->tabelle['COMEST_revisioni']->getTabName()." AS c WHERE c.commessa=a.rif)";

        if ( (isset($arr['targa']) && $arr['targa']!="") && (isset($arr['telaio']) && $arr['telaio']!="") ) {
            $this->query.=" AND (a.targa LIKE '%".$arr['targa']."%' OR a.telaio LIKE '%".$arr['telaio']."%')";
        }
        elseif ( (isset($arr['targa']) && $arr['targa']!="") && (!isset($arr['telaio']) || $arr['telaio']=="") ) {
            $this->query.=" AND (a.targa LIKE '%".$arr['targa']."%')";
        }
        elseif ( (!isset($arr['targa']) || $arr['targa']=="") && (isset($arr['telaio']) && $arr['telaio']!="") ) {
            $this->query.=" AND (a.telaio LIKE '%".$arr['telaio']."%')";
        }

        if (isset($arr['annullate']) && $arr['annullate']==0) {
            $this->query.=" AND isnull(a.d_annullo,'')=''";
        }

        return true;
    }

} 


?>