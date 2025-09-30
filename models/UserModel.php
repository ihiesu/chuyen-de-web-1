<?php
// models/UserModel.php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {
    public function getUserByIdSingle($id) {
        $rows = $this->select("SELECT * FROM users WHERE id = ?", [$id], "i");
        return !empty($rows) ? $rows[0] : null;
    }

    public function findUserById($id) {
        $sql = 'SELECT * FROM users WHERE id = '.$id;
        $user = $this->select($sql);

        return $user;
    }

    
    public function findUser($keyword) {
        return $this->select(
            "SELECT * FROM users WHERE name LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%')",
            [$keyword, $keyword],
            "ss"
        );
    }

    public function auth($nameOrEmail, $password) {
        $rows = $this->select(
            "SELECT * FROM users WHERE (name = ? OR email = ?) LIMIT 1",
            [$nameOrEmail, $nameOrEmail],
            "ss"
        );
        if (empty($rows)) return null;
        $user = $rows[0];

        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            return $user;
        }

        // migrate md5 -> password_hash
        if (!empty($user['password']) && $user['password'] === md5($password)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->update("UPDATE users SET password = ? WHERE id = ?", [$newHash, $user['id']], "si");
            $user['password'] = $newHash;
            return $user;
        }

        return null;
    }

    public function insertUser($input) {
        if (empty($input['name']) || empty($input['password'])) {
            throw new Exception("Username và password bắt buộc.");
        }
        $sql = "INSERT INTO users (name, fullname, email, type, password) VALUES (?, ?, ?, ?, ?)";
        $params = [
            $input['name'],
            $input['fullname'] ?? '',
            $input['email'] ?? '',
            $input['type'] ?? '',
            password_hash($input['password'], PASSWORD_DEFAULT)
        ];
        return $this->insert($sql, $params, "sssss");
    }

    public function updateUser($input) {
        $fields = [];
        $params = [];
        $types = "";

        if (isset($input['name']) && $input['name'] !== '') {
            $fields[] = "name = ?";
            $params[] = $input['name'];
            $types .= "s";
        }
        if (isset($input['fullname'])) {
            $fields[] = "fullname = ?";
            $params[] = $input['fullname'];
            $types .= "s";
        }
        if (isset($input['email'])) {
            $fields[] = "email = ?";
            $params[] = $input['email'];
            $types .= "s";
        }
        if (isset($input['type'])) {
            $fields[] = "type = ?";
            $params[] = $input['type'];
            $types .= "s";
        }
        if (!empty($input['password'])) {
            if (strlen($input['password']) < 6) {
                throw new Exception("Mật khẩu quá ngắn (tối thiểu 6 ký tự).");
            }
            $fields[] = "password = ?";
            $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            $types .= "s";
        }

        if (empty($fields)) return 0;

        $params[] = $input['id'];
        $types .= "i";

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        return $this->update($sql, $params, $types);
    }

    public function deleteUserById($id) {
        return $this->delete("DELETE FROM users WHERE id = ?", [$id], "i");
    }

    public function getUsers($params = []) {
        if (!empty($params['keyword'])) {
            return $this->select("SELECT * FROM users WHERE name LIKE CONCAT('%', ?, '%')", [$params['keyword']], "s");
        }
        return $this->select("SELECT * FROM users");
    }

    
}
