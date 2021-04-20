<?php
include ("../../../inc/includes.php");

if (! isset($_REQUEST['tickets_id'])) {
   Html::displayErrorAndDie(__("missing parameters", "escalade"));
}

PluginEscaladeTicket::assign_me((int) $_REQUEST['tickets_id']);