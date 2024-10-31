<?php
class mainFunc {

	public static function gab_tots($str) {
		// YYYYMMDD to unix timestamp
		if ($str=='') return 0;
		return mktime(0,0,0,(int)substr($str,4,2),(int)substr($str,6,2),(int)substr($str,0,4));
	}

	public static function gab_totsmin($str) {
		// YYYYMMDD:HH:II to unix timestamp
		if ($str=='') return 0;
		return mktime((int)substr($str,9,2),(int)substr($str,12,2),0,(int)substr($str,4,2),(int)substr($str,6,2),(int)substr($str,0,4));
	}

	public static function gab_weektotag($w) {

		$tag=array('Domenica','Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato');

		return $tag[$w];
	}

	public static function gab_monthtotag($m) {

		$mesi=array('Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');

		return $mesi[$m];
	}
	
	public static function gab_todb($str) {
		// DD-MM-YYYY to YYYYMMDD 
		if ($str=='') return $str;
		return substr($str,6,4).substr($str,3,2).substr($str,0,2);
	}

	public static function gab_input_to_db($str) {
		// YYYY-MM-DD to YYYYMMDD 
		if ($str=='') return $str;
		return substr($str,0,4).substr($str,5,2).substr($str,8,2);
	}
	
	public static function gab_todata($str) {
		// YYYYMMDD to DD/MM/YYYY
		if ($str=='') return $str;
		return substr($str,6,2)."/".substr($str,4,2)."/".substr($str,0,4);
	}
	
	public static function gab_toinput($str) {
		// YYYYMMDD to YYYY-MM-DD
		if ($str=='') return $str;
		//return substr($str,6,2)."-".substr($str,4,2)."-".substr($str,0,4);
		return substr($str,0,4)."-".substr($str,4,2)."-".substr($str,6,2);
	}
	
	public static function gab_stringtomin($str) {
		// HH:MM to minuti totali
		if ($str=='') return 0;
		return ((int)substr($str,0,2)*60)+(int)substr($str,3,2);
	}
	
	public static function gab_mintostring($min) {
		// minuti to HH:MM
		$h=floor($min/60);
		$m=$min-($h*60);
		
		$str="";
		
		if ($h<10) $str.='0';
		$str.=$h.':';
		if ($m<10) $str.="0";
		$str.=$m;
		
		return $str;
	}

	public static function gab_delta_min($t1,$t2) {
		//$t2>$t1 (HH:MM)
		//restituisce differenza in minuti
		
		$i=explode(':',$t1);
        $f=explode(':',$t2);

        $delta=((int)$f[0]*60+(int)$f[1])-((int)$i[0]*60+(int)$i[1]);

		return $delta;
	}
	
	public static function gab_delta_tempo ($d1,$d2,$unita) {
		//d2>d1 (YYYYMMDD)
	 
	 	$data1 = strtotime(substr($d1,0,4).'-'.substr($d1,4,2).'-'.substr($d1,6,2));
	 	$data2 = strtotime(substr($d2,0,4).'-'.substr($d2,4,2).'-'.substr($d2,6,2));
	 
		switch($unita) {
			case "s": $unita = 1/3600; break;	//SECONDI
			case "m": $unita = 1/60; break; 	//MINUTI
			case "h": $unita = 1; break;		//ORE
			case "g": $unita = 24; break;		//GIORNI
			case "a": $unita = 8760; break;     //ANNI
		}
	 
	 	$differenza = round((($data2-$data1)/3600)/$unita);
	 	return $differenza;
	}
	
	public static function gab_delta_tempo_2 ($d1,$d2,$unita) {
		//d2>d1
		//AAAAMMDD:HH:MM:SS
	 
	 	$data1 = strtotime(substr($d1,0,4).'-'.substr($d1,4,2).'-'.substr($d1,6,2).' '.substr($d1,9,8));
	 	$data2 = strtotime(substr($d2,0,4).'-'.substr($d2,4,2).'-'.substr($d2,6,2).' '.substr($d2,9,8));
	 
		switch($unita) {
			case "s": $unita = 1/3600; break;	//SECONDI
			case "m": $unita = 1/60; break; 	//MINUTI
			case "h": $unita = 1; break;		//ORE
			case "g": $unita = 24; break;		//GIORNI
			case "a": $unita = 8760; break;     //ANNI
			case "ut" : $unita = 1/100; break;	//UT
		}
	 
	 	$differenza = round((($data2-$data1)/3600)/$unita);
	 	return $differenza;
	}

