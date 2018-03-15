<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__.'/CapperDB.php';
require_once __DIR__.'/UserDB.php';
require_once __DIR__.'/ForecastDB.php';
require_once __DIR__.'/SubscriptionDB.php';
require_once __DIR__.'/SubscriberDB.php';
require_once __DIR__.'/ForecastSendedDB.php';
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;
CapperDB::initializeCapper();
UserDB::initializeUser();
ForecastDB::initializeForecast();
SubscriptionDB::initializeSubscription();
SubscriberDB::initializeSubscriber();
ForecastSendedDB::initializeForecastSended();

if(!file_exists('config.php')) {
    die("Please rename example_config.php to config.php and try again. \n");
} else {
    require_once 'config.php';
}

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    // Add commands paths containing your custom commands
    $telegram->addCommandsPaths($commands_paths);
    $telegram->enableLimiter();
    // Enable MySQL
    $telegram->enableMySql($mysql_credentials);

    if(!DB::isDbConnected()) {
    	print date('Y-m-d H:i:s', time()). " - Can't connect to mysql database. \n";
    }

    $subscriptions = SubscriptionDB::selectSubscription($_GET['subscription_id']);

	if(count($subscriptions)) {
		$subscription = $subscriptions[0];
		$hash = md5($merchant_id.":".$subscription['price'].":".$merchant_secret_form.":".$_GET['user_id']);
		
		print '
		<h2>Оплата через <a href="http://wwww.free-kassa.ru">free-kassa.ru</a></h2>
		<form method=GET action="http://www.free-kassa.ru/merchant/cash.php">
		    <input type="hidden" name="m" value="'.$merchant_id.'">
		    <input type="hidden" name="oa" value="'.$subscription['price'].'">
		    <input type="hidden" name="s" value="'.$hash.'">
		    <input type="hidden" name="o" value="'.$_GET['user_id'].'">
		    <input type="hidden" name="us_capper_id" value="'.$_GET['capper_id'].'">
		    <input type="submit" value="Оплатить">
		</form>
		';
	} else {
		print 'Такой подписки не существует.';
	}
	

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Catch log initialisation errors
    echo $e->getMessage();
}

?>