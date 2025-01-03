<?php
// db.php
class Database {
    protected $db;

    public function __construct() {
        try {
            $this->db = new SQLite3('shops.db');
            $this->createTable();
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    protected function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS shops (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            shop_name TEXT NOT NULL,
            proprietor TEXT NOT NULL,
            phones TEXT NOT NULL,
            current_isp TEXT,
            package_name TEXT,
            monthly_bill TEXT,
            billing_date TEXT,
            latitude TEXT,
            longitude TEXT,
            interested INTEGER DEFAULT 0,
            processed INTEGER DEFAULT 0,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->exec($query);

        // Add column if it doesn't exist (for existing databases)
        // $this->db->exec("ALTER TABLE shops ADD COLUMN processed INTEGER DEFAULT 0");
    }
    
    public function insertShop($data) {
        $stmt = $this->db->prepare("INSERT INTO shops (
            shop_name, proprietor, phones, current_isp, 
            package_name, monthly_bill, billing_date, 
            latitude, longitude, interested, notes
        ) VALUES (
            :shop_name, :proprietor, :phones, :current_isp,
            :package_name, :monthly_bill, :billing_date, 
            :latitude, :longitude, :interested, :notes
        )");
        
        $stmt->bindValue(':shop_name', $data['shop_name']);
        $stmt->bindValue(':proprietor', $data['proprietor']);
        $stmt->bindValue(':phones', json_encode($data['phones']));
        $stmt->bindValue(':current_isp', $data['current_isp']);
        $stmt->bindValue(':package_name', $data['package_name']);
        $stmt->bindValue(':monthly_bill', $data['monthly_bill']);
        $stmt->bindValue(':billing_date', $data['billing_date']);
        $stmt->bindValue(':latitude', $data['latitude']);
        $stmt->bindValue(':longitude', $data['longitude']);
        $stmt->bindValue(':interested', $data['interested'] ? 1 : 0);
        $stmt->bindValue(':notes', $data['notes']);
        
        return $stmt->execute();
    }
}