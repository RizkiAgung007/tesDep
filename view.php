<?php
// File: view.php
include 'config.php';

// Get table name and ID from URL
$table = isset($_GET['table']) ? $_GET['table'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Validate table name to prevent SQL injection
$allowed_tables = [
    'sale_2024',
    'pbc_gt', 'pbc_gt_mt', 'pbc_mt',
    'sbc_gt', 'sbc_gt_mt', 'sbc_mt',
    'sdn_gt', 'sdn_gt_mt', 'sdn_mt',
    'sales_agu24', 'sales_apr24', 'sales_feb24', 'sales_jan24',
    'sales_jul24', 'sales_jun24', 'sales_mar24', 'sales_mei24', 'sales_sep24'
];

if (!in_array($table, $allowed_tables)) {
    die('Invalid table selection');
}

// Initialize error message
$error_message = '';

try {
    // Get column information for the selected table
    $columns_query = "SHOW COLUMNS FROM $table";
    $columns_result = $conn->query($columns_query);
    if (!$columns_result) {
        throw new Exception("Error fetching table structure: " . $conn->error);
    }

    $columns = [];
    while ($column = $columns_result->fetch_assoc()) {
        $columns[] = $column['Field'];
    }

    // Fetch data based on ID with error handling
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Error executing query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        throw new Exception("No data found for ID: " . $id);
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// Function to format column names for display
function formatColumnName($column) {
    // Handle special column names
    $special_columns = [
        'asps' => 'ASPS',
        'office' => 'Office',
        'region' => 'Region'
    ];

    if (isset($special_columns[strtolower($column)])) {
        return $special_columns[strtolower($column)];
    }

    // Format product names
    $formatted = preg_replace('/^(\d+)_/', '$1 ', $column); // Handle numeric prefixes
    $formatted = str_replace(['_value', '_volume'], '', $formatted);
    $formatted = ucwords(str_replace('_', ' ', $formatted));

    // Add suffix based on column type
    if (strpos($column, '_value') !== false) {
        return $formatted . ' Value (Rupiah)';
    } else if (strpos($column, '_volume') !== false) {
        return $formatted . ' Volume (In Karton)';
    }

    return $formatted;
}

// Function to format values based on type
function formatValue($key, $value) {
    if ($value === null) {
        return '-';
    }

    if (strpos(strtolower($key), '_value') !== false || 
        strpos(strtolower($key), 'rupiah') !== false) {
        return 'Rp ' . number_format((float)$value, 2, ',', '.');
    }
    if (strpos(strtolower($key), '_volume') !== false || 
        strpos(strtolower($key), 'quantity') !== false) {
        return number_format((float)$value, 2, ',', '.');
    }
    if (strpos(strtolower($key), 'date') !== false && !empty($value)) {
        return date('d-m-Y', strtotime($value));
    }
    return $value;
}

// Function to sort columns for display
function sortColumns($columns) {
    $info_columns = ['asps', 'region', 'office']; // Removed 'id' from this array
    $volume_columns = [];
    $value_columns = [];
    $other_columns = [];
    
    foreach ($columns as $column) {
        $lower_column = strtolower($column);
        if (in_array($lower_column, $info_columns)) {
            $other_columns[] = $column;
        } elseif (strpos($lower_column, '_volume') !== false) {
            $volume_columns[] = $column;
        } elseif (strpos($lower_column, '_value') !== false) {
            $value_columns[] = $column;
        } else {
            $other_columns[] = $column;
        }
    }
    
    // Sort each category
    sort($volume_columns);
    sort($value_columns);
    
    // Put "total" at the end of each section
    $volume_columns = sortTotalToEnd($volume_columns);
    $value_columns = sortTotalToEnd($value_columns);
    
    return array_merge($other_columns, $volume_columns, $value_columns);
}

// Helper function to move "total" items to the end
function sortTotalToEnd($array) {
    $total_items = array_filter($array, function($item) {
        return stripos($item, 'total') !== false;
    });
    $non_total_items = array_filter($array, function($item) {
        return stripos($item, 'total') === false;
    });
    return array_merge($non_total_items, $total_items);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Data <?php echo ucwords(str_replace('_', ' ', $table)); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .detail-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1rem;
        }
        .detail-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: #f9fafb;
        }
        .detail-label {
            font-size: 0.875rem;
            color: #4b5563;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .detail-value {
            font-size: 1rem;
            color: #1f2937;
        }
        .section-title {
            font-size: 1.25rem;
            color: #2563eb;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        .error-message {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                background-color: white;
            }
            .detail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="detail-container">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">
                Detail Data <?php echo ucwords(str_replace('_', ' ', $table)); ?>
            </h1>

            <?php if (isset($data) && is_array($data)): ?>
                <?php
                $sorted_columns = sortColumns($columns);
                
                // Display general information first
                $info_columns = array_filter($sorted_columns, function($col) {
                    return !strpos($col, '_volume') && !strpos($col, '_value');
                });
                if (!empty($info_columns)): ?>
                    <div class="section-title">Informasi Umum</div>
                    <div class="detail-grid">
                        <?php foreach ($info_columns as $column): ?>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo formatColumnName($column); ?></div>
                                <div class="detail-value"><?php echo formatValue($column, $data[$column] ?? null); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                // Display volume information
                $volume_columns = array_filter($sorted_columns, function($col) {
                    return strpos($col, '_volume') !== false;
                });
                if (!empty($volume_columns)): ?>
                    <div class="section-title">Volume Information</div>
                    <div class="detail-grid">
                        <?php foreach ($volume_columns as $column): ?>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo formatColumnName($column); ?></div>
                                <div class="detail-value"><?php echo formatValue($column, $data[$column] ?? null); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                // Display value information
                $value_columns = array_filter($sorted_columns, function($col) {
                    return strpos($col, '_value') !== false;
                });
                if (!empty($value_columns)): ?>
                    <div class="section-title">Value Information</div>
                    <div class="detail-grid">
                        <?php foreach ($value_columns as $column): ?>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo formatColumnName($column); ?></div>
                                <div class="detail-value"><?php echo formatValue($column, $data[$column] ?? null); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="mt-8 flex justify-center space-x-4 no-print">
                <a href="dashboard.php" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                    Kembali ke Dashboard
                </a>
                <button onclick="window.print()" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition duration-200">
                    Cetak Data
                </button>
            </div>
        </div>
    </div>
</body>
</html>