<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_tipo",
        "liz_data"
    );

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    if ($txt!="") return $txt;

    return $txt;
};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('stkvei','Inventario veicoli al: '.mainfunc::gab_todata(mainfunc::gab_input_to_db($this->param['liz_data'])));

    //########################
    //inizializzazione excalibur
    $conv=array(
        "count"=>"count",
        "id_veicolo"=>"id_veicolo",
        "targa"=>"targa",
        "telaio"=>"telaio",
        "d_entrata"=>"d_entrata",
        "d_uscita"=>"d_uscita",
        "d_inventario"=>"d_inventario",
        "cod_ubi_act"=>"cod_ubi_act",
        "ubicattuale"=>"ubicattuale"
    );

    $mappa=array(
        "count"=>array("tag"=>""),
        "id_veicolo"=>array("tag"=>"Veicolo"),
        "targa"=>array("tag"=>"Targa"),
        "telaio"=>array("tag"=>"Telaio"),
        "d_entrata"=>array("tag"=>"Iniziale","tipo"=>"data"),
        "d_uscita"=>array("tag"=>"Finale","tipo"=>"data"),
        "d_inventario"=>array("tag"=>"Inventario","tipo"=>"data"),
        "cod_ubi_act"=>array("tag"=>"cod."),
        "ubicattuale"=>array("tag"=>"ubicazione")
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $nebulaDefault=array();
    $obj=new galileoInfinityVeicoli();
    $nebulaDefault['veicoli']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $arr=array(
        "tipo"=>$this->param['liz_tipo'],
        "d"=>mainFunc::gab_input_to_db($this->param['liz_data'])
    );

    $this->galileo->executeGeneric('veicoli','inventarioVeicoli',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('veicoli');

        $c=0;

        while ($row=$this->galileo->getFetch('veicoli',$fid)) {

            $row['cod_ubi_act']='';
            $row['ubicattuale']='';
            $row['rif_data']='';

            if ($row['id_bolla_c']!=0) {
                $row['cod_ubi_act']=$row['ubi_c'];
                $row['ubicattuale']=$row['des_c'];
                $row['rif_data']=$row['d_bolla_c'];

            }

            if ($row['id_bolla_i']!=0) {
                if ($row['d_bolla_i']>$row['rif_data']) {
                    $row['cod_ubi_act']=$row['ubi_i'];
                    $row['ubicattuale']=$row['des_i'];
                    $row['rif_data']=$row['d_bolla_i'];
                }
            }

            if ($row['id_bolla_f']!=0) {
                if ($row['d_bolla_f']>$row['rif_data']) {
                    $row['cod_ubi_act']=$row['ubi_f'];
                    $row['ubicattuale']=$row['des_f'];
                }
            }

            if ($row['ubicattuale']=='') {
                $row['cod_ubi_act']=$row['ubicazione'];
                $row['ubicattuale']=$row['des_ubi'];
            }

            $c++;
            $row['count']=$c;

            $this->lista->add($row);
        }
    }
};

?>