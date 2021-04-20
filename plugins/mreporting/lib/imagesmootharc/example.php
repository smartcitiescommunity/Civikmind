<?php
/*
    
    Copyright (c) 2006-2007 Ulrich Mierendorff

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
    DEALINGS IN THE SOFTWARE.

*/

include ("./imageSmoothArc.php");

$img = imageCreateTrueColor( 648, 648 );
imagealphablending($img,true);
$color = imageColorAllocate( $img, 255, 255, 255);
imagefill( $img, 5, 5, $color );

imageSmoothArc ( $img, 648/2, 648/2, 320,640, array(0,0,0,0),M_PI+1 , M_PI+0.3);

$n = 32.0;
for ($i = 0; $i < $n; $i++)
{
    $h = 360.0/$n*$i;
    $s = 1.0;
    $v = 1.0;
    $hi = floor ($h/60);
    $f = $h/60.0 - $hi;
    $p = $v * (1-$s);
    $q = $v * (1-($s*$f));
    $t = $v * (1-($s*(1-$f)));
    switch ($hi)
    {
        case 0:
            $r = $v; $g = $t; $b = $p;
            break;
        case 1:
            $r = $q; $g = $v; $b = $p;
            break;
        case 2:
            $r = $p; $g = $v; $b = $t;
            break;
        case 3:
            $r = $p; $g = $q; $b = $v;
            break;
        case 4:
            $r = $t; $g = $p; $b = $v;
            break;
        case 5:
            $r = $v; $g = $p; $b = $q;
            break;
        default:
            break;
    }
    //imageSmoothArc ( $img, 325, 155, 620,300, array(128-$i%2*128,128-$i%2*128,128-$i%2*128,0),($i)*2*M_PI/$n+0.1 , ($i+1)*2*M_PI/$n+0.1);
    imageSmoothArc ( $img, 325, 155, 620,300, array($r*255,$g*255,$b*255,32),($i)*2*M_PI/$n+0.1 , ($i+1)*2*M_PI/$n+0.1);
}

$n = 128.0;
for ($i = 0; $i < $n; $i+=2)
{
    $h = 360.0/$n*$i;
    $s = 1.0;
    $v = 1.0;
    $hi = floor ($h/60);
    $f = $h/60.0 - $hi;
    $p = $v * (1-$s);
    $q = $v * (1-($s*$f));
    $t = $v * (1-($s*(1-$f)));
    switch ($hi)
    {
        case 0:
            $r = $v; $g = $t; $b = $p;
            break;
        case 1:
            $r = $q; $g = $v; $b = $p;
            break;
        case 2:
            $r = $p; $g = $v; $b = $t;
            break;
        case 3:
            $r = $p; $g = $q; $b = $v;
            break;
        case 4:
            $r = $t; $g = $p; $b = $v;
            break;
        case 5:
            $r = $v; $g = $p; $b = $q;
            break;
        default:
            break;
    }
    
    imageSmoothArc ( $img, 325, 460, 620,300, array($r*255,$g*255,$b*255,0),($i)*2*M_PI/$n , ($i+1)*2*M_PI/$n);
}

header( 'Content-Type: image/png' );
imagePNG( $img );

?>
