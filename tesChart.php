<?php
// Include file config.php untuk koneksi ke database
include 'config.php';

// Membuat query untuk mendapatkan data berdasarkan filter waktu
function getFilteredData($startDate, $endDate) {
    global $conn;

    // Query untuk data penjualan berdasarkan filter tanggal
    $query = "SELECT SUM(GI_Amount) AS total_penjualan, Actual_GI_Date
              FROM sales2024
              WHERE Actual_GI_Date BETWEEN ? AND ?
              GROUP BY Actual_GI_Date";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result;
}

function getTopProducts($startDate, $endDate) {
    global $conn;

    // Query untuk mendapatkan produk dengan penjualan tertinggi berdasarkan filter waktu
    $topProductsQuery = "SELECT Product as product_name,
                         SUM(GI_Amount) as total_sales, Actual_GI_Date 
                         FROM sales2024
                         WHERE Actual_GI_Date BETWEEN ? AND ?
                         GROUP BY Product 
                         ORDER BY total_sales DESC 
                         LIMIT 5";

    $stmt = $conn->prepare($topProductsQuery);
    $stmt->bind_param("ss", $startDate, $endDate); 
    $stmt->execute();
    $result = $stmt->get_result();

    // Mengambil data dan mengembalikannya sebagai array
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    return $products;
}




// Fungsi untuk mendapatkan total seluruh GI_Amount
function getTotalGIAmount() {
    global $conn;

    // Query untuk menghitung total seluruh GI_Amount dari tabel sales2024
    $query = "SELECT SUM(GI_Amount) AS total_amount FROM sales2024";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    return $row['total_amount'];
}

function getTotalTarget() {
    global $conn;

    // Query untuk menghitung total seluruh target GI_Amount
    $query = "SELECT SUM(Value_Target_2024_Rupiah) AS total_target FROM sbc_gt_mt";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    return $row['total_target'];
}

// Fungsi untuk mendapatkan jumlah Total Amount berdasarkan filter waktu
function getFilteredAmount($startDate, $endDate) {
    global $conn;

    // Query untuk menghitung jumlah GI_Amount dalam rentang tanggal yang difilter
    $query = "SELECT SUM(GI_Amount) AS amount FROM sales2024 WHERE Actual_GI_Date BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['amount'];
}

