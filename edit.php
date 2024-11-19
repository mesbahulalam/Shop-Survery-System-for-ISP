<?php
// edit.php
require_once 'db.php';

class ShopEditor extends Database {
    public function __construct() {
        parent::__construct(); // Call parent constructor to initialize $db
    }
    
    public function getShop($id) {
        $stmt = $this->db->prepare("SELECT * FROM shops WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $shop = $result->fetchArray(SQLITE3_ASSOC);
        if ($shop) {
            $shop['phones'] = json_decode($shop['phones'], true);
        }
        return $shop;
    }
    
    public function updateShop($id, $data) {
        $stmt = $this->db->prepare("UPDATE shops SET 
            shop_name = :shop_name,
            proprietor = :proprietor,
            phones = :phones,
            current_isp = :current_isp,
            package_name = :package_name,
            billing_date = :billing_date,
            latitude = :latitude,
            longitude = :longitude,
            interested = :interested,
            notes = :notes
            WHERE id = :id");
        
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':shop_name', $data['shop_name']);
        $stmt->bindValue(':proprietor', $data['proprietor']);
        $stmt->bindValue(':phones', json_encode($data['phones']));
        $stmt->bindValue(':current_isp', $data['current_isp']);
        $stmt->bindValue(':package_name', $data['package_name']);
        $stmt->bindValue(':billing_date', $data['billing_date']);
        $stmt->bindValue(':latitude', $data['latitude']);
        $stmt->bindValue(':longitude', $data['longitude']);
        $stmt->bindValue(':interested', $data['interested'] ? 1 : 0);
        $stmt->bindValue(':notes', $data['notes']);
        
        return $stmt->execute();
    }
}

$editor = new ShopEditor();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'shop_name' => $_POST['shop_name'],
        'proprietor' => $_POST['proprietor'],
        'phones' => $_POST['phones'],
        'current_isp' => $_POST['current_isp'],
        'package_name' => $_POST['package_name'],
        'billing_date' => $_POST['billing_date'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'interested' => isset($_POST['interested']),
        'notes' => $_POST['notes']
    ];
    
    if ($editor->updateShop($_POST['id'], $data)) {
        header('Location: list.php?success=1');
        exit;
    }
}

// Get shop data
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$shop = $editor->getShop($id);

if (!$shop) {
    header('Location: list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Shop - <?= htmlspecialchars($shop['shop_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit Shop</h1>
            <a href="list.php" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Back to List</a>
        </div>
        
        <form action="edit.php" method="POST" id="shopForm" class="space-y-6">
            <input type="hidden" name="id" value="<?= $shop['id'] ?>">
            
            <!-- Shop Name -->
            <input type="text" name="shop_name" required placeholder="Shop Name" 
                value="<?= htmlspecialchars($shop['shop_name']) ?>"
                class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

            <!-- Proprietor -->
            <input type="text" name="proprietor" required placeholder="Shop Proprietor" 
                value="<?= htmlspecialchars($shop['proprietor']) ?>"
                class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

            <!-- Phone Numbers -->
            <div id="phoneFields" class="space-y-3">
                <?php foreach ($shop['phones'] as $index => $phone): ?>
                    <div class="flex gap-2">
                        <input type="tel" name="phones[]" required placeholder="Phone Number" 
                            value="<?= htmlspecialchars($phone) ?>"
                            class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php if ($index === 0): ?>
                            <button type="button" onclick="addPhoneField()"
                                class="px-4 h-12 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center min-w-[48px]">+</button>
                        <?php else: ?>
                            <button type="button" onclick="this.parentElement.remove()"
                                class="px-4 h-12 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center justify-center min-w-[48px]">-</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Current ISP -->
            <input type="text" name="current_isp" placeholder="Current ISP" 
                value="<?= htmlspecialchars($shop['current_isp']) ?>"
                class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

            <!-- Package Name/Speed -->
            <input type="text" name="package_name" placeholder="Package Name/Speed" 
                value="<?= htmlspecialchars($shop['package_name']) ?>"
                class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                
            <!-- Current Monthly Bill -->
            <input type="number" name="monthly_bill" placeholder="Current Monthly Bill"
                value="<?= htmlspecialchars($shop['monthly_bill']) ?>"
                class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                
            <!-- Billing Date -->
            <input type="date" name="billing_date" placeholder="Billing Date" 
                value="<?= htmlspecialchars($shop['billing_date']) ?>"
                class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

            <!-- Location -->
            <div class="flex gap-2">
                <input type="text" name="latitude" id="latitude" placeholder="Latitude" 
                    value="<?= htmlspecialchars($shop['latitude']) ?>"
                    class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <input type="text" name="longitude" id="longitude" placeholder="Longitude" 
                    value="<?= htmlspecialchars($shop['longitude']) ?>"
                    class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <button type="button" onclick="getLocation()"
                    class="px-4 h-12 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center justify-center min-w-[120px]">
                    Update Location
                </button>
            </div>

            <!-- Interested Toggle -->
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="interested" class="sr-only peer" <?= $shop['interested'] ? 'checked' : '' ?>>
                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 
                        rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white 
                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white 
                        after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all 
                        peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Interested in Our Services</span>
                </label>
            </div>

            <!-- Notes -->
            <textarea name="notes" rows="4" placeholder="Additional Notes"
                class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            ><?= htmlspecialchars($shop['notes']) ?></textarea>

            <!-- Submit Button -->
            <div class="flex gap-4">
                <button type="submit"
                    class="flex-1 h-12 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium">
                    Update Shop
                </button>
                <a href="list.php"
                    class="flex-1 h-12 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 font-medium flex items-center justify-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function addPhoneField() {
            const phoneFields = document.getElementById('phoneFields');
            const newField = document.createElement('div');
            newField.className = 'flex gap-2';
            newField.innerHTML = `
                <input type="tel" name="phones[]" required placeholder="Phone Number"
                    class="block w-full h-12 px-4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <button type="button" onclick="this.parentElement.remove()"
                    class="px-4 h-12 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center justify-center min-w-[48px]">-</button>
            `;
            phoneFields.appendChild(newField);
        }

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                    },
                    (error) => {
                        alert("Error getting location: " + error.message);
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    </script>
</body>
</html>
