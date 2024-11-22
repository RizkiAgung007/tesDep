<?php
// Include file config.php untuk koneksi ke database
include 'config.php';

// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to get data for charts
function getDataForCharts($conn) {
    $queries = [
        'monthly' => "  SELECT 'January' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_jan24) AS total_amount,
                            (SELECT SUM(Jan_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'February' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_feb24) AS total_amount,
                            (SELECT SUM(Feb_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'March' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_mar24) AS total_amount,
                            (SELECT SUM(Mar_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'April' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_apr24) AS total_amount,
                            (SELECT SUM(Apr_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'May' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_mei24) AS total_amount,
                            (SELECT SUM(May_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'June' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_jun24) AS total_amount,
                            (SELECT SUM(Jun_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'July' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_jul24) AS total_amount,
                            (SELECT SUM(Jul_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'August' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_agu24) AS total_amount,
                            (SELECT SUM(Aug_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'September' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_sep24) AS total_amount,
                            (SELECT SUM(Sep_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'October' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_okt24) AS total_amount,
                            (SELECT SUM(Oct_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'November' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_nov24) AS total_amount,
                            (SELECT SUM(Nov_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
                        UNION
                        SELECT 'December' AS month, 
                            (SELECT SUM(GI_Amount) FROM sales_des24) AS total_amount,
                            (SELECT SUM(Dec_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total;

",
                          
        'quarterly' => " SELECT 'Q1' AS quarter, 
        SUM(GI_Amount) AS total_sales, 
        (SELECT SUM(Jan_24_Value_Rupiah) + SUM(Feb_24_Value_Rupiah) + SUM(Mar_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
    FROM (
        SELECT GI_Amount FROM sales_jan24
        UNION ALL
        SELECT GI_Amount FROM sales_feb24
        UNION ALL
        SELECT GI_Amount FROM sales_mar24
    ) AS q1_sales

    UNION ALL

    SELECT 'Q2' AS quarter, 
        SUM(GI_Amount) AS total_sales,
        (SELECT SUM(Apr_24_Value_Rupiah) + SUM(May_24_Value_Rupiah) + SUM(Jun_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
    FROM (
        SELECT GI_Amount FROM sales_apr24
        UNION ALL
        SELECT GI_Amount FROM sales_mei24
        UNION ALL
        SELECT GI_Amount FROM sales_jun24
    ) AS q2_sales

    UNION ALL

    SELECT 'Q3' AS quarter, 
        SUM(GI_Amount) AS total_sales,
        (SELECT SUM(Jul_24_Value_Rupiah) + SUM(Aug_24_Value_Rupiah) + SUM(Sep_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
    FROM (
        SELECT GI_Amount FROM sales_jul24
        UNION ALL
        SELECT GI_Amount FROM sales_agu24
        UNION ALL
        SELECT GI_Amount FROM sales_sep24
    ) AS q3_sales

    UNION ALL

    SELECT 'Q4' AS quarter, 
        SUM(GI_Amount) AS total_sales,
        (SELECT SUM(Oct_24_Value_Rupiah) + SUM(Nov_24_Value_Rupiah) + SUM(Dec_24_Value_Rupiah) FROM sbc_gt_mt) AS target_total
    FROM (
        SELECT GI_Amount FROM sales_okt24
        UNION ALL
        SELECT GI_Amount FROM sales_nov24
        UNION ALL
        SELECT GI_Amount FROM sales_des24
    ) AS q4_sales;
",

        
        'yearly' => "SELECT 2024 as year,
                    -- (SELECT SUM(CAST(Volume_Target_2024_Karton AS UNSIGNED)) FROM projek.sbc_gt) + 
                    (SELECT SUM(GI_Amount) FROM projek.sales2024) as total_sales,
                    (SELECT SUM(Value_Target_2024_Rupiah) FROM projek.sbc_gt_mt) as total_target",

        'area' =>   "SELECT Region as area_name, SUM(GI_Amount) as total_sales 
                    FROM projek.sales2024
                    GROUP BY Region 
                    ORDER BY total_sales DESC",
    
        'topAreas' =>   "SELECT Region as area_name, SUM(GI_Amount) as total_sales 
                        FROM projek.sales2024 
                        GROUP BY Region 
                        ORDER BY total_sales DESC 
                        LIMIT 5",
        
        'topProducts' =>    "SELECT Product as product_name,
                            SUM(GI_Amount) as total_sales FROM projek.sales2024
                            GROUP BY Product 
                            ORDER BY total_sales DESC 
                            LIMIT 5",
        
        'giAmount' => "SELECT SUM(GI_Amount) AS total_gi_amount FROM projek.sales2024",
        'giRupiah' => "SELECT SUM(Value_Target_2024_Rupiah) AS total_rupiah FROM projek.sbc_gt_mt"

    ];

    $data = [];
    foreach ($queries as $key => $query) {
        try {
            $result = $conn->query($query);
            if ($result === false) {
                throw new Exception("Query failed: " . $conn->error);
            }
            $data[$key] = $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error in query '$key': " . $e->getMessage());
            $data[$key] = []; // Return empty array for this query
        }
    }
    
    return $data;
}

// Usage
try {
    $data = getDataForCharts($conn);
} catch (Exception $e) {
    error_log("Fatal error in getDataForCharts: " . $e->getMessage());
    die("An error occurred while retrieving data. Please check the error log for details.");
}

// Calculate KPIs
$totalGIAmount = !empty($data['giAmount']) ? (int)$data['giAmount'][0]['total_gi_amount'] : 0;
$totalSales = $totalGIAmount; // Menggunakan GI Amount sebagai total sales

$totalgiRupiah = !empty($data['giRupiah']) ? (int)$data['giRupiah'][0]['total_rupiah'] : 0;
$totalTarget = $totalgiRupiah;
// $totalTarget = 475590081681; // Target tetap

$achievementPercentage = ($totalTarget > 0) ? ($totalSales / $totalTarget) * 100 : 0;

// Debugging
error_log("Total Sales (GI Amount): $totalSales, Total Target: $totalTarget, Achievement: $achievementPercentage%");
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

    /* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100%;
    background-color: #1e40af;
    padding-top: 20px;
    transition: all 0.3s;
    z-index: 1000; /* Sidebar di atas konten lainnya */
}

/* Sidebar Header */
.sidebar-header {
    text-align: center;
    padding: 20px;
}

/* Sidebar Menu */
.sidebar-menu {
    list-style: none;
    padding: 0;
}

/* Sidebar Links */
.sidebar-link {
    display: block;
    padding: 15px;
    color: white;
    text-decoration: none;
    transition: background 0.3s;
}

/* Hover and active state for sidebar links */
.sidebar-link:hover, .sidebar-link.active {
    background-color: #2563eb;
}

/* Main Content Styles */
.main-content {
    margin-left: 250px;
    transition: margin-left 0.3s;
}

/* Header Styles */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #3b82f6;
    padding: 10px 20px;
    color: white;
    position: sticky;
    top: 0;
    z-index: 999; /* Set header behind the sidebar */
}

.header-title {
    font-size: 1.25rem;
    font-weight: bold;
}

/* Media Query for Mobile */
@media (max-width: 768px) {
    /* Hide the sidebar on mobile */
    .sidebar {
        left: -250px;
    }

    .main-content {
        margin-left: 0;
    }

    /* Button to show sidebar on mobile */
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
        z-index: 1001; /* Make sure toggle button is on top */
    }

    /* Sidebar active class (when shown on mobile) */
    .sidebar.active {
        left: 0;
    }

    /* Ensure the header stays behind when the sidebar is active */
    .header {
        z-index: 998; /* Header stays behind sidebar */
    }

    .header-title {
        font-size: 1rem;
    }
}


    </style>
</head>

<body class="bg-gray-100">
    <div id="sidebar" class="sidebar z-10 bg-blue-800 text-white">
        <div class="text-center p-5">
            <h1 class="text-white text-xl font-bold">Performance Sales</h1>
        </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="sidebar-link active">Dashboard</a></li>
                <li><a href="display.php" class="sidebar-link">Display</a></li>
                <li><a href="dashboard.php" class="sidebar-link">Data Management</a></li>
                <li><a href="create.php" class="sidebar-link">Tambah Penjualan</a></li>
                <li><a href="outlet.php" class="sidebar-link">Outlet</a></li>
                <li><a href="tesChart.php" class="sidebar-link">Chart</a></li>
            </ul>
        </div>

        <!-- Button to toggle sidebar on mobile -->
        <button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>

        <div id="content" class="main-content">
        <header class="header">
            <div class="header-title">
                <h1 class="text-xl font-bold">Dashboard Penjualan</h1>
            </div>
        </header>

        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">Total Penjualan</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalSales, 0, ',', '.'); ?></p>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">Total Target</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalTarget, 0, ',', '.'); ?></p>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">Pencapaian Target</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($achievementPercentage, 2); ?>%</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Penjualan vs Target Bulanan</h2>
                    <canvas id="monthlyChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Penjualan vs Target Kuartalan</h2>
                    <canvas id="quarterlyChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Penjualan vs Target Tahunan</h2>
                    <canvas id="yearlyChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Penjualan per Area</h2>
                    <canvas id="areaChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Top Performing Areas</h2>
                    <canvas id="topAreasChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Top Products</h2>
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Get the sidebar and toggle button elements
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebarToggle');

        // Add event listener to toggle sidebar
        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });


        // Data for charts
        const monthlyData = <?php echo json_encode($data['monthly']); ?>;
        const quarterlyData = <?php echo json_encode([
            'sales' => array_column($data['quarterly'], 'total_sales'),
            'targets' => array_column($data['quarterly'], 'target_total')
        ]); ?>;
        const yearlyData = <?php echo json_encode($data['yearly']); ?>;
        const areaData = <?php echo json_encode($data['area']); ?>;
        const topAreasData = <?php echo json_encode($data['topAreas']); ?>;
        const topProductsData = <?php echo json_encode($data['topProducts']); ?>;

        // Debugging
        console.log('Monthly Data:', monthlyData);
        console.log('Quarterly Data:', quarterlyData);
        console.log('Yearly Data:', yearlyData);
        console.log('Top Products Data:', topProductsData);
        

        // Monthly Chart
        const monthlyChartCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyChartCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month), // Display months as labels
                datasets: [{
                    label: 'Total Penjualan',
                    data: monthlyData.map(item => item.total_amount),  // Sum of sales amount
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                },
                {
                    label: 'Total Target',
                    data: monthlyData.map(item => item.target_total),  // Sum of target quantity
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false
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

        // Quarterly Chart
        const quarterlyChartCtx = document.getElementById('quarterlyChart').getContext('2d');
new Chart(quarterlyChartCtx, {
    type: 'bar',
    data: {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        datasets: [
            {
                label: 'Total Penjualan',
                data: quarterlyData.sales,  // menggunakan data penjualan
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: true
            },
            {
                label: 'Total Target',
                data: quarterlyData.targets,  // menggunakan data target
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Kuartal'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Total Penjualan'
                }
            }
        }
    }
});


        // Yearly Chart
        const yearlyChartCtx = document.getElementById('yearlyChart').getContext('2d');
        new Chart(yearlyChartCtx, {
            type: 'bar',
            data: {
                labels: ['2024'], 
                datasets: [{
                    label: 'Total Penjualan',
                    data: [yearlyData[0].total_sales], 
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)', 
                    borderWidth: 1 
                },
                {
                    label: 'Total Target', 
                    data: [yearlyData[0].total_target], 
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)', 
                    borderWidth: 1 
                }]
            },
            options: {
                responsive: true, 
                scales: {
                    y: {
                        beginAtZero: true, 
                        suggestedMax: 1000000000000, // Nilai maksimum yang disarankan
                        ticks: {
                            callback: function(value) {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); 
                            },
                            stepSize: 10000000000, // Ukuran langkah
                        },
                        min: 0 
                    }
                }
            }
        });



        // Area Chart
        const areaChartCtx = document.getElementById('areaChart').getContext('2d');
        new Chart(areaChartCtx, {
            type: 'doughnut',
            data: {
                labels: areaData.map(data => data.area_name),
                datasets: [{
                    data: areaData.map(data => parseInt(data.total_sales)),
                    backgroundColor: areaData.map(() => 'rgba(' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math.random() * 255) + ', 0.6)'),
                }]
            },
            options: {
                responsive: true
            }
        });

        // Top Areas Chart
        const topAreasChartCtx = document.getElementById('topAreasChart').getContext('2d');
        new Chart(topAreasChartCtx, {
            type: 'bar',
            data: {
                labels: topAreasData.map(data => data.area_name),
                datasets: [{
                    label: 'Total Penjualan',
                    data: topAreasData.map(data => parseInt(data.total_sales)),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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

        // Top Products Chart
        const topProductsChartCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(topProductsChartCtx, {
            type: 'bar',
            data: {
                labels: topProductsData.map(data => data.product_name),
                datasets: [{
                    label: 'Total Penjualan',
                    data: topProductsData.map(data => parseFloat(data.total_sales) || 0),
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
</body>
</html>