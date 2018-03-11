<?php

set_time_limit(0);
ini_set('display_errors','on');
ignore_user_abort(true);
// Use internal libxml errors -- turn on in production, off for debugging
libxml_use_internal_errors(true);

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

    $number_sended_forecasts = 0;
    $subscribers = SubscriberDB::selectActiveSubscriber();

    foreach ($subscribers as $subscriber) {
    	$forecasts = ForecastDB::selectForecast(null, $subscriber['capper_id']);

    	foreach ($forecasts as $forecast) {
    		echo time(). ' '. $forecast['disabling_timestamp'].' '. $forecast['sending_timestamp'].PHP_EOL;
    		if(time() < $forecast['disabling_timestamp'] && time() > $forecast['sending_timestamp']) {
    			$forecastSended = ForecastSendedDB::selectForecastSended(null, $forecast['id'], $subscriber['id']);
    			var_dump($forecastSended);
    			// if forecast dont sended to current subscriber
    			if(!count($forecastSended)) {
    				// send
    				$text = $forecast['name'].PHP_EOL.$forecast['description'];
					Request::sendMessage([
					    'chat_id' => $subscriber['chat_id'],
					    'text' => $text
					]);

    				// mark sended
    				// ForecastSendedDB::insertForecastSended($forecast['id'], $subscriber['id']);

    				$number_sended_forecasts++;
    			}
    			// ForecastDB::updateForecast(['sended' => 1], ['id' => $forecast['id']]);
    		}
    	}

    }
    
	print date('Y-m-d H:i:s', time()). " - Sended forecast: $number_sended_forecasts \n";

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Catch log initialisation errors
    echo $e->getMessage();
}

?>
