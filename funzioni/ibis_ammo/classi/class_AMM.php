<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,'0.9','AMM');

        $this->common['expo']=array(
        );
        
        $this->common['conv']=array(
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array();

        $this->uncommon['tipi']=array();

        $this->uncommon['expo']=array();

        $this->uncommon['conv']=array();

        $this->uncommon['mappa']=array();

        //##########################################
    };

    $this->closure['postApp']=function() {

        //26.02.2021 non ci sono condizioni (UNDER CONSTRUCTION)
        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>