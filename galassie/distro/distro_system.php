<?php

class distroSystem extends nebulaSystem {

    function __construct($tag,$contesto,$versione,$galileo) {

        parent::__construct($tag,$contesto,$versione,$galileo);

        $this->menuTagColor="cyan";

    }
}

?>