<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'GEN');

        $this->ribbon->setClass('nebulaNoBorderRibbon');

        $this->common['expo']=array(
            "reparto"=>""
        );
        
        $this->common['conv']=array(
            "reparto"=>"reparto"
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
        );

        $this->uncommon['tipi']=array(
        );

        $this->uncommon['expo']=array(
        );

        $this->uncommon['conv']=array(
        );

        $this->uncommon['mappa']=array(
        );
    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>