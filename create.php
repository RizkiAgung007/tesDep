<?php
require 'config.php';

// Define available tables with their structures
$table_structures = [
    'pbc' => [
        'tables' => ['pbc_gt', 'pbc_gt_mt', 'pbc_mt'],
        'fields' => [
            'ID' => 'number',
            'ASPS' => 'text',
            'Region' => 'text',
            'Office' => 'text',
            '01_CF_Volume_In_Karton' => 'number',
            '02_Mie_100_Volume_In_Karton' => 'number',
            '02_Mie_100_Cup_Volume_In_Karton' => 'number',
            '02_Mie_100_Hot_Series_Volume_In_Karton' => 'number',
            '03_Mie_1000_Volume_In_Karton' => 'number',
            '04_Mie_A1_Volume_In_Karton' => 'number',
            '05_Mie_Gepeng_Volume_In_Karton' => 'number',
            '06_Sosis_Loncat_Volume_In_Karton' => 'number',
            '07_Otak_Otakku_Volume_In_Karton' => 'number',
            '08_Gamie_Aussie_Volume_In_Karton' => 'number',
            '11_Sosis_Otak2_Pgng_Volume_In_Karton' => 'number',
            '13_Arirang_Volume_In_Karton' => 'number',
            '15_Bakmi_Volume_In_Karton' => 'number',
            '16_Saus_Sambal_Volume_In_Karton' => 'number',
            '17_Indonesia_Mie_Goreng_Volume_In_Karton' => 'number',
            'TOTAL_Volume_In_Karton' => 'number',
            '01_CF_Value_Rupiah' => 'number',
            '02_Mie_100_Value_Rupiah' => 'number',
            '02_Mie_100_Cup_Value_Rupiah' => 'number',
            '02_Mie_100_Hot_Series_Value_Rupiah' => 'number',
            '03_Mie_1000_Value_Rupiah' => 'number',
            '04_Mie_A1_Value_Rupiah' => 'number',
            '05_Mie_Gepeng_Value_Rupiah' => 'number',
            '06_Sosis_Loncat_Value_Rupiah' => 'number',
            '07_Otak_Otakku_Value_Rupiah' => 'number',
            '08_Gamie_Aussie_Value_Rupiah' => 'number',
            '11_Sosis_Otak2_Pgng_Value_Rupiah' => 'number',
            '13_Arirang_Value_Rupiah' => 'number',
            '15_Bakmi_Value_Rupiah' => 'number',
            '16_Saus_Sambal_Value_Rupiah' => 'number',
            '17_Indonesia_Mie_Goreng_Value_Rupiah' => 'number',
            'TOTAL_Value_Rupiah' => 'number'
        ]
    ],
    'sbc' => [
        'tables' => ['sbc_gt', 'sbc_gt_mt', 'sbc_mt'],
        'fields' => [
            'ID' => 'number',
            'ASPS' => 'text',
            'Region' => 'text',
            'Office' => 'text'
        ]
    ],
    'sdn' => [
        'tables' => ['sdn_gt', 'sdn_gt_mt', 'sdn_mt'],
        'fields' => [
            'ID' => 'number',
            'Product' => 'text'
        ]
    ],
    'monthly_sales' => [
        'tables' => ['sales_jan24', 'sales_feb24', 'sales_mar24', 'sales_apr24', 'sales_mei24', 'sales_jun24', 'sales_jul24', 'sales_agu24', 'sales_sep24'],
        'fields' => [
            'id' => 'number',
            'Division_Description' => 'text',
            'Sales_Office' => 'text',
            'Sales_Office_Description' => 'text',
            'Distribution_Channel' => 'text',
            'Customer_Group' => 'text',
            'Customer_Group_2' => 'text',
            'Sold_to_Party' => 'text',
            'Sold_to_Party_Name' => 'text',
            'Ship_To_Party' => 'text',
            'Ship_To_Party_Name' => 'text',
            'Ship_To_Party_Name_2' => 'text',
            'Kecamatan' => 'text',
            'Kelurahan' => 'text',
            'Material_Code' => 'text',
            'Material_Description' => 'text',
            'Sales_Code_Description' => 'text',
            'Sales_Order_Number' => 'text',
            'SO_Creation_Date' => 'date',
            'SO_Quantity' => 'number',
            'SO_Unit' => 'text',
            'SO_Amount' => 'number',
            'DO_Number' => 'text',
            'DO_Qty' => 'number',
            'DO_Unit' => 'text',
            'DO_Amount' => 'number',
            'DO_Creation_Date' => 'date',
            'GI_Number' => 'text',
            'Actual_GI_Date' => 'date',
            'GI_Quantity' => 'number',
            'GI_Unit' => 'text',
            'GI_Amount' => 'number',
            'Billing_Number' => 'text',
            'Billing_Date' => 'date',
            'Billing_Quantity' => 'number',
            'Billing_Unit' => 'text',
            'Billing_Amount' => 'number',
            'Description' => 'text',
            'Touchpoint_Source' => 'text',
            'Address' => 'text',
            'SO_Quantity_Ktn' => 'number',
            'DO_Quantity_Ktn' => 'number',
            'GI_Quantity_Ktn' => 'number',
            'Billing_Quantity_Ktn' => 'number',
            'Product' => 'text',
            'Region' => 'text',
            'Channel' => 'text',
            'Week' => 'text',
            'GI_Month' => 'text',
            'Year' => 'text',
            'SO' => 'text',
            'SO_CF' => 'text',
            'SO_100_Exist' => 'text',
            'SO_Hot_Series' => 'text',
            'SO_A1' => 'text',
            'SO_1000' => 'text',
            'SO_Sostak' => 'text',
            'Loyalty_Sostak' => 'text',
            'Type_Customer' => 'text',
            'Sales_Office2' => 'text',
            'Chain' => 'text',
            'Ket_System' => 'text',
            'Ket_Transaksi' => 'text',
            'GI_Date_2' => 'date',
            'SKU' => 'text',
            'Harga_DPP' => 'number',
            'ASPS' => 'text',
            'Salesman' => 'text',
            'Level_GT' => 'text',
            'Remarks' => 'text',
            'Product_2' => 'text'
        ]
    ],
    'sale_2024' => [
        'tables' => ['sale_2024'],
        'fields' => [
            'id' => 'number',
            'Month' => 'text',
            'Ship_To_Party' => 'text',
            'Ship_To_Party_Name' => 'text',
            'Channel' => 'text',
            'Region' => 'text',
            'Sales_Office_Description' => 'text',
            'Type_Customer' => 'text',
            'Product' => 'text',
            'GI_ktn' => 'number',
            'Gi_amount' => 'number'
        ]
    ]
];

