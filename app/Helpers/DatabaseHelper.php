<?php

class DatabaseHelper
{
    private static $connection = null;

    /**
     * Get database connection
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            try {
                $config = require_once __DIR__ . '/../../config/database.php';
                
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                
                self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }

    /**
     * Execute a query and return results
     */
    public static function query($sql, $params = [])
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all results
     */
    public static function fetchAll($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single result
     */
    public static function fetchOne($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Insert record and return last insert ID
     */
    public static function insert($table, $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = self::query($sql, $data);
        return self::getConnection()->lastInsertId();
    }

    /**
     * Update record(s)
     */
    public static function update($table, $data, $where, $whereParams = [])
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete record(s)
     */
    public static function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Check if record exists
     */
    public static function exists($table, $where, $params = [])
    {
        $sql = "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1";
        $result = self::fetchOne($sql, $params);
        return $result !== false;
    }

    /**
     * Count records
     */
    public static function count($table, $where = '1=1', $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = self::fetchOne($sql, $params);
        return $result['count'];
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit()
    {
        return self::getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback()
    {
        return self::getConnection()->rollBack();
    }

    /**
     * Paginate results
     */
    public static function paginate($table, $page = 1, $perPage = 10, $where = '1=1', $params = [], $orderBy = 'id DESC')
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalSql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $total = self::fetchOne($totalSql, $params)['count'];
        
        // Get results
        $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $results = self::fetchAll($sql, $params);
        
        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
}