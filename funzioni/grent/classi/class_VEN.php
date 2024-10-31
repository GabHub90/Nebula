<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,10);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'VEN');

        $this->common['expo']=array(
            "tipoRent"=>""
        );
        
        $this->common['conv']=array(
            "tipoRent"=>"tipoRent"
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