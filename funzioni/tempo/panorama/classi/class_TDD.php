<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'3.0.9','TDD');

        $this->common['expo']=array(
            "tpo_macroreparto"=>"",
            "tpo_today"=>"",
            "tpo_reparto"=>"",
            "tpo_coll"=>""
        );
        
        $this->common['conv']=array(
            "tpo_macroreparto"=>"macroreparto",
            "tpo_today"=>"today",
            "tpo_reparto"=>"reparto",
            "tpo_coll"=>"coll"

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

        //###############################################################

        $this->uncommon['mappa']=array(
           
        );
        
    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>