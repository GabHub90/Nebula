<?php

include ($_SERVER['DOCUMENT_ROOT'].'/nebula/main/main_func.php');

echo '<head>';

    echo '<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >';

    echo '<title>SkyHawk</title>';
    echo '<link rel="shortcut icon" href="img/favicon.ico">';
        
    echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/jquery-1.10.2.js"></script>';
    //echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/datejs/build/date-it-IT.js"></script>';
    echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/skyhawk/code.js?v='.time().'"></script>';

    echo '<script type="text/javascript">';
        echo 'window.skyhawk=new skyhawk();';
    echo '</script>';

echo '</head>';

?>

<body>

<input id="sky_today" type="hidden" value="<?php echo date('Ymd');?>" />

<div>
    <img style="width:50px;height:50px;" src="img/skyhawk.png" />
    <span style="font-weight:bold;font-size:16pt;margin-left:20px;">Nebula Sky Hawk</span>
</div>

<hr/>

<div style="font-size:14pt;">

    <div><b>Chiusura automatica marcature tecnici:</b></div>

    <div style="margin-top:10px;">
        <div style="position:relative;display:inline-block;width:300px;">Avvio:<?php echo date(' d/m/Y H:i:s');?></div>
        <div style="position:relative;display:inline-block;width:200px;">Prossimo controllo:</div>
        <div id="skyhawk_workshop_nextcheck" style="position:relative;display:inline-block;width:200px;" data-today="<?php echo date('Ymd');?>" data-now="<?php echo date('H:i:s');?>" data-limit="0"></div>
    </div>

    <div style="margin-top:10px;">
        <div style="position:relative;display:inline-block;width:300px;">

            <span style="font-weight:bold;">Prossimo:</span>
            <select id="skyhawk_workshop_checkpoints" style="margin-left:10px;font-size:15pt;">
                <?php
                    $arr=array(
                        '12:05:00','12:20:00','12:40:00','13:10:00','13:40:00','16:40:00','17:40:00','18:40:00','19:10:00','19:40:00','20:10:00','23:59:59'
                    );

                    $first=true;

                    foreach ($arr as $a) {
                        echo '<option value="'.$a.'" ';
                            if ($first) {
                                echo 'selected="selected" ';
                                $first=false;
                            }
                        echo '>'.$a.'</option>';
                    }
                ?>

            </select>
        </div>

        <div style="position:relative;display:inline-block;width:200px;">Ultimo allineamento:</div>
        <div id="skyhawk_workshop_nexteffect" style="position:relative;display:inline-block;width:200px;" data-effect="00:00:00"></div>
    </div>

    <script type="text/javascript">
        window.skyhawk.workshop_init();
    </script>

</div>

<hr/>

<div style="font-size:14pt;">

    <div><b>Spedizione timbrature HR:</b></div>

    <div style="margin-top:10px;">
        <div style="position:relative;display:inline-block;width:300px;">Avvio:<?php echo date(' d/m/Y H:i:s');?></div>
        <div style="position:relative;display:inline-block;width:200px;">Prossimo controllo:</div>
        <div id="skyhawk_HR_nextcheck" style="position:relative;display:inline-block;width:200px;" data-today="<?php echo date('Ymd');?>" data-now="<?php echo date('H:i:s');?>" data-limit="0"></div>
    </div>

    <div style="margin-top:10px;">
        <div style="position:relative;display:inline-block;width:300px;">

            <span style="font-weight:bold;">Prossimo:</span>
            <select id="skyhawk_HR_checkpoints" style="margin-left:10px;font-size:15pt;">
                <?php
                    $arr=array(
                        '07:05:00','08:10:00','08:40:00','09:10:00','09:40:00','12:25:00','12:40:00','16:10:00','16:40:00','17:40:00','18:40:00','19:40:00','23:59:59'
                    );

                    $first=true;

                    foreach ($arr as $a) {
                        echo '<option value="'.$a.'" ';
                            if ($first) {
                                echo 'selected="selected" ';
                                $first=false;
                            }
                        echo '>'.$a.'</option>';
                    }
                ?>

            </select>
        </div>

        <div style="position:relative;display:inline-block;width:200px;">Ultimo allineamento:</div>
        <div id="skyhawk_HR_nexteffect" style="position:relative;display:inline-block;width:200px;" data-effect="00:00:00"></div>
    </div>

    <script type="text/javascript">
        window.skyhawk.HR_init();
    </script>

</div>




</body>