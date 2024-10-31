<?php
require_once("avalon_set_day.php");

class avalonSearch extends  avalonSetday {

    function __construct($param,$galileo) {

        parent::__construct($param,$galileo);
        
    }

    function getPren() {

        $this->wh->searchPrenotazioni($this->param['inizio'],$this->param['fine'],$this->param['officina'],$this->param['search']);
        
    }

}

?>