<?php

class qcDatas {

    //periodo           tipo di analisi secondaria (settimana,quindicina,mese,bimestre,trimestre,quadrimestre,semestre,anno)
    //giorni            giorni per l'analisi primaria (oggi-giorni)
    protected $args=array(
        "data_i"=>"",
        "data_f"=>"",
        "utente"=>""
    );

    protected $galileo;

    function __construct($args,$galileo) {

        $this->galileo=$galileo;

        foreach ($this->args as $k=>$v) {
            if ( array_key_exists($k,$args) ) {
                $this->args[$k]=$args[$k];
            }
        }

    }

    function getCollab($utente,$modulo) {
        
        $res=array();

        $this->args['utente']=$utente;

        //echo json_encode($this->args);

        $order="sm.ID_controllo";

        $this->galileo->executeGeneric('qcheck','getModuliUtente',$this->args,$order);

        $fetID=$this->galileo->preFetch('qcheck');

        while( $row=$this->galileo->getFetch('qcheck',$fetID) ) {
            if ($row['modulo']==$modulo) {
                $res[]=$row;
            }
        }

        //echo json_encode($res,JSON_UNESCAPED_SLASHES);
        /*{
            "ID_controllo": 1,
            "modulo": "1",
            "variante": "1",
            "esecutore": "m.cecconi",
            "operatore": "f.olmeda",
            "d_modulo": "20210310:10:08",
            "risposte": "{\"qc1\":\"1\",\"qc1n\":\"\",\"qc2\":\"2\",\"qc2n\":\"\",\"qc3\":\"0\",\"qc3n\":\"\",\"qc4\":\"1\",\"qc4n\":\"\",\"qc5\":\"1\",\"qc5n\":\"\",\"qc6\":\"1\",\"qc6n\":\"\",\"qc7\":\"1\",\"qc7n\":\"\",\"qc8\":\"1\",\"qc8n\":\"\",\"qc9\":\"1\",\"qc9n\":\"\",\"qc10\":\"1\",\"qc10n\":\"\",\"qc11\":\"1\",\"qc11n\":\"\",\"qc12\":\"1\",\"qc12n\":\"\",\"qc13\":\"2\",\"qc13n\":\"\"}",
            "punteggio": "{\"punteggio\":\"92\",\"risposte\":\"11\",\"domande\":\"11\"}",
            "stato": "chiuso",
            "rif_operatore": null,
            "controllo":1,
            "titolo": "Controllo del processo di riparazione",
            "des_rif_operatore": ""
        }*/

        $controllo=0;

        $arr=array();

        $c=array(
            "esecutore"=>0,
            "operatore"=>0,
            "punteggio"=>0,
            "domande"=>0,
            "risposte"=>0
        );

        foreach ($res as $r) {

            if ($controllo!=$r['controllo']) {
                $controllo=$r['controllo'];
                $arr[$controllo]['titolo']=$r['titolo'];
                $arr[$controllo]['stat']=$c;
            }
            
            if ($r['esecutore']==$this->utente) {
                $arr[$controllo]['stat']['esecutore']++;
            }

            if ($r['operatore']==$this->utente || $r['des_rif_operatore']==$this->utente) {
                $arr[$controllo]['stat']['operatore']++;

                try{
                    $t=json_decode($r['punteggio'],true);

                    $arr[$controllo]['stat']['punteggio']+=$t['punteggio'];
                    $arr[$controllo]['stat']['risposte']+=$t['risposte'];
                    $arr[$controllo]['stat']['domande']+=$t['domande'];
                }catch(Exception $e) {};
            }
        }

        return $arr;
    }
 
}

?>