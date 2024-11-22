<?php
require_once 'config.php';

// Function to get data based on selected filters
function getFilteredData($table, $shipToParty = '', $shipToPartyName = '', $channel = '', $region = '', $salesOffice = '', $search = '', $page = 1, $limit = 100, $startDate = '', $endDate = '') {
    global $conn;
    
    $offset = ($page - 1) * $limit;
    
    $whereClause = [];
    if ($shipToParty) $whereClause[] = "Ship_To_Party = '$shipToParty'";
    if ($shipToPartyName) $whereClause[] = "Ship_To_Party_Name = '$shipToPartyName'";
    if ($channel) $whereClause[] = "Channel = '$channel'";
    if ($region) $whereClause[] = "Region = '$region'";
    if ($salesOffice) $whereClause[] = "Sales_Office_Description = '$salesOffice'";
    if ($startDate && $endDate) {
        $whereClause[] = "Actual_GI_Date BETWEEN '$startDate' AND '$endDate'";
    }
    
    // Add search functionality across multiple columns
    if ($search) {
        $searchClause = "(Ship_To_Party LIKE '%$search%' OR 
                         Ship_To_Party_Name LIKE '%$search%' OR 
                         Channel LIKE '%$search%' OR 
                         Region LIKE '%$search%' OR 
                         Sales_Office_Description LIKE '%$search%' OR
                         Actual_GI_Date LIKE '%$search%')";
        $whereClause[] = $searchClause;
    }
    
    $whereString = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";
    
    // Get total records for pagination
    $countSql = "SELECT COUNT(*) as total FROM $table $whereString";
    $countResult = $conn->query($countSql);
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    // Get filtered data
    $sql = "SELECT * FROM $table $whereString LIMIT $offset, $limit";
    $result = $conn->query($sql);
    
    return [
        'data' => $result,
        'total' => $totalRecords,
        'totalPages' => ceil($totalRecords / $limit)
    ];
}

// Function to get unique values for dropdowns
function getUniqueValues($table, $column) {
    global $conn;
    $sql = "SELECT DISTINCT $column FROM $table WHERE $column IS NOT NULL AND $column != '' ORDER BY $column";
    return $conn->query($sql);
}

// Get current page, search term and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$selectedTable = isset($_GET['table']) ? $_GET['table'] : 'sales2024';
$selectedShipToParty = isset($_GET['ship_to_party']) ? $_GET['ship_to_party'] : '';
$selectedShipToPartyName = isset($_GET['ship_to_party_name']) ? $_GET['ship_to_party_name'] : '';
$selectedChannel = isset($_GET['channel']) ? $_GET['channel'] : '';
$selectedRegion = isset($_GET['region']) ? $_GET['region'] : '';
$selectedSalesOffice = isset($_GET['sales_office']) ? $_GET['sales_office'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';


// Get filtered data
$result = getFilteredData(
    $selectedTable,
    $selectedShipToParty,
    $selectedShipToPartyName,
    $selectedChannel,
    $selectedRegion,
    $selectedSalesOffice,
    $search,
    $page,
    100, 
    $startDate,
    $endDate
);


$data = $result['data'];
$totalPages = $result['totalPages'];

// Function to get total GI_Amount based on selected filters
function getTotalGIAmount($table, $shipToParty = '', $shipToPartyName = '', $channel = '', $region = '', $salesOffice = '', $search = '', $startDate = '', $endDate = '') {
    global $conn;

    $whereClause = [];
    if ($shipToParty) $whereClause[] = "Ship_To_Party = '$shipToParty'";
    if ($shipToPartyName) $whereClause[] = "Ship_To_Party_Name = '$shipToPartyName'";
    if ($channel) $whereClause[] = "Channel = '$channel'";
    if ($region) $whereClause[] = "Region = '$region'";
    if ($salesOffice) $whereClause[] = "Sales_Office_Description = '$salesOffice'";
    if ($startDate && $endDate) {
        $whereClause[] = "Actual_GI_Date BETWEEN '$startDate' AND '$endDate'";
    }

    // Add search functionality across multiple columns
    if ($search) {
        $searchClause = "(Ship_To_Party LIKE '%$search%' OR 
                         Ship_To_Party_Name LIKE '%$search%' OR 
                         Channel LIKE '%$search%' OR 
                         Region LIKE '%$search%' OR 
                         Sales_Office_Description LIKE '%$search%' OR
                         Actual_GI_Date LIKE '%$search%')";
        $whereClause[] = $searchClause;
    }

    $whereString = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";

    // Query to get the sum of GI_Amount
    $sql = "SELECT SUM(GI_Amount) as total_amount FROM $table $whereString";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total_amount'] ?? 0;
}

