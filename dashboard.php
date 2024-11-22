<?php
// Include file config.php untuk koneksi ke database
require 'config.php';

// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Daftar tabel yang tersedia
$available_tables = [
    'sales2024' => 'Sales 2024',
    'sale_2024' => 'Sale 2024',
    'pbc_gt' => 'PBC GT',
    'pbc_gt_mt' => 'PBC GT MT',
    'pbc_mt' => 'PBC MT',
    'sbc_gt' => 'SBC GT',
    'sbc_gt_mt' => 'SBC GT MT',
    'sbc_mt' => 'SBC MT',
    'sdn_gt' => 'SDN GT',
    'sdn_gt_mt' => 'SDN GT MT',
    'sdn_mt' => 'SDN MT',
    'sales_jan24' => 'Sales January 2024',
    'sales_feb24' => 'Sales February 2024',
    'sales_mar24' => 'Sales March 2024',
    'sales_apr24' => 'Sales April 2024',
    'sales_mei24' => 'Sales May 2024',
    'sales_jun24' => 'Sales June 2024',
    'sales_jul24' => 'Sales July 2024',
    'sales_agu24' => 'Sales August 2024',
    'sales_sep24' => 'Sales September 2024'
];

// Ambil tabel yang dipilih, default ke 'sale_2024'
$table_selected = isset($_GET['table']) ? $_GET['table'] : 'sale_2024';

// Validasi tabel yang dipilih
if (!array_key_exists($table_selected, $available_tables)) {
    $table_selected = 'sale_2024';
}

// Function to get column names from a table
function getTableColumns($conn, $table) {
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    return $columns;
}

// Get table columns
$table_columns = getTableColumns($conn, $table_selected);

// Function to safely query distinct values from a column
function getDistinctValues($conn, $table, $column) {
    if (in_array($column, getTableColumns($conn, $table))) {
        return $conn->query("SELECT DISTINCT $column FROM $table");
    }
    return false;
}

// Safely get distinct values
$months = in_array('Month', $table_columns) ? getDistinctValues($conn, $table_selected, 'Month') : false;
$regions = in_array('Region', $table_columns) ? getDistinctValues($conn, $table_selected, 'Region') : false;
$products = in_array('Product', $table_columns) ? getDistinctValues($conn, $table_selected, 'Product') : false;
$stpn = in_array('Ship_To_Party_Name', $table_columns) ? getDistinctValues($conn, $table_selected, 'Ship_To_Party_Name') : false;

// Initialize filter variables
$month_filter = isset($_GET['month']) ? $_GET['month'] : '';
$channel_filter = isset($_GET['channel']) ? $_GET['channel'] : '';
$region_filter = isset($_GET['region']) ? $_GET['region'] : '';
$product_filter = isset($_GET['product']) ? $_GET['product'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : '';
$ship_tpn = isset($_GET['partyName']) ? $_GET['partyName'] : '';

// Build the base query
$query = "SELECT * FROM $table_selected WHERE 1=1";

// Add filters only if the columns exist
if ($month_filter && in_array('Month', $table_columns)) {
    $query .= " AND Month = '" . $conn->real_escape_string($month_filter) . "'";
}
if ($channel_filter && in_array('Channel', $table_columns)) {
    $query .= " AND Channel = '" . $conn->real_escape_string($channel_filter) . "'";
}
if ($region_filter && in_array('Region', $table_columns)) {
    $query .= " AND Region = '" . $conn->real_escape_string($region_filter) . "'";
}
if ($product_filter && in_array('Product', $table_columns)) {
    $query .= " AND Product = '" . $conn->real_escape_string($product_filter) . "'";
}
if ($search && in_array('Product', $table_columns)) {
    $query .= " AND Product LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if ($ship_tpn && in_array('Ship_To_Party_Name', $table_columns)) {
    $query .= " AND Ship_To_Party_Name LIKE '%" . $conn->real_escape_string($ship_tpn) . "%'";
}

// Add sorting
if (in_array('id', $table_columns)) {
    if ($sort_by == 'New') {
        $query .= " ORDER BY id DESC";
    } elseif ($sort_by == 'Latest') {
        $query .= " ORDER BY id ASC";
    }
}

// Get total filtered records
$total_result = $conn->query($query);
$total_data = $total_result ? $total_result->num_rows : 0;

// Pagination
$limit = 100;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Final query with pagination
$sql = $query . " LIMIT $limit OFFSET $offset";
$data_result = $conn->query($sql);

// Calculate total pages
$total_pages = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Performa Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100%;
    background-color: #1e40af;
    padding-top: 20px;
    transition: all 0.3s;
    z-index: 1000;
}

