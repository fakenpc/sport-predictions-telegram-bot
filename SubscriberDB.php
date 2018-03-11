<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class SubscriberDB extends DB
{
    /**
     * Initialize subscriber table
     */
    public static function initializeSubscriber()
    {
        if (!defined('TB_SUBSCRIBER')) {
            define('TB_SUBSCRIBER', self::$table_prefix . 'subscriber');
        }
    }

    /**
     * Select a subscribers from the DB
     *
     * @param int   $id
     * @param int   $sended
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectSubscriber($id = null, $capper_id = null, $capper_subscription_id = null, $user_id = null, $chat_id = null, $start_timestamp = null, $end_timestamp = null, $paid = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_SUBSCRIBER . '`
            ';

            $where = array();

            if($id !== null) {
                if(is_array($id)) {
                    // 
                }
                else {
                    $where[] = '`id` = :id';
                }
            }

            if($capper_id !== null) {
                $where[] = '`capper_id` = :capper_id';
            }

            if($capper_subscription_id !== null) {
                $where[] = '`capper_subscription_id` = :capper_subscription_id';
            }

            if($user_id !== null) {
                $where[] = '`user_id` = :user_id';
            }

            if($chat_id !== null) {
                $where[] = '`chat_id` = :chat_id';
            }

            if($start_timestamp !== null) {
                $where[] = '`start_timestamp` = :start_timestamp';
            }

            if($end_timestamp !== null) {
                $where[] = '`end_timestamp` = :end_timestamp';
            }

            if($paid !== null) {
                $where[] = '`paid` = :paid';
            }

            if(count($where)) {
                $sql .= ' WHERE '.join(' AND ', $where);
            }

            if ($limit !== null) {
                $sql .= ' LIMIT :limit';
            }

            $sth = self::$pdo->prepare($sql);

            if($id !== null) {
                $sth->bindValue(':id', $id, PDO::PARAM_INT);
            }

            if($capper_id !== null) {
                $sth->bindValue(':capper_id', $capper_id, PDO::PARAM_INT);
            }

            if($capper_subscription_id !== null) {
                $sth->bindValue(':capper_subscription_id', $capper_subscription_id, PDO::PARAM_INT);
            }

            if($user_id !== null) {
                $sth->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            }

            if($chat_id !== null) {
                $sth->bindValue(':chat_id', $chat_id, PDO::PARAM_INT);
            }

            if($start_timestamp !== null) {
                $sth->bindValue(':start_timestamp', $start_timestamp, PDO::PARAM_INT);
            }

            if($end_timestamp !== null) {
                $sth->bindValue(':end_timestamp', $end_timestamp, PDO::PARAM_INT);
            }

            if($paid !== null) {
                $sth->bindValue(':paid', $paid, PDO::PARAM_INT);
            }

            if ($limit !== null) {
                $sth->bindValue(':limit', $limit, PDO::PARAM_INT);
            }

            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public function selectActiveSubscriber() 
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_SUBSCRIBER . '`
              WHERE `paid` = 1
              AND `end_timestamp` > "'.time().'"
            ';

            $sth = self::$pdo->prepare($sql);

            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }    
    }

    /**
     * Insert the subscriber in the database
     *
     * @param int $id
     * @param int $sended
     *
     * @return bool
     * @throws TelegramException
     */
    public static function insertSubscriber($capper_id = null, $capper_subscription_id = null, $user_id = null, $chat_id = null, $start_timestamp = null, $end_timestamp = null, $paid = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_SUBSCRIBER . '`
                (`capper_id`, `capper_subscription_id`, `user_id`, `chat_id`, `start_timestamp`, `end_timestamp`, `paid`)
                VALUES
                (:capper_id, :capper_subscription_id, :chat_id, :start_timestamp, :start_timestamp, :end_timestamp, :paid)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':capper_id', $capper_id);
            $sth->bindValue(':capper_subscription_id', $capper_subscription_id);
            $sth->bindValue(':user_id', $user_id);
            $sth->bindValue(':chat_id', $chat_id);
            $sth->bindValue(':start_timestamp', $start_timestamp);
            $sth->bindValue(':end_timestamp', $end_timestamp);
            $sth->bindValue(':paid', $paid);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    /**
     * Update a specific run
     *
     * @param array $fields_values
     * @param array $where_fields_values
     *
     * @return bool
     * @throws TelegramException
     */
    public static function updateSubscriber(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_SUBSCRIBER, $fields_values, $where_fields_values);
    }
}
