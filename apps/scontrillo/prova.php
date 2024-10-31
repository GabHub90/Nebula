<?php
class K {

    protected $a=0;
    protected $b=0;
    protected $c=array('gatto'=>'felino','cane'=>'canide','giraffa'=>'gxsge');

    function scriviA($x) {
        $a=$x;
        $this->b=$this->b+1;

        echo $this->c['gatto'];
    }

}
?>