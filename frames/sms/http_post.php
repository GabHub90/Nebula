<?php 
# 
# http_post - PHP3 class for posting a 'form' from within a php3 script 
# Version 0.5b 
# 
# Copyright 2000  
# Alan van den Bosch (alan@sanguis.com.au) 
# Sanguis Pty Ltd (acn 061 444 031)  
# 
# Licence: 
# You are granted the right to use and/or redistribute this 
# code only if this licence and the copyright notice are included 
# and you accept that no warranty of any kind is made or implied  
# by the author or Sanguis Pty Ltd. 
#  
# 
# Methods: 
# 
# http_post()  
#    Constructor used when creating a new instance of the http_post class. 
#    Returns true on success. 
#    ie. 
#        $a=new http_post; 
# 
# 
# set_server(string SERVER)  
#    Set the server of the URI you wish to post to. see also set_action() 
#    Returns true on success. 
#    ie. 
#        $a->set_server("127.0.0.1"); 
#    or 
#        $a->set_server("www.somehost.org"); 
# 
# 
# set_port(string PORT)  
#    Set the tcp port of the URI you wish to    post to. see also set_action() 
#    Returns true on success. 
#    ie. 
#        $a->set_port("8080"); 
# 
# 
# set_file(string FILENAME)  
#    Set the filename of the URI you wish to    post to. see also set_action() 
#    Returns true on success. 
#    ie. 
#        $a->set_file("/incoming.php3"); 
# 
# 
# set_action(string ACTION)  
#    Set the URI you wish to post to. 
#    Returns true on success. 
#    ie. 
#        $a->set_action("http://www.somehost.org:8080/incoming.php3"); 
# 
# set_enctype(string ENCTYPE) 
#    Set the encoding type used for the post. Can have the values 
#    "application/x-www-form-urlencoded" or "multipart/form-data" 
#    Returns true on success. 
#    ie. 
#        $a->set_enctype("multipart/form-data"); 
# 
# 
# set_element(string NAME, string VALUE) 
#    Set or update a single name/value pair to be posted 
#    Returns true on success. 
#    ie. 
#        $a->set_element("username","John Doe"); 
# 
# 
# set_element(array ELEMENTS) 
#    Set or update a number of name/value pairs to be posted 
#    Returns true on success. 
#    ie. 
#        $a->set_element(array("username" => "John Doe", 
#                      "password" => "dead-ringer", 
#                      "age" => "99")); 
# 
# 
# set_timeout(integer TIMEOUT) 
#    Set the number of seconds to wait for the server to connect 
#    when posting. minimum value of 1 second. 
#    Returns true on success. 
#    ie. 
#        $a->set_timeout(10);  
# 
# show_post() 
#    Show the current internal state of an instance, for debugging. 
#    Returns true on success. 
#    ie. 
#        $a->show_post(); 
# 
# 
# send(boolean DISPLAY) 
#    Send the name/value pairs using the post method. The response 
#    can be echoed by setting DISPLAY to a true value.  
#    Returns a string containing the raw response on success, false 
#    on failure. 
#    ie. 
#        $a->send(1); 
# 


class http_post 
{ 
    function http_post(){ 
        $this->_method="post"; 
        $this->_server=$GLOBALS["HTTP_HOST"]; 
        $this->_file="\\"; 
        $this->_port="80"; 
        $this->_enctype="application/x-www-form-urlencoded"; 
        $this->_element=array(); 
        $this->_timeout=20; 
    } 

    function set_server($newServer=""){ 
        if(strlen($newServer)<1)$newServer=$HTTP_HOST; 
        $this->_server=$newServer; 
        return 1; 
    }     

    function set_port($newPort="80"){ 
        $newPort=intval($newPort); 
        if($newPort < 0 || $newPort > 65535)$newPort=80; 
        $this->_port=$newPort; 
        return 1; 
    }     

    function set_file($newFile="\\"){ 
        $this->_file=$newFile; 
        return 1; 
    }     

