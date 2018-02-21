<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
if (!Bitrix\Main\Loader::includeModule("sale") ||
    !Bitrix\Main\Loader::includeModule("arsenalmedia.arsenalpay")) {
	return;
}

use Bitrix\Sale;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadLanguageFile(__FILE__);

$callbackParams = $_POST;
$REMOTE_ADDR    = $_SERVER["REMOTE_ADDR"];
$strLog         = date("Y-m-d H:i:s") . " " . $REMOTE_ADDR . " post params: ";
$strLog         .= json_encode($callbackParams, JSON_UNESCAPED_UNICODE);
logf($strLog);

if (!checkParams($callbackParams)) {
	exitf("ERR");
}

//take requred data
list($orderId, $paymentId) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment($callbackParams['ACCOUNT']);
if ($orderId > 0) {
	/* @var $order \Bitrix\Sale\Order */
	$order = Sale\Order::load($orderId);
	if ($order) {
		/* @var $paymentCollection \Bitrix\Sale\PaymentCollection */
		$paymentCollection = $order->getPaymentCollection();
		if ($paymentCollection && $paymentId > 0) {
			/* @var $payment \Bitrix\Sale\Payment */
			$payment = $paymentCollection->getItemById($paymentId);
		}
	}
}
if (!$order || !$payment) {
	$callbackParams['FUNCTION'] == 'check' ? exitf('NO') : exitf('ERR');
}
/** @var \Bitrix\Sale\PaySystem\Service $service */
$service = PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
$params  = $service->getParamsBusValue($payment);
if (!array_key_exists('CALLBACK_KEY', $params) || strlen($params['CALLBACK_KEY']) == 0) {
	$callbackParams['FUNCTION'] == 'check' ? exitf('NO') : exitf('ERR');
}
$KEY = $params['CALLBACK_KEY'];

if (!checkSign($callbackParams, $KEY)) {
	logf("ERROR Sign is invalid");
	exitf("ERR");
}

//put params into vars
$changeStatus     = $params['CHANGE_STATUS'] == 'Y' ? true : false;
$currency         = "RUB";
$shouldPay        = $payment->getSum();
$arsenalPaidSum   = $payment->getSumPaid();
$isOrderPaid      = $order->isPaid();
$isArsenalPaid    = $payment->isPaid();
$userId           = $order->getUserId();
$oldStatusPayment = $payment->getField('PS_STATUS_DESCRIPTION');

if ($callbackParams['FUNCTION'] == 'cancelinit') {
	$callbackParams['FUNCTION'] = 'cancel';
	$callbackParams['STATUS']   = 'cancel';
}
if ($callbackParams['FUNCTION'] == 'reversal') {
	$callbackParams['FUNCTION'] = 'reverse';
	$callbackParams['STATUS']   = 'reverse';
}


if (!checkPaymentStatus($oldStatusPayment, $callbackParams['FUNCTION'])) {
	logf("Error payment status: cant change '{$oldStatusPayment}' to '{$callbackParams['FUNCTION']}'");
	$callbackParams['FUNCTION'] == 'check' ? exitf('NO') : exitf('ERR');
}

