<?php

namespace Sale\Handlers\PaySystem;

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Request,
	\Bitrix\Main\Loader,
	\Bitrix\Sale,
	\Bitrix\Sale\PaySystem,
	\Bitrix\Sale\Payment;

Loc::loadMessages(__FILE__);
if (!Loader::includeModule('arsenalmedia.arsenalpay')) {
	return;
}

/**
 * Class ArsenalmediaHandler
 * @package Sale\Handlers\PaySystem
 */
Class ArsenalmediaHandler extends PaySystem\BaseServiceHandler {

	/**
	 * @param Sale\Payment $payment
	 * @param Request|null $request
	 *
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null) {
		return $this->showTemplate($payment, "template");
	}

	/**
	 * @return array
	 */
	public function getCurrencyList() {
		return array('RUB');
	}

}

