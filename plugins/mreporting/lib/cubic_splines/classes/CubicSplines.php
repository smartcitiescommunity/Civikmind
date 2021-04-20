<?php

class CubicSplines {
    protected $aCoords;
    protected $aCrdX;
    protected $aCrdY;
    protected $aSplines = array();
    protected $iMinX;
    protected $iMaxX;
    protected $iStep;

    protected function prepareCoords(&$aCoords, $iStep, $iMinX = -1, $iMaxX = -1) {
        $this->aCrdX = array();
        $this->aCrdY = array();
        $this->aCoords = array();

        ksort($aCoords);
        foreach ($aCoords as $x => $y) {
            $this->aCrdX[] = $x;
            $this->aCrdY[] = $y;
        }

        $this->iMinX = $iMinX;
        $this->iMaxX = $iMaxX;

        if ($this->iMinX == -1)
            $this->iMinX = min($this->aCrdX);
        if ($this->iMaxX == -1)
            $this->iMaxX = max($this->aCrdX);

        $this->iStep = $iStep;
    }

    public function setInitCoords(&$aCoords, $iStep = 1, $iMinX = -1, $iMaxX = -1) {
        $this->aSplines = array();

        if (count($aCoords) < 4) {
            return false;
        }

        $this->prepareCoords($aCoords, $iStep, $iMinX, $iMaxX);
        $this->buildSpline($this->aCrdX, $this->aCrdY, count($this->aCrdX));
    }

    public function processCoords() {
        for ($x = $this->iMinX; $x <= $this->iMaxX; $x += $this->iStep) {
            $this->aCoords[$x] = $this->funcInterp($x);
        }

        return $this->aCoords;
    }

    private function buildSpline($x, $y, $n) {
        for ($i = 0; $i < $n; ++$i) {
            $this->aSplines[$i]['x'] = $x[$i];
            $this->aSplines[$i]['a'] = $y[$i];
        }

        $this->aSplines[0]['c'] = $this->aSplines[$n - 1]['c'] = 0;
        $alpha[0] = $beta[0] = 0;
        for ($i = 1; $i < $n - 1; ++$i) {
            $h_i = $x[$i] - $x[$i - 1];
            $h_i1 = $x[$i + 1] - $x[$i];
            $A = $h_i;
            $B = $h_i1;
            
            if ($h_i == $h_i1) {
               $C = 7.0 * ($h_i + $h_i1);
            } else {
               $C = 2.3 * ($h_i + $h_i1);
            }
            $B = $h_i1;
            if ($h_i == $h_i1) {
               $F = 3.5 * (($y[$i + 1] - $y[$i]) / $h_i1 - ($y[$i] - $y[$i - 1]) / $h_i);
            } else {
               $F = 6.0 * (($y[$i + 1] - $y[$i]) / $h_i1 - ($y[$i] - $y[$i - 1]) / $h_i);
            }
            $z = ($A * $alpha[$i - 1] + $C);
            $alpha[$i] = - $B / $z;
            $beta[$i] = ($F - $A * $beta[$i - 1]) / $z;
        }

        for ($i = $n - 2; $i > 0; --$i) {
            $this->aSplines[$i]['c'] = $alpha[$i] * $this->aSplines[$i + 1]['c'] + $beta[$i];
        }

        for ($i = $n - 1; $i > 0; --$i) {
            $h_i = $x[$i] - $x[$i - 1];
            $this->aSplines[$i]['d'] = ($this->aSplines[$i]['c'] - $this->aSplines[$i - 1]['c']) / $h_i;
            $this->aSplines[$i]['b'] = $h_i * (2.0 * $this->aSplines[$i]['c'] + $this->aSplines[$i - 1]['c']) / 6.0 + ($y[$i] - $y[$i - 1]) / $h_i;
        }
    }

    private function funcInterp($x) {
        $n = count($this->aSplines);
        if ($x <= $this->aSplines[0]['x'])  {
            $s = $this->aSplines[1];
        } else {
            if ($x >= $this->aSplines[$n - 1]['x']) {
                $s = $this->aSplines[$n - 1];
            } else {
                $i = 0;
                $j = $n - 1;
                while ($i + 1 < $j) {
                    $k = $i + ($j - $i) / 2;
                    if ($x <= $this->aSplines[$k]['x']) {
                        $j = $k;
                    } else {
                        $i = $k;
                    }
                }

                $s = $this->aSplines[$j];
            }
        }

        $dx = ($x - $s['x']);
        return $s['a'] + ($s['b'] + ($s['c'] / 2.0 + $s['d'] * $dx / 6.0) * $dx) * $dx;
    }
}

?>
