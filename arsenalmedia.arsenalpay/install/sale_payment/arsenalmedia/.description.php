<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Файл с описанием настроек обработчика. Этот файл всегда должен присутствовать в папке обработчика.
 */


use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$callbackUrl = ($_SERVER['HTTPS'] ? 'https' : 'http') . '://' . ($_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']) . '/callback/index.php';

$description = '<a href=\'https://arsenalpay.ru/\' target=\'_blank\'>	https://arsenalpay.ru/</a>';
$vatOptions  = array(
	'none'   => Loc::getMessage('AM_TAX_NONE'),
	'vat0'   => Loc::getMessage('AM_TAX_VAT0'),
	'vat10'  => Loc::getMessage('AM_TAX_VAT10'),
	'vat18'  => Loc::getMessage('AM_TAX_VAT18'),
	'vat110' => Loc::getMessage('AM_TAX_VAT110'),
	'vat118' => Loc::getMessage('AM_TAX_VAT118'),
);
$data        = array(
	'NAME'  => Loc::getMessage('AM_TITLE'),
	'SORT'  => 500,
	'CODES' => array(
		'WIDGET_ID'          => array(
			'NAME'        => Loc::getMessage('AM_WIDGET_ID'),
			'DESCRIPTION' => Loc::getMessage('AM_WIDGET_ID_DESCR'),
			'SORT'        => 50,
			'GROUP'       => Loc::getMessage('AM_GROUP_NAME'),
		),
		'WIDGET_KEY'         => array(
			'NAME'        => Loc::getMessage('AM_WIDGET_KEY'),
			'DESCRIPTION' => Loc::getMessage('AM_WIDGET_KEY_DESCR'),
			'SORT'        => 100,
			'GROUP'       => Loc::getMessage('AM_GROUP_NAME'),
		),
		'CALLBACK_KEY'       => array(
			'NAME'        => Loc::getMessage('AM_CALLBACK_KEY'),
			'DESCRIPTION' => Loc::getMessage('AM_CALLBACK_KEY_DESCR'),
			'SORT'        => 150,
			'GROUP'       => Loc::getMessage('AM_GROUP_NAME'),
		),
		'CALLBACK_URL'       => array(
			'NAME'        => Loc::getMessage('AM_CALLBACK_URL'),
			'DESCRIPTION' => Loc::getMessage('AM_CALLBACK_URL_DESCR'),
			'SORT'        => 200,
			'GROUP'       => Loc::getMessage('AM_GROUP_NAME'),
			'DEFAULT'     => array(
				'PROVIDER_KEY'   => 'VALUE',
				'PROVIDER_VALUE' => $callbackUrl
			)
		),
		'CHANGE_STATUS'      => array(
			'NAME'        => Loc::getMessage('AM_CHANGE_STATUS'),
			'DESCRIPTION' => Loc::getMessage('AM_CHANGE_STATUS_DESCR'),
			'SORT'        => 150,
			'GROUP'       => 'GENERAL',
			'INPUT'       => array('TYPE' => 'Y/N'),
			'DEFAULT'     => array(
				'PROVIDER_KEY'   => 'INPUT',
				'PROVIDER_VALUE' => 'Y'
			)
		),
		'PAYMENT_ID'         => array(
			'NAME'        => Loc::getMessage('AM_PAYMENT_ID'),
			'DESCRIPTION' => Loc::getMessage('AM_PAYMENT_ID_DESCR'),
			'SORT'        => 100,
			'DEFAULT'     => array(
				'PROVIDER_KEY'   => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			),
			'GROUP'       => 'PAYMENT',

		),
		'USER_ID'            => array(
			'NAME'        => Loc::getMessage('AM_USER_ID'),
			'DESCRIPTION' => Loc::getMessage('AM_USER_ID_DESCR'),
			'SORT'        => 200,
			'DEFAULT'     => array(
				'PROVIDER_KEY'   => 'USER',
				'PROVIDER_VALUE' => 'ID'
			),
			'GROUP'       => 'PAYMENT'
		),
		'PAYMENT_SHOULD_PAY' => array(
			'NAME'        => Loc::getMessage('AM_PAYMENT_SHOULD_PAY'),
			'DESCRIPTION' => Loc::getMessage('AM_PAYMENT_SHOULD_PAY_DESCR'),
			'SORT'        => 300,
			'DEFAULT'     => array(
				'PROVIDER_KEY'   => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			),
			'GROUP'       => 'PAYMENT'
		),
		'DEFAULT_TAX'        => array(
			'NAME'        => Loc::getMessage('AM_PAYMENT_TAX_DEFAULT_NAME'),
			'DESCRIPTION' => Loc::getMessage('AM_PAYMENT_TAX_DEFAULT_DESCR'),
			'SORT'        => 2000,
			'GROUP'       => Loc::getMessage('AM_OFD_GROUP_NAME'),
			'INPUT'       => array(
				'TYPE'    => 'ENUM',
				'OPTIONS' => $vatOptions
			),
		),

	)
);

if (CModule::IncludeModule('catalog')) {
	$q        = \Bitrix\Catalog\VatTable::getList();
	$shopVats = $q->fetchAll();

	foreach ($shopVats as $shopVat) {
		$vatCodeName                 = 'CATALOG_VAT_' . $shopVat['ID'];
		$data['CODES'][$vatCodeName] = array(
			'NAME'        => Loc::getMessage('AM_PAYMENT_TAX_TEMPLATE_NAME') . $shopVat['NAME'],
			'DESCRIPTION' => Loc::getMessage('AM_PAYMENT_TAX_GENERAL_DESCR'),
			'SORT'        => 2700 + $shopVat['SORT'],
			'GROUP'       => Loc::getMessage('AM_OFD_GROUP_NAME'),
			'INPUT'       => array(
				'TYPE'    => 'ENUM',
				'OPTIONS' => $vatOptions
			),
		);
	}

}


