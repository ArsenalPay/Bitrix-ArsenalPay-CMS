<?php
/**
 * Main module file, install, uninstall
 */

global $MESS;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class arsenalmedia_arsenalpay extends CModule
{
	public $MODULE_ID = 'arsenalmedia.arsenalpay';
	public $PARTNER_NAME = "Arsenal Media";
	public $PARTNER_URI = "https://arsenalpay.ru";

	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;

	public $errors;

	public function __construct() {
		$this->MODULE_ID = 'arsenalmedia.arsenalpay';
		$this->PARTNER_NAME = "Arsenal Media";
		$this->PARTNER_URI = "https://arsenalpay.ru";
	
		$arModuleVersion = array();

		include __DIR__ . "/version.php";

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
			$this->MODULE_VERSION      = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage('AM_MODULE_NAME');;
		$this->MODULE_DESCRIPTION = Loc::getMessage('AM_MODULE_DESCRIPTION');
	}

	public function InstallFiles($arParams = array()) {
		CopyDirFiles(Loader::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/components', Loader::getDocumentRoot() . "/bitrix/components", true, true);
		CopyDirFiles(Loader::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/sale_payment', Loader::getDocumentRoot() . "/bitrix/php_interface/include/sale_payment", true, true);
		CopyDirFiles(Loader::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/files', Loader::getDocumentRoot(), true, true);
		CopyDirFiles(Loader::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/images', Loader::getDocumentRoot() . "/bitrix/images/sale/sale_payments", true, true);

		return true;
	}

	public function UnInstallFiles() {
		DeleteDirFilesEx("/bitrix/components/arsenalmedia");
		DeleteDirFilesEx("/callback");
		DeleteDirFilesEx("/bitrix/php_interface/include/sale_payment/arsenalmedia");

		return true;
	}

	public function DoInstall() {
		Loader::includeModule("sale");
		$this->AddStatusCode('AH');
		$this->InstallFiles();
		RegisterModule($this->MODULE_ID);

		return true;
	}

	public function DoUninstall() {
		UnRegisterModule($this->MODULE_ID);
		$this->UnInstallFiles();

		return true;
	}

	public function AddStatusCode($ID) {
		$lang   = array();
		$b = "sort";
		$o = "asc";
		$dbLang = CLangAdmin::GetList($b, $o, array("ACTIVE" => "Y"));
		while ($arLang = $dbLang->Fetch()) {
			$lang[] = array(
				'LID'         => $arLang["LID"],
				'NAME'        => Loc::getMessage("AM_PP_STATUS_" . $ID . "_NAME"),
				'DESCRIPTION' => Loc::getMessage("AM_PP_STATUS_" . $ID . "_DESCR")
			);
		}

		$newStatus = array(
			'ID'   => $ID,
			'SORT' => 1000,
			'LANG' => $lang
		);

		$arStatus = CSaleStatus::GetByID($newStatus['ID']);
		if (!$arStatus) {
			CSaleStatus::Add($newStatus);
		}
	}
}

