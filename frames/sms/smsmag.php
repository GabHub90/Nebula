<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(0);

include ("config.php");
include ("top.php");
//leggi database
$db = mysqli_connect($db_host, $db_user, $db_password);
if ($db == FALSE) die ("Errore nella connessione. Verificare i parametri nel file config.inc.php");
mysqli_select_db($db,$db_name)
	or die ("Errore nella selezione del database. Verificare i parametri nel file config.inc.php");
top();
?>
<script type="text/javascript" src="outsms.js">
</script>
<script type="text/javascript" src="script.js">
</script>
<script type="text/javascript">
	restoreData="";
	restoreH="";
	restoreM="";
	modelli=new Array();
	modelloAttuale=0;
</script>
</head>
<body onload="calcolaLunghezza();">
	<div class="cornicemag">SMS - Magazzino
	</div>
	<!--scelta dei modelli-->
	<div id="modelli">
		<?php
			$query="SELECT * FROM modellimag WHERE stato=1";
			$modelli=mysqli_query($db,$query);
			$x=0;
			while ($row=mysqli_fetch_array($modelli)) {
				echo"<button class=\"modello\" onclick=\"cambiaModello($x);\">$row[nome]</button>";
				echo"<script type=\"text/javascript\">";
				$row[corpo]=addslashes($row[corpo]);
				echo"modelli[$x]=new Array('$row[nome]','$row[corpo]','$row[data]','$row[ora]','$row[hr]','$row[min]','$row[extra]');";
				echo"</script>";
				$x += 1;
			}
		?>
	</div>
	<!--tabella form-->
	<form id="messaggio" onsubmit="return false">
		<table>
			<colgroup>
				<col span="1" width="20"/>
				<col span="1" width="120"/>
				<col span="1" width="200"/>
				<col span="1" width="550"/>
				<col span="1" width="80"/>
			</colgroup>
			<thed>
				<tr>
					<th></th>
					<th></th>
					<th>Opzioni</th>
					<th>Contenuto</th>
					<th>Chr.</th>
				</tr>
			</thed>
			<tbody>
				<!--saluto-->
				<tr>
					<td>
						<input id="saluto" type="checkbox" name="saluto" value="true" checked="checked" onchange="switchSaluto();"/>
					</td>
					<td>
						<span class="parte">Saluto</span>
					</td>
					<td>
						<div id="saluto_opz">
							<table class="opzioni" width="100%">
								<tr>
									<td class="tab" width="33%">Auto
										</br>
										<input type="radio" name="saluto_ra" form="messaggio" value="auto" checked="checked" onchange="modificaSaluto();"/>
									</td>
									<td class="tab" width="33%">AM
										</br>
										<input type="radio" name="saluto_ra" form="messaggio" value="am" onchange="modificaSaluto();"/>
									</td>
									<td class="tab" width="33%">PM
										</br>
										<input type="radio" name="saluto_ra" form="messaggio" value="pm" onchange="modificaSaluto();"/>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td>
						<div id="saluto_cont" class="contenuto">
							<?php
								$ora=getdate();
								if (((int)$ora["hours"])>14) $saluto="Buonasera";
								else $saluto="Buongiorno";
								echo $saluto;
							?>
						</div>
						<?php
							echo "<input id=\"salutoval\" type=\"hidden\" value=\"".$saluto."\"/>";
						?>
					</td>
					<td>
						<div class="chr">
							<div id="saluto_chr">
								<?php
									$y=strlen($saluto)+1;
									echo $y;
								?>
							</div>
						</div>
						<?php
							echo "<input id=\"salchr\" type=\"hidden\" value=\"".$y."\"/>";
						?>
					</td>
				</tr>
				<!--intestazione-->
				<tr>
					<td>
						<input id="intest" type="checkbox" name="intest" value="true" onchange="switchIntest();"/>
					</td>
					<td>
						<span class="parte">Intestazione</span>
					</td>
					<td>
						<div id="intest_opz" style="display:none;">
							<table class="opzioni" width="100%">
								<tr>
									<td class="tab" width="33%"> Sig.
										</br>
										<input type="radio" name="intest_ra" value="m" checked="checked" onchange="modificaIntest();"/>
									</td>
									<td class="tab" width="33%">Sig.ra
										</br>
										<input type="radio" name="intest_ra" value="f" onchange="modificaIntest();"/>
									</td>
									<td class="tab" width="33%">Nessuna
										</br>
										<input type="radio" name="intest_ra" value="n" onchange="modificaIntest();"/>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td class="contenuto">
						<div id="intest_cont" style="display:none;">
							<span id="sesso" style="margin-left:15px;">Sig.</span>
							<input type="text" id="cliente" length="20" maxlength="20" style="margin-left:15px;font-size:16px;" value="" onkeypress="calcolaLunghezza();" onblur="calcolaLunghezza();"/>
							<span id="clierror" class="error" style="visibility:hidden;">COMPILA IL NOME</span>
						</div>
						<input id="sessoval" type="hidden" value="Sig."/>
					</td>
					<td>
						<div class="chr">
							<div id="intest_chr" style="color:#000000;display:none;"></div>
						</div>
						<input id="intchr" type="hidden" value=""/>
					</td>
				</tr>
				<!--corpo-->
				<tr>
					<td>
					</td>
					<td>
						<span class="parte">Corpo</span>
					</td>
					<td>
						<div id="corpo_opz" class="corpo">
							Libero
						</div>
						<div id="corpoerror" class="error" style="visibility:hidden;">
							COMPILA IL MESSAGGIO
						</div>
					</td>
					<td class="contenuto">
						<div id="corpo_cont">
							<textarea id="corpo" form="messaggio" cols="60" rows="4" maxlength="140" style="margin-left:15px;margin-top:10px;font-size:16;" value="" onkeypress="calcolaLunghezza();" onblur="calcolaLunghezza();"></textarea>
						</div>
					</td>
					<td>
						<div class="chr2">
							<div id="corpo_chr">
							</div>	
						</div>
						<input id="corchr" type="hidden" value="0"/>
					</td>
				</tr>
				<!--data-->
				<tr>
					<td>
						<input id="data" type="checkbox" name="data" value="true" onchange="switchData();"/>
					</td>
					<td>
						<span class="parte">Data</span>
					</td>
					<td>
						<div id="data_opz" style="display:none;">
							<table class="opzioni" width="100%">
								<tr>
									<td class="tab" width="33%">Oggi
										</br>
										<input type="radio" name="data_ra" value="o" onchange="modificaOptData();"/>
									</td>
									<td class="tab" width="33%">Domani
										</br>
										<input type="radio" name="data_ra" value="d" onchange="modificaOptData();"/>
									</td>
									<td class="tab" width="33%">Altra
										</br>
										<input type="radio" name="data_ra" value="a" checked="checked" onchange="modificaOptData();"/>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td>
						<div id="data_cont" class="contenuto" style="display:none;">
							<span id="datastr">
								<?php
									echo stringaData();
								?>
							</span>
							<?php
								$anno=getdate();
								echo "<input id=\"dataval\" type=\"hidden\" value=\"".stringaData()."\"/>";
								echo "<input id=\"annoval\" type=\"hidden\" value=\"".$anno["year"]."\"/>";
							?>
							<img id="sx" class="freccia" src="left.png" style="margin-left:15px;" onclick="modificaData(-1);"/>
							<img id="dx" class="freccia" src="right.png" onclick="modificaData(1);"/>
						</div>
					</td>
					<td>
						<div class="chr">
							<div id="data_chr" style="display:none;">10</div>
						</div>
						<input id="datchr" type="hidden" value="10"/>
					</td>
				</tr>
				<!--ora-->
				<tr>
					<td>
						<input id="ora" type="checkbox" name="ora" value="true" onchange="switchOra();"/>
					</td>
					<td>
						<span class="parte">Ora</span>
					</td>
					<td>
						<div id="ora_opz" style="display:none;">
						</div>
					</td>
					<td class="contenuto">
						<div id="ora_cont" style="display:none;">
							<table class="ora">
								<tr>
									<td class="tab"><span style="margin-right:15px;">alle</span>
									</td>
									<td class="tab">
										<table width="150">
											<tr>
												<td id="oratd" class="tab" width="70" style="text-align:center;">
													<?php
														$d=getdate();
														$ora=$d["hours"];
														$min=$d["minutes"];
														$oraint=(int)$ora;
														if ((int)$min<8) $min="00";
														elseif ((int)$min<23) $min="15";
														elseif ((int)$min<38) $min="30";
														elseif ((int)$min<53) $min="45";
														else {
															$min="00";
															$oraint += 1;
														}
														if ($oraint<10) $ora="0".(string)$oraint;
														else $ora=(string)$oraint;
													
														echo $ora;
													?>
												</td>
												<td class="tab" widht="10">
													:
												</td>
												<td id="mintd" class="tab" width="70" style="text-align:center;">
													<?php
														echo $min;
													?>
												</td>
											</tr>
											<tr>
												<td class="tab" style="text-align:center;">
													<img class="freccia2" src="up.png" onclick="modificaOra(1);"/>
													<img class="freccia2" src="down.png" onclick="modificaOra(-1);"/>
												</td>
												<td class="tab">
												</td>
												<td class="tab" style="text-align:center;">
													<img class="freccia2" src="up.png" onclick="modificaMin(15);"/>
													<img class="freccia2" src="down.png" onclick="modificaMin(-15);"/>
												</td>	
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>
						<?php
						echo"<input id=\"oraval\" type=\"hidden\" value=\"".$ora."\"/>";
						echo"<input id=\"minval\" type=\"hidden\" value=\"".$min."\"/>";
						?>
					</td>
					<td>
						<div class="chr">
							<div id="ora_chr" style="display:none;">11</div>	
						</div>
						<input id="orachr" type="hidden" value="11"/>
					</td>
				</tr>
				<!--extra-->
				<tr>
					<td>
						<input id="extra" type="checkbox" name="extra" value="true" onchange="switchExtra();"/>
					</td>
					<td>
						<span class="parte">Extra</span>
					</td>
					<td>
						<div id="extra_opz" style="display:none;">
							<table class="opzioni" width="100%">
								<tr>
									<td>
										<select id="ext" name="ext" class="chr" form="messaggio" onchange="modificaExtra();">
											<?php
												$query="SELECT * FROM extramag WHERE stato=1 ORDER BY ID ASC";
												if ($extra=mysqli_query($db,$query)) {
													while ($row=mysqli_fetch_array($extra)) {
														echo"<option class=\"chr\" value=\"$row[ID]\">$row[titolo]</option>";
														echo"<script type=\"text/javascript\">";
														$row[testo]=addslashes($row[testo]);
														echo"extra[".$row[ID]."]=new Array('$row[testo]','$row[attributo]','$row[etichetta]','$row[titolo]');";
														echo"</script>";
														$x += 1;
													}
												}
											?>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td class="contenuto">
						<div id="extra_cont" style="display:none;">
							<div>
								<input name="exttext" id="exttext" type="text" size="60" maxlength="60" style="margin-left:15px;" onkeypress="calcolaLunghezza();" onblur="calcolaLunghezza();"/>
							</div>
							<div>
								<input name="extattr" id="extattr" type="text" size="15" maxlength="15" style="visibility:hidden;margin-left:15px;" onkeypress="calcolaLunghezza();" onblur="calcolaLunghezza();"/>
								<span id="extet" style="margin-left:10px;visibility:hidden;"></span>
							</div>
							<script text/javascript>
								modificaExtra();
							</script>
						</div>
					</td>
					<td>
						<div class="chr">
							<div id="extra_chr" style="display:none;"></div>	
						</div>
						<input id="extrachr" type="hidden"/>
					</td>
				</tr>
				<!--chiusura-->
				<tr>
					<td>
					</td>
					<td>
						<span class="parte">Chiusura</span>
					</td>
					<td>
						<div id="chiusura_opz">
							<table class="opzioni" width="100%">
								<tr>
									<td class="tab" width="50%">Gabellini
										</br>
										<input type="radio" name="chiusura_ra" value="gab" onchange="modificaChiusura();"/>
									</td>
									<td class="tab" width="50%">Collaboratore
										<br/>
										<input type="radio" name="chiusura_ra" value="col" checked="checked" onchange="modificaChiusura();"/>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td>
						<div id="chiusura_cont" class="contenuto">
							<div>
								<label id="num" class="num">Numero di telefono</label>
								<br/>
								<input name="numero" id="numero" type="text" length="15" maxlength="10" style="font-size:16px;"/>
								<button id="gab" style="margin-left:25px;display:none;" onclick="validate('A.Gabellini','0721279325');">Invia Gabellini</button>
							</div>
							<br/>
							<div id="col">
								<?php
									mysqli_select_db($db,$db2_name)
										or die ("Errore nella selezione del database. Verificare i parametri nel file config.inc.php");
									$query="SELECT nome,telefono,marche.marca AS mc FROM collaboratori,marche,funzioni WHERE collaboratori.ID=collaboratore AND programma=11 AND collaboratori.marca=marche.ID ORDER BY mc";
									$col=mysqli_query($db,$query);
									while ($row=mysqli_fetch_array($col)) {
										if ($row[mc]=="mag") $img="mag.jpg";
										if ($row[mc]=="tutte") $img="star.png";
										echo"<button class=\"butinvia\" onclick=\"validate('$row[nome]','$row[telefono]');\"><img class=\"invia\" src=\"".$img."\"/><br/>".$row[nome]."</button>";
									}
								?>
							</div>
						</div>
					</td>
					<td>
						<div id="chiusura_chr" class="chr" style="color:#777777;">
							[20]	
						</div>
						<input id="chichr" type="hidden" value="20"/>
					</td>
				</tr>
				<!--footer tabella-->
				<tr>
					<td></td>
					<td></td>
					<td style="text-align:center;height:50px;">
						<!-- <button onclick="cambiaModello(modelloAttuale);">Reimposta</button> -->
					</td>
					<td style="text-align:right;">
						<span class="corpo">Lunghezza messaggio:</span>
					</td>
					<td>
						<div id="total_chr" class="chr">	
						</div>
						<input id="totchr" type="hidden" value="0"/>
					</td>
				</tr>				
			</tbody>
		</table>
	</form>
	<!--registrazione valori da inviare-->
	<input name="pmsg" id="pmsg" type="hidden" value=""/>
	<input name="pnumero" id="pnumero" type="hidden" value=""/>
	<!--overlay invio sms-->
	<div id="overlay" class="overlay" style="display:none;">
		<table width="100%" class="overtab">
			<tr>
				<td width="20%"class="num">Destinatario:</td>
				<td id="ds" width="80%"></td>
			</tr>
		</table>
		<table width="100%" class="overtab">
			<tr>
				<td width="20%"class="num">Messaggio:</td>
				<td id="mxg" width="80%"></td>
			</tr>
		</table>
		<table width="100%" class="overtab">
			<tr>
				<td width="20%"class="num">Lunghezza:</td>
				<td id="len" width="80%"></td>
			</tr>
		</table>
		<div id="waiter" class="overtxt" style="margin-top:30px;height:40;text-align:center;"></div>
		<div style="margin-top:10px;">
			<iframe id="sender" class="sender" style="margin-top:10px;"></iframe>
		</div>
		<p style="text-align:center;margin-top:130px;"><button id="ok" onclick="conferma();" disabled="disabled">Conferma</button></p>
		<p id="footer" style="text-align:center;margin-top:10px;"><button id="ko" onclick="chiudiOverlay();">Chiudi</button></p>
	</div>	
</body>
</html>
<?php
	mysqli_close($db);
?>
					
				
	