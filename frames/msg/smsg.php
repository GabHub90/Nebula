<?php
	//error_reporting(E_ERROR | E_PARSE);
	error_reporting(0);
	
	include ("msg.config.php");
	$insert=0;
	$stringa=array_map('addslashes',$_REQUEST);
	$insert=$stringa['insert'];
	$op=0;
	$rep="";
	$op=$_GET["op"];
	$opid=$_GET["id"];
	$opdelta=$_GET["delta"];
	$opfine=$_GET["fine"];
	$rep=$_GET["rep"];
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	
<style type="text/css"> 
.intest {
	font: 14px verdana,tahoma,arial;
	font-weight: 700;
	color: black; 
	background-color:#99aa00;
}
.tipo {
	font: 10px verdana,tahoma,arial;
	font-weight: 700;
	color: black; 
}
.valore {
	font: 12px verdana,tahoma,arial;
	color: black; 
}
.titolo {
	font: 14px verdana,tahoma,arial;
	font-weight: 700;
	color: black; 
}
.error {
	font: 11px verdana,tahoma,arial;
	font-weight: 700;
	color: #FF0000;
}
.info {
	font: 11px verdana,tahoma,arial;
	font-weight: 700;
	color: #0000FF;
}
.tbin {
	width: 800px;
}
.tbbut {
	width: 50px;
}
.tblist {
	border-collapse:collapse;
	border:1px solid black;
}
tr.lista {
	border:1px solid black;
}
</style>

<script type="text/javascript" src="jquery-1.10.2.js"></script>

<script type="text/javascript">

<?php
	echo "sito='".$sito."';";
	echo "_reparto='".(isset($_GET['rep'])?$_GET['rep']:'')."';";
?>

function send(rep) {
	var err=0;
	if (document.frm.nome.value=="") {
		document.getElementById("field1").setAttribute("class","error");
		document.getElementById("field1").innerHTML="Nome - campo obbligatorio";
		err=1;
	}
	else {
		document.getElementById("field1").setAttribute("class","tipo");
		document.getElementById("field1").innerHTML="Nome";
	}
	if (document.frm.richiesta.value=="") {
		document.getElementById("field2").setAttribute("class","error");
		document.getElementById("field2").innerHTML="Richiesta - campo obbligatorio";
		err=1;
	}
	else {
		document.getElementById("field2").setAttribute("class","tipo");
		document.getElementById("field2").innerHTML="Richiesta";
	}
	if (document.frm.recapito.value=="") {
		document.getElementById("field3").setAttribute("class","error");
		document.getElementById("field3").innerHTML="Recapito - campo obbligatorio";
		err=1;
	}
	else {
		document.getElementById("field3").setAttribute("class","tipo");
		document.getElementById("field3").innerHTML="Recapito";
	}
	if (err==0) {
		if (confirm("Vuoi inserire il messaggio per "+rep)) {
			document.frm.insert.value=1;
			document.frm.reparto.value=rep;
			document.frm.submit();
		}
	}
}

function azzera() {
	document.frm.reset();
	document.getElementById("field1").setAttribute("class","tipo");
	document.getElementById("field1").innerHTML="Nome";
	document.getElementById("field2").setAttribute("class","tipo");
	document.getElementById("field2").innerHTML="Richiesta";
	document.getElementById("field3").setAttribute("class","tipo");
	document.getElementById("field3").innerHTML="Recapito";
}

function archivia(id,nome,delta) {
	if (confirm("vuoi archiviare il messaggio di "+nome+"?"))
		location.href = sito+"?op=1&id="+id+"&delta="+delta;
}

function cancella(id,nome) {
	if (confirm("vuoi CANCELLARE il messaggio di "+nome+"?"))
		location.href = sito+"?op=2&id="+id;
}

function veditutti() {
	location.href=sito;
}