// Add monthly fields for SBC and SDN
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
foreach ($months as $month) {
    $table_structures['sbc']['fields'][$month . '_24_Volume_Karton'] = 'number';
    $table_structures['sbc']['fields'][$month . '_24_Value_Rupiah'] = 'number';
    $table_structures['sdn']['fields'][$month . '_24'] = 'number';
    $table_structures['sdn']['fields'][$month . '_24_Value'] = 'number';
}

// Add summary fields for SBC and SDN
$summary_fields = [
    'sbc' => [
        'Total_Target_Volume_Semester_1_Karton',
        'Total_Target_Volume_Semester_2_Karton',
        'Total_Target_Value_Semester_1_Rupiah',
        'Total_Target_Value_Semester_2_Rupiah',
        'Volume_Target_2024_Karton',
        'Value_Target_2024_Rupiah',
        'Average_Volume_Target_2024',
        'Average_Value_Target_2024'
    ],
    'sdn' => [
        'Total_Target_Volume_Semester_1_Karton',
        'Total_Target_Volume_Semester_2_Karton',
        'Total_Target_Value_Semester_1_Rupiah',
        'Total_Target_Value_Semester_2_Rupiah',
        'Target_Volume_Karton',
        'Target_Value_Rupiah',
        'Target_Average_Volume',
        'Target_Average_Rupiah'
    ]
];

foreach ($summary_fields['sbc'] as $field) {
    $table_structures['sbc']['fields'][$field] = 'number';
}
foreach ($summary_fields['sdn'] as $field) {
    $table_structures['sdn']['fields'][$field] = 'number';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_table = $_POST['selected_table'];
    $table_type = '';
    
    // Determine table type
    foreach ($table_structures as $type => $structure) {
        if (in_array($selected_table, $structure['tables'])) {
            $table_type = $type;
            break;
        }
    }
    
    if (!empty($table_type)) {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            $fields = array_keys($table_structures[$table_type]['fields']);
            $escaped_fields = array_map(function($field) use ($conn) {
                return "`" . $conn->real_escape_string($field) . "`";
            }, $fields);
            
            $values = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO `" . $conn->real_escape_string($selected_table) . "` (" . 
                   implode(", ", $escaped_fields) . ") VALUES (" . implode(", ", $values) . ")";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $types = '';
                $bind_params = array();
                
                foreach ($fields as $field) {
                    $field_type = $table_structures[$table_type]['fields'][$field];
                    if ($field_type == 'number') {
                        $types .= 'd';
                        // Convert empty string to 0 for number fields
                        $bind_params[] = empty($_POST[$field]) ? 0 : (float)$_POST[$field];
                    } else if ($field_type == 'date') {
                        $types .= 's';
                        // Convert empty string to NULL for date fields
                        $bind_params[] = empty($_POST[$field]) ? NULL : $_POST[$field];
                    } else {
                        $types .= 's';
                        $bind_params[] = $_POST[$field] ?? '';
                    }
                }
                
                $stmt->bind_param($types, ...$bind_params);
                
                if ($stmt->execute()) {
                    // Commit transaction
                    $conn->commit();
                    echo "<script>
                            alert('Data berhasil ditambahkan!');
                            window.location.href = 'display.php';
                          </script>";
                    exit;
                } else {
                    throw new Exception($stmt->error);
                }
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "<script>alert('Gagal menambahkan data: " . $e->getMessage() . "');</script>";
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penjualan Baru</title>
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
    z-index: 40;
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

        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }

        .form-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: white;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }

        .submit-button {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #1d4ed8;
        }

        /* Add loading spinner styles */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

    .header {
        z-index: 999;
    }
}

    </style>
