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

require_once __DIR__.'/../SubscriberDB.php';
require_once __DIR__.'/../CapperDB.php';

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use SubscriberDB;
use CapperDB;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StartCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * @var bool
     */
    protected $private_only = true;
    
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
        SubscriberDB::initializeSubscriber();
        CapperDB::initializeCapper();

        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        
        $cappers = CapperDB::selectCapper();

        foreach ($cappers as $capper) {
            $images_dir_full_path = __DIR__.'/../images/';
            $images_dir = '../images/';
            $images = glob($images_dir_full_path.$capper['id'].'.*');
            
            if(count($images)) {
                // send capper photo
                $result = Request::sendPhoto([
                    'chat_id' => $chat_id,
                    'photo'   => Request::encodeFile($images[0]),
                ]);
            }

            $text = "Каппер: ".$capper['name'].PHP_EOL
                ."Обо мне: ".$capper['description'];

            $inline_keyboard = new InlineKeyboard([
                ['text' => "Выбрать", 'callback_data' => 'capper '.$capper['id']],
            ]);
    
            Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => $text,
                'reply_markup' => $inline_keyboard
            ]);
        }

        return true;
    }
}
