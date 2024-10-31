<?php
class ermesChat {

    protected $id=0;
    protected $lista=array();

    protected $stat=array(
        "numQ"=>0,
        "lastQ"=>"",
        "numA"=>0,
        "lastA"=>"",
        "numE"=>0,
        "lastE"=>"",
        "lastone"=>""
    );

    protected $css=array(
        "QbColor"=>"#ffffff",
        "AbColor"=>"#eafaea",
        "EbColor"=>"#ffffff",
        "QbdColor"=>"#ec8830",
        "AbdColor"=>"#26c726",
        "EbdColor"=>"#999999",
        "QbdPx"=>"2",
        "AbdPx"=>"2",
        "EbdPx"=>"2",
        "AnoteColor"=>"#006400"
    );

    protected $galileo;

    function __construct($id,$css,$galileo) {
        
        $this->id=$id;
        $this->galileo=$galileo;

        foreach ($this->css as $k=>$i) {
            if (array_key_exists($k,$css)) $this->css[$k]=$css[$k];
        }

        $this->galileo->executeSelect('ermes','ERMES_chat',"ID='".$this->id."'","riga");

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('ermes');
            while ($row=$this->galileo->getFetch('ermes',$fid)) {
                $this->lista[$row['riga']]=$row;

                $this->stat["num".$row['tipo']]++;
                $this->stat["last".$row['tipo']]=$row['dataora'];
                $this->stat["lastone"]=$row['dataora'];
            }
        }

    }

    function getStat($elem) {

        if($elem=='') return $this->stat;
        else return $this->stat[$elem];

    }

    function buildPanorama() {

        //recupera l'ultimo "Q", l'ultimo "A" e l'ultimo "E"
        //Q=question , A=answer , E=event
        //#########################################
        //TEST
        /*$row=array(
            "Q"=>array(
                "chat"=>1,
                "line"=>1,
                "tipo"=>"Q",
                "d_creazione"=>"20230831:11:21",
                "testo"=>"Appuntamento tagliando Golf GD456RD"
            ),
            "A"=>array(
                "chat"=>1,
                "line"=>2,
                "tipo"=>"A",
                "d_creazione"=>"20230831:13:40",
                "testo"=>"Sarà contattato al più presto da unmio collega"
            )
        );*/
        //#########################################

        $res=array(
            'Q'=>false,
            'A'=>false,
            'E'=>false
        );

        foreach ($this->lista as $k=>$l) {

            if ($l['tipo']=='Q' && !$res['Q']) $res['Q']=$k;

            elseif ($l['tipo']=='A') {
                $res['A']=$k;
                $res['E']=false;
            }

            elseif ($l['tipo']=='E') {
                $res['E']=$k;
                $res['A']=false;
            }

        }

        return $res;
    }

    function drawBubble($b,$flag) {

        //$flag = TRUE = no panorama
        if (!$flag) {
            if (strlen($b['testo'])>25) $b['testo']=substr($b['testo'],0,25).'...';
        }

        if ($b['tipo']!='E') {

            if (!$flag) {
                echo '<div style="position:'.($b['tipo']=='A'?'absolute':'relative').';width:95%;top:0px;border:'.$this->css[$b['tipo'].'bdPx'].'px solid '.$this->css[$b['tipo'].'bdColor'].';border-radius:10px;padding:5px;box-sizing:border-box;';
                    echo 'background-color:'.$this->css[$b['tipo'].'bColor'].';';
                    if ($b['tipo']=='A') echo 'right:0px;';
                echo '">';
            }
            else {
                echo '<div style="position:relative;width:95%;top:0px;border:'.$this->css[$b['tipo'].'bdPx'].'px solid '.$this->css[$b['tipo'].'bdColor'].';border-radius:10px;padding:5px;box-sizing:border-box;';
                    echo 'background-color:'.$this->css[$b['tipo'].'bColor'].';';
                    if ($b['tipo']=='A') echo 'margin-left:5%;';
                echo '">';
            }

                if ($flag) {
                    echo '<div style="font-size:0.8em;font-weight:bold;">';
                        echo $b['utente'].' - '.mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($b['dataora'],0,8)))).' '.mainFunc::gab_todata(substr($b['dataora'],0,8)).' '.substr($b['dataora'],9,5);
                    echo '</div>';
                }

                echo '<div style="text-align:center;" >'.(($flag)?nl2br($b['testo']):$b['testo']).'</div>';

            echo '</div>';
        
        }
        else {
            echo '<div style="position:relative;width:100%;padding:3px;box-sizing:border-box;text-align:center;" >';
                echo $b['testo'];
            echo '</div>';
        }
    }

    function drawPanorama() {

        $res=$this->buildPanorama();

        echo '<div style="position:relative;margin-top:5px;padding:3px;box-sizing:border-box;color:black;">';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;" >';
                if ($res['Q']) $this->drawBubble($this->lista[$res['Q']],false);
            echo '</div>';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;" >';
                if ($res['A']) $this->drawBubble($this->lista[$res['A']],false);
                if ($res['E']) $this->drawBubble($this->lista[$res['E']],false);
            echo '</div>';
        echo '</div>';
    }

    function draw() {
        
        echo '<div style="position:relative;margin-top:5px;padding:3px;box-sizing:border-box;color:black;width:95%;">';

            foreach ($this->lista as $k=>$l) {

                switch($l['tipo']) { 

                    case 'Q':
                        echo '<div style="position:relative;width:70%;margin-top:5px;margin-bottom:5px;" >';
                            $this->drawBubble($l,true);
                        echo '</div>';
                    break;

                    case 'A':
                        echo '<div style="position:relative;width:70%;left:30%;margin-top:5px;margin-bottom:5px;" >';
                            $this->drawBubble($l,true);
                        echo '</div>';
                    break;

                    case 'E':
                        echo '<div style="position:relative;width:100%;margin-top:5px;margin-bottom:5px;background-color:beige;font-weight:bold;" >';
                            $this->drawBubble($l,true);
                        echo '</div>';
                    break;
                
                }

            }

        echo '</div>';
    }

    function newBubble($tipo,$logged,$comp) {
        //comp = COMPARAZIONE = '' se tipo A
        //comp = utente gestore se tipo 'Q'

        switch($tipo) { 

            case 'Q':
                echo '<div style="position:relative;width:70%;text-align:center;margin-top:10px;" >';
                    $this->drawInsert($tipo,$logged,$comp);
                echo '</div>';
            break;

            case 'A':
                echo '<div style="position:relative;width:70%;left:30%;text-align:center;margin-top:10px;" >';
                    $this->drawInsert($tipo,$logged,$comp);
                echo '</div>';
            break;
        
        }

    }

    function drawInsert($tipo,$logged,$comp) {

        echo '<div style="position:'.($tipo=='A'?'relative':'relative').';width:95%;top:0px;border:'.$this->css[$tipo.'bdPx'].'px solid '.$this->css[$tipo.'bdColor'].';border-radius:10px;padding:5px;box-sizing:border-box;';
            echo 'background-color:'.$this->css[$tipo.'bColor'].';text-align:center;margin-bottom:20px;';
            if ($tipo=='A') echo 'left:5%;';
        echo '">';

            $temp=array(
                "ID"=>$this->id,
                "tipo"=>$tipo,
                "utente"=>$logged,
                "comp"=>$comp
            );

            echo '<textarea id="chat_form_messaggio" style="text-align:center;width:90%;resize:none;" rows="3" data-info="'.base64_encode(json_encode($temp)).'" onkeyup="window._ermesTicket.checkChat();" />';


            if ($tipo=='A' && $logged!=$comp) {
                echo '<div style="position:relative;margin-top:10px;width:100%;text-align:center;color:'.$this->css['AnoteColor'].';font-weight:bold;font-size:0.9em;">';
                    echo "L'invio genererà un cambio di gestione...";
                echo '</div>';
            }

            echo '<div style="position:relative;margin-top:10px;width:90%;text-align:right;height:20px;margin-bottom:5px;margin-left:5%;">';
                echo '<span id="chat_form_error" style="color:red;font-weight:bold;margin-right:10px;"></span>';
                echo '<button id="chat_form_button" style="display:none;" onclick="window._ermesTicket.confirmChat();" >Invia</button>';
            echo '</div>';

        echo '</div>';

    }

}

?>