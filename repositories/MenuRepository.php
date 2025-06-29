<?php
require_once __DIR__ . '/../config/database.php';

class MenuRepository {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM menus ORDER BY name ASC");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : [];
        } catch (Exception $e) {
            error_log("Error in MenuRepository::getAll(): " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllPaginated($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM menus ORDER BY name ASC LIMIT ?, ?");
        $stmt->bindValue(1, $offset, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    public function create($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO menus (name, description, price, image_url, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'], 
                $data['description'], 
                $data['price'],
                $data['image'] ?? null,
                $data['category'] ?? 'main'
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Menu create error: " . $e->getMessage());
            return false;
        }
    }
      public function update($id, $data) {        try {
            $stmt = $this->db->prepare("UPDATE menus SET 
                name = ?, 
                description = ?, 
                price = ?, 
                image_url = ?,
                category = ?
                WHERE id = ?");
            
            return $stmt->execute([
                $data['name'], 
                $data['description'], 
                $data['price'],
                $data['image'] ?? null,
                $data['category'] ?? 'main',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Menu update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM menus WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Menu delete error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM menus");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    public function getByCategory($category) {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE category = ? ORDER BY name ASC");
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    // Get popular menu items (most ordered)
    public function getPopular($limit = 5) {
        // In a real application, this would join with order items to count popularity
        // For now, just return all items sorted by ID
        $limit = (int)$limit; // Convert to integer for safety
        $stmt = $this->db->prepare("SELECT * FROM menus ORDER BY id DESC LIMIT $limit");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function search($term) {
        $term = "%$term%";        $stmt = $this->db->prepare("SELECT * FROM menus WHERE name LIKE ? OR description LIKE ? ORDER BY name ASC");
        $stmt->execute([$term, $term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