	public static function gab_delta_tempo_c($d1,$d2,$unita) {
		//d2>d1
		//AAAAMMDD:HH:MM:SS
	 
	 	$data1 = strtotime(substr($d1,0,4).'-'.substr($d1,4,2).'-'.substr($d1,6,2).' '.substr($d1,9,8));
	 	$data2 = strtotime(substr($d2,0,4).'-'.substr($d2,4,2).'-'.substr($d2,6,2).' '.substr($d2,9,8));
	 
		switch($unita) {
			case "s": $unita = 1/3600; break;	//SECONDI
			case "m": $unita = 1/60; break; 	//MINUTI
			case "h": $unita = 1; break;		//ORE
			case "g": $unita = 24; break;		//GIORNI
			case "a": $unita = 8760; break;     //ANNI
			case "ut" : $unita = 1/100; break;	//UT
		}
	 
		$differenza = floor((($data2-$data1)/3600)/$unita);
		if ($differenza<0) $differenza=0;

	 	return $differenza;
	}

	public static function calcolaScadenza($ts,$s) {
		//$ts=TIMESTAMP di YYYYMMDD:HH:ii
		//$s=termini scadenza DD:HH:ii

		$rif=$ts;
		$temp=explode(':',$s);

		if ((int)$temp[0]!=0) $rif=strtotime('+'.((int)$temp[0]).' day',$rif);
		if ((int)$temp[1]!=0) $rif=strtotime('+'.((int)$temp[1]).' hour',$rif);
		if ((int)$temp[2]!=0) $rif=strtotime('+'.((int)$temp[2]).' minutes',$rif);

		return date('Ymd:H:i',$rif);
	}

	public static function replace_unicode_escape_sequence($match) {
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}

	public static function unicode_decode($str) {
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'mainFunc::replace_unicode_escape_sequence', $str);
	}

	public static function errorHandler($errno, $errstr, $errfile, $errline) {
		// Stampa un messaggio di errore personalizzato
		echo "Si è verificato un errore: $errstr";
	
		// Puoi fare altre operazioni qui, ad esempio registrare l'errore in un file di log
	
		// Interrompi l'esecuzione dello script
		exit();
	}

	public static function nebulaIncludePart($file,$separator,$tag,$index) {
		//separa un file PHP in più blocchi in base alla stringa separatrice
		//poi seleziona il blocco $index in base al blocco //<$tag>$index</$tag> contenuto nel file
		//e restituisce il blocco

		$txt_file = file_get_contents($file);
		$blocks = explode($separator, $txt_file);
		//toglie il primo array con il tag "<?php"
		array_shift($blocks);

		$len=strlen($tag);

		//$log=array();

		foreach ($blocks as $b) {
			$tempVar="";
			$tagStart=strpos($b,'<'.$tag.'>')+$len+2;
			$tagEnd=strpos($b,'</'.$tag.'>');
			$tempVar=substr( $b,$tagStart,($tagEnd-$tagStart) );
			
			//$log[]=$tempVar;
			//$log[]=$tagStart;
			//$log[]=$tagEnd;
			if ($tempVar==$index) return $b;
		}

		//return $log;

	}

	public static function adjustBrightness($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));
	
		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}
	
		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';
	
		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}
	
		return $return;
	}

	/**
	 * Using json_encode() with some character sets can result in false being returned.
	 * These functions allow creation of JSON strings from any value/object that may need
	 * UTF-8 encoding.
	 */

	/*public static function utf8Adjust($input, $encode=TRUE) {
		if (preg_match('//u', $input) === 1) {
		// Already using UTF-8, no encoding to do
	
		if ($encode) return $input;
		} else {
		// Not UTF-8, no decoding to do
	
		if (!$encode) return $input;
		}
	
		$output = '';
	
		// Copy input string characters until we get to 's' - a string, or 'O' - a class name
		for ($i=0; $i<strlen($input); ++$i) {
			$output .= $input{$i};
		
			if ($input{$i} === 's' || $input{$i} === 'O') {
				++$i; // ':'
		
				// After 's:' or 'O:' is the length
				$length = '';
		
				while ($input{++$i} !== ':') {
					$length .= $input{$i};
				}
		
				$length = (int) $length;
		
				$i += 2; // ':"'
		
				// Now we can get the string itself
				$string = substr($input, $i, $length);
		
				// We can use this function to encode and decode
				$string = ($encode) ? utf8_encode($string) : utf8_decode($string);
		
				// Join everything back together
				$output .= ':' . strlen($string) . ':"' . $string . '"';
		
				$i += $length;
			}
		}
	
		return $output;
	}
	
	public static function utf8Encode($input) {
		return unserialize(MainFunc::utf8Adjust(serialize($input), TRUE));
	}
	
	public static function utf8Decode($input) {
		return unserialize(MainFunc::utf8Adjust(serialize($input), FALSE));
	}
	
	public static function jsonEncode($input) {
		return json_encode(MainFunc::utf8Encode($input));
	}
	
	public static function jsonDecode($input, $assoc=FALSE, $depth=512, $options=0) {
		return MainFunc::utf8Decode(json_decode($input, $assoc, $depth, $options));
	}*/
	
}
?>	