<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

//{"form":{"gdmForm_0":{"flag":1,"chg":0,"expo":{"dimeASx":"205/55R16 97W","dimeADx":"205/55R16 97W","dimePSx":"205/55R16 97W","dimePDx":"205/55R16 97W","marcaASx":"ciccio","marcaADx":"ciccio","marcaPSx":"ciccio","marcaPDx":"ciccio","dotASx":"5023","dotADx":"5023","dotPSx":"5023","dotPDx":"5023","usuraASx":"8.0","usuraADx":"8.0","usuraPSx":"8.0","usuraPDx":"8.0","annotazioni":"","operazione":"0","destinazione":"Deposito","compoGomme":"TRENO","tipoGomme":"ESTIVO"}}},
//"telaio":"WVWZZZCDZMW352615",
//"veicolo":{"rif":5051338,"telaio":"WVWZZZCDZMW352615","targa":"GE567PD","cod_marca":"V","des_marca":"VOLKSWAGEN","modello":"CD14SY","des_veicolo":"GOLF 8 1.4 E-HYBRID DSG 204CV MY 21","cod_interno":"","cod_esterno":"","des_interno":"","des_esterno":"","cod_alim":"","d_imm":"20210518","d_cons":"20210520","d_fine_gar":"20230519","d_rev":"","des_note_off":"","num_gestione":0,"d_gestione":"","cod_natura_vendita":"","mat_motore":"DGE","cod_ente_venditore":"","cod_vw_tipo_veicolo":"4F","des_vw_tipo_veicolo":"Golf 8 2020>","cod_po_tipo_veicolo":"","des_po_tipo_veicolo":"","anno_modello":null,"cod_infocar":"","cod_infocar_anno":"","cod_infocar_mese":"","cod_marca_infocar":"","dms":"infinity","nomeCliente":"CECCONI MATTEO"}}

$arr=array('ASx','ADx','PSx','PDx');

//è uno solo
foreach ($param['form'] as $k=>$f) {
    $info=$f['expo'];
}

$info['tipologia']=$param['tipologia'];
$info['idTelaio']=$param['veicolo']['telaio'];
$info['proprietario']=$info['destinazione'];
$info['dataCreazione']=date('Ymd');

$info['isFull']='True';
$info['isAnnullabile']='True';

if ($info['tipologia']=='Pneumatici') {

    foreach ($arr as $k=>$p) {
        if ((int)$info['usura'.$p]<0) $info['isFull']='False';

        //se è segnata come MANCANTE
        /*if ((int)$info['usura'.$p]==-1) {
            $info['dime'.$p]=="";
            $info['marca'.$p]=="";
            $info['dot'.$p]=="";
            $info['usura'.$p]=="";
        }*/
    }
}

$mat=new gdmMateriale($info,$galileo);

$mat->insert($param['veicolo']);

echo json_encode($galileo->getLog('query'));


?>