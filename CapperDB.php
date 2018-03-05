<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class CapperDB extends DB
{
    /**
     * Initialize capper table
     */
    public static function initializeCapper()
    {
        if (!defined('TB_CAPPER')) {
            define('TB_CAPPER', self::$table_prefix . 'capper');
        }
    }

    /**
     * Select a cappers from the DB
     *
     * @param int   $id
     * @param int   $sended
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectCapper($id = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_CAPPER . '`
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
     * Insert the capper in the database
     *
     * @param int $id
     * @param int $sended
     *
     * @return bool
     * @throws TelegramException
     */
    public static function insertCapper($name, $description)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_CAPPER . '`
                (`name`, `description`)
                VALUES
                (:name, :description)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':name', $name);
            $sth->bindValue(':description', $description);

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
    public static function updateCapper(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_CAPPER, $fields_values, $where_fields_values);
    }
}
