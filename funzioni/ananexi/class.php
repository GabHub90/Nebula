<?php

class ananexi extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Ananexi';

        $this->suffix=array('TDD');
        
    }

    function setClass() {
        //05.02.2021 per il momento è fisso a TDD ma questo metodo contiene la logica con la quale
        //viene scelto il file di inizializzazione della classe
        $this->loadClass('TDD');

        //TABELLARE IN GAB500 ????? la combinazione GALASSIA - SISTEMA - FUNZIONE 
        //+ (macroreparto,reparto,gruppo,ID) per ottenere --> SUFFISSO della classe da includere

    }

    function prova() {

        $this->addBody('<div>'.json_encode($this->contesto).'</div>');

        ob_start();

        try {
            $dbh = new PDO("mysql:host=172.19.10.10;port=3306;dbname=cdrasterisk", "partner", "Centrocomp01", array());
        }
        catch (PDOException $e) {
            echo "\nPDO::errorInfo():\n";
            print $e->getMessage();
        }

        $query="SELECT * from cdr limit 1 ";

        try {
			$sth = $dbh->query($query);
		}
		catch (PDOException $e) {
			print $e->getMessage();
        }
        
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
        }
        
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode($sth);
        }

        $txt=ob_get_clean();

        $this->addBody('<div>'.$txt.'</div>');

        echo $this->export();
    }

}
?>