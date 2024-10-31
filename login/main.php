<?php
    $nebulaVersion="0.6";
?>

<div class="app_login_background" style="background-image: url('http://<?php echo $_SERVER['SERVER_ADDR'];?>/nebula/login/img/nebula.jpg');">

    <div id="app_login_main_div" class="app_login_main_div">

        <div class="app_login_logdiv">
            <div>
                <div>user</div>
                <input id="app_login_input_usr" style="font-size:16pt;" type="text"/>
            </div>
            <div>
                <div>password</div>
                <input id="app_login_input_psw" style="font-size:16pt;" type="password" onkeydown="if(event.keyCode==13) window._nebulaApp.confirm();" />
            </div>
            <div id="app_login_error" class="nebula_error"></div>
            <div style="text-align: right;margin-top: 20px;">
                <button onclick="window._nebulaApp.confirm();">log in</button>
            </div>
            <div style="text-align:left;">
                <img style="width:150px;height:50px;" src="http://<?php echo $_SERVER['SERVER_ADDR'];?>/nebula/main/img/Nebula.png" />
                <span style="font-size:0.6em;"><?php echo $nebulaVersion; ?></span>
            </div>
        </div>


        <div style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:2;">
            <div id="openlog_hype_container" style="margin:auto;position:relative;width:300px;height:200px;overflow:hidden;">
                <script type="text/javascript" charset="utf-8" src="http://<?php echo $_SERVER['SERVER_ADDR'];?>/nebula/login/hype/openLog.hyperesources/openlog_hype_generated_script.js?19472"></script>
            </div>
        </div> 

    </div>

</div>
