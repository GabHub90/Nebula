<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,'0.3','RM');

        $this->common['expo']=array(
            "magazzino"=>""
        );
        
        $this->common['conv']=array(
            "magazzino"=>"magazzino"
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
            "operatore"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "operatore"=>"none"
        );

        $this->uncommon['expo']=array(
            "operatore"=>""
        );

        $this->uncommon['conv']=array(
            "operatore"=>"operatore"
        );

        $this->uncommon['mappa']=array(
            "operatore"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            )
        );

        //##########################################

        $this->galileo->getCollaboratoriGruppi("'49','30','31'",date('Ymd'));
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('base','maestro');
        if ( $result=$this->galileo->getResult() ) {
            $fetID=$this->galileo->preFetchBase('maestro');
            while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                $this->uncommon['mappa']['operatore']['prop']['options'][$row['concerto']]=$row;
                $this->uncommon['mappa']['operatore']['prop']['options'][$row['concerto']]['disabled']=false;
                if ($row['concerto']==$this->id->getLogged()) $this->uncommon['mappa']['operatore']['prop']['default']=$row['concerto'];
            }
        }
    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>