if ($callbackParams['FUNCTION'] == "check") {
	$isCorrectAmount = ($callbackParams['MERCH_TYPE'] == 0 && $shouldPay == $callbackParams['AMOUNT']) ||
	                   ($callbackParams['MERCH_TYPE'] == 1 && $shouldPay >= $callbackParams['AMOUNT'] && $shouldPay == $callbackParams['AMOUNT_FULL']);

	if (!$isCorrectAmount || $order->isCanceled() || $isArsenalPaid) {
		exitf("NO");
	}
	else {
		exitf("YES");
	}
}
elseif ($callbackParams['FUNCTION'] == "payment") {
	$isCorrectAmount = ($callbackParams['MERCH_TYPE'] == 0 && $shouldPay == $callbackParams['AMOUNT']) ||
	                   ($callbackParams['MERCH_TYPE'] == 1 && $shouldPay >= $callbackParams['AMOUNT'] && $shouldPay == $callbackParams['AMOUNT_FULL']);

	if (!$isCorrectAmount) {
		logf("Error amount");
		exitf("ERR");
	}

	$payInfo = array(
		"PAID"                  => "Y",
		"PS_STATUS"             => "Y",
		"PS_STATUS_CODE"        => "-",
		"PS_STATUS_DESCRIPTION" => $callbackParams['FUNCTION'],
		"PS_STATUS_MESSAGE"     => Loc::getMessage("AM_PAID_STATUS_MESSAGE"),
		"PS_SUM"                => $callbackParams['AMOUNT'],
		"PS_CURRENCY"           => $currency,
		"PS_RESPONSE_DATE"      => new DateTime()
	);
	$payment->setFields($payInfo);
	$result = $order->save();
	if (!$result->isSuccess()) {
		logf("Error during save order status");
		exitf('ERR');
	}
	else {
		if ($changeStatus && $order->isPaid()) {
			changeStatus($order, "P");
		}
		exitf('OK');
	}
}
elseif ($callbackParams['FUNCTION'] == "reverse") {
	$isCorrectAmount = ($callbackParams['MERCH_TYPE'] == 0 && $arsenalPaidSum == $callbackParams['AMOUNT']) ||
	                   ($callbackParams['MERCH_TYPE'] == 1 && $arsenalPaidSum >= $callbackParams['AMOUNT'] && $arsenalPaidSum == $callbackParams['AMOUNT_FULL']);

	if (!$isArsenalPaid) {
		logf("Error: Order was not paid!");
		exitf("ERR");
	}
	elseif (!$isCorrectAmount) {
		logf("Error amount");
		exitf("ERR");
	}

	$payInfo = array(
		"PAID"                  => "N",
		"PS_STATUS"             => "N",
		"PS_STATUS_CODE"        => "-",
		"PS_STATUS_DESCRIPTION" => $callbackParams['FUNCTION'],
		"PS_STATUS_MESSAGE"     => Loc::getMessage("AM_REVERSE_STATUS_MESSAGE"),
		"PS_SUM"                => 0,
		"PS_CURRENCY"           => $currency,
		"PS_RESPONSE_DATE"      => new DateTime()
	);
	$payment->setFields($payInfo);
	cancelOrder($order);
	$result = $order->save();
	if (!$result->isSuccess()) {
		logf("Error during save order status");
		exitf('ERR');
	}
	else {
		logf("For order#{$orderId} reverse");
		exitf('OK');
	}
}
elseif ($callbackParams['FUNCTION'] == 'refund') {
	$isCorrectAmount = ($callbackParams['MERCH_TYPE'] == 0 && $arsenalPaidSum >= $callbackParams['AMOUNT']) ||
	                   ($callbackParams['MERCH_TYPE'] == 1 && $arsenalPaidSum >= $callbackParams['AMOUNT'] && $arsenalPaidSum >= $callbackParams['AMOUNT_FULL']);

	if (!$isCorrectAmount) {
		logf("Error amount");
		exitf("ERR");
	}
	$diff          = DoubleVal($arsenalPaidSum - DoubleVal($callbackParams['AMOUNT']));
	$isFirstRefund = intval($payment->getField('PS_STATUS_CODE')) == 1 ? false : true;
	$payInfo       = array(
		"PAID"                  => "N",
		"PS_STATUS"             => "N",
		"PS_STATUS_CODE"        => 1,
		"PS_STATUS_DESCRIPTION" => $callbackParams['FUNCTION'],
		"PS_STATUS_MESSAGE"     => Loc::getMessage("AM_REFUND_STATUS_MESSAGE"),
		"PS_SUM"                => $diff,
		"PS_CURRENCY"           => $currency,
		"PS_RESPONSE_DATE"      => new DateTime()
	);
	$payment->setFields($payInfo);
	cancelOrder($order);
	$result = $order->save();
	if (!$result->isSuccess()) {
		logf("Error during save order status");
		exitf('ERR');
	}
	else {
		if ($isFirstRefund) {
			$value = $diff;
			$msg   = Loc::getMessage("AM_REFUND_STATUS_MESSAGE");
		}
		else {
			$value = - $callbackParams['AMOUNT'];
			$msg   = Loc::getMessage("AM_REPEATED_REFUND_MESSAGE");
		}
		if (!CSaleUserAccount::UpdateAccount($userId, $value, $currency, $msg, $orderId)) {
			/*
				Добавьте сюда код, который срабатывает, если
				внутренний счет клиента был заблокирован и
				его пополнение невозможно.
			 */
			logf("Failed transaction for user#{$userId} on sum={$diff}");
		}
		logf("For order#{$orderId} refund {$callbackParams['AMOUNT']}");
		exitf('OK');
	}
}
elseif ($callbackParams['FUNCTION'] == 'cancel') {
	if ($isArsenalPaid) {
		logf(Loc::getMessage("AM_ORDER_WAS_PAID_FAIL"));
		exitf('ERR');
	}
	$payInfo = array(
		"PAID"                  => "N",
		"PS_STATUS"             => "N",
		"PS_STATUS_CODE"        => "-",
		"PS_STATUS_DESCRIPTION" => $callbackParams['FUNCTION'],
		"PS_STATUS_MESSAGE"     => Loc::getMessage("AM_CANCEL_STATUS_MESSAGE"),
		"PS_SUM"                => 0,
		"PS_CURRENCY"           => $currency,
		"PS_RESPONSE_DATE"      => new DateTime()
	);
	$payment->setFields($payInfo);
	cancelOrder($order);
	$result = $order->save();
	if (!$result->isSuccess()) {
		logf("Error during save order status");
		exitf('ERR');
	}
	else {
		logf("Cancel for order#{$orderId}");
		exitf('OK');
	}
}
elseif ($callbackParams['FUNCTION'] == "hold") {
	$isCorrectAmount = ($callbackParams['MERCH_TYPE'] == 0 && $shouldPay == $callbackParams['AMOUNT']) ||
	                   ($callbackParams['MERCH_TYPE'] == 1 && $shouldPay >= $callbackParams['AMOUNT'] && $shouldPay == $callbackParams['AMOUNT_FULL']);

	if (!$isCorrectAmount) {
		logf("Error amount");
		exitf("ERR");
	}
	elseif ($isArsenalPaid) {
		logf(Loc::getMessage("AM_ORDER_WAS_PAID_FAIL"));
		exitf('ERR');
	}
	$payInfo = array(
		"PAID"                  => "N",
		"PS_STATUS"             => "N",
		"PS_STATUS_CODE"        => "-",
		"PS_STATUS_DESCRIPTION" => $callbackParams['FUNCTION'],
		"PS_STATUS_MESSAGE"     => Loc::getMessage("AM_HOLD_STATUS_MESSAGE"),
		"PS_SUM"                => 0,
		"PS_CURRENCY"           => $currency,
		"PS_RESPONSE_DATE"      => new DateTime()
	);
	$payment->setFields($payInfo);
	$result = $order->save();
	if (!$result->isSuccess()) {
		logf("Error during save order status");
		exitf('ERR');
	}
	else {
		changeStatus($order, "AH");
		logf("Hold {$callbackParams['AMOUNT']} for order #{$orderId}");
		exitf('OK');
	}
}