// Ambil nilai start_date dan end_date dari parameter GET
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Memanggil fungsi untuk mendapatkan data dan total GI_Amount berdasarkan filter waktu
$filteredData = getFilteredData($startDate, $endDate);
$totalGIAmount = getTotalGIAmount();
$totalTarget = getTotalTarget();
$filteredAmount = getFilteredAmount($startDate, $endDate);
$topProducts = getTopProducts($startDate, $endDate);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Performa Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
<div id="sidebar" class="sidebar bg-blue-800 z-10 text-white">
        <div class="sidebar-header">
            <h1>Performance Sales</h1>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="sidebar-link">Dashboard</a></li>
            <li><a href="display.php" class="sidebar-link">Display</a></li>
            <li><a href="dashboard.php" class="sidebar-link">Data Management</a></li>
            <li><a href="create.php" class="sidebar-link">Tambah Penjualan</a></li>
            <li><a href="outlet.php" class="sidebar-link">Outlet</a></li>
            <li><a href="tesChart.php" class="sidebar-link active">Chart</a></li>
        </ul>
    </div>

    <!-- Button to toggle sidebar on mobile -->
    <button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>

    <div id="content" class="main-content">
        <header class="header">
            <div class="header-title">
                <h1 class="text-xl font-bold text-white">Dashboard Penjualan</h1>
            </div>
        </header>

        <div class="container mx-auto grid md:grid-cols-3 grid-cols-1">
            <!-- Tampilan Total GI_Amount -->
            <div class="my-4 mx-4 shadow-lg w-96 px-4 py-6 rounded-lg">
                <h3 class="text-lg font-semibold">Total Amount
                    <span class="text-blue-600">
                        Rp. <?= number_format($totalGIAmount, 2, ',', '.'); ?>
                    </span>
                </h3>
            </div>

            <!-- Tampilan Total Target -->
            <div class="my-4 mx-4 shadow-lg w-96 px-4 py-6 rounded-lg">
                <h3 class="text-lg font-semibold">Total Target
                    <span class="text-blue-600">
                        Rp. <?= number_format($totalTarget, 2, ',', '.'); ?>
                    </span>
                </h3>
            </div>
            
            <!-- Tampilan Total Amount berdasarkan filter waktu -->
            <div class="my-4 mx-4 shadow-lg w-96 px-4 py-6 rounded-lg">
                <h3 class="text-lg font-semibold">Amount
                    <span class="text-blue-600">
                        Rp. <?= number_format($filteredAmount, 2, ',', '.'); ?>
                    </span>
                </h3>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Form Filter -->
            <form id="filterForm" method="GET" action="" class="mb-6">
                <div class="w-full flex flex-row gap-4 px-4 py-2 mb-4 border border-gray-300 rounded-lg shadow-sm focus-within:ring-2 focus-within:ring-blue-500">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                        <label for="start_date">Tanggal Mulai:</label>
                        <input type="text" id="start_date" name="start_date" placeholder="Masukkan YYYY-MM-DD" class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $startDate; ?>">
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                        <label for="end_date">Tanggal Akhir:</label>
                        <input type="text" id="end_date" name="end_date" placeholder="Masukkan YYYY-MM-DD" class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $endDate; ?>">
                    </div>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    Apply Filters
                </button>
            </form>


            <!-- Chart Section -->
            <?php if ($filteredData && $startDate && $endDate): ?>
                <div class="bg-white rounded-lg shadow-md p-6 grid grid-cols-2 gap-8">
                    <div class="mb-20">
                        <canvas id="barChart" width="400" height="200"></canvas>
                    </div>
                    <div class="mb-20">
                        <canvas id="lineChart" width="400" height="200"></canvas>
                    </div>
                    <div class="mb-20">
                        <canvas id="pieChart" width="400" height="200"></canvas>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Top Products</h2>
                        <canvas id="topProductsChart" width="400" height="200"></canvas>
                    </div>
                </div>

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

                const topProductsData = <?php echo json_encode($topProducts); ?>;

                // Fungsi untuk mengelompokkan data berdasarkan bulan dan tahun
                function groupDataByMonthAndYear(data) {
                    const groupedData = {};
                    data.forEach((item) => {
                        const date = new Date(item.Actual_GI_Date);
                        const key = date.toLocaleString('default', { month: 'short', year: 'numeric' });
            
                        if (!groupedData[key]) {
                            groupedData[key] = { total_penjualan: 0};
                        }
            
                        groupedData[key].total_penjualan += item.total_penjualan;
                    });
        
                    return groupedData;
                }

                // Data untuk grafik
                var rawData = [
                    <?php while ($row = $filteredData->fetch_assoc()): ?>
                    {
                        Actual_GI_Date: "<?= $row['Actual_GI_Date']; ?>",
                        total_penjualan: <?= $row['total_penjualan']; ?>,
                    },
                    <?php endwhile; ?>
                ];

                console.log(rawData);

                // Menentukan rentang waktu lebih dari satu bulan
                var startDate = new Date("<?= $startDate; ?>");
                var endDate = new Date("<?= $endDate; ?>");
                var isMoreThanOneMonth = (endDate - startDate) > (30 * 24 * 60 * 60 * 1000); // 30 hari dalam milidetik

                var labels = [];
                var totalPenjualan = [];

                if (isMoreThanOneMonth) {
                    // Kelompokkan data berdasarkan bulan dan tahun jika rentang waktu lebih dari satu bulan
                    var groupedData = groupDataByMonthAndYear(rawData);
                    for (const [key, values] of Object.entries(groupedData)) {
                        labels.push(key);
                        totalPenjualan.push(values.total_penjualan);
                    }
                } else {
                // Tampilkan tanggal harian jika rentang waktu hanya dalam satu bulan
                    rawData.forEach((item) => {
                        var date = new Date(item.Actual_GI_Date);
                        var formattedDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                        labels.push(formattedDate);
                        totalPenjualan.push(item.total_penjualan);
                    });
                }

                // Bar Chart
                var barChartCtx = document.getElementById('barChart').getContext('2d');
                var barChart = new Chart(barChartCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Total Penjualan',
                                data: totalPenjualan,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Line Chart
                var lineChartCtx = document.getElementById('lineChart').getContext('2d');
                var lineChart = new Chart(lineChartCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Total Penjualan',
                                data: totalPenjualan,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                fill: false,
                                tension: 0.1
                            },
                        ]
                    }
                });

                // Pie Chart
                var pieChartCtx = document.getElementById('pieChart').getContext('2d');
                var pieChart = new Chart(pieChartCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Total Penjualan',
                                data: totalPenjualan,
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                        ]
                    },
                        options: {
                            plugins: {
                                legend: {
                                    position: 'right'
                            }
                        }
                    }
                });

                // Top Products Chart
const topProductsChartCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(topProductsChartCtx, {
    type: 'bar',
    data: {
        labels: topProductsData.map(data => data.product_name),  // Menampilkan nama produk
        datasets: [{
            label: 'Total Penjualan',
            data: topProductsData.map(data => parseFloat(data.total_sales) || 0),  // Menampilkan total penjualan
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

        </script>

            <?php else: ?>
                <p class="text-red-500">Tidak ada data untuk rentang tanggal yang dipilih.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
