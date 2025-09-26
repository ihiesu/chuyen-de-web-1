<?php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {

    /**
     * Tìm user theo ID (trả mảng)
     */
    public function findUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->select($sql, [$id], "i");
    }

    /**
     * Trả về 1 user (hoặc null)
     */
    public function getUserByIdSingle($id) {
        $rows = $this->findUserById($id);
        return !empty($rows) ? $rows[0] : null;
    }

    /**
     * Tìm user theo keyword (username hoặc email)
     */
    public function findUser($keyword) {
        $sql = "SELECT * FROM users 
                WHERE name LIKE CONCAT('%', ?, '%') 
                   OR email LIKE CONCAT('%', ?, '%')";
        return $this->select($sql, [$keyword, $keyword], "ss");
    }

    /**
     * Xác thực đăng nhập user (tìm theo name OR email)
     * Trả về user (mảng) hoặc null
     */
    public function auth($userNameOrEmail, $password) {
        $sql = "SELECT * FROM users WHERE (name = ? OR email = ?) LIMIT 1";
        $users = $this->select($sql, [$userNameOrEmail, $userNameOrEmail], "ss");

        if (empty($users)) {
            return null;
        }

        $user = $users[0];

        // Nếu password đã hash chuẩn
        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            return $user;
        }

        // Nếu DB còn lưu md5 -> xác thực + migrate sang password_hash
        if (!empty($user['password']) && $user['password'] === md5($password)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password = ? WHERE id = ?";
            $this->update($updateSql, [$newHash, $user['id']], "si");
            $user['password'] = $newHash;
            return $user;
        }

        return null;
    }

    /**
     * Xóa user theo ID
     */
    public function deleteUserById($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->delete($sql, [$id], "i");
    }

    /**
     * Cập nhật user
     * $input: associative array gồm id + các trường cần update
     */
    public function updateUser($input) {
        $fields = [];
        $params = [];
        $types = "";

        if (!empty($input['name'])) {
            $fields[] = "name = ?";
            $params[] = $input['name'];
            $types .= "s";
        }

        if (isset($input['fullname']) && $input['fullname'] !== '') {
            $fields[] = "fullname = ?";
            $params[] = $input['fullname'];
            $types .= "s";
        }

        if (isset($input['email']) && $input['email'] !== '') {
            $fields[] = "email = ?";
            $params[] = $input['email'];
            $types .= "s";
        }

        if (isset($input['type']) && $input['type'] !== '') {
            $fields[] = "type = ?";
            $params[] = $input['type'];
            $types .= "s";
        }

        if (!empty($input['password'])) {
            // validate password length tối thiểu
            if (strlen($input['password']) < 6) {
                throw new Exception("Mật khẩu quá ngắn (ít nhất 6 ký tự).");
            }
            $fields[] = "password = ?";
            $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            $types .= "s";
        }

        if (empty($fields)) {
            return 0; // không có gì để update
        }

        $params[] = $input['id'];
        $types .= "i";

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        return $this->update($sql, $params, $types);
    }

    /**
     * Thêm user mới
     */
    public function insertUser($input) {
        // Basic validation
        if (empty($input['name']) || empty($input['password'])) {
            throw new Exception("Username và password là bắt buộc.");
        }

        $sql = "INSERT INTO users (name, fullname, email, type, password) 
                VALUES (?, ?, ?, ?, ?)";
        $params = [
            $input['name'],
            $input['fullname'] ?? '',
            $input['email'] ?? '',
            $input['type'] ?? '',
            password_hash($input['password'], PASSWORD_DEFAULT)
        ];
        return $this->insert($sql, $params, "sssss");
    }

    /**
     * Lấy danh sách user (có filter keyword)
     */
    public function getUsers($params = [], $limit = null, $offset = null) {
        if (!empty($params['keyword'])) {
            $sql = "SELECT * FROM users WHERE name LIKE CONCAT('%', ?, '%')";
            return $this->select($sql, [$params['keyword']], "s");
        } else {
            $sql = "SELECT * FROM users";
            if ($limit !== null && $offset !== null) {
                // ensure integers
                $limit = (int)$limit;
                $offset = (int)$offset;
                $sql .= " LIMIT ? OFFSET ?";
                return $this->select($sql, [$limit, $offset], "ii");
            }
            return $this->select($sql);
        }
    }
}
