<?php
require_once __DIR__ . '/../configs/database.php';

abstract class BaseModel {
    // Database connection
    protected static $_connection;

    public function __construct() {
        if (!isset(self::$_connection)) {
            mysqli_report(MYSQLI_REPORT_OFF); // tắt warning mặc định, ta xử lý lỗi thủ công
            self::$_connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            if (self::$_connection->connect_errno) {
                throw new Exception("Kết nối database thất bại: " . self::$_connection->connect_error);
            }
            self::$_connection->set_charset("utf8mb4");
        }
    }

    public function __destruct() {
        // không đóng connection nếu dùng lại ở nhiều model trong cùng request;
        // chỉ đóng khi PHP kết thúc (optional). Nếu muốn đóng sớm, có thể thực hiện.
        if (self::$_connection) {
            // Không gọi close ở đây nếu có khả năng nhiều instance dùng chung trong cùng request.
            // Nếu bạn muốn đóng, uncomment dòng tiếp theo:
            // self::$_connection->close();
        }
    }

    /**
     * Helper bind params using references to support bind_param requirement
     */
    protected function bindParams(mysqli_stmt $stmt, string $types, array $params) {
        if ($types === '' || empty($params)) {
            return;
        }
        // build array of references
        $bindNames = [];
        $bindNames[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            // must use variable reference
            $bindNames[] = &$params[$i];
        }
        // call bind_param with references
        call_user_func_array([$stmt, 'bind_param'], $bindNames);
    }

    /**
     * Thực thi SELECT với prepared statement
     * @param string $sql
     * @param array $params
     * @param string $types
     * @return array
     * @throws Exception
     */
    protected function select($sql, $params = [], $types = '') {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Lỗi prepare (SELECT): " . self::$_connection->error);
        }

        if (!empty($params)) {
            if ($types === '') {
                $types = str_repeat('s', count($params));
            }
            $this->bindParams($stmt, $types, $params);
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Lỗi execute (SELECT): " . $err);
        }

        $resultRows = [];

        // Nếu get_result khả dụng (mysqlnd)
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $resultRows[] = $row;
                }
                $result->free();
            }
        } else {
            // Fallback: bind_result + fetch
            $meta = $stmt->result_metadata();
            if ($meta) {
                $fields = [];
                $row = [];
                while ($field = $meta->fetch_field()) {
                    $fields[] = $field->name;
                    $row[$field->name] = null;
                }
                $meta->free();

                // create references
                $refs = [];
                foreach ($row as $key => $val) {
                    $refs[] = &$row[$key];
                }
                call_user_func_array([$stmt, 'bind_result'], $refs);

                while ($stmt->fetch()) {
                    $tmp = [];
                    foreach ($fields as $f) {
                        $tmp[$f] = $row[$f];
                    }
                    $resultRows[] = $tmp;
                }
            }
        }

        $stmt->close();
        return $resultRows;
    }

    /**
     * Thực thi INSERT với prepared statement
     * @param string $sql
     * @param array $params
     * @param string $types
     * @return int insert_id
     * @throws Exception
     */
    protected function insert($sql, $params = [], $types = '') {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Lỗi prepare (INSERT): " . self::$_connection->error);
        }

        if (!empty($params)) {
            if ($types === '') {
                $types = str_repeat('s', count($params));
            }
            $this->bindParams($stmt, $types, $params);
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Lỗi execute (INSERT): " . $err);
        }

        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Thực thi UPDATE với prepared statement
     * @param string $sql
     * @param array $params
     * @param string $types
     * @return int affected_rows
     * @throws Exception
     */
    protected function update($sql, $params = [], $types = '') {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Lỗi prepare (UPDATE): " . self::$_connection->error);
        }

        if (!empty($params)) {
            if ($types === '') {
                $types = str_repeat('s', count($params));
            }
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

    /**
     * Thực thi DELETE với prepared statement
     * @param string $sql
     * @param array $params
     * @param string $types
     * @return int affected_rows
     * @throws Exception
     */
    protected function delete($sql, $params = [], $types = '') {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Lỗi prepare (DELETE): " . self::$_connection->error);
        }

        if (!empty($params)) {
            if ($types === '') {
                $types = str_repeat('s', count($params));
            }
            $this->bindParams($stmt, $types, $params);
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Lỗi execute (DELETE): " . $err);
        }

        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
}
