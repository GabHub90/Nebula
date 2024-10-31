<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'0.4','TDD');

        $this->common['expo']=array(
            "ens_macroreparto"=>"",
            "ens_today"=>"",
            "ens_reparto"=>""
        );
        
        $this->common['conv']=array(
            "ens_macroreparto"=>"macroreparto",
            "ens_today"=>"today",
            "ens_reparto"=>"reparto"
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array();

        $this->uncommon['tipi']=array();

        $this->uncommon['expo']=array();

        $this->uncommon['conv']=array();

        //###############################################################

        $this->uncommon['mappa']=array();
        
    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>