$totalGIAmount = getTotalGIAmount(
    $selectedTable,
    $selectedShipToParty,
    $selectedShipToPartyName,
    $selectedChannel,
    $selectedRegion,
    $selectedSalesOffice,
    $search,
    $startDate,
    $endDate
);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Outlet</title>
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

        .table-container {
            overflow-x: auto;
        }

        th {
            position: sticky;
            top: 0;
            background-color: #f3f4f6;
        }

        .filter-container {
            gap: 1rem;
            margin-bottom: 1rem;
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
            <li><a href="outlet.php" class="sidebar-link active">Outlet</a></li>
            <li><a href="tesChart.php" class="sidebar-link">Chart</a></li>
        </ul>
    </div>

    <!-- Button to toggle sidebar on mobile -->
    <button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>

    <div class="main-content">
        <header class="header">
            <h1 class="text-white text-lg font-bold">Data Outlet</h1>
        </header>

        <main class="p-6">
            <form id="filterForm" method="GET" action="" class="mb-6">
                <!-- Search Bar -->
                <div class="mb-4">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search across all fields..."
                               class="w-full px-4 py-2 pr-10 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="absolute right-2 top-2 text-gray-500 hover:text-blue-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="filter-container">
                    <!-- Month Selection -->
                    <select name="table" class="w-full px-4 py-2 mb-4 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                        <?php
                        $months = [
                            'sales2024' => 'Sales 2024',
                            'sales_jan24' => 'January 2024',
                            'sales_feb24' => 'February 2024',
                            'sales_mar24' => 'March 2024',
                            'sales_apr24' => 'April 2024',
                            'sales_mei24' => 'May 2024',
                            'sales_jun24' => 'June 2024',
                            'sales_jul24' => 'July 2024',
                            'sales_agu24' => 'August 2024',
                            'sales_sep24' => 'September 2024',
                        ];
                        
                        foreach ($months as $value => $label) {
                            $selected = ($selectedTable == $value) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>

                    <!-- Ship To Party Filter -->
                    <select name="ship_to_party" class="w-full px-4 py-2 mb-4 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Ship To Party</option>
                        <?php
                        $shipToParties = getUniqueValues($selectedTable, 'Ship_To_Party');
                        while ($row = $shipToParties->fetch_assoc()) {
                            $selected = ($selectedShipToParty == $row['Ship_To_Party']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['Ship_To_Party']) . "' $selected>" . 
                                htmlspecialchars($row['Ship_To_Party']) . "</option>";
                        }
                        ?>
                    </select>

                    <!-- Ship To Party Name Filter -->
                    <select name="ship_to_party_name" class="w-full px-4 py-2 mb-4 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Ship To Party Name</option>
                        <?php
                        $shipToPartyNames = getUniqueValues($selectedTable, 'Ship_To_Party_Name');
                        while ($row = $shipToPartyNames->fetch_assoc()) {
                            $selected = ($selectedShipToPartyName == $row['Ship_To_Party_Name']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['Ship_To_Party_Name']) . "' $selected>" . 
                                htmlspecialchars($row['Ship_To_Party_Name']) . "</option>";
                        }
                        ?>
                    </select>

                    <!-- Channel Filter -->
                    <select name="channel" class="w-full px-4 py-2 mb-4 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Channel</option>
                        <?php
                        $channels = getUniqueValues($selectedTable, 'Channel');
                        while ($row = $channels->fetch_assoc()) {
                            $selected = ($selectedChannel == $row['Channel']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['Channel']) . "' $selected>" . 
                                htmlspecialchars($row['Channel']) . "</option>";
                        }
                        ?>
                    </select>

                    <!-- Region Filter -->
                    <select name="region" class="w-full px-4 py-2 mb-4 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Region</option>
                        <?php
                        $regions = getUniqueValues($selectedTable, 'Region');
                        while ($row = $regions->fetch_assoc()) {
                            $selected = ($selectedRegion == $row['Region']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['Region']) . "' $selected>" . 
                                htmlspecialchars($row['Region']) . "</option>";
                        }
                        ?>
                    </select>

                    <!-- Sales Office Description Filter -->
                    <select name="sales_office" class="w-full px-4 py-2 mb-4 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Sales Office</option>
                        <?php
                        $salesOffices = getUniqueValues($selectedTable, 'Sales_Office_Description');
                        while ($row = $salesOffices->fetch_assoc()) {
                            $selected = ($selectedSalesOffice == $row['Sales_Office_Description']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['Sales_Office_Description']) . "' $selected>" . 
                                htmlspecialchars($row['Sales_Office_Description']) . "</option>";
                        }
                        ?>
                    </select>

                    <!-- Time filter -->
                    <div class="w-full flex flex-row gap-4 px-4 py-2 mb-4 border border-gray-300 rounded-lg shadow-sm focus-within:ring-2 focus-within:ring-blue-500">
                        <!-- Tanggal Mulai -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                            <label for="start_date">Tanggal Mulai:</label>
                            <input type="text" id="start_date" name="start_date" placeholder="Masukkan YYYY-MM-DD" class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
    
                        <!-- Tanggal Akhir -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                            <label for="end_date">Tanggal Akhir:</label>
                            <input type="text" id="end_date" name="end_date" placeholder="Masukkan YYYY-MM-DD"class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>


                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Apply Filters
                    </button>
                </div>
            </form>

            <div class="mb-4 shadow-lg w-72 px-4 py-6 rounded-lg">
                <h3 class="text-lg font-semibold">Total Amount: 
                    <span class="text-blue-600">
                        <?php echo number_format($totalGIAmount, 2); ?>
                    </span>
                </h3>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="table-container">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ship To Party</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ship To Party Name</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Office Description</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantitiy</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            if ($data->num_rows > 0) {
                                while($row = $data->fetch_assoc()) {
                                    // Highlight search terms in the results
                                    $highlightSearch = function($text) use ($search) {
                                        if ($search && $text) {
                                            return preg_replace('/(' . preg_quote($search, '/') . ')/i', 
                                                '<span class="bg-yellow-200">$1</span>', htmlspecialchars($text));
                                        }
                                        return htmlspecialchars($text);
                                    };

                                    echo "<tr>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . htmlspecialchars($row['Actual_GI_Date']) . "</td>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . $highlightSearch($row['Ship_To_Party']) . "</td>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . $highlightSearch($row['Ship_To_Party_Name']) . "</td>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . $highlightSearch($row['Channel']) . "</td>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . $highlightSearch($row['Region']) . "</td>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . $highlightSearch($row['Sales_Office_Description']) . "</td>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . $highlightSearch($row['GI_Amount']) . "</td>";
                                    echo "<td class='px-6 py-4 text-center whitespace-nowrap'>" . $highlightSearch($row['GI_Quantity_Ktn']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='11' class='px-6 py-4 text-center'>No data available</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
<div class="mt-4 flex items-center justify-between">
    <div class="flex-1 flex justify-between sm:hidden">
        <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                First
            </a>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Previous
            </a>
        <?php endif; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Next
            </a>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>" 
               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Last
            </a>
        <?php endif; ?>
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium"><?php echo ($page - 1) * 100 + 1; ?></span>
                to
                <span class="font-medium"><?php echo min($page * 100, $result['total']); ?></span>
                of
                <span class="font-medium"><?php echo $result['total']; ?></span>
                results
            </p>
        </div>

        <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only"><<</span>
                        <<
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                       class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);

                if ($start > 1) {
                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo ($i === $page) ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor;

                if ($end < $totalPages) {
                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                }

                if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">>></span>
                        >>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</div>

        </main>
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

        // Enhance dropdowns with automatic form submission
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => {
                // Reset page to 1 when filters change
                const pageInput = document.querySelector('input[name="page"]');
                if (pageInput) pageInput.value = '1';
                document.getElementById('filterForm').submit();
            });
        });

        // Add loading state to form submission
        document.getElementById('filterForm').addEventListener('submit', function() {
            this.classList.add('opacity-50');
            this.querySelector('button[type="submit"]').disabled = true;
        });

        // Add keyboard shortcut for search
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
        });
    </script>
</body>
</html>