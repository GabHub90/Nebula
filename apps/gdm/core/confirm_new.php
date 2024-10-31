<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

/*{
    "d":"20220422",
    "lines":2,
    "operazioni":{
        "Vettura":[{"id":"5837","origine":"Deposito","destinazione":"Vettura"}],
        "Deposito":[{"id":"5836","origine":"Vettura","destinazione":"Deposito"}]
    },
    "veicolo":{"rif":5051338,"telaio":"WVWZZZCDZMW352615","targa":"GE567PD","cod_marca":"V","des_marca":"VOLKSWAGEN","modello":"CD14SY","des_veicolo":"GOLF 8 1.4 E-HYBRID DSG 204CV MY 21","cod_interno":"","cod_esterno":"","des_interno":"","des_esterno":"","cod_alim":"","d_imm":"20210518","d_cons":"20210520","d_fine_gar":"20230519","d_rev":"","des_note_off":"","num_gestione":0,"d_gestione":"","cod_natura_vendita":"","mat_motore":"DGEA189641","cod_ente_venditore":"","cod_vw_tipo_veicolo":"4F","des_vw_tipo_veicolo":"Golf 8 2020>","cod_po_tipo_veicolo":"","des_po_tipo_veicolo":"","anno_modello":null,"cod_infocar":"","cod_infocar_anno":"","cod_infocar_mese":"","cod_marca_infocar":"","dms":"infinity"}
}*/

$arr=array(
    'richiesta'=>array(
        'id'=>'',
        'statoRi'=>'APERTA',
        'dataRi'=>$param['d'],
        'idTelaio'=>$param['veicolo']['telaio'],
        'nomeCliente'=>$param['veicolo']['nomeCliente'],
        'targa'=>$param['veicolo']['targa'],
        'tipoVeicolo'=>$param['veicolo']['des_veicolo'],
        'numPratica'=>(isset($param['numPratica']))?$param['numPratica']:'',
        'dms'=>$param['veicolo']['dms']
    ),

    $operazioni=array()
);

$ambiti=['Edit','Deposito','Vettura','Cliente'];
$stati=array(
    'Edit'=>'Pronto',
    'Deposito'=>'Richiesto',
    'Vettura'=>'Installato',
    'Cliente'=>'Pronto'
);

foreach ($ambiti as $k=>$a) {   

    if (isset($param['operazioni'][$a])) {

        foreach ($param['operazioni'][$a] as $j=>$o) {
            $arr['operazioni'][]=array(
                'idRi'=>"",
                'idMat'=>$o['id'],
                'destinazione'=>$o['destinazione'],
                'statoOp'=>$stati[$o['origine']],
                'origine'=>$o['origine'],
                'storico'=>'',
                'dataOperazione'=>''
            );
        }
    }
}

$galileo->setTransaction(true);
$galileo->executeGeneric('gdm','creaRichiesta',$arr,'');

echo json_encode($galileo->getLog('query'));

?>