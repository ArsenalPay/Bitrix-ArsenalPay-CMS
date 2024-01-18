<?php
global $MESS;

/** @var CUpdater $updater */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;



if (IsModuleInstalled('arsenalmedia.arsenalpay')) {
	if (is_dir(dirname(__FILE__) . '/install/components')) {
		$updater->CopyFiles("install/components", "components/");
	}
	if (is_dir(dirname(__FILE__) . '/install/sale_payment')) {
		$updater->CopyFiles("install/sale_payment", "php_interface/include/sale_payment");
	}
	if (is_dir(dirname(__FILE__) . '/install/images')){
		$updater->CopyFiles("install/images", "images/sale/sale_payments");
	}

	Loader::includeModule("sale");
	$code_id  = 'AH';
	$arStatus = CSaleStatus::GetByID($code_id);
	if (!$arStatus) {
		Loc::loadMessages(__DIR__ . "/install/index.php");
		$lang   = array();
		$b = "sort";
		$o = "asc";
		$dbLang = CLangAdmin::GetList($b, $o, array("ACTIVE" => "Y"));
		while ($arLang = $dbLang->Fetch()) {
			$lang[] = array(
				'LID'         => $arLang["LID"],
				'NAME'        => Loc::getMessage("AM_PP_STATUS_" . $code_id . "_NAME"),
				'DESCRIPTION' => Loc::getMessage("AM_PP_STATUS_" . $code_id . "_DESCR")
			);
		}

		$newStatus = array(
			'ID'   => $code_id,
			'SORT' => 1000,
			'LANG' => $lang
		);

		CSaleStatus::Add($newStatus);
	}


}