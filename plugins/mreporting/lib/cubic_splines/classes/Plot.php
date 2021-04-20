<?php

class Plot {
    private $aCoords;

    function __construct(&$aCoords) {
        $this->aCoords = &$aCoords;
    }

    public function drawLine($vImage, $vColor, $iPosX = 0, $iPosY = false) {
        if ($iPosY === false)
            $iPosY = imagesy($vImage);

        reset($this->aCoords);
        list($iPrevX, $iPrevY) = each($this->aCoords);

        while (list ($x, $y) = each($this->aCoords)) {
            imageline($vImage, $iPosX + round($iPrevX), $iPosY - round($iPrevY), $iPosX + round($x), $iPosY - round($y), $vColor);
            $iPrevX = $x;
            $iPrevY = $y;
        }
    }

    public function drawDots($vImage, $vColor, $iPosX = 0, $iPosY = false, $iDotSize = 1) {
        if ($iPosY === false)
            $iPosY = imagesy($vImage);

        $vBorderColor = imagecolorallocate($vImage, 0, 0, 0);
        foreach ($this->aCoords as $x => $y) {
            imagefilledellipse($vImage, $iPosX + round($x), $iPosY - round($y), $iDotSize, $iDotSize, $vColor);
            imageellipse($vImage, $iPosX + round($x), $iPosY - round($y), $iDotSize, $iDotSize, $vBorderColor);
        }
    }

    public function drawAxis($vImage, $vColor, $iPosX = 0, $iPosY = false) {
        if ($iPosY === false)
            $iPosY = imagesy($vImage);

        $vImageWidth = imagesx($vImage);
        imageline($vImage, $iPosX, $iPosY, $iPosX, 0, $vColor);
        imageline($vImage, $iPosX, $iPosY, $vImageWidth, $iPosY, $vColor);

        imagefilledpolygon($vImage, array($iPosX, 0, $iPosX - 3, 5, $iPosX + 3, 5), 3, $vColor);
        imagefilledpolygon($vImage, array($vImageWidth, $iPosY, $vImageWidth - 5, $iPosY - 3, $vImageWidth - 5, $iPosY + 3), 3, $vColor);
    }
}

?>
