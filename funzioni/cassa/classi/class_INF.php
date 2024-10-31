<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'0.3','INF');

        $this->common['expo']=array(
            "strillo_cassa"=>""
        );
        
        $this->common['conv']=array(
            "strillo_cassa"=>"strillo_cassa"
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