<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $sale_date = $_POST['Month'];  // Perbaiki sesuai nama input
    $area_name = $_POST['Ship_To_Party'];
    $party_name = $_POST['Ship_To_Party_Name'];
    $channel = $_POST['Channel'];
    $region = $_POST['Region'];
    $sales_office = $_POST['Sales_Office_Description'];
    $type_customer = $_POST['Type_Customer'];
    $product_name = $_POST['product_name'];
    $gi_ktn = $_POST['GI_ktn'];  // Perbaiki sesuai nama input
    $gi_amount = $_POST['Gi_amount'];  // Perbaiki sesuai nama input

    // Query SQL untuk memasukkan data ke tabel sale_2024
    $sql = "INSERT INTO sale_2024 (Month, Ship_To_Party, Ship_To_Party_Name, Channel, Region, Sales_Office_Description, Type_Customer, Product, GI_ktn, Gi_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssdd", 
        $sale_date,          // Menyimpan data bulan (sesuaikan sesuai struktur database)
        $area_name,          // Area (Ship_To_Party)
        $party_name,         // Ship_To_Party_Name
        $channel,            // Channel
        $region,             // Region
        $sales_office,       // Sales Office Description
        $type_customer,      // Type Customer
        $product_name,       // Product
        $gi_ktn,             // GI_ktn
        $gi_amount           // Gi_amount
    );
    
    if ($stmt->execute()) {
        // Redirect setelah berhasil menyimpan
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch product names and areas for dropdowns
$product_names = $conn->query("SELECT DISTINCT Product FROM sale_2024");
$areas = $conn->query("SELECT DISTINCT Ship_To_Party FROM sale_2024");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penjualan Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<style>
    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100%;
        background-color: #1e40af; /* Warna sidebar */
        padding-top: 20px;
    }

    .sidebar-header {
        text-align: center;
        padding: 20px;
    }

    .sidebar h1 {
        font-size: 1.5rem; /* Ukuran font di sidebar */
        margin-bottom: 20px;
        color: white;
        font-family: 'Arial', sans-serif; /* Jenis font di sidebar */
        font-weight: bold; /* Menjadikan teks lebih tebal */
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
        font-family: 'Arial', sans-serif; /* Jenis font untuk link di sidebar */
    }

    .sidebar-link:hover, .sidebar-link.active {
        background-color: #2563eb; /* Warna saat hover atau aktif */
    }

    .main-content {
        margin-left: 250px;
        padding: 20px;
    }
</style>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Performance Sales</h1>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="sidebar-link">Dashboard</a></li>
            <li><a href="dashboard.php" class="sidebar-link active">Data Management</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-64 p-6">
        <header class="flex justify-between items-center bg-blue-500 text-white p-4 rounded">
            <h1 class="text-lg font-bold">Tambah Penjualan Baru</h1>
            <nav>
                <a href="dashboard.php" class="bg-green-500 px-4 py-2 rounded">Kembali ke Dashboard</a>
            </nav>
        </header>

        <main class="mt-4">
            <form method="POST" class="bg-white p-6 rounded shadow">
                <div class="mb-4">
                    <label for="Month" class="block text-sm font-medium text-gray-700">Bulan</label>
                    <input type="text" name="Month" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="mb-4">
                    <label for="Ship_To_Party" class="block text-sm font-medium text-gray-700">Ship To Party</label>
                    <input type="text" name="Ship_To_Party" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="mb-4">
                    <label for="Ship_To_Party_Name" class="block text-sm font-medium text-gray-700">Ship To Party Name</label>
                    <input type="text" name="Ship_To_Party_Name" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="mb-4">
                    <label for="Channel" class="block text-sm font-medium text-gray-700">Channel</label>
                    <select name="Channel" required class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="mt">MT</option>
                        <option value="gt">GT</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="Region" class="block text-sm font-medium text-gray-700">Region</label>
                    <input type="text" name="Region" step="0.01" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="mb-4">
                    <label for="Sales_Office_Description" class="block text-sm font-medium text-gray-700">Sales Office Description</label>
                    <input type="text" name="Sales_Office_Description" step="0.01" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="mb-4">
                    <label for="Type_Customer" class="block text-sm font-medium text-gray-700">Type Customer</label>
                    <input type="text" name="Type_Customer" step="0.01" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="mb-4">
                    <label for="product_name" class="block text-sm font-medium text-gray-700">Nama Produk:</label>
                    <select name="product_name" required class="mt-1 block w-full border rounded px-3 py-2">
                        <?php while ($row = $product_names->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['Product']); ?>"><?php echo htmlspecialchars($row['Product']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="GI_ktn" class="block text-sm font-medium text-gray-700">GI ktn</label>
                    <input type="number" name="GI_ktn" step="0.01" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="mb-4">
                    <label for="Gi_amount" class="block text-sm font-medium text-gray-700">GI amount</label>
                    <input type="number" name="Gi_amount" step="0.01" required class="mt-1 block w-full border rounded px-3 py-2" />
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
