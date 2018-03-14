<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;

require_once __DIR__.'/../SubscriberDB.php';
require_once __DIR__.'/../CapperDB.php';
require_once __DIR__.'/../ForecastDB.php';
use SubscriberDB;
use CapperDB;
use ForecastDB;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.0.0';
    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $callback_query = $this->getCallbackQuery();
        $callback_query_id = 0;
        $callback_data = '';

        if($callback_query) {
            $message = $callback_query->getMessage();    
            $callback_query_id = $callback_query->getId();
            $callback_data     = $callback_query->getData();
            $user = $callback_query->getFrom();
        } else {
            $message = $this->getMessage();
            $user = $message->getFrom();
        }
        
        $chat = $message->getChat();
        $text = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        @list($command, $command_data) = explode(" ", $callback_data, 2);
        $result = Request::emptyResponse();
        
        SubscriberDB::initializeSubscriber();
        ForecastDB::initializeForecast();

        switch ($command) {

            case 'capper':

                $capper_id = $command_data;
                $subscribers = SubscriberDB::selectActiveSubscriber(null, $capper_id, null, $user_id);

                $subscription_paid = false;
                $subscription_end_timestamp = 0;

                var_dump($subscribers);

                foreach($subscribers as $subscriber) {
                    if($subscriber['paid']) {
                        $subscription_paid = true;
                        $subscription_end_timestamp = $subscriber['end_timestamp'];
                    }
                }

                $forecasts = ForecastDB::selectForecast(null, $capper_id);
                $flag = false;

                foreach ($forecasts as $forecast) { 
                    if(time() < $forecast['disabling_timestamp'] && time() > $forecast['sending_timestamp']) {
                        $flag = true;

                        if($subscription_paid) {
                            $text = 'Прогноз: '.PHP_EOL.$forecast['name'].PHP_EOL.$forecast['description'];
                        } else {
                            $text = 'Прогноз: '.PHP_EOL.$forecast['name'];
                        }

                        Request::sendMessage([
                            'chat_id'      => $chat_id,
                            'text'         => $text
                        ]);
                    }
                }

                if(!$flag) {
                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => 'В данный момент у каппера нет актуальных прогнозов. Зайдите позже.'
                    ]);
                }

                if($subscription_paid) {
                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => 'Вы подписаны на рассылку данного каппера. Как только каппер добавит новый прогноз, вы сразу его получите. '.PHP_EOL.'Ваша подписка истекает: '.date('Y-m-d H:i:s', $subscription_end_timestamp)
                    ]);
                }
                else {

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => "Приобрести подписку", 'callback_data' => 'subscription_buy '.$capper_id],
                    ]);

                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => 'Вы можете подписаться и в автоматическом режиме получать все самые свежие прогнозы этого каппера.',
                        'reply_markup' => $inline_keyboard
                    ]);

                }

                break;
                
            case 'subscription_buy':
                $capper_id = $command_data;

                break;
            
            default:
                # code...
                break;
        }

        return $result;
    }

   
}
