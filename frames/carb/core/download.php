<?php  
//forzare il download del file in html
$file="temp/carb.txt";

header("Cache-Control:public");
header("Content-Description:File Trasfer");
header("Content-Disposition:attachment;filename=carb.txt");
header("Content-Transfer-Encoding:binary");

readfile($file);

?>