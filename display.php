<?php
// Include file config.php untuk koneksi ke database
require_once 'config.php';

// Class untuk mengelola tampilan data penjualan
class SalesDisplay {
    private $conn;
    private $table_selected;
    private $available_tables;
    private $filters;
    private $columns;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->available_tables = [
            'sales2024' => 'Sales 2024', 
            'sales_jan24' => 'Sales January 2024',
            'sales_feb24' => 'Sales February 2024',
            'sales_mar24' => 'Sales March 2024',
            'sales_apr24' => 'Sales April 2024',
            'sales_mei24' => 'Sales May 2024',
            'sales_jun24' => 'Sales June 2024',
            'sales_jul24' => 'Sales July 2024',
            'sales_agu24' => 'Sales August 2024',
            'sales_sep24' => 'Sales September 2024',
            'sale_2024', 'pbc_gt', 'pbc_gt_mt', 'pbc_mt',
            'sbc_gt', 'sbc_gt_mt', 'sbc_mt',
            'sdn_gt', 'sdn_gt_mt', 'sdn_mt'
        ];
        $this->initializeFilters();
    }

    private function initializeFilters() {
        $this->table_selected = $this->sanitizeInput($_GET['table'] ?? 'sale_2024');
        $this->filters = [
            'month' => $this->sanitizeInput($_GET['month'] ?? ''),
            'channel' => $this->sanitizeInput($_GET['channel'] ?? ''),
            'region' => $this->sanitizeInput($_GET['region'] ?? ''),
            'product' => $this->sanitizeInput($_GET['product'] ?? ''),
            'partyName' => $this->sanitizeInput($_GET['partyName'] ?? ''),
            'sort' => $this->sanitizeInput($_GET['sort'] ?? '')
        ];
        $this->columns = $this->getTableColumns();
    }

    private function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    private function getTableColumns() {
        $columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM `" . $this->conn->real_escape_string($this->table_selected) . "`");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
        }
        return $columns;
    }

    private function buildQuery() {
        $query = "SELECT * FROM " . $this->conn->real_escape_string($this->table_selected) . " WHERE 1=1";

        foreach ($this->filters as $key => $value) {
            if (!empty($value) && in_array(ucfirst($key), $this->columns)) {
                if ($key === 'partyName') {
                    $query .= " AND Ship_To_Party_Name LIKE '%" . $this->conn->real_escape_string($value) . "%'";
                } else {
                    $query .= " AND " . ucfirst($key) . " = '" . $this->conn->real_escape_string($value) . "'";
                }
            }
        }

        if ($this->filters['sort'] === 'New') {
            $query .= " ORDER BY id DESC";
        } elseif ($this->filters['sort'] === 'Latest') {
            $query .= " ORDER BY id ASC";
        }

        return $query;
    }

    public function getPaginatedData($page = 1, $limit = 100) {
        $query = $this->buildQuery();
        
        // Get total rows
        $total_result = $this->conn->query($query);
        $total_data = $total_result ? $total_result->num_rows : 0;
        $total_pages = ceil($total_data / $limit);

        // Validate page number
        $page = max(1, min($page, $total_pages));
        $offset = ($page - 1) * $limit;

        // Get paginated data
        $query .= " LIMIT $limit OFFSET $offset";
        $result = $this->conn->query($query);

        return [
            'data' => $result,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'total_records' => $total_data
        ];
    }

    public function getDistinctValues($column) {
        $values = [];
        if (in_array($column, $this->columns)) {
            $query = "SELECT DISTINCT $column FROM " . $this->conn->real_escape_string($this->table_selected);
            $result = $this->conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $values[] = $row[$column];
                }
            }
        }
        return $values;
    }

    public function renderFilterForm() {
        ?>
        <form method="GET" class="space-y-4 mb-6">
            <div class="flex flex-wrap gap-2">
                <!-- Table Selection -->
                <select name="table" class="border rounded px-4 py-2">
                    <?php foreach ($this->available_tables as $table): ?>
                        <option value="<?php echo $table; ?>" 
                                <?php echo $this->table_selected === $table ? 'selected' : ''; ?>>
                            <?php echo $table; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php $this->renderFilterSelects(); ?>

                <button type="submit" class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600">
                    Apply Filters
                </button>
            </div>
        </form>
        <?php
    }

    private function renderFilterSelects() {
        $filterConfig = [
            'Ship_To_Party_Name' => ['type' => 'text', 'name' => 'partyName', 'placeholder' => 'Party Name'],
            'Product' => ['type' => 'select', 'name' => 'product'],
            'Month' => ['type' => 'select', 'name' => 'month'],
            'Channel' => ['type' => 'select', 'name' => 'channel', 'options' => ['MT', 'GT']],
            'Region' => ['type' => 'select', 'name' => 'region']
        ];

        foreach ($filterConfig as $column => $config) {
            if (in_array($column, $this->columns)) {
                if ($config['type'] === 'text') {
                    ?>
                    <input type="text" 
                           name="<?php echo $config['name']; ?>" 
                           value="<?php echo $this->filters[$config['name']]; ?>"
                           placeholder="<?php echo $config['placeholder']; ?>"
                           class="border rounded px-4 py-2">
                    <?php
                } else {
                    ?>
                    <select name="<?php echo $config['name']; ?>" class="border rounded px-4 py-2">
                        <option value=""><?php echo $column; ?></option>
                        <?php
                        if (isset($config['options'])) {
                            foreach ($config['options'] as $option) {
                                $selected = $this->filters[$config['name']] === $option ? 'selected' : '';
                                echo "<option value='$option' $selected>$option</option>";
                            }
                        } else {
                            $values = $this->getDistinctValues($column);
                            foreach ($values as $value) {
                                $selected = $this->filters[$config['name']] === $value ? 'selected' : '';
                                echo "<option value='$value' $selected>$value</option>";
                            }
                        }
                        ?>
                    </select>
                    <?php
                }
            }
        }

        // Sort selection
        ?>
        <select name="sort" class="border rounded px-4 py-2">
            <option value="">Sort By</option>
            <option value="New" <?php echo $this->filters['sort'] === 'New' ? 'selected' : ''; ?>>Newest</option>
            <option value="Latest" <?php echo $this->filters['sort'] === 'Latest' ? 'selected' : ''; ?>>Oldest</option>
        </select>
        <?php
    }

    public function renderTable($result) {
        ?>
        <div class="table-container overflow-x-auto">
            <table class="border-collapse border w-full">
                <thead>
                    <tr>
                        <?php foreach ($this->columns as $column): ?>
                            <th class="border px-4 py-2 bg-gray-100"><?php echo $column; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <?php foreach ($this->columns as $column): ?>
                                    <td class="border px-4 py-2">
                                        <?php echo htmlspecialchars($row[$column] ?? ''); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($this->columns); ?>" class="border px-4 py-2 text-center">
                                No data found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function renderPagination($total_pages, $current_page, $total_records) {
        if ($total_pages <= 1) return;
        
        $range = 2; // Number of pages before and after current page
        ?>
        <div class="mt-4 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Showing data <?php echo (($current_page - 1) * 100 + 1); ?> - 
                <?php echo min($current_page * 100, $total_records); ?> 
                of <?php echo $total_records; ?>
            </div>
            <div class="space-x-1 flex items-center">
                <?php
                // First page
                if ($current_page > 1) {
                    $first_url = $this->generatePageUrl(1);
                    echo "<a href='$first_url' class='px-3 py-1 border rounded bg-white text-blue-500 hover:bg-blue-50'>«</a>";
                }

                // Previous page
                if ($current_page > 1) {
                    $prev_url = $this->generatePageUrl($current_page - 1);
                    echo "<a href='$prev_url' class='px-3 py-1 border rounded bg-white text-blue-500 hover:bg-blue-50'>‹</a>";
                }

                // Page numbers
                for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++) {
                    $url = $this->generatePageUrl($i);
                    $active_class = $i === $current_page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 hover:bg-blue-50';
                    echo "<a href='$url' class='px-3 py-1 border rounded $active_class'>$i</a>";
                }

                // Next page
                if ($current_page < $total_pages) {
                    $next_url = $this->generatePageUrl($current_page + 1);
                    echo "<a href='$next_url' class='px-3 py-1 border rounded bg-white text-blue-500 hover:bg-blue-50'>›</a>";
                }

                // Last page
                if ($current_page < $total_pages) {
                    $last_url = $this->generatePageUrl($total_pages);
                    echo "<a href='$last_url' class='px-3 py-1 border rounded bg-white text-blue-500 hover:bg-blue-50'>»</a>";
                }
                ?>
            </div>
        </div>
        <?php
    }

    private function generatePageUrl($page) {
        $params = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    }
}