.sidebar-header {
    text-align: center;
    padding: 20px;
}

.sidebar h1 {
    font-size: 1.5rem;
    margin-bottom: 20px;
    color: white;
    font-family: 'Arial', sans-serif;
    font-weight: bold;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
}

.sidebar-link {
    display: block;
    padding: 15px;
    color: white;
    text-decoration: none;
    transition: background 0.3s;
    font-family: 'Arial', sans-serif;
}

.sidebar-link:hover, .sidebar-link.active {
    background-color: #2563eb;
}

.main-content {
    margin-left: 250px;
    transition: margin-left 0.3s;
}

.header {
    background-color: #3b82f6;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 999;
}

.header-title {
    font-size: 1.25rem;
    font-weight: bold;
    font-family: 'Arial', sans-serif;
}

.header-button {
    background-color: #10b981;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    text-decoration: none;
    font-family: 'Arial', sans-serif;
    transition: background 0.3s;
}

.header-button:hover {
    background-color: #059669;
}

@media (max-width: 768px) {
    .sidebar {
        left: -250px;
    }

    .main-content {
        margin-left: 0;
    }

    .sidebar-toggle {
        display: block;
        background-color: #1e40af;
        color: white;
        padding: 10px;
        font-size: 1.5rem;
        border: none;
        cursor: pointer;
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1001;
    }

    .sidebar.active {
        left: 0;
    }

    .header-title {
        font-size: 1rem;
    }

    .header.sidebar-active {
        z-index: 998;
    }
}



    </style>
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar bg-blue-800 z-10 text-white">
        <div class="sidebar-header">
            <h1>Performance Sales</h1>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="sidebar-link">Dashboard</a></li>
            <li><a href="display.php" class="sidebar-link">Display</a></li>
            <li><a href="dashboard.php" class="sidebar-link active">Data Management</a></li>
            <li><a href="create.php" class="sidebar-link">Tambah Penjualan</a></li>
            <li><a href="outlet.php" class="sidebar-link">Outlet</a></li>
            <li><a href="tesChart.php" class="sidebar-link">Chart</a></li>
        </ul>
    </div>

    <!-- Button to toggle sidebar on mobile -->
    <button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>

    <!-- Main Content -->
    <div class="main-content">
        <header class="header flex justify-between items-center sticky top-0 text-white">
            <h1 class="header-title">Data Penjualan - <?php echo htmlspecialchars($available_tables[$table_selected]); ?></h1>
        </header>

        <main class="mt-4 px-4">
            <!-- Form Pencarian dan Filter -->
            <form method="GET" class="mb-4">
                <!-- Table Selection -->
                <select name="table" class="border rounded px-4 py-2 mr-2" onchange="this.form.submit()">
                    <?php foreach ($available_tables as $table_key => $table_name): ?>
                        <option value="<?php echo htmlspecialchars($table_key); ?>" 
                                <?php echo $table_selected === $table_key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($table_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if (in_array('Ship_To_Party_Name', $table_columns)): ?>
                    <input type="text" name="partyName" id="partyName" 
                           value="<?php echo htmlspecialchars($ship_tpn); ?>" 
                           placeholder="Party Name" 
                           class="border rounded px-4 py-2 mr-2">
                <?php endif; ?>

                <?php if ($products): ?>
                    <select name="product" class="border rounded px-4 py-2 mr-2">
                        <option value="">Produk</option>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['Product']); ?>" 
                                    <?php echo $product_filter === $row['Product'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['Product']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                <?php endif; ?>

                <?php if ($months): ?>
                    <select name="month" class="border rounded px-4 py-2 mr-2">
                        <option value="">Bulan</option>
                        <?php while ($row = $months->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['Month']); ?>" 
                                    <?php echo $month_filter === $row['Month'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['Month']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                <?php endif; ?>

                <?php if (in_array('Channel', $table_columns)): ?>
                    <select name="channel" class="border rounded px-4 py-2 mr-2">
                        <option value="">Channel</option>
                        <option value="MT" <?php echo $channel_filter === 'MT' ? 'selected' : ''; ?>>MT</option>
                        <option value="GT" <?php echo $channel_filter === 'GT' ? 'selected' : ''; ?>>GT</option>
                    </select>
                <?php endif; ?>

                <?php if ($regions): ?>
                    <select name="region" class="border rounded px-4 py-2 mr-2">
                        <option value="">Region</option>
                        <?php while ($row = $regions->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['Region']); ?>" 
                                    <?php echo $region_filter === $row['Region'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['Region']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                <?php endif; ?>

                <select name="sort" class="border rounded px-4 py-2 mr-2">
                    <option value="">Sorted By</option>
                    <option value="New" <?php echo $sort_by === 'New' ? 'selected' : ''; ?>>Newest</option>
                    <option value="Latest" <?php echo $sort_by === 'Latest' ? 'selected' : ''; ?>>Oldest</option>
                </select>

                <button type="submit" class="bg-green-500 text-white rounded px-4 py-2">Cari</button>
            </form>

            <!-- Tabel Data Penjualan -->
            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead>
                        <tr>
                            <?php foreach ($table_columns as $column): ?>
                                <?php if ($column != 'id'): ?>
                                    <th class="border px-4 py-2"><?php echo htmlspecialchars(str_replace('_', ' ', $column)); ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <th class="border px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($data_result && $data_result->num_rows > 0): ?>
                            <?php while ($row = $data_result->fetch_assoc()): ?>
                                <tr>
                                    <?php foreach ($table_columns as $column): ?>
                                        <?php if ($column != 'id'): ?>
                                            <td class="border px-4 py-2">
                                                <?php echo isset($row[$column]) ? htmlspecialchars($row[$column]) : ''; ?>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <td class="border px-4 py-2 flex justify-center items-center gap-2">
                                    <a href="view.php?id=<?php echo htmlspecialchars($row['id'] ?? ''); ?>&table=<?php echo urlencode($table_selected); ?>" 
                                    class="bg-blue-500 text-white px-2 py-1 rounded">View</a>
                                    <a href="update.php?id=<?php echo htmlspecialchars($row['id'] ?? ''); ?>&table=<?php echo urlencode($table_selected); ?>" 
                                    class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</a>
                                    <a href="delete.php?id=<?php echo htmlspecialchars($row['id'] ?? ''); ?>&table=<?php echo urlencode($table_selected); ?>" 
                                    class="bg-red-500 text-white px-2 py-1 rounded"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Delete</a>
                                </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($table_columns); ?>" class="border px-4 py-2 text-center">
                                    Tidak ada data yang ditemukan
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-between items-center">
                <div>
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_data); ?> of <?php echo $total_data; ?> entries
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page - 1); ?>&table=<?php echo urlencode($table_selected); ?>&month=<?php echo urlencode($month_filter); ?>&channel=<?php echo urlencode($channel_filter); ?>&region=<?php echo urlencode($region_filter); ?>&product=<?php echo urlencode($product_filter); ?>&sort=<?php echo urlencode($sort_by); ?>&partyName=<?php echo urlencode($ship_tpn); ?>" 
                           class="bg-blue-500 text-white px-4 py-2 rounded">Previous</a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo ($page + 1); ?>&table=<?php echo urlencode($table_selected); ?>&month=<?php echo urlencode($month_filter); ?>&channel=<?php echo urlencode($channel_filter); ?>&region=<?php echo urlencode($region_filter); ?>&product=<?php echo urlencode($product_filter); ?>&sort=<?php echo urlencode($sort_by); ?>&partyName=<?php echo urlencode($ship_tpn); ?>" 
                           class="bg-blue-500 text-white px-4 py-2 rounded">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Optional JavaScript -->
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const header = document.querySelector('.header');

        sidebarToggle.addEventListener('click', () => {
            // Toggle sidebar visibility
            sidebar.classList.toggle('active');
        
            // Toggle the header's z-index to send it behind the sidebar on mobile
            header.classList.toggle('sidebar-active');
        });

        // Add any JavaScript functionality here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight active sidebar link
            const currentPath = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>