<?php
require_once __DIR__ . '/../../config/database.php';

class ServiceController {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function getAll(){
        $stmt = $this->conn->prepare("SELECT * FROM services ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(){
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM services");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    public function getPaginated($limit = 6, $offset = 0){
        $stmt = $this->conn->prepare("
            SELECT * FROM services 
            ORDER BY created_at DESC, name ASC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){
        $stmt = $this->conn->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data){
        $stmt = $this->conn->prepare("
            INSERT INTO services (name, description, price, duration, image) 
            VALUES (:name, :description, :price, :duration, :image)
        ");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration' => $data['duration'] ?? null,
            'image' => $data['image'] ?? null
        ]);
    }

    public function update($id, $data){
        $stmt = $this->conn->prepare("
            UPDATE services SET 
                name = :name, 
                description = :description, 
                price = :price, 
                duration = :duration, 
                image = :image 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration' => $data['duration'] ?? null,
            'image' => $data['image'] ?? null
        ]);
    }

    public function delete($id){
        // Xóa ảnh nếu có
        $service = $this->getById($id);
        if($service && $service['image']){
            $imagePath = __DIR__ . '/../../public/uploads/services/' . $service['image'];
            if(file_exists($imagePath)){
                @unlink($imagePath);
            }
        }
        
        $stmt = $this->conn->prepare("DELETE FROM services WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