function aggiorna(rep) {
	if (rep==0) {
		document.frm.insert.value=0;
		document.frm.submit();
		
	}
	else {
		window.location.replace(sito+"?rep="+rep);
		window.location.reload(true);
	}
}

function inc_att(index,num) {
	
	$.ajax({"url":"core/inc_att.php","async":false,"cache":false, "data":{"index":index,"num":num}, "type":"POST", "success":function(ret) {
		aggiorna(_reparto);
	}});
	
}

</script>
</head>
<body bgcolor=ffffff text=000000>

<!--Intestazione-->
	<div class="intest">
		<p align="center">Service Instant Messaging&nbsp
			<span style="font: 10px verdana,tahoma,arial;">versione 1.2</span>
		</p>
	</div>

<!--Modulo di inserimento-->
	<table width="1000px">
		<tr>
			<td width="600px">
				<form id="frm" name="frm" method="post" action="<?php echo "http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/smsg.php"; ?>">
					<input type="hidden" name="insert" value="0">
					<input type="hidden" name="reparto" value="">
					<table id="tbl" width="90%">
						<tr>
							<td id="field1" class="tipo" widht="30%">
								Nome
							</td>
							<td id="field2" class="tipo">
								Richiesta
							</td>
						</tr>
						<tr>
							<td class="tbin">
								<input class="valore" type="text" size="30" maxlength="30" name="nome">
							</td>
							<td>
								<input class="valore" type="text" size="80" maxlength="80" name="richiesta">
							</td>
						</tr>
						<tr>
							<td id="field3" class="tipo" widht="30%">
								Recapito
							</td>
							<td class="tipo">
								Note
							</td>
						</tr>
						<tr>
							<td class="tbin">
								<input class="valore" type="text" size="30" maxlength="30" name="recapito">
							</td>
							<td>
								<input class="valore" type="text" size="80" maxlength="80" name="note">
							</td>
						</tr>		
					</table>
				</form>
			</td>	
<!--Bottoni-->
			<td width="200px" style="vertical-align:top;">	
				<table>
					<tr>
						<td class="tbbut">
							<button onclick="send('vw')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/vw.jpg" width="40" height="25">' ;?>
							</button>
						</td>
						<td class="tbbut">
							<button onclick="send('audi')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/audi.jpg" width="40" height="25">' ;?>
							</button>
						</td>
					</tr>
					<tr>
						<td class="tbbut">
							<button onclick="send('porsche')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/porsche.jpg" width="40" height="25">' ;?>
							</button>
						</td>
						<td class="tbbut">
							<button onclick="send('upm')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/UPM.png" width="40" height="25">' ;?>
							</button>
						</td>
					</tr>	
					<tr>	
						<td class="tbbut" align="center">
							<button onclick="azzera()"> Azzera </button>
						</td>
					</tr>
				</table>
			</td>
			
			<td width="100px"></td>
			
			<td width="200px" style="vertical-align:top;">	
				<table>
					<tr>
						<td class="tbbut">
							<button onclick="send('vwv')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/vwv.png" width="40" height="25">' ;?>
							</button>
						</td>
						<td class="tbbut">
							<button onclick="send('audiv')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/audiv.png" width="40" height="25">' ;?>
							</button>
						</td>
					</tr>
					<tr>
						<td class="tbbut">
							<button onclick="send('porschev')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/porschev.png" width="40" height="25">' ;?>
							</button>
						</td>
						<td class="tbbut">
							<button onclick="send('upmv')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/UPMV.png" width="40" height="25">' ;?>
							</button>
						</td>
					</tr>
				</table>
			</td>
			
			<td width="100px"></td>
			
			<td width="200px" style="vertical-align:top;">	
				<table>
					<tr>
						<td class="tbbut">
							<button onclick="send('magazzino')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/mag.jpg" width="40" height="25">' ;?>
							</button>
						</td>
					</tr>
					<tr>
						<td class="tbbut">
							<button onclick="send('ancona')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/AN.jpg" width="40" height="25">' ;?>
							</button>
						</td>
					</tr>
					<tr>
						<td class="tbbut">
							<button onclick="send('cesena')">
								<?php echo '<img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/cesena.png" width="40" height="25">' ;?>
							</button>
						</td>
					</tr>		
				</table>
			</td>
			
		</tr>
	</table>
			
	<hr width="100%" color="black" size="2">
	
