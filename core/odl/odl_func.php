<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_odl.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alert.php');

class nebulaOdlFunc {
    //utilities per un ordine di lavoro
    //non passa per wormHole in quanto sappiamo già l'odl in quale piattaforma risiede

    protected $repNebulaDms=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $repDmsNebula=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $subrepNebulaDms=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $subrepDmsNebula=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $marcaDmsNebula=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $marcaNebulaDms=array(
        "concerto"=>array(),
        "infinity"=>array()
    );
    
    protected $addebiti=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $statiLam=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $marca=array(
        "A"=>"Audi",
        "C"=>"Skoda",
        "N"=>"Vic",
        "P"=>"Porsche",
        "S"=>"Seat",
        "V"=>"Volkswagen",
        "U"=>"Cupra",
        "X"=>"Generico"
    );

    protected $convLam=array();

    //sono i reparti che gestiscono i ripristini e le preconsegne
    protected $interni=array('PN','PU','CN','CU');
    protected $repint=array('PNP','UPM','CPM','CNC');

    protected $trasporto=array();
    protected $statoOdl=array();

    protected $piattaforma="";
    protected $dms="";

    protected $log=array();

    //in questo caso GALILEO è una COPIA dell'originale
    protected $galileo;

    function __construct($galileo) {

        $this->galileo=clone $galileo;

        $obj=new galileoAlert();
        $nebulaDefault['alert']=array("gab500",$obj);

        $this->galileo->setFunzioniDefault($nebulaDefault);

        //nebula
        $this->setGalileo('nebula');

        $this->galileo->getReparti('S','');
        if ($result=$this->galileo->getResult()) {

            $fid=$this->galileo->preFetchBase('reparti');

            while ($row=$this->galileo->getFetchBase('reparti',$fid)) {

                if ($row['concerto']!='') {
                    $this->repNebulaDms['concerto'][$row['reparto']]=$row['concerto'];
                    $this->repDmsNebula['concerto'][$row['concerto']]=$row['reparto'];
                }
                if ($row['infinity']!='') {
                    $this->repNebulaDms['infinity'][$row['reparto']]=$row['infinity'];
                    $this->repDmsNebula['infinity'][$row['infinity']]=$row['reparto'];
                }
            }
        }

        $this->galileo->clearQuery();
        $this->galileo->getReparti('M','');
        if ($result=$this->galileo->getResult()) {

            $fid=$this->galileo->preFetchBase('reparti');

            while ($row=$this->galileo->getFetchBase('reparti',$fid)) {

                if ($row['concerto']!='') {
                    $this->repNebulaDms['concerto'][$row['reparto']]=$row['concerto'];
                    $this->repDmsNebula['concerto'][$row['concerto']]=$row['reparto'];
                }
                if ($row['infinity']!='') {
                    $this->repNebulaDms['infinity'][$row['reparto']]=$row['infinity'];
                    $this->repDmsNebula['infinity'][$row['infinity']]=$row['reparto'];
                }
            }
        }

        $this->galileo->clearQuery();
        $this->galileo->getQuartetSubrep();
        if ($result=$this->galileo->getResult()) {

            $fid=$this->galileo->preFetchBase('schemi');

            while ($row=$this->galileo->getFetchBase('schemi',$fid)) {

                if (!is_null($row['concerto']) && $row['concerto']!='') {
                    $this->subrepNebulaDms['concerto'][$row['subrep']]=array("subrep"=>$row['concerto'],"officina"=>$row['off_concerto']);
                    //$this->subrepDmsNebula['concerto'][$row['off_concerto']][$row['concerto']]=array("subrep"=>$row['subrep'],"officina"=>$row['reparto']);
                }
            }
        }


        //TEST
        $this->marcaDmsNebula['concerto']=array(
            "A"=>"A",
            "C"=>"C",
            "N"=>"N",
            "P"=>"P",
            "S"=>"S",
            "V"=>"V",
            "U"=>"U"
        );

        $this->marcaDmsNebula['infinity']=array(
            "A"=>"A",
            "C"=>"C",
            "N"=>"N",
            "P"=>"P",
            "S"=>"S",
            "V"=>"V",
            "U"=>"U"
        );

        $this->marcaNebulaDms['concerto']=array(
            "A"=>"A",
            "C"=>"C",
            "N"=>"N",
            "P"=>"P",
            "S"=>"S",
            "V"=>"V",
            "U"=>"U"
        );

        $this->marcaNebulaDms['infinity']=array(
            "A"=>"A",
            "C"=>"C",
            "N"=>"N",
            "P"=>"P",
            "S"=>"S",
            "V"=>"V",
            "U"=>"U"
        );

        $this->addebiti['concerto']=array(
            "OOP"=>array(
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Pagamento","c2rClasse"=>"mec","info"=>""),
                    array("cg"=>"E","tipo"=>"pag","gruppo"=>"pack","colore"=>"#b5ffff","tag"=>"Pagamento","c2rClasse"=>"mec","info"=>"")
                ),
                "OGO"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>""),
                    array("cg"=>"O","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>""),
                    array("cg"=>"G","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>""),
                    array("cg"=>"R","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "OGOA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione"),
                    array("cg"=>"O","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione"),
                    array("cg"=>"G","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione"),
                    array("cg"=>"R","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione")
                ),
                "OPO"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Correntezza","c2rClasse"=>"mec","info"=>""),
                    array("cg"=>"P","tipo"=>"gar","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Correntezza","c2rClasse"=>"mec","info"=>"")
                ),
                "OCC"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Carr.Pag","c2rClasse"=>"car","info"=>"")
                ),
                "OCG"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"b5f3c8","tag"=>"Carr.Gar","c2rClasse"=>"car","info"=>"")
                ),
                "OIG"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Interno","c2rClasse"=>"mec","info"=>"")
                ),
                "ZERO"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Pagamento","c2rClasse"=>"mec","info"=>"")
                )
            ),
            "OOA"=>array(
                "OLP00"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"app","colore"=>"#cccccc","tag"=>"Approntamento","c2rClasse"=>"mec","info"=>"")
                ),
                "OLP01"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"app","colore"=>"#cccccc","tag"=>"Approntamento","c2rClasse"=>"mec","info"=>"")
                ),
                "OLRIP"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"rip","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "OCI"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Carr.Int","c2rClasse"=>"car","info"=>"")
                ),
                "OCC"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Carr.Int","c2rClasse"=>"car","info"=>"")
                ),
                "OCG"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Carr.Int","c2rClasse"=>"car","info"=>"")
                ),
                "OGO"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "OGOA"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>""),
                ),
                "ZERO"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Approntamento","c2rClasse"=>"mec","info"=>"")
                )
            ),
            "OOI"=>array(
                "OIG"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"","colore"=>"white","tag"=>"Interno","c2rClasse"=>"mec","info"=>"")
                )
            ),
            "OOPN"=>array(
                "ONCO"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"","colore"=>"white","tag"=>"Accredito","c2rClasse"=>"mec","info"=>"")
                ),
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"","colore"=>"white","tag"=>"Accredito","c2rClasse"=>"mec","info"=>"")
                ),
                "OCC"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"","colore"=>"white","tag"=>"Accredito","c2rClasse"=>"car","info"=>"")
                ),
                "ZERO"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"","colore"=>"white","tag"=>"Accredito","c2rClasse"=>"mec","info"=>"")
                )
            )
        );

        $this->addebiti['infinity']=array(
            "OOP"=>array(
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Pagamento","c2rClasse"=>"mec","info"=>"")
                ),
                "ALLA"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Pagamento","c2rClasse"=>"mec","info"=>"")
                ),
                "GAAU"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GAVW"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GRIV"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GRIA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GRIS"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GRIC"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GRIN"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GARA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione")
                ),
                "GARV"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione")
                ),
                "GAN"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GAS"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GAC"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GACV"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GACS"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GACC"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "GARC"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione")
                ),
                "GARS"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione")
                ),
                "GARN"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"azione")
                ),
                "GCOA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Correntezza","c2rClasse"=>"mec","info"=>"")
                ),
                "GCOV"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Correntezza","c2rClasse"=>"mec","info"=>"")
                ),
                "GCON"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Correntezza","c2rClasse"=>"mec","info"=>"")
                ),
                "GCOS"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Correntezza","c2rClasse"=>"mec","info"=>"")
                ),
                "GCOC"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Correntezza","c2rClasse"=>"mec","info"=>"")
                ),
                "GRBA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "AGO"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"","colore"=>"white","tag"=>"Interno","c2rClasse"=>"mec","info"=>"")
                ),
                "GACA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>"","colore"=>"#b5f3c8","tag"=>"Garanzia","c2rClasse"=>"mec","info"=>"")
                ),
                "AU"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Pagamento","c2rClasse"=>"mec","info"=>"")
                ),
                "AGN"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"rip","colore"=>"#b5ffff","tag"=>"Pagamento","c2rClasse"=>"mec","info"=>"")
                ),
                "OCC"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Carr.Pag","c2rClasse"=>"car","info"=>"")
                ),
                "NOL"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"nol","colore"=>"#b5ffff","tag"=>"Noleggio","c2rClasse"=>"ser","info"=>"")
                ),
                "CLT"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>"terzi","colore"=>"#b5ffff","tag"=>"Terzi","c2rClasse"=>"ter","info"=>"")
                )
            ),
            "OOA"=>array(
                "AN"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"app","colore"=>"#cccccc","tag"=>"Approntamento","c2rClasse"=>"mec","info"=>"")
                ),
                "ACN"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"app","colore"=>"#cccccc","tag"=>"Approntamento","c2rClasse"=>"mec","info"=>"")
                ),
                "ACU"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"rip","colore"=>"#cccccc","tag"=>"Approntamento","c2rClasse"=>"mec","info"=>"")
                ),
                "AGN"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"rip","colore"=>"#cccccc","tag"=>"Approntamento","c2rClasse"=>"mec","info"=>"")
                ),
                "AU"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"rip","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "AS"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"rip","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"rip","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "OCI"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Carr.Int","c2rClasse"=>"car","info"=>"")
                ),
                "OIG"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"","colore"=>"#cccccc","tag"=>"Interno","c2rClasse"=>"mec","info"=>"")
                ),
                "AGO"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"","colore"=>"#cccccc","tag"=>"Interno","c2rClasse"=>"mec","info"=>"")
                ),
                "AGU"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"","colore"=>"#cccccc","tag"=>"Interno","c2rClasse"=>"mec","info"=>"")
                ),
                "OCC"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Carr.Int","c2rClasse"=>"car","info"=>"")
                ),
                "ALLA"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Interno","c2rClasse"=>"mec","info"=>"")
                ),
                "NOL"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"nol","colore"=>"#cccccc","tag"=>"Noleggio","c2rClasse"=>"ser","info"=>"")
                ),
                "GARV"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "GARS"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "GAVW"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                ),
                "GAC"=>array(
                    array("cg"=>"","tipo"=>"int","gruppo"=>"","colore"=>"#cccccc","tag"=>"Ripristino","c2rClasse"=>"mec","info"=>"")
                )
            ),
            "OOPN"=>array(
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"","colore"=>"white","tag"=>"Accredito","c2rClasse"=>"mec","info"=>"")
                ),
                "NOL"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"nol","colore"=>"white","tag"=>"Accredito","c2rClasse"=>"ser","info"=>"")
                ),
                "OCC"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Accredito","c2rClasse"=>"car","info"=>"")
                ),
                "ALLA"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"","colore"=>"#b5ffff","tag"=>"Accredito","c2rClasse"=>"mec","info"=>"")
                ),
                "GCOA"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"corr","colore"=>"#dcc3f7","tag"=>"Accredito","c2rClasse"=>"mec","info"=>"")
                )
            )
        );

        $this->statiLam['concerto']=array(
            "L"=>"In lavorazione",
            "M"=>"Attesa ricambi",
            "T"=>"Terminato",
            "R"=>"Sospeso",
            "X"=>"Nessuno"
        );
        $this->statiLam['infinity']=array(
            "6"=>"In lavorazione",
            "5"=>"Attesa ricambi",
            "1"=>"Terminato",
            "2"=>"Sospeso",
            "0"=>"Nessuno"
        );

        //conversione stati lam DMS --> nebula ALERT
        $this->convLam['concerto']=array(
            "L"=>"XX",
            "M"=>"RO",
            "T"=>"RP",
            "R"=>"SO",
            "X"=>"XX"
        );
        $this->convLam['infinity']=array(
            "6"=>"XX",
            "5"=>"RO",
            "1"=>"RP",
            "2"=>"SO",
            "0"=>"XX"
        );
        /////////////////////////////////////////////

        $this->trasporto=array(
            'concerto'=>array(
                'NO'=>array(
                    "codice"=>"NO",
                    "testo"=>"NESSUNO",
                    "stato"=>1,
                    "concerto"=>"NO",
                    "infinity"=>""
                ),
                'ALT'=>array(
                    "codice"=>"CHI",
                    "testo"=>"LASCIA CHIAVI",
                    "stato"=>1,
                    "concerto"=>"ALT",
                    "infinity"=>""
                ),
                'ASP'=>array(
                    "codice"=>"ASP",
                    "testo"=>"ASPETTA",
                    "stato"=>1,
                    "concerto"=>"ASP",
                    "infinity"=>""
                ),
                'BUS'=>array(
                    "codice"=>"BUS",
                    "testo"=>"AUTOBUS",
                    "stato"=>1,
                    "concerto"=>"BUS",
                    "infinity"=>""
                ),
                'RIT'=>array(
                    "codice"=>"RIT",
                    "testo"=>"RITIRO E RICONSEGNA",
                    "stato"=>1,
                    "concerto"=>"RIT",
                    "infinity"=>""
                ),
                'SOS'=>array(
                    "codice"=>"SOS",
                    "testo"=>"SOSTITUTIVA",
                    "stato"=>1,
                    "concerto"=>"SOS",
                    "infinity"=>""
                ),
                'TAX'=>array(
                    "codice"=>"TAX",
                    "testo"=>"TAXI",
                    "stato"=>1,
                    "concerto"=>"TAX",
                    "infinity"=>""
                )
            ),
            'infinity'=>array(
                'ASP'=>array(
                    "codice"=>"ASP",
                    "testo"=>"ASPETTA",
                    "stato"=>1,
                    "concerto"=>"ASP",
                    "infinity"=>""
                )
            )
        );

        $this->statoOdl=array(

            'concerto'=>array(
                'XX'=>array(
                    "codice"=>"XX",
                    "testo"=>"Nessuno",
                    "stato"=>1,
                    "colore"=>"#cccccc"
                ),
                'AP'=>array(
                    "codice"=>"AP",
                    "testo"=>"Aperto",
                    "stato"=>1,
                    "colore"=>"#ffff7b"
                ),
                'CH'=>array(
                    "codice"=>"CH",
                    "testo"=>"Chiuso",
                    "stato"=>1,
                    "colore"=>"white"
                ),
                'CO'=>array(
                    "codice"=>"CO",
                    "testo"=>"Consegnata",
                    "stato"=>1,
                    "colore"=>"pink"
                ),
                'KO'=>array(
                    "codice"=>"KO",
                    "testo"=>"Prova Negativa",
                    "stato"=>1,
                    "colore"=>"red"
                ),
                'DL'=>array(
                    "codice"=>"DL",
                    "testo"=>"da Lavare",
                    "stato"=>1,
                    "colore"=>"#589bff"
                ),
                'LA'=>array(
                    "codice"=>"LA",
                    "testo"=>"Lavata",
                    "stato"=>1,
                    "colore"=>"#6dfde2"
                ),
                'OK'=>array(
                    "codice"=>"OK",
                    "testo"=>"Pronta",
                    "stato"=>1,
                    "colore"=>"#6dfde2"
                ),
                'RO'=>array(
                    "codice"=>"RO",
                    "testo"=>"Ricambi",
                    "stato"=>1,
                    "colore"=>"#ff9bf3"
                ),
                'RP'=>array(
                    "codice"=>"RP",
                    "testo"=>"Regolare",
                    "stato"=>1,
                    "colore"=>"#85ee85"
                ),
                'SO'=>array(
                    "codice"=>"SO",
                    "testo"=>"Sospeso",
                    "stato"=>1,
                    "colore"=>"orange"
                ),
                'PK'=>array(
                    "codice"=>"PK",
                    "testo"=>"PrePicking",
                    "stato"=>1,
                    "colore"=>"#eb76f3"
                ),
                'AT'=>array(
                    "codice"=>"AT",
                    "testo"=>"Alert",
                    "stato"=>1,
                    "colore"=>"#fe8b8b"
                ),
                'EX'=>array(
                    "codice"=>"EX",
                    "testo"=>"Esterno",
                    "stato"=>1,
                    "colore"=>"#ffd67a"
                ),
                'NP'=>array(
                    "codice"=>"NP",
                    "testo"=>"Non arrivata",
                    "stato"=>1,
                    "colore"=>"yellow"
                )
            ),
            'infinity'=>array(
                'AP'=>array(
                    "codice"=>"AP",
                    "testo"=>"Aperto",
                    "stato"=>1,
                    "colore"=>"#ffff7b"
                ),
                'CH'=>array(
                    "codice"=>"CH",
                    "testo"=>"Chiuso",
                    "stato"=>1,
                    "colore"=>"white"
                ),
                'RP'=>array(
                    "codice"=>"RP",
                    "testo"=>"Regolare",
                    "stato"=>1,
                    "colore"=>"#85ee85"
                ),
                'XX'=>array(
                    "codice"=>"XX",
                    "testo"=>"Nessuno",
                    "stato"=>1,
                    "colore"=>"#cccccc"
                ),
                'SO'=>array(
                    "codice"=>"SO",
                    "testo"=>"Sospeso",
                    "stato"=>1,
                    "colore"=>"orange"
                ),
                'PK'=>array(
                    "codice"=>"PK",
                    "testo"=>"PrePicking",
                    "stato"=>1,
                    "colore"=>"#eb76f3"
                ),
                'AT'=>array(
                    "codice"=>"AT",
                    "testo"=>"Alert",
                    "stato"=>1,
                    "colore"=>"#fe8b8b"
                ),
                'OK'=>array(
                    "codice"=>"OK",
                    "testo"=>"Pronta",
                    "stato"=>1,
                    "colore"=>"#6dfde2"
                ),
                'EX'=>array(
                    "codice"=>"EX",
                    "testo"=>"Esterno",
                    "stato"=>1,
                    "colore"=>"#ffd67a"
                ),
                'RO'=>array(
                    "codice"=>"RO",
                    "testo"=>"Ricambi",
                    "stato"=>1,
                    "colore"=>"#ff9bf3"
                ),
                'DL'=>array(
                    "codice"=>"DL",
                    "testo"=>"da Lavare",
                    "stato"=>1,
                    "colore"=>"#589bff"
                ),
                'LA'=>array(
                    "codice"=>"LA",
                    "testo"=>"Lavata",
                    "stato"=>1,
                    "colore"=>"#6dfde2"
                ),
                'NP'=>array(
                    "codice"=>"NP",
                    "testo"=>"Non arrivata",
                    "stato"=>1,
                    "colore"=>"yellow"
                )
            )
        );

        //END TEST

    }

    function getLog() {
        return $this->log;
    }

    function exportGalileo() {
        return $this->galileo;
    }

    function setGalileo($dms) {

        $this->dms=$dms;

        if ($dms=='nebula') {

            //in questo momento ci affidiamo alle informazioni di TEST

            $obj=new galileoODL();
            $nebulaDefault['odl']=array("gab500",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->piattaforma='gab500';
        }

        elseif ($dms=='concerto') {

            $obj=new galileoConcertoODL();
            $nebulaDefault['odl']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->piattaforma='maestro';
        }

        elseif ($dms=='infinity') {

            $obj=new galileoInfinityODL();
            $nebulaDefault['odl']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->piattaforma='rocket';
        }

    }

    function getPiattaforma() {
        return $this->piattaforma;
    }

    /////////////////////////////////////////////////////////////////////////////////////

    /*function checkAddebito($movimento,$acarico,$dms) {

        return isset($this->addebiti[$dms][$movimento][$acarico]);
    }*/

    function getAddebito($row,$dms) {
        //viene eseguito il check prima di proseguire

        if (!isset($row['cod_movimento']) || !isset($row['acarico']) || !isset($row['cg']) ) return false;

        if (!isset($this->addebiti[$dms][$row['cod_movimento']][$row['acarico']])) return false;

        //if (!$this->checkAddebito($row['cod_movimento'],$row['acarico'],$dms)) return false;

        $ret=false;

        foreach ($this->addebiti[$dms][$row['cod_movimento']][$row['acarico']] as $a) {
            if ($a['cg']=='' || $a['cg']==$row['cg']) {
                
                $ret=$a;

                if($a['cg']==$row['cg']) break;
            }
        }

        return $ret;
    }

    function getInterni() {
        return $this->interni;
    }

    function getRepint() {
        return $this->repint;
    }

    function getStatiLam($dms) {
        return $this->statiLam[$dms];
    }

    function getStatoOdl($codice,$dms) {
        if (isset($this->statoOdl[$dms][$codice])) {
            return $this->statoOdl[$dms][$codice];
        }
        else return false;
    }

    function getTrasporto($codice,$dms) {
        if (isset($this->trasporto[$dms][$codice])) {
            return $this->trasporto[$dms][$codice];
        }
        else return false;
    }

    function exportTrasporto($dms) {
        return $this->trasporto[$dms];
    }

    function checkStatoLam($stato,$dms) {
        return isset($this->statiLam[$dms][$stato]);
    }

    function getStatoLam($row,$dms) {

        if (!isset($row['ind_stato']) ) return false;

        if (!$this->checkStatoLam($row['ind_stato'],$dms)) return false;

        else return $this->statiLam[$dms][$row['ind_stato']];
    }

    function convStatoLam($slam,$dms) {

        if (isset($this->convLam[$dms][$slam])) {
            return $this->convLam[$dms][$slam];
        }
        else return 'XX';
    }

    function getLamStats($rif,$lam,$dms) {

        $this->setGalileo($dms);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $arr=array(
            "rif"=>$rif,
            "lam"=>$lam
        );

        $this->galileo->executeGeneric('odl','getLamStats',$arr,'');
        
        //i riferimenti del codice operaio sono quelli proprietari del DMS 
        return $this->galileo->getResult();

    }

    function getLamMarcato($rif,$lam,$dms,$operaio) {
        //ritorna le marcature di un determinato operaio in un lamentato

        $this->setGalileo($dms);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $arr=array(
            "rif"=>$rif,
            "lam"=>$lam,
            "operaio"=>$operaio
        );

        $this->galileo->executeGeneric('odl','getLamMarcato',$arr,'');
        
        //i riferimenti del codice operaio sono quelli proprietari del DMS 
        return $this->galileo->getResult();
    }

    function getNebulaRep($dms,$repDms) {

        if(isset($this->repDmsNebula[$dms][$repDms])) {
            return $this->repDmsNebula[$dms][$repDms];
        }
        else return false;
    }

    function getDmsRep($dms,$repNebula) {

        if(isset($this->repNebulaDms[$dms][$repNebula])) {
            return $this->repNebulaDms[$dms][$repNebula];
        }
        else return false;
    }

    function repStringify($dms,$a) {
        //sostituisce il codice dei reparti di nebula con quello del dms
        foreach ($this->repNebulaDms[$dms] as $n=>$d) {
            $a=str_replace("'".$n."'","'".$d."'",$a);
        }

        return $a;
    }

    function getMarcheStandard() {
        return $this->marca;
    }

    function getDesNebulaMarca($dms,$codice) {

        if (isset($this->marcaDmsNebula[$dms][$codice])) {
            if (isset($this->marca[$this->marcaDmsNebula[$dms][$codice]])) return $this->marca[$this->marcaDmsNebula[$dms][$codice]];
            else return '';
        }
        else return '';
    }

    function getMarcaDms($dms,$marca) {

        if (isset($this->marcaNebulaDms[$dms][$marca])) return $this->marcaNebulaDms[$dms][$marca];
        else return '';
    }

    //////////////////////////////////////////////////////////////////////

    function getOdl($rif,$dms,$ambito) {

        //ambito= 'cli' || 'pre' (che servono per infinity)

        if ($this->dms!=$dms) $this->setGalileo($dms);

        /*$arg=false;

        if ($dms=='concerto') {
            $arg=array(
                "num_rif_movimento"=>$rif
            );
        }

        elseif ($dms=='infinity') {
            $arg=array(
                "num_rif_movimento"=>$rif
            );
        }

        if (!$arg) return false;*/

        $arg=array(
            "num_rif_movimento"=>$rif,
            "lista"=>$ambito,
            "tipo"=>"tutti"
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getOdlLamentati',$arg,'');

        //echo json_encode($this->galileo->getLog('query'));

        return $this->galileo->getResult();
    }

    function getFittizioInfinity() {

        $res=array();

        if ($this->dms!='infinity') $this->setGalileo('infinity');

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getAcaricoMarcatempo',array(),'');
        $result=$this->galileo->getResult();

        if (!$result) return false;

        $fid=$this->galileo->preFetchPiattaforma($this->piattaforma,$result);
        while ($row=$this->galileo->getFetchPiattaforma($this->piattaforma,$fid)) {

            $res['0k'][$row['codice']]=array(
                'dms'=>'infinity',
                'nebulaAddebito'=>'',
                'rif'=>'0k',
                'cod_movimento'=>'OOS',
                'lam'=>$row['codice'],
                'des_riga'=>$row['descrizione']
            );
        }

        return $res;
    }

    function getRiltem($param) {

        if ($param['map']['dms']!=$this->dms) $this->setGalileo($param['map']['dms']);

        if($param['map']['dms']=='concerto') {
            $oby="res.num_rif_movimento,res.cod_inconveniente,CAST (res.cod_operaio AS int),d_inizio,o_inizio";
        }
        if($param['map']['dms']=='infinity') {
            $oby="m.num_rif_movimento,m.cod_inconveniente,m.cod_operaio,m.d_inizio,m.o_inizio";
        }

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','getRiltem',$param,$oby);

        $param['map']['result']=$this->galileo->getResult();

        return $param;

    }

    //////////////////////////////////////////////////////////////////////////

    function readCommesse($dms,$vei) {
        //recupera lo storico delle commesse per un determinato veicolo
        //$vei è un array con le informazioni del veicolo tra cui RIF
        //restituisce una $map wormhole

        if ($dms!=$this->dms) $this->setGalileo($dms);

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        if (!$vei['rif'] || $vei['rif']=='') return $map;

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','getStorico',array('rif'=>$vei['rif']),'');

        $map['result']=$this->galileo->getResult();

        return $map;
    }

    function readPassMan($telaio) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','getPassMan',array('telaio'=>$telaio),'');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTmarche($marca) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_marche',"codice='".$marca."'",'');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTalim() {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_alim','','');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTtraz() {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_traz','','');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTcambio($marca) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_cambio',"marca='".$marca."'",'');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTmanut($marca) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_manut',"marca='".$marca."'",'');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTBase() {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','getOggettiBase',array(),'');

        $map['result']=$this->galileo->getResult();

        return $map;
    }

    function getOTDefault($marca) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_oggetti_default',"stato=1 AND marca='".$marca."'",'');

        $map['result']=$this->galileo->getResult();

        return $map;
    }

    function getOTGruppi($marca) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_gruppi',"marca='".$marca."'",'descrizione');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function updateOTGruppi($row) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeUpdate('odl','OT2_gruppi',$row,"codice='".$row['codice']."' AND indice='".$row['indice']."'");

        //sovrascrive e non incrementa
        //$this->log=$this->galileo->getLog('query');

    }

    function getOTGruppoMM($marca,$modello) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_link_modgru',"marca='".$marca."' AND modello='".$modello."'",'');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTGruppoID($codice,$indice) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_gruppi',"codice='".$codice."' AND indice='".$indice."'",'');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTCriteriModello($marca,$modello) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $wc="edit!=''";
        if ($marca!="") $wc.=" AND marca='".$marca."'";
        if ($modello!="") $wc.=" AND modello='".$modello."'";

        $this->galileo->executeSelect('odl','OT2_criteri_mod',$wc,'marca,modello');

        $map['result']=$this->galileo->getResult();

        //$this->log[]=$this->galileo->getLog('query');

        return $map;

    }

    function updateOTCriteriMod($row) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeUpdate('odl','OT2_criteri_mod',$row,"marca='".$row['marca']."' AND modello='".$row['modello']."'");

        //sovrascrive e non incrementa
        //$this->log=$this->galileo->getLog('query');

    }

    function getOTCriteriTelaio($telaio) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_criteri_tel',"telaio='".$telaio."'",'');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTeventi($str) {
        //str = stringa degli oggetti da filtrare (IN)

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $map=array(
            "dms"=>$this->dms,
            "piattaforma"=>$this->piattaforma,
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $wc=($str!="")?"oggetto IN (".$str.")":"";

        $this->galileo->executeSelect('odl','OT2_eventi',$wc,'tipo,codice,oggetto');

        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getOTevento($oggetto,$codice,$tipo) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $res=false;
       
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeSelect('odl','OT2_eventi',"oggetto='".$oggetto."' AND codice='".$codice."' AND tipo='".$tipo."'",'');

        if ($this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('odl');

            while ($row=$this->galileo->getFetch('odl',$fid)) {
                $res=$row;
            }
        }

        return $res;
    }

    function insertPassman($a) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->setTransaction(true);

        $this->galileo->executeGeneric('odl','insertPassman',$a,"");


    }

    function updatePassman($a) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->setTransaction(true);

        $this->galileo->executeGeneric('odl','updatePassman',$a,"");

        //echo json_encode($this->galileo->getLog('query'));
        
    }

    function deletePassman($a) {

        if ($this->dms!='nebula') $this->setGalileo('nebula');

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->setTransaction(true);

        $this->galileo->executeGeneric('odl','deletePassman',$a,"");

    }

    //////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////

    function getPraticaRif($pratica,$dms,$pren) {
        //prende i riferimenti delle commesse di una pratica (S=prenotazione)

        if ($this->dms!=$dms) $this->setGalileo($dms);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $a=array(
            "pratica"=>$pratica,
            "pren"=>$pren
        );

        $ret=array();
        
        $this->galileo->executeGeneric('odl','getPraticaRif',$a,"");

        if ($this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('odl');
            while ($row=$this->galileo->getFetch('odl',$fid)) {
                $ret[]=$row;
            }
        }

        return $ret;
        
    }

    function getStatiPratica($pratica,$pren) {

        $ret=array();

        //$this->galileo->executeSelect('alert','AVALON_stato_lam',"pratica='".$pratica."' AND pren='".$pren."'",'');
        $this->galileo->executeSelect('alert','AVALON_stato_lam',"pratica IN (".$pratica.")",'');

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('alert');

            while($row=$this->galileo->getFetch('alert',$fid)) {

                if (!isset($ret[$row['rif']])) {
                    $ret[$row['rif']]=array(
                        "cc"=>false,
                        "pren"=>false,
                        "lam"=>array()
                    );
                }

                if ($row['lam']=='') {
                    //se sto guardando le prenotazioni e lo stato è della prenoitazione allora scrivi lo stato in CC
                    if ($pren=='S' && $row['pren']=='S') $ret[$row['rif']]['cc']=$row;
                    //altrimenti scrivi lo stato al posto giusto
                    else {
                        if ($row['pren']=='S') $ret[$row['rif']]['pren']=$row;
                        else $ret[$row['rif']]['cc']=$row;
                    }
                }
                else {
                    $ret[$row['rif']]['lam'][$row['lam']]=$row;
                }
            }
        }

        return $ret;
    }

    function richiesteMateriale($dms,$pren,$rif) {

        if ($this->dms!=$dms) $this->setGalileo($dms);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $a=array(
            "rif"=>$rif,
            "pren"=>$pren
        );
        
        $this->galileo->executeGeneric('odl','richiesteMateriale',$a,"");

        //$this->log=$this->galileo->getLog('query');

        $res=array();

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('odl');
            while ($row=$this->galileo->getFetch('odl',$fid)) {
                $res[$row['num_doc_ordcli']][$row['lam']][$row['riga']]=$row;
            }
        }

        return $res;

        //INFINITY
        /*select
            "dba"."ord_cli"."precodice",
            "dba"."ord_cli"."articolo",
            "dba"."ord_cli"."descr_articolo",
            "dba"."off_richieste"."id_prenotazione",
            "dba"."off_richieste"."id_commessa",
            "dba"."off_richieste"."id_riga" as "riga_rich",
            "dba"."off_richieste"."id_inconveniente",
            "dba"."tord_cli"."id_documento" as "id_documento_cliente",
            "dba"."ord_cli"."id_ordine" as "id_ordine_cliente",
            "dba"."off_richieste"."id_richiesta" as "id_richiesta",
            case when "dba"."ord_cli"."id_ordine_for" is null then 'ST'
            when "dba"."off_richieste"."id_commessa" is not null then 'CO'
            when "dba"."off_richieste"."id_prenotazione" is not null then 'PO'
            when "dba"."off_richieste"."id_preventivo" is not null then 'PR'
            when "UPPER"("dba"."tipi_doc"."provenienza_ordine") = 'OFF' then 'CO' else 'OC' end as "canale",
            "dba"."tord_cli"."tipo_doc" as "tipo_doc_ordcli",
            "dba"."tord_cli"."data_doc" as "data_doc_ordcli",
            "dba"."tord_cli"."num_doc" as "num_doc_ordcli",
            "dba"."tord_cli"."anno" as "anno_ordcli",
            "dba"."tord_cli"."id_cliente" as "id_cliente_ordcli",
            "dba"."ord_cli"."id_riga" as "id_riga_ordcli",
            "dba"."ord_cli"."deposito" as "deposito_ordcli",
            "dba"."ord_cli"."quantita" as "quantita_ordcli",
            "dba"."ord_cli"."qta_eva" as "qta_eva_ordcli",
            "dba"."ord_cli"."qta_dev" as "qta_dev_ordcli",
            "dba"."giacenze"."esist_att" as "giacenza",
            "dba"."giacenze"."qta_imp" as "impegnato",
            "dba"."ord_cli"."id_ordine_for",
            "dba"."ord_for"."quantita" as "quantita_ordfor",
            "dba"."ord_for"."qta_eva" as "qta_eva_ordfor",
            "dba"."ord_for"."qta_dev" as "qta_dev_ordfor"
            from "dba"."tord_cli"
            left outer join "dba"."ord_cli"
            on "dba"."tord_cli"."tipo_doc" = "dba"."ord_cli"."tipo_doc"
            and "dba"."tord_cli"."num_doc" = "dba"."ord_cli"."numero_doc"
            and "dba"."tord_cli"."data_doc" = "dba"."ord_cli"."data_doc"
            and "dba"."tord_cli"."anno" = "dba"."ord_cli"."anno"
            and "dba"."tord_cli"."id_cliente" = "dba"."ord_cli"."id_cliente"
            left outer join "dba"."tipi_doc"
            on "dba"."tord_cli"."tipo_doc" = "dba"."tipi_doc"."codice"
            left outer join "dba"."off_richieste"
            on "dba"."off_richieste"."id_ordine_cli" = "dba"."ord_cli"."id_ordine"
            left outer join "dba"."ord_for" ON  "dba"."ord_cli"."id_ordine_for"="dba"."ord_for"."id_ordine"
            left outer join "dba"."giacenze" ON  "dba"."ord_cli"."precodice"="dba"."giacenze"."precodice" 
            AND "dba"."ord_cli"."articolo"="dba"."giacenze"."articolo" 
            AND "dba"."ord_cli"."deposito"="dba"."giacenze"."deposito"
            
            
        where "dba"."off_richieste"."id_prenotazione"='10114' */

    }

    function getWash() {

        $ret=array();

        $this->setGalileo('nebula');

        $gal=clone ($this->galileo);

        $gal->executeSelect('alert','AVALON_stato_lam',"stato='DL' AND pren='N'",'');

        if ($gal->getResult()) {

            $fid=$gal->preFetch('alert');

            while($row=$gal->getFetch('alert',$fid)) {

                if ($this->dms!=$row['dms']) $this->setGalileo($row['dms']);

                $this->galileo->clearQuery();
                $this->galileo->clearQueryOggetto('default','odl');

                $this->galileo->resetHandler($this->galileo->getPiattaforma('default','odl'));

                $arg=array(
                    "lista"=>"cli",
                    "rif"=>$row['rif'],
                    "d_uscita"=>true
                );

                $this->galileo->executeGeneric('odl','getOdlHead',$arg,'');

                if ($this->galileo->getResult()) {

                    $fid2=$this->galileo->preFetch('odl');

                    while($row2=$this->galileo->getFetch('odl',$fid2)) {

                        if ($row2['d_uscita']!="") continue 2;

                        $row['cod_officina']=$row2['cod_officina'];
                        $row['telaio']=$row2['telaio'];
                        $row['targa']=$row2['targa'];
                        $row['cod_marca']=$row2['cod_marca'];
                        $row['des_marca']=$row2['des_marca'];
                        $row['des_veicolo']=$row2['des_veicolo'];
                        $row['intest_ragsoc']=$row2['intest_ragsoc'];
                        $row['intest_contratto']=$row2['intest_contratto'];
                        //$row['d_uscita']=$row2['d_uscita'];
                    }
                }

                $ret[]=$row;
            }
        }

        return $ret;

    }

    function getCliHeadBudget($dms,$arg,$comest) {

        if ($this->dms!=$dms) $this->setGalileo($dms);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');
        
        $this->galileo->executeGeneric('odl','getCliHeadBudget',$arg,"");

        $res=array();

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('odl');
            while ($row=$this->galileo->getFetch('odl',$fid)) {   
                $res[$row['rif']]=$comest[$row['rif']];
                $res[$row['rif']]['reparto']=$this->repDmsNebula[$dms][$row['cod_officina']];
            }
        }

        return $res;
    }

    function getScontrillo($m) {

        if ($this->dms!=$m['dms']) $this->setGalileo($m['dms']);

        if ($m['ambito']=='OFF') $m['officina']=$this->getDmsRep($m['dms'],$m['repNebula']);
        elseif ($m['ambito']=='MAG') $m['magazzino']=$this->getDmsRep($m['dms'],$m['repNebula']);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');
        
        $this->galileo->executeGeneric('odl','getScontrillo',$m,"");

        $m['result']=$this->galileo->getResult();

        return $m;
    }

    //////////////////////////////////////////////////////////////////////

    function editLam($rif,$lam,$dms,$pren,$param) {

        $param['txt']=trim($param['txt']);

        if (!isset($param['txt']) || $param['txt']=="") return;

        if ($this->dms!=$dms) $this->setGalileo($dms);
    
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        //19.11.2022 l'UPDATE è limitato al solo TESTO 
        $a=array(
            "rif"=>$rif,
            "lam"=>$lam,
            "pren"=>$pren,
            "txt"=>$param['txt']
        );
        
        $this->galileo->executeGeneric('odl','editLamTxt',$a,"");      
    }


    //////////////////////////////////////////////////////////////////////

    function allineaDms($arr) {

        foreach ($arr as $d=>$o) {
            
            if ($this->dms!=$o['dms']) $this->setGalileo($o['dms']);

            foreach ($o['subtot'] as $sub=>$s) {

                if ($s['dispo']>0) {

                    //$this->subrepNebulaDms['concerto'][$row['reparto']][$row['subrep']]=array("subrep"=>$row['concerto'],"officina"=>$row['off_concerto']);

                    if (isset($this->subrepNebulaDms[$o['dms']][$sub])) {

                        /*$a=array(
                            "officina"=>$this->getDmsRep($this->dms,$o['reparto']),
                            "tecnico"=>$sub,
                            "d"=>$d,
                            "dispo"=>round($s['dispo']*0.8)
                        );*/

                        $this->galileo->clearQuery();
                        $this->galileo->clearQueryOggetto('default','odl');

                        $a=array(
                            "officina"=>$this->subrepNebulaDms[$o['dms']][$sub]['officina'],
                            "tecnico"=>$this->subrepNebulaDms[$o['dms']][$sub]['subrep'],
                            "d"=>$d,
                            "dispo"=>round($s['dispo']*0.8)
                        );

                        $this->galileo->executeGeneric('odl','allineaDms',$a,'');
                    }
                }
            }

        }
    }

}

?>