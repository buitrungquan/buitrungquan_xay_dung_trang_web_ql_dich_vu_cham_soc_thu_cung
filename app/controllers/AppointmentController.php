<?php
require_once __DIR__ . '/../../config/database.php';

class AppointmentController {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function getAll(){
        $stmt = $this->conn->prepare("
            SELECT a.*, 
                   c.name as customer_name, c.phone as customer_phone,
                   p.name as pet_name, p.type as pet_type,
                   s.name as service_name, s.price as service_price
            FROM appointments a
            LEFT JOIN customers c ON a.customer_id = c.id
            LEFT JOIN pets p ON a.pet_id = p.id
            LEFT JOIN services s ON a.service_id = s.id
            ORDER BY a.appointment_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){
        $stmt = $this->conn->prepare("
            SELECT a.*, 
                   c.name as customer_name, c.phone as customer_phone,
                   p.name as pet_name, p.type as pet_type,
                   s.name as service_name, s.price as service_price
            FROM appointments a
            LEFT JOIN customers c ON a.customer_id = c.id
            LEFT JOIN pets p ON a.pet_id = p.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data){
        // Nếu pet_id là null và database yêu cầu, tạo pet mặc định
        $petId = $data['pet_id'];
        if($petId === null || $petId === ''){
            // Kiểm tra xem customer đã có pet nào chưa
            $stmt = $this->conn->prepare("SELECT id FROM pets WHERE customer_id = :customer_id LIMIT 1");
            $stmt->execute(['customer_id' => $data['customer_id']]);
            $existingPet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($existingPet){
                $petId = $existingPet['id'];
            } else {
                // Tạo pet mặc định cho customer
                $stmt = $this->conn->prepare("
                    INSERT INTO pets (customer_id, name, type, breed, age, gender) 
                    VALUES (:customer_id, 'Không xác định', 'other', NULL, NULL, NULL)
                ");
                $stmt->execute(['customer_id' => $data['customer_id']]);
                $petId = $this->conn->lastInsertId();
            }
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO appointments (customer_id, pet_id, service_id, appointment_date, status, notes) 
            VALUES (:customer_id, :pet_id, :service_id, :appointment_date, :status, :notes)
        ");
        return $stmt->execute([
            'customer_id' => $data['customer_id'],
            'pet_id' => $petId,
            'service_id' => $data['service_id'],
            'appointment_date' => $data['appointment_date'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null
        ]);
    }

    public function update($id, $data){
        $stmt = $this->conn->prepare("
            UPDATE appointments SET 
                customer_id = :customer_id, 
                pet_id = :pet_id, 
                service_id = :service_id, 
                appointment_date = :appointment_date, 
                status = :status, 
                notes = :notes 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'customer_id' => $data['customer_id'],
            'pet_id' => $data['pet_id'],
            'service_id' => $data['service_id'],
            'appointment_date' => $data['appointment_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null
        ]);
    }

    public function updateStatus($id, $status){
        $stmt = $this->conn->prepare("UPDATE appointments SET status = :status WHERE id = :id");
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM appointments WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

