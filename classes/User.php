<?php
class User {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function register($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
            $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
            return $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $hashed,
                $data['phone'] ?? null,
                $data['address'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Registration failed: " . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            return true;
        }
        return false;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}