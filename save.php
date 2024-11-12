<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $data = [
        'shop_name' => $_POST['shop_name'],
        'proprietor' => $_POST['proprietor'],
        'phones' => $_POST['phones'],
        'current_isp' => $_POST['current_isp'],
        'package_name' => $_POST['package_name'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'interested' => isset($_POST['interested']),
        'notes' => $_POST['notes']
    ];
    
    if ($db->insertShop($data)) {
        header('Location: create.html?success=1');
    } else {
        header('Location: create.html?error=1');
    }
    exit;
}