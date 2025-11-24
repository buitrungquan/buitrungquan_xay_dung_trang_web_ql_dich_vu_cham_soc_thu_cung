<?php
require_once __DIR__ . '/../../config/database.php';

class InvoiceController {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function getAll(){
        $stmt = $this->conn->prepare("
            SELECT i.*, 
                   a.appointment_date,
                   c.name as customer_name, c.phone as customer_phone,
                   p.name as pet_name,
                   s.name as service_name
            FROM invoices i
            LEFT JOIN appointments a ON i.appointment_id = a.id
            LEFT JOIN customers c ON a.customer_id = c.id
            LEFT JOIN pets p ON a.pet_id = p.id
            LEFT JOIN services s ON a.service_id = s.id
            ORDER BY i.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){
        $stmt = $this->conn->prepare("
            SELECT i.*, 
                   a.appointment_date,
                   c.name as customer_name, c.phone as customer_phone, c.address as customer_address,
                   p.name as pet_name, p.type as pet_type,
                   s.name as service_name, s.description as service_description
            FROM invoices i
            LEFT JOIN appointments a ON i.appointment_id = a.id
            LEFT JOIN customers c ON a.customer_id = c.id
            LEFT JOIN pets p ON a.pet_id = p.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE i.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($appointmentId, $totalAmount){
        // Tạo số hóa đơn tự động
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Kiểm tra số hóa đơn đã tồn tại chưa
        while($this->invoiceNumberExists($invoiceNumber)){
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        $stmt = $this->conn->prepare("
            INSERT INTO invoices (appointment_id, invoice_number, total_amount, payment_status) 
            VALUES (:appointment_id, :invoice_number, :total_amount, 'unpaid')
        ");
        return $stmt->execute([
            'appointment_id' => $appointmentId,
            'invoice_number' => $invoiceNumber,
            'total_amount' => $totalAmount
        ]);
    }

    public function updatePaymentStatus($id, $status, $paymentDate = null){
        $stmt = $this->conn->prepare("
            UPDATE invoices SET 
                payment_status = :payment_status, 
                payment_date = :payment_date 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'payment_status' => $status,
            'payment_date' => $paymentDate
        ]);
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM invoices WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function invoiceNumberExists($invoiceNumber){
        $stmt = $this->conn->prepare("SELECT id FROM invoices WHERE invoice_number = :invoice_number");
        $stmt->execute(['invoice_number' => $invoiceNumber]);
        return $stmt->fetch() !== false;
    }
}

