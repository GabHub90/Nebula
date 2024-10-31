<?php

class fotoneImage {

    protected $path="";
    protected $ext;

    protected $temp_path="";

    protected $original;
    protected $edit;

    protected $quality = ["jpg"=>100, "jpeg"=>100, "png"=>0];

    function __construct($path,$ext,$temp_path) {

        $this->path=$path;
        $this->temp_path=$temp_path;

        $this->ext = $ext;

        $fn = "imagecreatefrom" . ($this->ext=="jpg"?"jpeg":$this->ext);
        $this->original = $fn($this->path);
    }

    function __destruct() {
        $this->erase();
    }

    function resize($w,$h) {

        $size = getimagesize($this->path);
        $width = $size[0];
        $height = $size[1];

        $res_w=0;
        $res_h=0;

        if ($width > $height) {
            $res_w = $w;
            $res_h = CEIL($height / ($width/$w));
        }
        // Square or portrait
        else {
            $res_h = $h;
            $res_w = CEIL($width / ($height/$h));
        }

        if ($res_w==0 || $res_h==0) return;

        $this->edit = imagecreatetruecolor($res_w, $res_h);

        imagecopyresampled($this->edit, $this->original, 0, 0, 0, 0, $res_w, $res_h, $width, $height);
    }

    function savepng($name) {
        
        $destinazione=$this->temp_path.'/'.$name;

        imagepng($this->edit,$destinazione,-1,-1);

        return $destinazione;
    }

    function erase() {
        imagedestroy($this->original);
        imagedestroy($this->edit);
    }


}

?>