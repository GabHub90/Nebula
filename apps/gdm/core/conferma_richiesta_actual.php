<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

/*{
    "form":{"gdmForm_7048":{"flag":1,"chg":0,"expo":{"id":"7048","dimeASx":"205/60R26 96H","dimeADx":"205/60R26 96H","dimePSx":"205/60R26 96H","dimePDx":"205/60R26 96H","marcaASx":"Pirelli","marcaADx":"Pirelli","marcaPSx":"Pirelli","marcaPDx":"Pirelli","dotASx":"3523","dotADx":"3523","dotPSx":"3523","dotPDx":"3523","usuraASx":"8.0","usuraADx":"8.0","usuraPSx":"8.0","usuraPDx":"8.0","annotazioni":"","operazione":"26785","destinazione":"Vettura"}}},
    "richiesta":"14021",
    "ambito":"prelievo"
}*/

/*CASO PRELIEVO
{"form":{"gdmForm_7048":{"flag":1,"chg":0,"expo":{"id":"7048","operazione":"26785","destinazione":"Vettura","annotazioni":"wsrwasgfaegfegfa"}}},"richiesta":"14021","ambito":"prelievo"}
*/

if ($param['ambito']=='prelievo') {

    foreach ($param['form'] as $k=>$f) {

        $galileo->setTransaction('true');

        $a=$f['expo'];
        $a['richiesta']=$param['richiesta'];

        if (isset($f['expo']['isAnnullabile'])) unset($f['expo']['isAnnullabile']);

        $galileo->clearQuery();
        $galileo->clearQueryOggetto('default','gdm');

        $galileo->executeGeneric('gdm','prelievo',$a,'');

        $galileo->setTransaction('false');
        
    }
}

