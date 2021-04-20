<?php

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access this file directly");
}

class PluginJsaddonsJsaddon extends CommonDBTM {
	static $rightname     = 'config';

	public static function getTypeName($nb = 0) {
		return __('JS Addons');
	}

	static function getMenuContent() {

		$menu = [];
		if (Config::canUpdate()) {
			$menu['title'] = self::getMenuName();
			$menu['page']  = '/' . Plugin::getWebDir('jsaddons', false) . '/front/jsaddon.php';
			$menu['icon']  = self::getIcon();
		}
		if (count($menu)) {
			return $menu;
		}

		return false;
	}

	function rawSearchOptions(){
		$tab=parent::rawSearchOptions();

		$tab[]=[
			'id'=>10,
			'table'=>$this->getTable(),
			'field'=>'is_active',
			'name'=>__('Active'),
			'datatype'=>'bool'
		];

		return $tab;
	}

	public static function getIcon() {
		return 'fas fa-file-code';
	}

	public function showForm($ID,$options=[]){
		global $CFG_GLPI,$DB;

		$this->initForm($ID,$options);
		$this->showFormHeader($options);
		$rand=mt_rand();

		$canedit=$this->canUpdate();

		echo "<tr class='tab_bg_1'>";
		echo "<td>".__('Active')."</td>";
		echo "<td>";
		Dropdown::showYesNo('is_active',$this->fields['is_active'],-1, [
			'use_checkbox' => true,
		]);
		echo "</td>";
		echo "</tr>";

		echo "<tr class='tab_bg_1'>";
		echo "<td>".__("Key")."</td>";
		echo "<td>";
		Html::autocompletionTextField($this, "key",['value'=>$this->fields["key"]]);
		echo "</td>";
		echo "</tr>";

		if ($canedit) {
			$this->showFormButtons($options);
		}else{
			echo "</table></div>";
			Html::closeForm();
		}

	}

	static function getScript(){
		global $DB;

		$query=[
			'SELECT'=>[
				'filename',
				'key',
			],
			'FROM'=>self::getTable(),
			'WHERE'=>[
				'is_active'=>1,
			]
		];
		$script=[];
		$iterator=$DB->request($query);
		while ($row = $iterator->next()) {
			$file=Plugin::getPhpDir('jsaddons')."/js/".$row['filename'];
			if (file_exists($file)) {
				$content=file_get_contents($file);
				$script[]=str_replace("##KEY##", $row['key'], $content);
			}
		}
		return $script;
	}

	public static function install(Migration $migration){
		global $DB;

		$table = self::getTable();

		if (!$DB->tableExists($table)) {
			$migration->displayMessage("Installing $table");

			$query = "CREATE TABLE IF NOT EXISTS `$table` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				`filename` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				`is_active` tinyint(1) NOT NULL DEFAULT '0',
				`date_creation` timestamp NULL DEFAULT NULL,
				`date_mod` timestamp NULL DEFAULT NULL,
				`key` varchar(255) COLLATE utf8_unicode_ci NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			$DB->query($query) or die($DB->error());

			$jsaddons=new self();
			$list=[
				[
					'name'=>'Metricool',
					'filename'=>'metricool.js'
				],[
					'name'=>'Tawk.to',
					'filename'=>'tawkto.js',
				],[
					'name'=>'Google Analytics',
					'filename'=>'gtag.js',
				]
			];
			foreach ($list as $key => $value) {
				$jsaddons->add([
					'name'=>$value['name'],
					'filename'=>$value['filename']
				]);
			}
		}
	}
}