// Initialize the display class
$display = new SalesDisplay($conn);

// Get current page from URL
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get paginated data
$result = $display->getPaginatedData($current_page);
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
            <li><a href="display.php" class="sidebar-link active">Display</a></li>
            <li><a href="dashboard.php" class="sidebar-link">Data Management</a></li>
            <li><a href="create.php" class="sidebar-link">Tambah Penjualan</a></li>
            <li><a href="outlet.php" class="sidebar-link">Outlet</a></li>
            <li><a href="tesChart.php" class="sidebar-link">Chart</a></li>
        </ul>
    </div>

    <!-- Button to toggle sidebar on mobile -->
    <button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>

    <!-- Main Content -->
    <div class="main-content">
        <header class="header mb-8">
            <h1 class="text-white text-xl font-bold">Data Penjualan</h1>
        </header>

        <?php
        // Render filter form
        $display->renderFilterForm();

        // Render data table
        $display->renderTable($result['data']);

// Render pagination
$display->renderPagination($result['total_pages'], $result['current_page'], $result['total_records']);
?>
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
        
document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to form submission
    const form = document.querySelector('form');
    const filters = form.querySelectorAll('select, input');
    const submitButton = form.querySelector('button[type="submit"]');

    // Add change event listeners to filters for auto-submit
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            // Update loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="inline-flex items-center">Loading... <svg class="animate-spin ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>';
            
            // Submit form
            form.submit();
        });
    });

    // Add loading state to pagination links
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Update link text to show loading
            this.innerHTML = '<span class="inline-flex items-center">Loading...</span>';
        });
    });

    // Add keyboard navigation for pagination
    document.addEventListener('keydown', function(e) {
        if (e.altKey) {
            // Alt + Left Arrow for previous page
            if (e.key === 'ArrowLeft') {
                const prevLink = document.querySelector('a[rel="prev"]');
                if (prevLink) prevLink.click();
            }
            // Alt + Right Arrow for next page
            if (e.key === 'ArrowRight') {
                const nextLink = document.querySelector('a[rel="next"]');
                if (nextLink) nextLink.click();
            }
        }
    });

    // Add responsive table handling
    const table = document.querySelector('table');
    const tableContainer = document.querySelector('.table-container');
    
    // Add horizontal scroll indicators if table is wider than container
    function updateScrollIndicators() {
        if (table.offsetWidth > tableContainer.offsetWidth) {
            tableContainer.classList.add('has-scroll');
            
            // Show/hide left/right scroll indicators based on scroll position
            const leftIndicator = tableContainer.querySelector('.scroll-indicator-left');
            const rightIndicator = tableContainer.querySelector('.scroll-indicator-right');
            
            if (tableContainer.scrollLeft > 0) {
                leftIndicator?.classList.remove('hidden');
            } else {
                leftIndicator?.classList.add('hidden');
            }
            
            if (tableContainer.scrollLeft + tableContainer.offsetWidth < table.offsetWidth) {
                rightIndicator?.classList.remove('hidden');
            } else {
                rightIndicator?.classList.add('hidden');
            }
        }
    }

    // Initial check and listen for window resize
    updateScrollIndicators();
    window.addEventListener('resize', updateScrollIndicators);
    tableContainer.addEventListener('scroll', updateScrollIndicators);
});

// Function to reset filters
function resetFilters() {
    const form = document.querySelector('form');
    const filters = form.querySelectorAll('select, input');
    
    filters.forEach(filter => {
        if (filter.tagName === 'SELECT') {
            filter.selectedIndex = 0;
        } else if (filter.type === 'text') {
            filter.value = '';
        }
    });
    
    form.submit();
}

// Function to export current data to Excel
function exportToExcel() {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tr');
    let csvContent = "data:text/csv;charset=utf-8,";
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = Array.from(cells).map(cell => {
            let content = cell.textContent.trim();
            // Escape quotes and wrap content in quotes if it contains commas
            if (content.includes(',')) {
                content = `"${content.replace(/"/g, '""')}"`;
            }
            return content;
        });
        csvContent += rowData.join(',') + '\r\n';
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'sales_data.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
</div>
</body>
</html>

<?php $conn->close(); ?>