</head>
<body class="bg-gray-100">
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner">
        <div class="spinner"></div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar bg-blue-800 text-white">
    <div class="text-center p-5">
        <h1 class="text-white text-xl font-bold">Performance Sales</h1>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.php" class="sidebar-link">Dashboard</a></li>
        <li><a href="display.php" class="sidebar-link">Display</a></li>
        <li><a href="dashboard.php" class="sidebar-link">Data Management</a></li>
        <li><a href="create.php" class="sidebar-link active">Tambah Penjualan</a></li>
        <li><a href="outlet.php" class="sidebar-link">Outlet</a></li>
        <li><a href="tesChart.php" class="sidebar-link">Chart</a></li>
    </ul>
</div>

<!-- Button to toggle sidebar on mobile -->
<button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>


    <!-- Main Content -->
    <div class="main-content">
        <header class="bg-blue-600 text-white p-4 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Tambah Penjualan Baru</h1>
                <a href="dashboard.php" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded transition-colors duration-200 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    <h1 class="md:block hidden">Kembali ke Dashboard</h1>   
                </a>
            </div>
        </header>

        <div class="form-container">
            <form method="POST" id="salesForm" class="space-y-6" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="selected_table" class="form-label">Pilih Tabel</label>
                    <select name="selected_table" id="selected_table" onchange="updateForm()" 
                            class="form-select focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php foreach ($table_structures as $type => $structure): ?>
                            <optgroup label="<?php echo ucfirst($type); ?>">
                                <?php foreach ($structure['tables'] as $table): ?>
                                    <option value="<?php echo htmlspecialchars($table); ?>">
                                        <?php echo htmlspecialchars($table); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="dynamic_fields" class="space-y-4">
                    <!-- Dynamic form fields will be inserted here -->
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="submit-button">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Simpan Data
                        </span>
                    </button>
                </div>
            </form>
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

        function showLoadingSpinner() {
            document.getElementById('loadingSpinner').style.display = 'flex';
        }

        function hideLoadingSpinner() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }

        function validateForm() {
            showLoadingSpinner();
            const form = document.getElementById('salesForm');
            const formData = new FormData(form);
            let isValid = true;
            let errorMessage = '';

            // Validate required fields
            for (let [key, value] of formData.entries()) {
                const input = form.querySelector(`[name="${key}"]`);
                const fieldType = input.type;

                if (fieldType === 'number' && value === '') {
                    value = '0'; // Set default value for empty number fields
                    formData.set(key, '0');
                } else if (value.trim() === '' && !input.hasAttribute('data-optional')) {
                    isValid = false;
                    errorMessage += `${key} harus diisi.\n`;
                }
            }

            if (!isValid) {
                hideLoadingSpinner();
                alert(errorMessage);
                return false;
            }

            return true;
        }

        function updateForm() {
            const selectedTable = document.getElementById('selected_table').value;
            const formFields = document.getElementById('dynamic_fields');
            formFields.innerHTML = '';
            
            <?php
            echo "const tableStructures = " . json_encode($table_structures) . ";\n";
            ?>
            
            let tableType = '';
            for (const type in tableStructures) {
                if (tableStructures[type].tables.includes(selectedTable)) {
                    tableType = type;
                    break;
                }
            }
            
            if (tableType) {
                const fields = tableStructures[tableType].fields;
                for (const [field, type] of Object.entries(fields)) {
                    const fieldId = field.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
                    const div = document.createElement('div');
                    div.className = 'form-group';
                    
                    const label = document.createElement('label');
                    label.className = 'form-label';
                    label.htmlFor = fieldId;
                    label.textContent = field;
                    
                    const input = document.createElement('input');
                    
                    if (type === 'date') {
                        input.type = 'date';
                    } else if (type === 'number') {
                        input.type = 'number';
                        input.step = '0.01';
                        input.min = '0';
                    } else {
                        input.type = 'text';
                    }
                    
                    input.name = field;
                    input.id = fieldId;
                    input.className = 'form-input focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
                    
                    // Make ID field optional and add readonly if it's auto-increment
                    if (field.toLowerCase() === 'id') {
                        input.setAttribute('data-optional', 'true');
                        input.readOnly = true;
                        input.value = '';
                    }
                    
                    div.appendChild(label);
                    div.appendChild(input);
                    formFields.appendChild(div);
                }
            }
        }

        // Initialize form on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateForm();
        });
    </script>
</body>
</html>