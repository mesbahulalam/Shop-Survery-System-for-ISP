<?php
// list.php
require_once 'db.php';

class ShopList extends Database {
    public function __construct() {
        parent::__construct(); // Call parent constructor to initialize $db
    }

    public function getShops($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM shops ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $perPage, SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $shops = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['phones'] = json_decode($row['phones'], true);
            $shops[] = $row;
        }
        
        return $shops;
    }
    
    public function getTotalShops() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM shops");
        return $result->fetchArray(SQLITE3_ASSOC)['count'];
    }
    
    public function deleteShop($id) {
        $stmt = $this->db->prepare("DELETE FROM shops WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }
}


$shopList = new ShopList();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$totalShops = $shopList->getTotalShops();
$totalPages = ceil($totalShops / $perPage);
$shops = $shopList->getShops($page, $perPage);

// Handle delete request
if (isset($_POST['delete'])) {
    $shopList->deleteShop($_POST['delete']);
    header('Location: list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Shop List</h1>
                <a href="create.html" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Add New Shop</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proprietor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phones</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($shops as $shop): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($shop['shop_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($shop['proprietor']) ?></td>
                            <td class="px-6 py-4">
                                <?php foreach ($shop['phones'] as $phone): ?>
                                    <div class="text-sm"><?= htmlspecialchars($phone) ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($shop['current_isp']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($shop['current_billing_date']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($shop['interested']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Interested
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Not Interested
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?= $shop['id'] ?>" 
                                        class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="delete" value="<?= $shop['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-6">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" 
                           class="<?= $i === $page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>