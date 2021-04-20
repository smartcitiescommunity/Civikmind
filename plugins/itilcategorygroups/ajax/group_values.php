<?php
$AJAX_INCLUDE = 1;

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

if (! isset($_REQUEST['itilcategories_id'])) {
   exit;
}

$ticket_id = (isset($_REQUEST['ticket_id'])) ? $_REQUEST['ticket_id'] : 0;

$canApplyFilter = PluginItilcategorygroupsCategory::canApplyFilter(
   intval($_REQUEST['itilcategories_id'])
);

$condition = PluginItilcategorygroupsCategory::getSQLCondition(
   intval($ticket_id),
   intval($_REQUEST['itilcategories_id']),
   $_REQUEST['type']
);

if (! $canApplyFilter || empty($condition)) {
   $condition = [
      'glpi_groups.is_assign' => 1,
   ] + getEntitiesRestrictCriteria("", "entities_id", $_SESSION['glpiactive_entity'], 1);
}

if (! empty($condition)) {
   $_POST['display_emptychoice'] = true;
   $_POST['itemtype'] = 'Group';
   $_POST['condition'] = Dropdown::addNewCondition($condition);

   require "../../../ajax/getDropdownValue.php";
}