<!--Inserimento record-->
	
	<table width="1150px">
		<tr>
			<td class="tbin">
				<div class="titolo">
					<span>Messaggi Aperti</span>
					<?php
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=vw"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/vw.jpg" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=audi"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/audi.jpg" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=porsche"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/porsche.jpg" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=upm"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/UPM.png" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=vwv"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/vwv.png" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=audiv"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/audiv.png" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=porschev"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/porschev.png" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=upmv"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/UPMV.png" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=magazzino"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/mag.jpg" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=ancona"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/AN.jpg" width="30" height="18"/></a>';
						echo '<a style="margin-left:10px;" href="'.$ref.'?rep=cesena"><img src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/frames/msg/cesena.png" width="30" height="18"/></a>';
					?>
				</div>
			</td>
			<td align="center" valign="center">
					<?php
						$db = mysqli_connect($db_host, $db_user, $db_password);
					  	if ($db == FALSE)
					  		die(" <div class=\"error\">Server Mysql non raggiungibile -".$db_host."-</div>");
						mysqli_select_db($db,$db_name)
					    	or die (" <div class=\"error\">Errore apertura Database</div>");
					    if ($insert == 1) {
					    	$reparto=$stringa['reparto'];
					    	$inizio=date("Y-m-d H:i:s");
					    	$query = "INSERT INTO messaggi (nome,richiesta,recapito,note,inizio,reparto) VALUES ('$stringa[nome]','$stringa[richiesta]','$stringa[recapito]','$stringa[note]','$inizio','$reparto')";
					    	if (mysqli_query($db,$query))
					    		echo" <div class=\"info\">Inserito messaggio $reparto : $inizio</div>";
					    	else echo" <div class=\"error\">Errore inserimento record</div>";
					    }
					    if ($op==1) {
					    	$query="UPDATE messaggi SET stato=1,delta='$opdelta' WHERE ID=$opid";
					    	if (mysqli_query($db,$query))
					    		echo" <div class=\"info\">Messaggio archiviato</div>";
					    	else echo" <div class=\"error\">Errore aggiornamento record</div>";
					    }
					    if ($op==2) {
					    	$query="UPDATE messaggi SET stato=2 WHERE ID=$opid";
					    	if (mysqli_query($db,$query))
					    		echo" <div class=\"info\">Messaggio cancellato</div>";
					    	else echo" <div class=\"error\">Errore aggiornamento record</div>";
					    }
					?>
			</td>
		</tr>
	</table>
	
