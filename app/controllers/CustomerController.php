<?php
require_once __DIR__ . '/../../config/database.php';

class CustomerController {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function getAll(){
        $stmt = $this->conn->prepare("SELECT * FROM customers ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){
        $stmt = $this->conn->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data){
        $stmt = $this->conn->prepare("INSERT INTO customers (name, email, phone, address) VALUES (:name, :email, :phone, :address)");
        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null
        ]);
    }

    public function update($id, $data){
        $stmt = $this->conn->prepare("UPDATE customers SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null
        ]);
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM customers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

