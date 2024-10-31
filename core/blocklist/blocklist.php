<?php

class BlockList {
	
	protected $index;
	protected $default;
	protected $head="";
	protected $body="";
	protected $arrowPcg=3;
	protected $headH="18px";
	
	function __construct($index,$default) {
		
		$this->index=$index;
		$this->default=$default;
	}

	static function blockListInit() {

        echo '<link href="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/blocklist/style.css" type="text/css" rel="stylesheet"/>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/blocklist/blocklist_js.js?v='.time().'" />';
    }
	
	function setHead($txt) {
		$this->head=$txt;
	}
	
	function setBody($txt) {
		$this->body=$txt;
	}

	function setPcg($x) {
		$this->arrowPcg=$x;
	}

	function setHeadH($h) {
		$this->headH=$h;
	}
	
	function get_rif() {
		return '_blocklist_'.$this->index;
	}
		
	function draw() {
	
		ob_start();
	
			echo '<div style="position:relative;width:100%;height:'.$this->headH.';">';

				echo '<div style="position:relative;display:inline-block;width:'.$this->arrowPcg.'%;">';
					if ($this->default==0) {
						echo '<img id="blocklist_img_'.$this->index.'" style="position:relative;width:70%;height:15px;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/blocklist/img/arrow_right.png" onclick="_blocklist_'.$this->index.'.switch();"/>';
					}
					if ($this->default==1) {
						echo '<img id="blocklist_img_'.$this->index.'" style="position:relative;width:70%;height:15px;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/blocklist/img/arrow_down.png" onclick="_blocklist_'.$this->index.'.switch();"/>';
					}
				echo '</div>';

				echo '<div style="position:relative;display:inline-block;width:'.(95-$this->arrowPcg).'%;">'.$this->head.'</div>';

			echo '</div>';
			
			echo '<div id="blocklist_'.$this->index.'" style="position:relative;width:100%;';
				if ($this->default==0) echo 'display:none;';
				if ($this->default==1) echo 'display:block;';
			echo '">';
				echo $this->body;
			echo '</div>';
			
			echo '<script type="text/javascript">';
				echo 'window._blocklist_'.$this->index.'=new BlockList(\''.$this->index.'\','.$this->default.');';
			echo '</script>';
			
		return ob_get_clean();
	}

}
?>