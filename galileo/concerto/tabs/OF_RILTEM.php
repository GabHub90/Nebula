<?php

class concerto_ofriltem extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].OF_RILTEM";
        $this->selectMap=array(
            "num_rif_movimento",
            "cod_inconveniente",
            "cod_operaio",
            "num_riga",
            "dat_ora_inizio",
            "dat_ora_fine",
            "qta_ore_lavorate",
            "des_note",
            "num_rif_riltem"
        );

        $this->default=array(
            "num_rif_movimento"=>"",
            "cod_inconveniente"=>"",
            "cod_operaio"=>"",
            "num_riga"=>"",
            "dat_ora_inizio"=>date('Ymd H:i'),
            "dat_ora_fine"=>"NULL",
            "qta_ore_lavorate"=>"0",
            "des_note"=>"",
            "ora_entrata_effettiva"=>"NULL"
        );

        $this->checkMap=array(
            "num_rif_movimento"=>array("NOTNULL"),
            "cod_inconveniente"=>array("NOTNULL"),
            "cod_operaio"=>array("NOTNULL"),
            "num_riga"=>array("NOTNULL"),
            "dat_ora_inizio"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){

        if ($tipo=='insert') {
            $this->actual['ora_entrata_effettiva']=$this->actual['dat_ora_inizio'];
            $this->actual['num_riga']="###(SELECT isnull(max(num_riga),0)+1 FROM OF_RILTEM WHERE num_rif_movimento='".$this->actual['num_rif_movimento']."' AND cod_inconveniente='".$this->actual['cod_inconveniente']."')";
        }

    }
}

?>