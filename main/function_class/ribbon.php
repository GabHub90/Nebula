<?php

require_once(DROOT.'/nebula/main/function_class/ribbon_form.php');

class nebulaRibbon {

    protected $tag="";
    //width %
    protected $wt=100;
    //height %
    protected $ht="";
    //classe
    protected $ribbonClass="nebulaRibbon";
    protected $bodyClass="nebulaFunctionBodyDiv";

    protected $sezioni=array(
        "titolo"=>false,
        "document"=>true,
        "msg"=>true
    );

    protected $titolo="";
    protected $rHT="";
    protected $bHT="";
    protected $bJS="";

    //classe ribbonForm EXTENDS chekko
    protected $ribbonForm;

    function __construct($tag,$w,$h) {
        
        $this->tag=$tag;
        $this->wt=$w;
        $this->ht=$h;

        $this->ribbonForm=new ribbonForm($this->tag);
    }

    function setClass($classe) {
        $this->ribbonClass=$classe;
    }

    function setSezione($sezione,$val) {
        $this->sezioni[$sezione]=$val;
    }

    function setTitle($tag,$version,$txt) {

        //definire le caratteristiche del titolo
        $this->titolo='<div class="ribbonTitle">';
            $this->titolo.='<div style="display:inline-block;">'.$tag.'</div>';
            $this->titolo.='<div style="display:inline-block;margin-left:5px;font-size:smaller;">v&nbsp;'.$version.'</div>';
            $this->titolo.='<div style="display:inline-block;margin-left:5px;">'.$txt.'</div>';
        $this->titolo.='</div>';

        $this->sezioni['titolo']=true;
    }

    function addRibbon($txt) {
        $this->rHT.=$txt;
    }

    function addBody($txt) {
        $this->bHT.=$txt;
    }

    function addJS($txt) {
        $this->bJS.=$txt;
    }

    function export() {

        $ret='<div class="'.$this->ribbonClass.'" style="width:'.$this->wt.'%;height:'.$this->ht.'%;" >';

            if ($this->sezioni['titolo']) {
                //scrivere il DIV del titolo
                $ret.=$this->titolo;
            }

            $ret.='<div>';
                $ret.=$this->rHT;
            $ret.='</div>';

            if ($this->sezioni['document']) {

                $ret.='<img style="width:55px;height:60px;position:absolute;top:50%;right:10px;margin-top:-30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/doc_icon.png" />';
            }

            if ($this->sezioni['msg']) {

                $ret.='<img style="width:55px;height:30px;position:absolute;top:50%;right:100px;margin-top:-5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/msg_icon.png" />';
            }

        $ret.='</div>';

        $ret.='<div id="nebulaFunctionBody_'.$this->tag.'" class="'.$this->bodyClass.'" style="width:'.$this->wt.'%;height:'.(100-$this->ht).'%;" >';
            $ret.=$this->bHT;
        $ret.='</div>';

        $ret.='<div id="nebulaFunctionBody_loader_'.$this->tag.'" class="'.$this->bodyClass.'" style="width:'.$this->wt.'%;height:'.(100-$this->ht).'%;display:none;" >';
            $ret.='<div style="text-align:center;">';
                $ret.='<img style="width:60px;height:60px;margin-top:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/busy.gif" />';
            $ret.='</div>';
        $ret.='</div>';

        $ret.='<script type="text/javascript">';
            $ret.=$this->bJS;
        $ret.='</script>';

        return $ret;
    }

    //##################################################################################
    //scrivere i metodi per configurare ribbonForm
    //##################################################################################

    function loadForm($fields,$tipi,$mappa,$expo,$conv) {
       
        //$this->ribbonForm->load_info($info);
        $this->ribbonForm->add_fields($fields);
        $this->ribbonForm->load_tipi($tipi);
        $this->ribbonForm->load_mappa($mappa);
        $this->ribbonForm->load_expo($expo);
        $this->ribbonForm->load_conv($conv);
    }

    function preBuild($cls) {

        $this->ribbonForm->load_closure($cls);
        $this->ribbonForm->set_closure();  

        ob_start();
            $this->ribbonForm->draw();
        $this->addRibbon(ob_get_clean());
    }

}

?>
