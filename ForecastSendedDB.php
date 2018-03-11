<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class ForecastSendedDB extends DB
{
    /**
     * Initialize forecast_sended table
     */
    public static function initializeForecastSended()
    {
        if (!defined('TB_FORECAST_SENDED_SENDED')) {
            define('TB_FORECAST_SENDED', self::$table_prefix . 'forecast_sended');
        }
    }

    /**
     * Select a forecast_sended from the DB
     *
     * @param int   $id
     * @param int   $forecast_id
     * @param int   $subscriber_id
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectForecastSended($id = null, $forecast_id = null, $subscriber_id = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_FORECAST_SENDED . '`
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

            if($forecast_id !== null) {
                $where[] = '`forecast_id` = :forecast_id';
            }

            if($subscriber_id !== null) {
                $where[] = '`subscriber_id` = :subscriber_id';
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

            if($forecast_id !== null) {
                $sth->bindValue(':forecast_id', $forecast_id, PDO::PARAM_INT);
            }

            if($subscriber_id !== null) {
                $sth->bindValue(':subscriber_id', $subscriber_id, PDO::PARAM_INT);
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
     * Insert the forecast in the database
     *
     * @param string $name
     * @param string $description
     * @param int $sending_timestamp
     * @param int $disabling_timestamp
     * @param int $subscriber_id
     *
     * @return string last insert id
     * @throws TelegramException
     */
    public static function insertForecastSended($forecast_id, $subscriber_id)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_FORECAST_SENDED . '`
                (`forecast_id`, `subscriber_id`)
                VALUES
                (:forecast_id, :subscriber_id)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':forecast_id', $forecast_id);
            $sth->bindValue(':subscriber_id', $subscriber_id);

            $sth->execute();

            return self::$pdo->lastInsertId();
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
    public static function updateForecastSended(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_FORECAST_SENDED, $fields_values, $where_fields_values);
    }

    public function deleteForecast($id) 
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('DELETE FROM `' . TB_FORECAST_SENDED . '`
                WHERE `id` = :id
            ');

            $sth->bindValue(':id', $id);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}
