<?php

use phpDocumentor\Configuration\Merger\Annotation\Replace;

$this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,5);
        $this->ribbon->setTitle($this->appTag,'0.1','TDD');

    };
?>