function exitf($msg) {
	logf($msg);
	echo $msg;
	exit;
}

function logf($str) {
	$fp = fopen(realpath(dirname(__FILE__)) . "/callback.log", "a");
	fwrite($fp, $str . "\r\n");
	fclose($fp);
}

/**
 * Check payment logic
 *
 * @param string $oldStatus
 * @param string $newStatus
 *
 * @return boolean
 */
function checkPaymentStatus($oldStatus = '', $newStatus) {
	$oldStatus = $oldStatus == '' ? $oldStatus = 'check' : $oldStatus;
	$checks    = array();

	$checks['check2check']   = ($oldStatus == 'check' && $newStatus == 'check');
	$checks['check2cancel']  = ($oldStatus == 'check' && $newStatus == 'cancel');
	$checks['check2hold']    = ($oldStatus == 'check' && $newStatus == 'hold');
	$checks['check2payment'] = ($oldStatus == 'check' && $newStatus == 'payment');

	$checks['hold2cancel'] = ($oldStatus == 'hold' && $newStatus == 'cancel');
	$checks['hold2payment'] = ($oldStatus == 'hold' && $newStatus == 'payment');

	$checks['payment2reverse'] = ($oldStatus == 'payment' && $newStatus == 'reverse');
	$checks['payment2refund']  = ($oldStatus == 'payment' && $newStatus == 'refund');

	$checks['refund2refund'] = ($oldStatus == 'refund' && $newStatus == 'refund');

	foreach ($checks as $check) {
		if ($check) {
			return true;
		}
	}

	return false;
}


