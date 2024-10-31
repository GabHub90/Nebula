<?php

class ensambleCollaboratori {

    //data di riferimento
    protected $rif="";

    protected $collaboratori=array();

    protected $galileo;

    function __construct($d,$galileo) {

        $this->rif=($d=="")?date('Ymd'):$d;
        
        $this->galileo=$galileo;
    }

    function getCollaboratori() {
        return $this->collaboratori;
    }

    function getCollaboratoriMacro($macroreparto) {

        $this->galileo->getCollaboratori('macroreparto',$macroreparto,$this->rif);

        $this->setCollaboratori();
    }

    function getCollaboratoriReparto($reparto) {

        $this->galileo->getCollaboratori('reparto',$reparto,$this->rif);

        //echo json_encode($this->galileo->getLog('query'));

        $this->setCollaboratori();
    }

    function getCollaboratoriRepartoIntervallo($reparto,$i,$f) {

        $this->galileo->getCollaboratoriIntervallo("'".$reparto."'",$i,$f);

        //echo json_encode($this->galileo->getLog('query'));

        $this->setCollaboratori();
    }

    function setCollaboratori() {

        //echo json_encode($this->galileo->getLog('query'))

        $fetID=$this->galileo->preFetchBase('maestro');

        while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
            $this->collaboratori[$row['reparto']]['info']['tag']=$row['reparto'];
            $this->collaboratori[$row['reparto']]['info']['descrizione']=$row['des_reparto'];

            $this->collaboratori[$row['reparto']]['gruppi'][$row['gruppo']]['info']['tag']=$row['gruppo'];
            $this->collaboratori[$row['reparto']]['gruppi'][$row['gruppo']]['info']['descrizione']=$row['des_gruppo'];
            $this->collaboratori[$row['reparto']]['gruppi'][$row['gruppo']]['info']['macrogruppo']=$row['macrogruppo'];
            $this->collaboratori[$row['reparto']]['gruppi'][$row['gruppo']]['info']['des_macrogruppo']=$row['des_macrogruppo'];

            $this->collaboratori[$row['reparto']]['gruppi'][$row['gruppo']]['coll'][''.$row['ID_coll']]=$row;
        }

    }

}

?>