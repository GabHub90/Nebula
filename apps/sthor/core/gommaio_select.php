<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/sthor/classi/gommaio.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/magazzino/wormhole.php");

$param=$_POST['param'];

$g=new Gommaio('sthor');

/*$sconti=array(
    "P"=>array('ZTS'=>54,'ZTR'=>54,'ZTW'=>46),
    "C"=>array('ZTS'=>43,'ZTR'=>40,'ZTW'=>56),
    "L"=>array('ZTS'=>37,'ZTR'=>37,'ZTW'=>53),
    "B"=>array('ZTS'=>41,'ZTR'=>41,'ZTW'=>51),
    "F"=>array('ZTS'=>35,'ZTR'=>35,'ZTW'=>49),
    "G"=>array('ZTS'=>42,'ZTR'=>43,'ZTW'=>44),
    "D"=>array('ZTS'=>42,'ZTR'=>43,'ZTW'=>42),
    "A"=>array('ZTS'=>38,'ZTR'=>38,'ZTW'=>40),
    "M"=>array('ZTS'=>32,'ZTR'=>32,'ZTW'=>32),
    "H"=>array('ZTS'=>41,'ZTR'=>41,'ZTW'=>36)
);*/

$sconti=$g->sconti;

/*$pneumatico=array(
    "L"=>"Barum",
    "B"=>"Bridgestone",
    "C"=>"Continental",
    "D"=>"Dunlop",
    "F"=>"Firestone",
    "A"=>"Fulda",
    "G"=>"Goodyear",
    "H"=>"Hankook",
    "M"=>"Michelin",
    "P"=>"Pirelli"
);*/

$pneumatico=$g->pneumatico;

$wh=new magazzinoWH($param['reparto'],$galileo);

$wh->build(array('inizio'=>date('Ymd'),'fine'=>date('Ymd')));

$wh->selectPneumaticiZT($param);

$txt="";

foreach($wh->exportMap() as $k=>$m) {

    if ($m['result']) {
        $fid=$galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

        while ($row=$galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

            $tipo=substr($row['articolo'],0,3);
            $marca=substr($row['articolo'],10,1);

            //{"magazzino":"04","precodice":"V","articolo":"ZTS215557WPC70","descr_articolo":"215\/55R17 94W P7 CINTURATO PIRELLI","giacenza":"4.000","dispo":"4.000"}

            $row['qta']=$param['qta'];
            if (isset($pneumatico[$marca])) {
                $row['des_marca']=$pneumatico[$marca];
            }
            else $row['des_marca']='';
            if (isset($sconti[$marca][$tipo])) {
                $row['scontoAC']=$sconti[$marca][$tipo];
            }
            else $row['scontoAC']=0;

            $txt.='<div style="position:relative;width:95%;height:30px;margin-top:10px;margin-bottom:10px;padding:3px;box-sizing:border-box;" >';
                $txt.='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:center;line-height:30px;width:7%;">';
                    $txt.='<div style="vertical-align:meddle;">'.$row['qta'].' x</div>';
                $txt.='</div>';
                $txt.='<div style="position:relative;display:inline-block;vertical-align:top;width:57%;">';
                    $txt.='<div style="font-size:0.8em;">'.$row['articolo'].' - '.$row['des_marca'].'</div>';
                    $txt.='<div style="font-size:0.8em;">'.substr($row['descr_articolo'],0,32).'</div>';
                $txt.='</div>';
                $txt.='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:right;line-height:30px;width:15%;">';
                    $txt.='<div>'.number_format($row['listino'],2,',','').'</div>';
                $txt.='</div>';
                $txt.='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:center;line-height:30px;width:13%;">';
                    $txt.='<div style="vertical-align:meddle;">';
                        $img='Y.png';
                        if ($row['giacenza']<$row['qta']) $img='R.png';
                        elseif ($row['dispo']<$row['qta']) $img='G.png';
                        elseif ($row['dispo']>=$row['qta']) $img='V.png';

                        $txt.='<img style="position:relative;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/sthor/img/'.$img.'" />';
                        $txt.='<span style="margin-left:5px;font-size:0.8em;">'.$row['magazzino'].'</span>';
                    $txt.='</div>';
                $txt.='</div>';
                $txt.='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:right;line-height:30px;width:8%;">';
                    $txt.='<div style="vertical-align:meddle;">';
                        $txt.='<img style="position:relative;width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/sthor/img/get.png" onclick="window._gom_'.$param['id'].'.get(\''.base64_encode(json_encode($row)).'\')" />';
                    $txt.='</div>';
                $txt.='</div>';
            $txt.='</div>';
        }
    }
}

echo json_encode(array('id'=>$param['id'],'txt'=>base64_encode($txt)));

?>