/**
 *
 * @param \Bitrix\Sale\Order $order
 */
function cancelOrder($order) {
	/* @var $paymentCollection \Bitrix\Sale\PaymentCollection */
	$paymentCollection = $order->getPaymentCollection();
	/* @var $innerPayment \Bitrix\Sale\Payment */
	if ($innerPayment = $paymentCollection->getInnerPayment()) {
		$innerService = PaySystem\Manager::getObjectById($innerPayment->getPaymentSystemId());
		$innerPayment->setReturn('Y');
	}
	$cancelInfo = array(
		'CANCELED'        => 'Y',
		'DATE_CANCELED'   => new DateTime(),
		'REASON_CANCELED' => Loc::getMessage("AM_REVERSE_STATUS_MESSAGE")
	);
	$order->setFields($cancelInfo);
}

/**
 * Change order status
 *
 * @param \Bitrix\Sale\Order $order
 * @param string             $status
 */
function changeStatus($order, $status) {
	$statusInfo = array(
		'STATUS_ID'     => $status,
		'DATE_STATUS'   => new DateTime(),
		'EMP_STATUS_ID' => false
	);
	$order->setFields($statusInfo);
	$order->save();
}

function checkSign($callback, $pass) {

	$validSign = ($callback['SIGN'] === md5(
			md5($callback['ID']) .
			md5($callback['FUNCTION']) . md5($callback['RRN']) .
			md5($callback['PAYER']) . md5($callback['AMOUNT']) . md5($callback['ACCOUNT']) .
			md5($callback['STATUS']) . md5($pass)
		)) ? true : false;

	return $validSign;
}

function checkParams($callback_params) {
	$required_keys = array
	(
		'ID',           /* Merchant identifier */
		'FUNCTION',     /* Type of request to which the response is received*/
		'RRN',          /* Transaction identifier */
		'PAYER',        /* Payer(customer) identifier */
		'AMOUNT',       /* Payment amount */
		'ACCOUNT',      /* Order number */
		'STATUS',       /* When /check/ - response for the order number checking, when
									// payment/ - response for status change.*/
		'DATETIME',     /* Date and time in ISO-8601 format, urlencoded.*/
		'SIGN',         /* Response sign  = md5(md5(ID).md(FUNCTION).md5(RRN).md5(PAYER).md5(request amount).
									// md5(ACCOUNT).md(STATUS).md5(PASSWORD)) */
	);

	/**
	 * Checking the absence of each parameter in the post request.
	 */
	foreach ($required_keys as $key) {
		if (empty($callback_params[$key]) || !array_key_exists($key, $callback_params)) {
			logf('Error in callback parameters ERR' . $key);

			return false;
		}
		else {
			logf(" $key=$callback_params[$key]");
		}
	}
	if ($callback_params['FUNCTION'] != $callback_params['STATUS']) {
		logf("Error: FUNCTION ({$callback_params['FUNCTION']} not equal STATUS ({$callback_params['STATUS']})");

		return false;
	}

	return true;
}
