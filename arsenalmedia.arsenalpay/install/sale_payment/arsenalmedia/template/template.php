<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Шаблон обработчика платежной системы
 */

$widget_key  = $params['WIDGET_KEY'];
$widget_id   = $params['WIDGET_ID'];
$amount      = floatval($params['PAYMENT_SHOULD_PAY']);
$destination = $params['PAYMENT_ID'];
$user_id     = $params['USER_ID'];

$nonce       = md5(microtime(true) . mt_rand(100000, 999999));
$sign_data   = "$user_id;$destination;$amount;$widget_id;$nonce";
$widget_sign = hash_hmac('sha256', $sign_data, $widget_key);
?>
<!doctype html>
<html lang="ru">
<head>
 <meta charset="utf-8">
 <title>ArsenalPay - Оплата заказа</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
</head>
<body>
<style>
    .sale_order_full_table {
        width: 100%;
        text-align: center;
    }
</style>
<script src='https://arsenalpay.ru/widget/script.js'></script>
<div id='app-widget'></div>
<script>
    var APWidget = new ArsenalpayWidget({
        element: 'app-widget',
        destination: "<?= $destination ?>",
        widget: "<?= $widget_id ?>",
        amount: "<?= $amount ?>",
        userId: "<?= $user_id ?>",
        nonce: "<?= $nonce ?>",
        widgetSign: "<?= $widget_sign ?>"
    });
    APWidget.render();
</script>
</body>
</html>
