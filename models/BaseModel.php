<?php
// models/BaseModel.php
require_once __DIR__ . '/../configs/database.php';

abstract class BaseModel {
    protected static $_connection;

    public function __construct() {
        if (!isset(self::$_connection)) {
            mysqli_report(MYSQLI_REPORT_OFF);
            self::$_connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            if (self::$_connection->connect_errno) {
                throw new Exception("Kết nối database thất bại: " . self::$_connection->connect_error);
            }
            self::$_connection->set_charset("utf8mb4");
        }
    }

    // bind params with references (bind_param requires references)
    protected function bindParams(mysqli_stmt $stmt, string $types, array $params) {
        if ($types === '' || empty($params)) return;
        $bind = [$types];
        for ($i = 0; $i < count($params); $i++) {
            $bind[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind);
    }

    protected function select($sql, $params = [], $types = '') {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Lỗi prepare (SELECT): " . self::$_connection->error);
        }

        if (!empty($params)) {
            if ($types === '') $types = str_repeat('s', count($params));
            $this->bindParams($stmt, $types, $params);
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Lỗi execute (SELECT): " . $err);
        }

        $rows = [];
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            $res->free();
        } else {
            // fallback: not covering full general case for simplicity
        }

        $stmt->close();
        return $rows;
    }

    protected function insert($sql, $params = [], $types = '') {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Lỗi prepare (INSERT): " . self::$_connection->error);
        }

        if (!empty($params)) {
            if ($types === '') $types = str_repeat('s', count($params));
            $this->bindParams($stmt, $types, $params);
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Lỗi execute (INSERT): " . $err);
        }

        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    protected function update($sql, $params = [], $types = '') {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Lỗi prepare (UPDATE): " . self::$_connection->error);
        }

        if (!empty($params)) {
            if ($types === '') $types = str_repeat('s', count($params));
            $this->bindParams($stmt, $types, $params);
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Lỗi execute (UPDATE): " . $err);
        }

        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    protected function delete($sql, $params = [], $types = '') {
        return $this->update($sql, $params, $types);
    }
}
