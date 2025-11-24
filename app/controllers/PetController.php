<?php
require_once __DIR__ . '/../../config/database.php';

class PetController {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function getAll(){
        $stmt = $this->conn->prepare("
            SELECT p.*, c.name as customer_name 
            FROM pets p 
            LEFT JOIN customers c ON p.customer_id = c.id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){
        $stmt = $this->conn->prepare("
            SELECT p.*, c.name as customer_name 
            FROM pets p 
            LEFT JOIN customers c ON p.customer_id = c.id 
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCustomerId($customerId){
        $stmt = $this->conn->prepare("SELECT * FROM pets WHERE customer_id = :customer_id ORDER BY name");
        $stmt->execute(['customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data){
        $stmt = $this->conn->prepare("
            INSERT INTO pets (customer_id, name, type, breed, age, gender, photo, notes) 
            VALUES (:customer_id, :name, :type, :breed, :age, :gender, :photo, :notes)
        ");
        return $stmt->execute([
            'customer_id' => $data['customer_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'breed' => $data['breed'] ?? null,
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? null,
            'photo' => $data['photo'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
    }

    public function update($id, $data){
        $stmt = $this->conn->prepare("
            UPDATE pets SET 
                customer_id = :customer_id, 
                name = :name, 
                type = :type, 
                breed = :breed, 
                age = :age, 
                gender = :gender, 
                photo = :photo, 
                notes = :notes 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'customer_id' => $data['customer_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'breed' => $data['breed'] ?? null,
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? null,
            'photo' => $data['photo'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
    }

    public function delete($id){
        // Xóa ảnh nếu có
        $pet = $this->getById($id);
        if($pet && $pet['photo']){
            $photoPath = __DIR__ . '/../../public/uploads/' . $pet['photo'];
            if(file_exists($photoPath)){
                @unlink($photoPath);
            }
        }
        
        $stmt = $this->conn->prepare("DELETE FROM pets WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

