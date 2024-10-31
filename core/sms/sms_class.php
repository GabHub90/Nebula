<?php
class nebulaSms {

    protected $config=array(
        "login"=>'sbuser804',
        "password"=>'dx8wosm',
        "tipo"=>1,
        "status"=>1
    );

    function getConfig() {
        return $this->config;
    }

    function send($arg) {

        $ch = curl_init('http://www.nsgateway.net/smsscript/sendsms.php');
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //$post=json_encode($arg);
        $post=http_build_query($arg);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
        //////////////////////////////////////////////////

        if( ! $result = curl_exec($ch)) { 
            trigger_error(curl_error($ch)); 
        } 
        curl_close($ch);

        return $result;
    }
}
?>