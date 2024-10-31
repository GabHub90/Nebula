<?php
include_once("inofficina.php");


class wsInofficina extends ideskInofficina {

    function __construct($param,$galileo) {

        parent::__construct($param,$galileo);
    }

    function drawInofficina($split) {

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        foreach ($this->pratiche as $id=>$p) {

            //ob_start();

                $txt='<div id="nebula_pratica_'.$id.'" data-marca="'.$p['marca'].'" data-pratica="'.$p['id_pratica'].'" style="position:relative;border:2px solid #777777;box-sizing:border-box;margin-top:2px;margin-bottom:10px;padding:2px;';
                    if (isset($this->param['marca']) && $this->param['marca']!="") {
                        if ($this->param['marca']!=$p['marca']) $txt.= 'display:none;';
                    }
                $txt.='" >';

                    ob_start();
                        $mainstato=$this->drawLine($id,$p);
                    $txt.=ob_get_clean();

                $txt.='</div>';

            //se lo split non è da fare
            if (!$split) {
                echo $txt;
            }
            elseif (!$p['pratica']->getPresenza()) {
                $this->sospeso.=$txt;
            }
            elseif ($mainstato=='EX') {
                $this->sospeso.=$txt;
                //ob_end_clean();
            }

            elseif ($mainstato=='SO') {
                $this->sospeso.=$txt;
                //ob_end_clean();
            }

            elseif ($mainstato=='RO') {
                $this->sospeso.=$txt;
                //ob_end_clean();
            }

            else {
                echo $txt;
            }

            unset($txt);
        }

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        //echo '<div>'.json_encode($this->log).'</div>';
    }

}

?>