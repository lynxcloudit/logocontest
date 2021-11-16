<div style="text-align: center;"><div>
<?php

require_once ('../../lib/tcpdf/tcpdf_barcodes_1d.php');

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// set the barcode content and type
$code = $_GET['code'];
$barcodeobj = new TCPDFBarcode($code, 'C128');

// output the barcode as SVG image
$bc = $barcodeobj->getBarcodeSVGCode(1.5, 40, 'black');

echo $bc;
echo "</div>";
echo '<div style="font-family: OCR A Std, monospace; text-align:center; padding:5px;">*'.$code.'*</div>';
//echo '<div style="font-family: Courier; text-align:center; padding:5px;">*'.$code.'*</div>';

?>
</div>