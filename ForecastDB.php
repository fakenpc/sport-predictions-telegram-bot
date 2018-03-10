<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class ForecastDB extends DB
{
    /**
     * Initialize forecast table
     */
    public static function initializeForecast()
    {
        if (!defined('TB_FORECAST')) {
            define('TB_FORECAST', self::$table_prefix . 'forecast');
        }
    }

    /**
     * Select a forecasts from the DB
     *
     * @param int   $id
     * @param int   $capper_id
     * @param int   $sended
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectForecast($id = null, $capper_id = null, $sended = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_FORECAST . '`
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

            if($sended !== null) {
                $where[] = '`sended` = :sended';
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

            if($sended !== null) {
                $sth->bindValue(':sended', $sended, PDO::PARAM_INT);
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
     * @param int $sended
     *
     * @return string last insert id
     * @throws TelegramException
     */
    public static function insertForecast($capper_id, $name, $description, $sending_timestamp, $disabling_timestamp, $sended)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_FORECAST . '`
                (`capper_id`, `name`, `description`, `sending_timestamp`, `disabling_timestamp`, `sended`)
                VALUES
                (:capper_id, :name, :description, :sending_timestamp, :disabling_timestamp, :sended)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':capper_id', $capper_id);
            $sth->bindValue(':name', $name);
            $sth->bindValue(':description', $description);
            $sth->bindValue(':sending_timestamp', $sending_timestamp);
            $sth->bindValue(':disabling_timestamp', $disabling_timestamp);
            $sth->bindValue(':sended', $sended);

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
    public static function updateForecast(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_FORECAST, $fields_values, $where_fields_values);
    }
}
