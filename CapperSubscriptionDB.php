<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class CapperSubscriptionDB extends DB
{
    /**
     * Initialize capper_subscription table
     */
    public static function initializeCapperSubscription()
    {
        if (!defined('TB_CAPPER_SUBSCRIPTION')) {
            define('TB_CAPPER_SUBSCRIPTION', self::$table_prefix . 'capper_subscription');
        }
    }

    /**
     * Select a capper_subscriptions from the DB
     *
     * @param int   $id
     * @param int   $sended
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectCapperSubscription($id = null, $capper_id = null, $price = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_CAPPER_SUBSCRIPTION . '`
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

            if ($capper_id !== null) {
                $where[] = '`capper_id` = :capper_id';
            }
            
            if ($price !== null) {
                $where[] = '`price` = :price';
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

            if($price !== null) {
                $sth->bindValue(':price', $price, PDO::PARAM_INT);
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

    /**
     * Insert the capper_subscription in the database
     *
     * @param int $id
     * @param int $sended
     *
     * @return bool
     * @throws TelegramException
     */
    public static function insertCapperSubscription($capper_id, $name, $duration, $price)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_CAPPER_SUBSCRIPTION . '`
                (`capper_id`, `name`, `duration`, `price`)
                VALUES
                (:capper_id, :name, :duration, :price)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':capper_id', $capper_id);
            $sth->bindValue(':name', $name);
            $sth->bindValue(':duration', $description);
            $sth->bindValue(':price', $price);

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
    public static function updateCapperSubscription(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_CAPPER_SUBSCRIPTION, $fields_values, $where_fields_values);
    }
}
