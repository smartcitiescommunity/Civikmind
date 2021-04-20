<?php
set_time_limit(100);

define('GRAPH_WIDTH',  500);
define('GRAPH_HEIGHT', 400);

include_once ('classes/Plot.php');
include_once ('classes/CubicSplines.php');

$iPoints = 15;
$dx = (GRAPH_WIDTH - 40) / ($iPoints - 1);
$x = 20;

for ($i = 0; $i < $iPoints; $i++) {
    $y = rand(20, GRAPH_HEIGHT - 20);
    $aCoords[$x] = $y;
    $x+= $dx;
}

$vImagegHeight = GRAPH_HEIGHT + 30;
$vImage = imagecreatetruecolor(GRAPH_WIDTH + 50, $vImagegHeight);

$vBgColor = imagecolorallocate($vImage, 160, 160, 160);
$vTextColor = imagecolorallocate($vImage, 0, 0, 0);
$vAxisColor = imagecolorallocate($vImage, 0, 0, 0);
$vDotColor  = imagecolorallocate($vImage, 192, 64, 64);

imagefill($vImage, 0, 0, $vBgColor);

$oPlot = new Plot($aCoords);
$oPlot->drawDots($vImage, $vDotColor, 10, GRAPH_HEIGHT, 8);

$oCurve = new CubicSplines();
$vColor = imagecolorallocate($vImage, 225, 64, 64);

$iStart = microtime(1);
if ($oCurve) {
    $oCurve->setInitCoords($aCoords, 1);
    $r = $oCurve->processCoords();
    if ($r)
        $curveGraph = new Plot($r);
    else
        continue;
} else {
    $curveGraph = $oPlot;
}

$curveGraph->drawLine($vImage, $vColor, 10, GRAPH_HEIGHT);

// unset($oCurve);
$sTime = sprintf("%1.4f", microtime(1) - $iStart);

imagefilledrectangle($vImage, 0, GRAPH_HEIGHT, GRAPH_WIDTH + 50, $vImagegHeight, $vBgColor);
$oPlot->drawAxis($vImage, $vAxisColor, 10, GRAPH_HEIGHT);
$iPanelY = GRAPH_HEIGHT;

imagefilledrectangle($vImage, 10, $iPanelY + 10, 20, $iPanelY + 20, $vColor);
imagerectangle($vImage, 10, $iPanelY + 10, 20, $iPanelY + 20, $vAxisColor);
imagettftext($vImage, 10, 0, 30, $iPanelY + 20, $vTextColor, 'Ds-digib.ttf', 'Cubic splines in PHP for graphs:         ' . $sTime . ' sec');

header("Content-type: image/png");
imagepng($vImage);
imagedestroy($vImage);

?>
