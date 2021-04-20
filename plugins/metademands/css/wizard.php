<?php
header("Content-type: text/css; charset: UTF-8");
include('../../../inc/includes.php');
$color = "#3a5693";
$hover = "#ff9c10";
?>

a .far, a .fas, .btn-linkstyled .fa, .btn-linkstyled .far, .btn-linkstyled .fas {
color: unset;
}

/* Wrapper Style */


div[class^="btnsc"] {
   margin: 0 10px 10px 0;
   height: 175px !important;
   cursor: pointer;
   transition: all .4s ease;
   user-drag: element;
   text-align: center;
   -moz-border-radius: 10px;
   width: 250px;
   float: left;
   list-style-type: none;
   padding: 4px 15px 15px 15px;
   overflow: auto;
   transition: all .4s ease;
   user-drag: element;
   border: solid #CCC 1px;
   background: #cccccc1f!important;
}

@media (max-width: 768px) {
div[class^="btnsc"] {
width: 200px;
}
}

div[class^="btnsc"]:hover {
   opacity: 0.9;
}

div[class^="btnsc"]:active {
   transform: scale(.98, .98);
}

.fa-menu-md {
   margin-top: 20px;
}
