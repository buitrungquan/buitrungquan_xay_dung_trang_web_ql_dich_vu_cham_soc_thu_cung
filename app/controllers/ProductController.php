<?php
require_once __DIR__ . '/../../config/database.php';

class ProductController {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function getAll($status = 'active', $search = null){
        $sql = "SELECT * FROM products";
        $conditions = [];
        $params = [];

        if($status){
            $conditions[] = "status = :status";
            $params['status'] = $status;
        }

        if($search){
            $conditions[] = "(name LIKE :search OR category LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if(!empty($conditions)){
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        foreach($params as $key => $value){
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($status = 'active'){
        $sql = "SELECT COUNT(*) as total FROM products";
        $params = [];
        if($status){
            $sql .= " WHERE status = :status";
            $params['status'] = $status;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    public function getPaginated($status = 'active', $limit = 8, $offset = 0){
        $sql = "SELECT * FROM products";
        $params = [];
        if($status){
            $sql .= " WHERE status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach($params as $key => $value){
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategory($category, $status = 'active'){
        $sql = "SELECT * FROM products WHERE category = :category";
        if($status){
            $sql .= " AND status = :status";
        }
        $sql .= " ORDER BY name";
        
        $stmt = $this->conn->prepare($sql);
        $params = ['category' => $category];
        if($status){
            $params['status'] = $status;
        }
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data){
        $stmt = $this->conn->prepare("
            INSERT INTO products (name, description, price, category, stock, image, status) 
            VALUES (:name, :description, :price, :category, :stock, :image, :status)
        ");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category' => $data['category'] ?? null,
            'stock' => $data['stock'] ?? 0,
            'image' => $data['image'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
    }

    public function update($id, $data){
        $stmt = $this->conn->prepare("
            UPDATE products SET 
                name = :name, 
                description = :description, 
                price = :price, 
                category = :category, 
                stock = :stock, 
                image = :image, 
                status = :status 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category' => $data['category'] ?? null,
            'stock' => $data['stock'] ?? 0,
            'image' => $data['image'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
    }

    public function delete($id){
        // Xóa ảnh nếu có
        $product = $this->getById($id);
        if($product && $product['image']){
            $imagePath = __DIR__ . '/../../public/uploads/products/' . $product['image'];
            if(file_exists($imagePath)){
                @unlink($imagePath);
            }
        }
        
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