elseif ($param['ambito']=='gdmr') {

    /*{"form":{
        "gdmForm_7040":{"flag":1,"chg":0,"expo":{"id":"7040","dimeASx":"205/60R16 92V","dimeADx":"205/60R16 92V","dimePSx":"205/60R16 92V","dimePDx":"205/60R16 92V","marcaASx":"KUMHO","marcaADx":"KUMHO","marcaPSx":"KUMHO","marcaPDx":"KUMHO","dotASx":"2823","dotADx":"2823","dotPSx":"2823","dotPDx":"2823","usuraASx":"8.0","usuraADx":"8.0","usuraPSx":"8.0","usuraPDx":"8.0","annotazioni":"","operazione":"26784","destinazione":"Deposito","origine":"Vettura"}},
        "gdmForm_7048":{"flag":1,"chg":0,"expo":{"id":"7048","operazione":"26785","destinazione":"Vettura","origine":"Deposito","annotazioni":""}}
    },
    "richiesta":"14021","ambito":"gdmr","smaltite":0}*/

    //Se ci sono GOMME SMALTITE occorre elaborare le OPERAZIONI
    //JS ha già escluso che TUTTE le gomme siano state smaltite (vale solo per le gomme perché hanno il parametro USURA)
    //if ($param['smaltite']>0) {

        $arr=array('ASx','ADx','PSx','PDx');

        foreach ($param['form'] as $k=>$f) {

            //per ogni FORM
            if ($f['expo']['smaltite']==0) continue;

            $tmat=$f;

            foreach ($arr as $kp=>$p) {

                if ((int)$f['expo']['usura'.$p]<0) {

                    $param['form'][$k]['expo']['isFull']='False';

                    //azzera l'originale
                    //$param['form'][$k]['expo']['dime'.$p]=="";
                    //$param['form'][$k]['expo']['marca'.$p]=="";
                    //$param['form'][$k]['expo']['dot'.$p]=="";
                    //$param['form'][$k]['expo']['usura'.$p]=="";  
                }
                else {
                    //azzera il nuovo materiale da inserire nello smaltito
                    //$tmat['expo']['dime'.$p]=="";
                    //$tmat['expo']['marca'.$p]=="";
                    //$tmat['expo']['dot'.$p]=="";
                    $tmat['expo']['usura'.$p]==-1;
                }
            }

            $param['form']['nuovo_'.$k]=$tmat;
            //a questo punto si ha una nuova operazione che DEVE ESSERE CREATA ED AGGIUNTA ALLA RICHIESTA
        }
    //}

    $allDone=true;

    foreach ($param['form'] as $k=>$f) {

        $f['expo']['isAnnullabile']='False';

        //SE $K inizia con "nuovo_" creare il movimento di smaltimento parziale
        if (substr($k,0,6)=='nuovo_') {

            $a=$f['expo'];
            $a['richiesta']=$param['richiesta'];

            $galileo->executeSelect('gdm','GDM_materiali',"id='".$a['id']."'",'');

            $fid=$galileo->preFetch('gdm');

            while($row=$galileo->getFetch('gdm',$fid)) {
                $a['compoGomme']=$row['compoGomme'];
                $a['tipoGomme']=$row['tipoGomme'];
                $a['tipologia']=$row['tipologia'];
            }

            if ((int)$a['usuraASx']<0) $a['usuraASx']=1;
            else $a['usuraASx']=0;
            if ((int)$a['usuraADx']<0) $a['usuraADx']=1;
            else $a['usuraADx']=0;
            if ((int)$a['usuraPSx']<0) $a['usuraPSx']=1;
            else $a['usuraPSx']=0;
            if ((int)$a['usuraPDx']<0) $a['usuraPDx']=1;
            else $a['usuraPDx']=0;

            $galileo->setTransaction('true');

            $galileo->clearQuery();
            $galileo->clearQueryOggetto('default','gdm');

            $galileo->executeGeneric('gdm','smaltimento',$a,'');

            $galileo->setTransaction('false');
        }

        //altrimenti
        elseif ($f['expo']['origine']=="Deposito") {
            //se origine è deposito va alla destinazione così com'è
            //$f['richiesta']=$param['richiesta'];

            $galileo->setTransaction('true');

            $galileo->clearQuery();
            $galileo->clearQueryOggetto('default','gdm');

            $galileo->executeGeneric('gdm','confermaDaDeposito',$f,'');

            $galileo->setTransaction('false');

            $galileo->clearQuery();
            $galileo->clearQueryOggetto('default','gdm');

            $galileo->executeSelect('gdm','GDM_materiali',"id='".$f['expo']['id']."'",'');
            
            if ($galileo->getResult()) {
                $fid=$galileo->preFetch('gdm');
                while ($row=$galileo->getFetch('gdm',$fid)) {
                    //se NON è stato aggiornato il materiale => nemmeno l'operazione (transaction ON)
                    if ($row['isBusy']=='True') $allDone=false;
                    //altrimenti aggiorna lo storico dell'operazione
                    else {
                        $a=array(
                            'storico'=>json_encode($row)
                        );

                        $galileo->clearQuery();
                        $galileo->clearQueryOggetto('default','gdm');

                        $galileo->executeUpdate('gdm','GDM_operazioni',$a,"id='".$f['expo']['operazione']."'");
                    }
                }
            }
            else $allDone=False;

        }

        //se ORIGINE != da Deposito il materiale viene modificato dall'operatore
        else {

            $galileo->setTransaction('true');

            $galileo->clearQuery();
            $galileo->clearQueryOggetto('default','gdm');

            $galileo->executeGeneric('gdm','confermaNoDeposito',$f,'');

            $galileo->setTransaction('false');

            $galileo->clearQuery();
            $galileo->clearQueryOggetto('default','gdm');

            $galileo->executeSelect('gdm','GDM_materiali',"id='".$f['expo']['id']."'",'');
            
            if ($galileo->getResult()) {
                $fid=$galileo->preFetch('gdm');
                while ($row=$galileo->getFetch('gdm',$fid)) {
                    //se NON è stato aggiornato il materiale => nemmeno l'operazione (transaction ON)
                    if ($row['proprietario']!=$f['expo']['destinazione']) $allDone=false;
                    //altrimenti aggiorna lo storico dell'operazione
                    else {
                        $a=array(
                            'storico'=>json_encode($row)
                        );

                        $galileo->clearQuery();
                        $galileo->clearQueryOggetto('default','gdm');

                        $galileo->executeUpdate('gdm','GDM_operazioni',$a,"id='".$f['expo']['operazione']."'");
                    }
                }
            }
            else $allDone=False;

        }
    }

    //aggiorna RICHIESTA
    if ($allDone) {

        $galileo->clearQuery();
        $galileo->clearQueryOggetto('default','gdm');

        $a=array(
            "statoRi"=>"CHIUSA"
        );

        $galileo->executeUpdate('gdm','GDM_richieste',$a,"id='".$param['richiesta']."'");
    }
    
}

elseif ($param['ambito']=='stoccaggio') {
    //la richiesta è già stata chiusa

    //{"form":{"gdmForm_1031":{"flag":1,"chg":0,"expo":{"id":"1031","operazione":"1088","destinazione":"Deposito","origine":"Vettura","annotazioni":"","locazione":"esff"}}},"richiesta":"546","ambito":"stoccaggio","smaltite":0}

    foreach ($param['form'] as $k=>$f) {

        $f['expo']['richiesta']=$param['richiesta'];
        $f['expo']['isAnnullabile']='False';

        $galileo->setTransaction('true');

        $galileo->clearQuery();
        $galileo->clearQueryOggetto('default','gdm');

        $galileo->executeGeneric('gdm','confermaStoccaggio',$f['expo'],'');

        $galileo->setTransaction('false');

        $galileo->executeSelect('gdm','GDM_materiali',"id='".$f['expo']['id']."'",'');

        if ($galileo->getResult()) {
            $fid=$galileo->preFetch('gdm');
            while ($row=$galileo->getFetch('gdm',$fid)) {
                //se è stato aggiornato il materiale => anche l'operazione (transaction ON)
                if ($row['isBusy']=='False') {
                
                    $a=array(
                        'storico'=>json_encode($row)
                    );

                    $galileo->clearQuery();
                    $galileo->clearQueryOggetto('default','gdm');

                    $galileo->executeUpdate('gdm','GDM_operazioni',$a,"id='".$f['expo']['operazione']."'");
                }
            }
        }
    }

}

//echo json_encode($galileo->getLog('query'));

?>