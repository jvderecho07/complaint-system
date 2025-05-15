<?php
class Complaint {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function create($user_id, $title, $description, $category, $image_path = null) {
        $stmt = $this->db->prepare("INSERT INTO complaints (user_id, title, description, category, image_path) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $title, $description, $category, $image_path]);
    }

    public function update($complaint_id, $title, $description, $category, $image_path = null) {
        if ($image_path) {
            $stmt = $this->db->prepare("UPDATE complaints SET title = ?, description = ?, category = ?, image_path = ? WHERE complaint_id = ?");
            return $stmt->execute([$title, $description, $category, $image_path, $complaint_id]);
        } else {
            $stmt = $this->db->prepare("UPDATE complaints SET title = ?, description = ?, category = ? WHERE complaint_id = ?");
            return $stmt->execute([$title, $description, $category, $complaint_id]);
        }
    }

    public function delete($complaint_id) {
        $stmt = $this->db->prepare("UPDATE complaints SET is_deleted = 1 WHERE complaint_id = ?");
        return $stmt->execute([$complaint_id]);
    }

    public function getAll($user_id = null) {
        if (User::isAdmin() && $user_id === null) {
            $query = "SELECT c.*, u.first_name, u.last_name FROM complaints c JOIN users u ON c.user_id = u.user_id WHERE c.is_deleted = 0 ORDER BY c.updated_at DESC";
            return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $user_id = $user_id ?? $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT * FROM complaints WHERE user_id = ? AND is_deleted = 0 ORDER BY updated_at DESC");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function updateStatus($complaint_id, $new_status) {
        $stmt = $this->db->prepare("UPDATE complaints SET status = ? WHERE complaint_id = ?");
        return $stmt->execute([$new_status, $complaint_id]);
    }

    public function getById($complaint_id) {
        $stmt = $this->db->prepare("SELECT c.*, u.first_name, u.last_name FROM complaints c JOIN users u ON c.user_id = u.user_id WHERE c.complaint_id = ?");
        $stmt->execute([$complaint_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function canEdit($complaint_id, $user_id, $user_role) {
        return $user_role === 'admin' || $this->isOwner($complaint_id, $user_id);
    }

    private function isOwner($complaint_id, $user_id) {
        $stmt = $this->db->prepare("SELECT user_id FROM complaints WHERE complaint_id = ?");
        $stmt->execute([$complaint_id]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($complaint && $complaint['user_id'] == $user_id);
    }
}