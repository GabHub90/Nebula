<?php

include('default.php');

foreach ($nebulaParams['link'] as $k=>$l) {
    //echo json_encode($l);

    /* {
        "ID_coll": "193",
        "ID_link": "0",
        "ID_piano": "1",
        "dlink_i": "20220101",
        "dlink_f": "20220630",
        "periodo_i": "5",
        "periodo_f": "9999",
        "variante": "TEC",
        "grado": {
            "1": "0",
            "2": "0",
            "3": "0"
        }
    }*/

    $arr=array(
        "coll"=>$l['ID_coll'],
        "piano"=>$l['ID_piano'],
        "variante"=>$l['variante'],
        "data_i"=>$l['dlink_i'],
        "data_f"=>$l['dlink_f'],
        "grado"=>json_encode($l['grado']),
        "periodo_inizio"=>$l['periodo_i'],
        "periodo_fine"=>$l['periodo_f']
    );

    $galileo->executeUpsert('centavos','CENTAVOS_link',$arr,"ID='".$l['ID_link']."'");
}

?>