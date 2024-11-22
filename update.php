<?php
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

    // Fetch existing data
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

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Build dynamic update query based on table columns
        $update_fields = [];
        $types = "";
        $params = [];
        
        foreach ($columns as $column) {
            if ($column != 'id' && isset($_POST[$column])) {
                $update_fields[] = "$column = ?";
                
                // Determine parameter type
                if (strpos($column, '_value') !== false || 
                    strpos($column, '_volume') !== false ||
                    strpos($column, 'quantity') !== false) {
                    $types .= "d"; // double for numeric values
                } else {
                    $types .= "s"; // string for text values
                }
                
                // Format numeric values
                if (strpos($column, '_value') !== false || 
                    strpos($column, '_volume') !== false ||
                    strpos($column, 'quantity') !== false) {
                    $params[] = floatval(str_replace([',', '.'], ['', '.'], $_POST[$column]));
                } else {
                    $params[] = $_POST[$column];
                }
            }
        }
        
        // Add ID to parameters
        $types .= "i";
        $params[] = $id;
        
        // Prepare and execute update query
        $update_query = "UPDATE $table SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        
        if (!$stmt) {
            throw new Exception("Error preparing update statement: " . $conn->error);
        }
        
        // Bind parameters dynamically
        $bind_params = array_merge([$types], $params);
        $tmp = [];
        foreach ($bind_params as $key => $value) {
            $tmp[$key] = &$bind_params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $tmp);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating data: " . $stmt->error);
        }
        
        // Redirect to view page after successful update
        header("Location: view.php?table=$table&id=$id");
        exit();
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

// Function to format values for display
function formatValue($key, $value) {
    if ($value === null) {
        return '';
    }

    if (strpos(strtolower($key), '_value') !== false || 
        strpos(strtolower($key), 'rupiah') !== false) {
        return number_format((float)$value, 2, ',', '.');
    }
    if (strpos(strtolower($key), '_volume') !== false || 
        strpos(strtolower($key), 'quantity') !== false) {
        return number_format((float)$value, 2, ',', '.');
    }
    if (strpos(strtolower($key), 'date') !== false && !empty($value)) {
        return date('Y-m-d', strtotime($value));
    }
    return $value;
}

// Function to sort columns for display
function sortColumns($columns) {
    $info_columns = ['asps', 'region', 'office'];
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
    
    sort($volume_columns);
    sort($value_columns);
    
    return array_merge($other_columns, $volume_columns, $value_columns);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data <?php echo ucwords(str_replace('_', ' ', $table)); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow">
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">
                Edit Data <?php echo ucwords(str_replace('_', ' ', $table)); ?>
            </h1>

            <form method="POST" class="space-y-6">
                <?php 
                $sorted_columns = sortColumns($columns);
                
                // Display general information first
                $info_columns = array_filter($sorted_columns, function($col) {
                    return $col !== 'id' && !strpos($col, '_volume') && !strpos($col, '_value');
                });
                if (!empty($info_columns)): ?>
                    <div class="text-xl font-semibold text-blue-600 mb-4 pb-2 border-b">Informasi Umum</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($info_columns as $column): ?>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700">
                                    <?php echo formatColumnName($column); ?>
                                </label>
                                <input type="text" 
                                       name="<?php echo $column; ?>" 
                                       value="<?php echo htmlspecialchars($data[$column] ?? ''); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                    <div class="text-xl font-semibold text-blue-600 mb-4 pb-2 border-b">Volume Information</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($volume_columns as $column): ?>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700">
                                    <?php echo formatColumnName($column); ?>
                                </label>
                                <input type="text" 
                                       name="<?php echo $column; ?>" 
                                       value="<?php echo formatValue($column, $data[$column] ?? null); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                    <div class="text-xl font-semibold text-blue-600 mb-4 pb-2 border-b">Value Information</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($value_columns as $column): ?>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700">
                                    <?php echo formatColumnName($column); ?>
                                </label>
                                <input type="text" 
                                       name="<?php echo $column; ?>" 
                                       value="<?php echo formatValue($column, $data[$column] ?? null); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="flex justify-end space-x-4 pt-6">
                    <a href="view.php?table=<?php echo urlencode($table); ?>&id=<?php echo urlencode($id); ?>" 
                       class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                        Batal
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>