<!--Lista messaggi aperti-->
	
	</br>

	<?php
	if ($rep=="")
		$query="SELECT * FROM messaggi WHERE stato=0 ORDER BY inizio ASC";
	else
		$query="SELECT * FROM messaggi WHERE stato=0 AND reparto='".$rep."' ORDER BY inizio ASC";
	$result=mysqli_query($db,$query);

	//echo '<div style="color:black;">'.$query.'</div>';

	?>

	<div style="overflow:scroll;overflow-x:hidden;height:360px;">

		<table class="tblist" style="width:95%;" >
			<tr class="tipo" style="background-color:#cccccc">
				<td width="20%">Nome</td>
				<td width="31%">Richiesta</td>
				<td width="17%">Inizio</td>
				<td width="8%">Tempo</td>
				<td width="7%" align="center">Reparto</td>
				<td width="6%"align="center">Operazioni</td>
				<td width="9%"align="center">Chiamate</td>
				<td width="3%"></td>
			</tr>

			<?php

				while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
					$row=array_map('htmlspecialchars',$row);
					echo"<tr class=\"lista\"><td widht=\"20%\"><div class=\"valore\">";
					echo"$row[nome]</div><div class=\"tipo\">";
					echo"$row[recapito]</div>";
					echo"</td>";
					echo"<td width=\"31%\"><div class=\"valore\">";
					echo"$row[richiesta]</div><div class=\"tipo\">";
					echo"$row[note]</div>";
					echo"</td>";
					if (date("Y-m-d",strtotime($row['inizio']))==date("Y-m-d"))
						echo"<td width=\"17%\"><div class=\"info\">";
					else echo"<td width=\"17%\"><div class=\"error\">";
					echo"$row[inizio]";
					echo"</td>";
					echo"<td width=\"8%\"><div ";
					$tempo1=strtotime($row['inizio']);
					$adesso=strtotime("-1 hours");
					$delta=$adesso-$tempo1;
					$strdelta=date("H:i:s",$delta);
				// cambio colore dopo 20 minuti
					if (($adesso-(strtotime("-40 minutes",$tempo1)))<0)
						echo"class=\"info\">$strdelta</div>";
					else echo"class=\"error\">$strdelta</div>";
					echo"<td width=\"7%\"><div class=\"valore\" align=\"center\">";
					$ref="$sito?rep=$row[reparto]";
					if ($row['reparto']=="vw") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/vw.jpg\" width=\"30\" height=\"18\">";
					if ($row['reparto']=="audi") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/audi.jpg\" width=\"35\" height=\"20\">";
					if ($row['reparto']=="porsche") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/porsche.jpg\" width=\"35\" height=\"22\">";
					if ($row['reparto']=="magazzino") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/mag.jpg\" width=\"35\" height=\"22\">";
					if ($row['reparto']=="vwv") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/vwv.png\" width=\"30\" height=\"18\">";
					if ($row['reparto']=="audiv") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/audiv.png\" width=\"35\" height=\"20\">";
					if ($row['reparto']=="porschev") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/porschev.png\" width=\"35\" height=\"22\">";
					if ($row['reparto']=="upm") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/UPM.png\" width=\"35\" height=\"20\">";
					if ($row['reparto']=="upmv") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/UPMV.png\" width=\"35\" height=\"22\">";
					if ($row['reparto']=="ancona") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/AN.jpg\" width=\"35\" height=\"22\">";
					if ($row['reparto']=="cesena") $img="<img src=\"http://".$_SERVER['SERVER_ADDR']."/nebula/frames/msg/cesena.png\" width=\"35\" height=\"22\">";
					echo"<a href=\"$ref\">$img</a>";
					$row=array_map('addslashes',$row);
					echo"<td width=\"6%\"><button onclick=\"archivia($row[ID],'$row[nome]','$strdelta')\">";
					echo"Archivia</button>";
					echo"</td>";
					
					echo '<td width="9%">';
						echo '<div style="text-align:center;">';
							echo '<span>'.$row['attempts'].'</span>';
							echo '<img style="width:10px;height:10px;margin-left:10px;cursor:pointer;" src="extra.png" onclick="inc_att('.$row['ID'].','.$row['attempts'].');"/>';
						echo '</div>';
						echo '<div style="text-align:center;font-size:8pt;">';
							echo $row['last'];
						echo '</div>';
					echo '</td>';
					
					echo"<td width=\"3%\"><button class=\"error\" onclick=\"cancella($row[ID],'$row[nome]')\">";
					echo"C</button>";
					echo"</td></tr>";
				}	
				mysqli_close($db);
			?>
		</table>
	
	</div>

	<div class="info" style="color:#889900">
		<?php
			$ora=date("Y-m-d H:i:s");
			echo"Lista aggiornata il $ora ";
		
		echo"<button onclick=\"aggiorna('$rep')\">Aggiorna</button>";
		echo"<button onclick=\"veditutti()\">Vedi tutti</button>";
		?>
	</div>
</body>
</html>