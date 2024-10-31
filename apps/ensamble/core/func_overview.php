<?php

$this->closure['drawMain']=function() {
    //elenca tutti i collaboratori di tutti i reparti del macroreparto

    //$this->galileo->getCollaboratori('macroreparto',$this->param['ens_macroreparto'],date('Ymd'));
    //$this->setCollaboratori();

    $this->galileo->getReparti($this->param['ens_macroreparto'],'');
    $result=$this->galileo->getResult();
    $fetID=$this->galileo->preFetchBase('reparti');

    while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
        $this->reparti[$row['reparto']]=$row;
    }

    $this->collaboratori->getCollaboratoriMacro($this->param['ens_macroreparto']);
    $this->ensLista=$this->collaboratori->getCollaboratori();

    echo '<div class="ensOverviewContainer" >';

        echo '<div class="ensOverview">';

            $opt=array(
                "repEdit"=>true,
                "collEdit"=>false
            );

            $this->drawReparti($opt);

        echo '</div>';

    echo '</div>';

}

?>