<?php

$this->controllo=array(
    array(
        "titolo"=>"Lavoro eseguito correttamente?",
        "opzioni"=>array("SI",'NO'),
        "valore"=>""
    ),
    array(
        "titolo"=>"Scadenza mantenuta?",
        "opzioni"=>array("SI",'NO'),
        "valore"=>""
    )
);

$this->closure['drawDanni']=function() {

    $this->operazioni=array(
        "10"=>array(
            "tag"=>"Sollevare Bozza",
            "txt"=>"Sollevare Bozza",
            "default"=>0
        ),
        "20"=>array(
            "tag"=>"Ritocco a Pennello",
            "txt"=>"Ritocco a Pennello",
            "default"=>0,
        ),
        "30"=>array(
            "tag"=>"Riverniciare",
            "txt"=>"Riverniciare",
            "default"=>1,
        ),
        "40"=>array(
            "tag"=>"Riparare e Riverniciare",
            "txt"=>"Riparare e Riverniciare",
            "default"=>0
        ),
        "50"=>array(
            "tag"=>"Sostituire",
            "txt"=>"Sostituire",
            "default"=>0
        ),
        "60"=>array(
            "tag"=>"Riparazione Grandine",
            "txt"=>"Riparazione Grandine",
            "default"=>0
        ),
        "70"=>array(
            "tag"=>"Lucidatura",
            "txt"=>"Lucidatura",
            "default"=>0
        ),
        "80"=>array(
            "tag"=>"PARTI CORRELATE",
            "txt"=>"",
            "default"=>0
        )
    );

    $defColor="#ffffffcc;";

    $this->danni=array(
        "1"=>array(
            "tag"=>"Vettura completa",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "10"=>array(
            "tag"=>"Paraurti Anteriore",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "11"=>array(
            "tag"=>"Spoiler Anteriore",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "12"=>array(
            "tag"=>"Fanale Ant. Dx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "13"=>array(
            "tag"=>"Fanale Ant. Sx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "20"=>array(
            "tag"=>"Cofano",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "21"=>array(
            "tag"=>"Parabrezza",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "22"=>array(
            "tag"=>"Tetto",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "30"=>array(
            "tag"=>"Parafango Ant. Sx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "31"=>array(
            "tag"=>"Cerchio Ant. Sx",
            "color"=>"#ff7bfbcc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "40"=>array(
            "tag"=>"Porta Ant. sx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "41"=>array(
            "tag"=>"Vetro Porta Ant. sx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "42"=>array(
            "tag"=>"Calotta Sx",
            "color"=>"#ff7bfbcc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "43"=>array(
            "tag"=>"Porta Post. sx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "44"=>array(
            "tag"=>"Vetro Porta Post. sx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "45"=>array(
            "tag"=>"Sottoporta Sx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "50"=>array(
            "tag"=>"Parafango Post. Sx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "51"=>array(
            "tag"=>"Cerchio Post. Sx",
            "color"=>"#ff7bfbcc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "60"=>array(
            "tag"=>"Paraurti Posteriore",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "61"=>array(
            "tag"=>"Spoiler Posteriore",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "62"=>array(
            "tag"=>"Fanale Post. Sx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "63"=>array(
            "tag"=>"Fanale Post. Dx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "70"=>array(
            "tag"=>"Portellone",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "71"=>array(
            "tag"=>"Lunotto",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "72"=>array(
            "tag"=>"Spoiler Tetto",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "80"=>array(
            "tag"=>"Parafango Post. Dx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "81"=>array(
            "tag"=>"Cerchio Post. Dx",
            "color"=>"#ff7bfbcc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "90"=>array(
            "tag"=>"Porta Post. Dx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "91"=>array(
            "tag"=>"Vetro Porta Post. Dx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "92"=>array(
            "tag"=>"Porta Ant. Dx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "93"=>array(
            "tag"=>"Vetro Porta Ant. Dx",
            "color"=>"#30f5becc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "94"=>array(
            "tag"=>"Calotta Dx",
            "color"=>"#ff7bfbcc;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "95"=>array(
            "tag"=>"Sottoporta Dx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "100"=>array(
            "tag"=>"Parafango Ant. Dx",
            "color"=>"#ffe000dd;",
            "defcolor"=>$defColor,
            "set"=>0
        ),
        "101"=>array(
            "tag"=>"Cerchio Ant. Dx",
            "color"=>"#ff7bfbcc;",
            "defcolor"=>$defColor,
            "set"=>0
        )
    );

    echo '<div style="position:relative;display:inline-block;width:60px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
        echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/muso.png" />';

        echo '<div id="comest_spot_11" data-color="'.$this->danni['11']['color'].'" data-defcolor="'.$this->danni['11']['defcolor'].'" style="position:absolute;top:30%;left:1%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['11']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'11\');" ></div>';
        echo '<div id="comest_spot_10" data-color="'.$this->danni['10']['color'].'" data-defcolor="'.$this->danni['10']['defcolor'].'" style="position:absolute;bottom:30%;right:1%;transform:translate(0,+50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['10']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'10\');" ></div>';        
    echo '</div>';

    echo '<div style="position:relative;display:inline-block;width:320px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
        echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/pianta.png" />';

        echo '<div id="comest_spot_12" data-color="'.$this->danni['12']['color'].'" data-defcolor="'.$this->danni['12']['defcolor'].'" style="position:absolute;top:1%;left:8%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['12']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'12\');" ></div>';
        echo '<div id="comest_spot_13" data-color="'.$this->danni['13']['color'].'" data-defcolor="'.$this->danni['13']['defcolor'].'" style="position:absolute;bottom:1%;left:8%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['13']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'13\');" ></div>';
        echo '<div id="comest_spot_20" data-color="'.$this->danni['20']['color'].'" data-defcolor="'.$this->danni['20']['defcolor'].'" style="position:absolute;top:50%;left:13%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['20']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'20\');" ></div>';
        echo '<div id="comest_spot_94" data-color="'.$this->danni['94']['color'].'" data-defcolor="'.$this->danni['94']['defcolor'].'" style="position:absolute;top:1%;left:34%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['94']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'94\');" ></div>';
        echo '<div id="comest_spot_42" data-color="'.$this->danni['42']['color'].'" data-defcolor="'.$this->danni['42']['defcolor'].'" style="position:absolute;bottom:1%;left:34%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['42']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'42\');" ></div>';
        echo '<div id="comest_spot_21" data-color="'.$this->danni['21']['color'].'" data-defcolor="'.$this->danni['21']['defcolor'].'" style="position:absolute;top:50%;left:29%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['21']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'21\');" ></div>';

        echo '<div id="comest_spot_22" data-color="'.$this->danni['22']['color'].'" data-defcolor="'.$this->danni['22']['defcolor'].'" style="position:absolute;top:50%;left:52%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['22']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'22\');" ></div>';
        echo '<div id="comest_spot_72" data-color="'.$this->danni['72']['color'].'" data-defcolor="'.$this->danni['72']['defcolor'].'" style="position:absolute;top:50%;left:70%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['72']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'72\');" ></div>';
        echo '<div id="comest_spot_71" data-color="'.$this->danni['71']['color'].'" data-defcolor="'.$this->danni['71']['defcolor'].'" style="position:absolute;top:50%;left:85%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['71']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'71\');" ></div>';
    echo '</div>';

    echo '<div style="position:relative;display:inline-block;width:80px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
        echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/coda.png" />';

        echo '<div id="comest_spot_63" data-color="'.$this->danni['63']['color'].'" data-defcolor="'.$this->danni['63']['defcolor'].'" style="position:absolute;top:1%;left:5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['63']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'63\');" ></div>';
        echo '<div id="comest_spot_62" data-color="'.$this->danni['62']['color'].'" data-defcolor="'.$this->danni['62']['defcolor'].'" style="position:absolute;bottom:1%;left:5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['62']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'62\');" ></div>';
        echo '<div id="comest_spot_70" data-color="'.$this->danni['70']['color'].'" data-defcolor="'.$this->danni['70']['defcolor'].'" style="position:absolute;top:50%;left:1%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['70']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'70\');" ></div>';

        echo '<div id="comest_spot_60" data-color="'.$this->danni['60']['color'].'" data-defcolor="'.$this->danni['60']['defcolor'].'" style="position:absolute;top:30%;right:10%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['60']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'60\');" ></div>';
        echo '<div id="comest_spot_61" data-color="'.$this->danni['61']['color'].'" data-defcolor="'.$this->danni['61']['defcolor'].'" style="position:absolute;bottom:30%;right:1%;transform:translate(0,+50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['61']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'61\');" ></div>';
    echo '</div>';

    echo '<div style="position:relative;display:inline-block;width:320px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
        echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/fiancosx.png" />';

        echo '<div id="comest_spot_30" data-color="'.$this->danni['30']['color'].'" data-defcolor="'.$this->danni['30']['defcolor'].'" style="position:absolute;top:45%;left:17.5%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['30']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'30\');" ></div>';
        echo '<div id="comest_spot_31" data-color="'.$this->danni['31']['color'].'" data-defcolor="'.$this->danni['31']['defcolor'].'" style="position:absolute;bottom:10%;left:17.5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['31']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'31\');" ></div>';

        echo '<div id="comest_spot_41" data-color="'.$this->danni['41']['color'].'" data-defcolor="'.$this->danni['41']['defcolor'].'" style="position:absolute;top:20%;left:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['41']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'41\');" ></div>';
        echo '<div id="comest_spot_40" data-color="'.$this->danni['40']['color'].'" data-defcolor="'.$this->danni['40']['defcolor'].'" style="position:absolute;top:60%;left:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['40']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'40\');" ></div>';
        echo '<div id="comest_spot_44" data-color="'.$this->danni['44']['color'].'" data-defcolor="'.$this->danni['44']['defcolor'].'" style="position:absolute;top:20%;left:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['44']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'44\');" ></div>';
        echo '<div id="comest_spot_43" data-color="'.$this->danni['43']['color'].'" data-defcolor="'.$this->danni['43']['defcolor'].'" style="position:absolute;top:60%;left:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['43']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'43\');" ></div>';
        echo '<div id="comest_spot_45" data-color="'.$this->danni['45']['color'].'" data-defcolor="'.$this->danni['45']['defcolor'].'" style="position:absolute;bottom:1%;left:54%;transform:translate(-50%,0);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['45']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'45\');" ></div>';
        echo '<div id="comest_spot_50" data-color="'.$this->danni['50']['color'].'" data-defcolor="'.$this->danni['50']['defcolor'].'" style="position:absolute;top:45%;right:10%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['50']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'50\');" ></div>';
        echo '<div id="comest_spot_51" data-color="'.$this->danni['51']['color'].'" data-defcolor="'.$this->danni['51']['defcolor'].'" style="position:absolute;bottom:10%;right:16%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['51']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'51\');" ></div>';
    echo '</div>';

    echo '<div style="position:relative;display:inline-block;width:320px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
        echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/fiancodx.png" />';

        echo '<div id="comest_spot_100" data-color="'.$this->danni['100']['color'].'" data-defcolor="'.$this->danni['100']['defcolor'].'" style="position:absolute;top:45%;right:17.5%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['100']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'100\');" ></div>';
        echo '<div id="comest_spot_101" data-color="'.$this->danni['101']['color'].'" data-defcolor="'.$this->danni['101']['defcolor'].'" style="position:absolute;bottom:10%;right:17.5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['101']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'101\');" ></div>';
        echo '<div id="comest_spot_93" data-color="'.$this->danni['93']['color'].'" data-defcolor="'.$this->danni['93']['defcolor'].'" style="position:absolute;top:20%;right:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['93']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'93\');" ></div>';
        echo '<div id="comest_spot_92" data-color="'.$this->danni['92']['color'].'" data-defcolor="'.$this->danni['92']['defcolor'].'" style="position:absolute;top:60%;right:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['92']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'92\');" ></div>';
        echo '<div id="comest_spot_91" data-color="'.$this->danni['91']['color'].'" data-defcolor="'.$this->danni['91']['defcolor'].'" style="position:absolute;top:20%;right:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['91']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'91\');" ></div>';
        echo '<div id="comest_spot_90" data-color="'.$this->danni['90']['color'].'" data-defcolor="'.$this->danni['90']['defcolor'].'" style="position:absolute;top:60%;right:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['90']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'90\');" ></div>';     
        echo '<div id="comest_spot_95" data-color="'.$this->danni['95']['color'].'" data-defcolor="'.$this->danni['95']['defcolor'].'" style="position:absolute;bottom:1%;right:46%;transform:translate(-50%,0);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['95']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'95\');" ></div>';
        echo '<div id="comest_spot_80" data-color="'.$this->danni['80']['color'].'" data-defcolor="'.$this->danni['80']['defcolor'].'" style="position:absolute;top:45%;left:10%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['80']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'80\');" ></div>';
        echo '<div id="comest_spot_81" data-color="'.$this->danni['81']['color'].'" data-defcolor="'.$this->danni['81']['defcolor'].'" style="position:absolute;bottom:10%;left:16%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['81']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'81\');" ></div>';
    echo '</div>';
}

?>