    function set_action($newAction=""){ 
        $pat="^((http://){1}([^:/]{0,}){1}(:([0-9]{1,})){0,1}){0,1}(.*)"; 

        if(eregi($pat,$newAction,$sub)){ 
            if(strlen($sub[3])>0)$this->_server=$sub[3]; 
            if(strlen($sub[5])>0)$this->_port=$sub[5]; 
            $this->_file=$sub[6]; 
            return 1; 
        } 
        return 0; 
    } 

    function set_enctype($newEnctype="application/x-www-form-urlencoded"){ 
        if($newEnctype != "application/x-www-form-urlencoded" && 
            $newEnctype != "multipart/form-data"){ 
            $newEnctype="application/x-www-form-urlencoded"; 
        } 
        $this->_enctype=$newEnctype; 
        return 1; 
    }     

    function set_element($key="",$val=""){ 
        if(is_array($key)){ 
            $len=sizeof($key); 
            reset($key); 
            for($i=0;$i<$len;$i++){ 
                $cur=each($key); 
                $k=$cur["key"]; 
                $v=$cur["value"]; 
                $this->_element[$k]=$v; 
            } 
        } 
        else{ 
            if(strlen($key)>0)$this->_element[$key]=$val; 
        } 
        return 1; 
    } 

    function set_timeout($newTimeout=20){ 
        $newTimeout=intval($newTimeout); 
        if($newTimeout<1)$newTimeout=1; 
        $this->_timeout=$newTimeout; 
        return 1; 
    }     
     
    function show_post(){ 
        $str=""; 
        $str.="Action:".$this->_action."<br>"; 
        $str.="Server:".$this->_server."<br>"; 
        $str.="Port:".$this->_port."<br>"; 
        $str.="File:".$this->_file."<br>"; 
        $str.="Enctype:".$this->_enctype."<br>"; 
     
        echo $str; 

        $len=sizeof($this->_element); 
        reset($this->_element); 
        for($i=0;$i<$len;$i++){ 
            $cur=each($this->_element); 
            $key=$cur["key"]; 
            $val=$cur["value"]; 
            echo"Field:$key = $val<br>\n"; 
        } 
        return 1; 
    } 

    function send($display=0){ 
        // open socket to server 
        $errno=$errstr=$retstr=""; 
        $sk = fsockopen($this->_server, 
                $this->_port, 
                &$errno, 
                &$errstr, 
                $this->_timeout 
                ); 
        if(!$sk){ 
            return 0; 
        } 
        else{ 
            $boundary="----".md5(uniqid(rand()))."----"; 
            $message=$this->_get_message($boundary); 
            $str=""; 
            $str.=strtoupper($this->_method)." "; 
            $str.=$this->_file." HTTP/1.0 \r\n"; 
            $str.="Referer: \r\n"; 
            $str.="User-Agent: SMSRelay/0.1 \r\n"; 
            $str.="Host: ".$this->_server."\r\n"; 

            $str.="Content-type: ".$this->_enctype. "\r\n"; 
            if($this->_enctype=="multipart/form-data"){ 
                $str.="; boundary=".$boundary; 
            } 
            $str.=" \r\n"; 
     
            $str.="Content-length: ".strlen($message)."\r\n\r\n"; 
            $str.=$message; 

            fputs($sk,$str); 

            while(!feof($sk)){ 
                $resp=fgets($sk,80); 
                $retstr.=$resp; 
                if($display)echo $resp; 
            } 

            fclose($sk); 
            return $retstr; 
        } 
    }         

    function _get_message($boundary=""){ 
        $retstr=""; 

        $len=sizeof($this->_element); 
        reset($this->_element); 

        $switch=($this->_enctype=="multipart/form-data")?0:1; 

        for($i=0;$i<$len;$i++){ 
            $cur=each($this->_element); 
            $key=$cur["key"]; 
            $val=$cur["value"]; 
             
            if($switch){ 
                if(strlen($retstr)!=0)$retstr.="&"; 
                $retstr.=rawurlencode($key)."="; 
                $retstr.=rawurlencode($val);     
            } 
            else{ 
                $retstr.=$boundary."\r\n"; 
                $retstr.="Content-Disposition: form-data; "; 
                $retstr.="name=\"$key\"\r\n\r\n$val\r\n\r\n"; 
            } 
        } 
        if(!$switch)$retstr.=$boundary."\r\n"; 
        return $retstr; 
    } 
} 

?> 