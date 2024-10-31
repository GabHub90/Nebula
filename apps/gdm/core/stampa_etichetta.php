<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
define('FPDF_FONTPATH',$_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/fonts/');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/utf8graffa.php');

//inizializza l'oggetto $pdf
//include($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/init.php');
$pdf = new utf8graffaPDF();
$fontName = 'DejaVu';
$pdf->AddFont($fontName,'','DejaVuSerif.ttf',true);
$pdf->AddFont($fontName,'B','DejaVuSans-Bold.ttf',true);

$pdf->AddPage('L','A4');

$pdf->SetFont($fontName,'B',15);

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////
//VALIDO PER PNEUMATICI

$galileo->executeSelect('gdm','GDM_materiali',"id='".$param['id']."'",'');

if ($galileo->getResult()) {

    $fid=$galileo->preFetch('gdm');
    while ($row=$galileo->getFetch('gdm',$fid)) {

        $pdf->SetLineWidth(1);

        $pdf->Rect(10,10,130,70,'D');

        $pdf->SetXY(11,18);

        $pdf->SetFont($fontName,'B',26);

        $pdf->Cell(128,10,(substr((isset($param['cliente'])?$param['cliente']:''),0,14)),0,1,'C');

        $pdf->SetFont($fontName,'B',36);

        $pdf->Cell(128,15,(substr((isset($param['targa'])?$param['targa']:''),0,10)),0,1,'C');

        $pdf->SetFont($fontName,'',18);

        $pdf->Cell(128,10,(substr((isset($row['idTelaio'])?$row['idTelaio']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);

        $pdf->Cell(128,10,(substr((isset($row['compoGomme'])?$row['compoGomme']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);

        $pdf->Cell(128,10,(isset($row['dimeASx'])?$row['dimeASx']:''). ' - '.(isset($row['dimeASx'])?$row['dimePSx']:''),0,1,'C');

        ///////////////////////////////////////////////////

        $pdf->Rect(145,10,130,70,'D');

        $pdf->SetXY(146,18);

        $pdf->SetFont($fontName,'B',26);

        $pdf->Cell(128,10,(substr((isset($param['cliente'])?$param['cliente']:''),0,14)),0,1,'C');

        $pdf->SetFont($fontName,'B',36);
        $pdf->SetX(146);

        $pdf->Cell(128,15,(substr((isset($param['targa'])?$param['targa']:''),0,10)),0,1,'C');

        $pdf->SetFont($fontName,'',18);
        $pdf->SetX(146);

        $pdf->Cell(128,10,(substr((isset($row['idTelaio'])?$row['idTelaio']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);
        $pdf->SetX(146);

        $pdf->Cell(128,10,(substr((isset($row['compoGomme'])?$row['compoGomme']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);
        $pdf->SetX(146);

        $pdf->Cell(128,10,(isset($row['dimeASx'])?$row['dimeASx']:''). ' - '.(isset($row['dimeASx'])?$row['dimePSx']:''),0,1,'C');

        ///////////////////////////////////////////////////

        $pdf->Rect(10,85,130,70,'D');

        $pdf->SetXY(11,93);

        $pdf->SetFont($fontName,'B',26);

        $pdf->Cell(128,10,(substr((isset($param['cliente'])?$param['cliente']:''),0,14)),0,1,'C');

        $pdf->SetFont($fontName,'B',36);

        $pdf->Cell(128,15,(substr((isset($param['targa'])?$param['targa']:''),0,10)),0,1,'C');

        $pdf->SetFont($fontName,'',18);

        $pdf->Cell(128,10,(substr((isset($row['idTelaio'])?$row['idTelaio']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);

        $pdf->Cell(128,10,(substr((isset($row['compoGomme'])?$row['compoGomme']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);

        $pdf->Cell(128,10,(isset($row['dimeASx'])?$row['dimeASx']:''). ' - '.(isset($row['dimeASx'])?$row['dimePSx']:''),0,1,'C');

        ///////////////////////////////////////////////////

        $pdf->Rect(145,85,130,70,'D');

        $pdf->SetXY(146,93);

        $pdf->SetFont($fontName,'B',26);

        $pdf->Cell(128,10,(substr((isset($param['cliente'])?$param['cliente']:''),0,14)),0,1,'C');

        $pdf->SetFont($fontName,'B',36);
        $pdf->SetX(146);

        $pdf->Cell(128,15,(substr((isset($param['targa'])?$param['targa']:''),0,10)),0,1,'C');

        $pdf->SetFont($fontName,'',18);
        $pdf->SetX(146);

        $pdf->Cell(128,10,(substr((isset($row['idTelaio'])?$row['idTelaio']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);
        $pdf->SetX(146);

        $pdf->Cell(128,10,(substr((isset($row['compoGomme'])?$row['compoGomme']:''),0,17)),0,1,'C');

        $pdf->SetFont($fontName,'',15);
        $pdf->SetX(146);

        $pdf->Cell(128,10,(isset($row['dimeASx'])?$row['dimeASx']:''). ' - '.(isset($row['dimeASx'])?$row['dimePSx']:''),0,1,'C');
    }
    

/*<div class="gdm_pdf_print">

    <div style="width:480px; height:260px; float:left;" >

        <table width="470px" height="260px" border="1" style="border-color:black;">
            <td>
                <div align="center">
                    <label style="font-weight:bold; font-size:36pt;"><?php echo substr($obj['nomeCliente'],0,14); ?></label>
                    <br/>
                    <label style="font-size:45pt;"><b><?php echo $obj['targa'];?></b></label>
                </div>
                <div align="center">
                    <div style="font-size:28pt; margin-left:2px"><?php echo $obj['idTelaio'];?></div>
                    <div style="font-size:10pt;"><?php echo $obj['compoGomme']."-".$obj['tipoGomme']."-".$obj['marcaADx']."-".$obj['dimeADx']."-".$obj['dimeASx'];?></div>
                </div>
                <div align="center">
                    <label style="font-size:35pt; margin-left:2px"><b><?php echo date('d/m/Y');?></b></label>
                </div>
            </td>
        </table>
    </div>

    <div style="width:480px; height:260px; float:left; margin-left:5px">
        <table width="480px" height="260px" border="1" style="border-color:black;">
            <td>
                <div align="center">
                    <label style="font-weight:bold; font-size:36pt;"><?php echo substr($obj['nomeCliente'],0,14); ?></label>
                    <br/>
                    <label style="font-size:45pt;"><b><?php echo $obj['targa'];?></b></label>
                </div>
                <div align="center">
                    <div style="font-size:28pt; margin-left:2px"><?php echo $obj['idTelaio'];?></div>
                    <div style="font-size:10pt;"><?php echo $obj['compoGomme']."-".$obj['tipoGomme']."-".$obj['marcaADx']."-".$obj['dimeADx']."-".$obj['dimeASx'];?></div>
                </div>
            </td>
        </table>
    </div>

    <div style="clear: both; margin-top:5px"></div>

    <div style="width:480px; height:260px; float:left;" >
        <table width="480px" height="260px" border="1" style="border-color:black;">
            <td>
                <div align="center">
                    <label style="font-weight:bold; font-size:36pt;"><?php echo substr($obj['nomeCliente'],0,14); ?></label>
                    <br/>
                    <label style="font-size:45pt;"><b><?php echo $obj['targa']; ?></b></label>
                </div>
                <div align="center">
                    <div style="font-size:28pt; margin-left:2px"><?php echo $obj['idTelaio']; ?></div>
                    <div style="font-size:10pt;"><?php echo $obj['compoGomme']."-".$obj['tipoGomme']."-".$obj['marcaPDx']."-".$obj['dimePSx']."-".$obj['dimePDx'];?></div>
                </div>
            </td>
        </table>
    </div>

    <div style="width:480px; height:260px; float:left; margin-left:5px" >
        <table width="480px" height="260px" border="1" style="border-color:black;">
            <td>
                <div align="center">
                    <label style="font-weight:bold; font-size:36pt;"><?php echo substr($obj['nomeCliente'],0,14); ?></label>
                    <br/>
                    <label style="font-size:45pt;"><b><?php echo $obj['targa']; ?></b></label>
                </div>
                <div align="center">
                    <div style="font-size:28pt; margin-left:2px"><?php echo $obj['idTelaio']; ?></div>
                    <div style="font-size:10pt;"><?php echo $obj['compoGomme']."-".$obj['tipoGomme']."-".$obj['marcaPDx']."-".$obj['dimePSx']."-".$obj['dimePDx'];?></div>
                </div>
            </td>
        </table>
    </div>

    <div style="clear: both"></div>

</div>




    }*/
}

else {
    $pdf->write(10,"Nessun Materiale Trovato");
}

echo base64_encode($pdf->Output('pdf','S'));

?>