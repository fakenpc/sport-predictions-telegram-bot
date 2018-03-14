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
use SubscriberDB;
use CapperDB;

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
        SubscriberDB::initializeSubscriberDB();

        switch ($command) {

            case 'capper':

                $capper_id = $command_data;
                $subscribers = SubscriberDB::selectActiveSubscriber(null, $capper_id, null, $user_id);

                $subscription_paid = false;
                $subscription_end_timestamp = 0;

                foreach($subscribers as $subscriber) {
                    $subscriber = $subscribers[0];

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
                            $text = $forecast['name'].PHP_EOL.$forecast['description'];
                        } else {
                            $text = $forecast['name'];
                        }

                        Request::sendMessage([
                            'chat_id'      => $chat_id,
                            'text'         => $text;
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
                        'text'         => 'Вы подписаны на данного каппера. '.PHP_EOL.' Ваша подписка истекает: '.date('Y-m-d H:i:s', $subscription_end_timestamp)
                    ]);
                }
                else {
                    $text = 'Приобрести подписку на каппера.';

                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => 'Приобрести подписку на каппера.'
                    ]);

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => "Выбрать", 'callback_data' => 'subscription_buy '.$capper['id']],
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

    /**
     * Show filters keyboard
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    private function showFiltersKeyboard()
    {
        $callback_query    = $this->getCallbackQuery();
        $chat_id = $callback_query->getMessage()->getChat()->getId();

        FilterDB::initializeFilter();
        $filters = FilterDB::selectFilter($chat_id);

        $keyboard_buttons = [];

        foreach ($filters as $filter) {
            $keyboard_buttons[] = [
                ['text' => "\xE2\x9E\x96 ".$filter['word'], 'callback_data' => 'filter_remove '.$filter['id']]
            ];
        }

        $keyboard_buttons[] = [
            ['text' => "\xE2\x9E\x95 Добавить", 'callback_data' => 'filter_add'],
            ['text' => "\xE2\x9D\x8C Удалить все", 'callback_data' => 'filter_remove_all'],
        ];

        $class_name = '\Longman\TelegramBot\Entities\InlineKeyboard';
        $inline_keyboard = new $class_name(...$keyboard_buttons);

        return Request::sendMessage([
            'chat_id'      => $chat_id,
            'text'         => 'Управление фильтрами.',
            'reply_markup' => $inline_keyboard
        ]);
    }
}
