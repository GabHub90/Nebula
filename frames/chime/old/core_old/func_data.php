<?php

function format_tohtml($txt) {
	return substr($txt,6,2)."/".substr($txt,4,2)."/".substr($txt,0,4);
}

function format_todb($txt) {
	return substr($txt,6,4).substr($txt,3,2).substr($txt,0,2);
}
?>