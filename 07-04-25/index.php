<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Query Runner</title>
    <style>
        /* (Keep the same CSS styles as before) */
        body {
            font-family: sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .header{
            text-align: center;
            font-size: 3rem;
        }
        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }
        textarea[name="query"] {
            width: 100%;
            max-width: 100%;
            min-width: 100%;
            min-height: 180px;
            max-height: 50vh;
            font-size: 16px;
            padding: 10px;
            box-sizing: border-box;
        }
        .button-group, .db-selector-form { /* Apply gap to selector form too */
            display: flex;
            flex-wrap: wrap;
            align-items: center; /* Align items vertically */
            gap: 10px;
        }
        .button-group input[type="submit"],
        .button-group input[type="button"],
        .button-group input[type="reset"],
        .button-group a.button, /* Style links like buttons */
        .db-selector-form input[type="submit"] /* Style submit in selector */
         {
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            color: black;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 3px;
            display: inline-block;
        }
        .button-group a.button:hover,
        .db-selector-form input[type="submit"]:hover {
            background-color: #e0e0e0;
        }
        .db-selector-form select {
            padding: 9px; /* Match button height */
            font-size: 14px;
            min-width: 200px; /* Give dropdown some width */
        }
        .results table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        .results th, .results td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .results th {
            background-color: #f2f2f2;
        }
        .error { color: red; font-weight: bold; border: 1px solid red; padding: 10px; margin-bottom: 15px; background-color: #ffecec; }
        .warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 15px; font-weight: bold; }
        .info { color: #004085; background-color: #cce5ff; border: 1px solid #b8daff; padding: 10px; margin-bottom: 15px; }
        .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; }
        hr { margin: 25px 0; border: 0; border-top: 1px solid #eee; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="header">SQL Query Runner</h1>

    <?php
        // --- Database Configuration ---
        $server = "localhost";
        $user   = "root";
        $pass   = "";

        // --- State Variables ---
        $selected_db = isset($_GET['db']) ? trim($_GET['db']) : null;
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        $query_to_run = isset($_GET['query']) ? trim($_GET['query']) : '';

        // Initialize connection variable
        $link = null;
        $db_error = null;
        $show_db_selector = ($action === 'select_db'); // Flag to show DB selector

        // Enable error reporting for mysqli
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // --- Function to Connect ---
        function connect_db($host, $username, $password, $dbname = null) {
            try {
                $conn = mysqli_connect($host, $username, $password, $dbname);
                if ($conn && $dbname) {
                   mysqli_set_charset($conn, "utf8mb4");
                }
                return $conn;
            } catch (mysqli_sql_exception $e) {
                throw new Exception("Database Connection Error: " . $e->getMessage());
            }
        }

        // --- Display Database Selector Form if action=select_db ---
        if ($show_db_selector) {
            echo "<h2>Step 1: Select a Database</h2>";
            try {
                // Connect without specifying a database
                $temp_link = connect_db($server, $user, $pass);
                $result = $temp_link->query("SHOW DATABASES");
                $databases = [];
                $system_dbs = ['information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin'];

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $db_name = $row['Database'];
                        if (!in_array($db_name, $system_dbs)) {
                            $databases[] = $db_name;
                        }
                    }
                }
                $temp_link->close();

                if (!empty($databases)) {
                    // --- Database Selection Form ---
                    echo '<form method="GET" action="" class="db-selector-form">';
                    echo '<label for="db_select">Choose Database:</label>';
                    echo '<select name="db" id="db_select" onchange="this.form.submit()">'; // Submit form on change
                    echo '<option value="">-- Select --</option>'; // Default empty option

                    foreach ($databases as $db) {
                        $escaped_db = htmlspecialchars($db);
                        // Check if this DB is the currently selected one
                        $is_selected = ($selected_db === $db) ? ' selected' : '';
                        echo "<option value='{$escaped_db}'{$is_selected}>{$escaped_db}</option>";
                    }
                    echo '</select>';
                    // Fallback submit button (optional, good practice)
                    echo '<input type="submit" value="Use Selected Database">';
                    echo '</form>';
                    // --- End Database Selection Form ---

                } else {
                    echo "<div class='info'>No user databases found (excluding system DBs).</div>";
                }

            } catch (Exception $e) {
                $db_error = $e->getMessage();
                 echo "<div class='error'>" . htmlspecialchars($db_error) . "</div>";
            }
            echo "<hr>";
        }

        // --- Connect to Selected Database (if selected via GET parameter) ---
        // This runs *after* the selector form might have submitted
        if ($selected_db && !$show_db_selector) { // Connect only if a DB is selected *and* we are not currently showing the selector
            try {
                 $link = connect_db($server, $user, $pass, $selected_db);
                 echo "<div class='success'>Connected to database: <strong>" . htmlspecialchars($selected_db) . "</strong></div>";
            } catch (Exception $e) {
                $db_error = $e->getMessage();
                 echo "<div class='error'>Failed to connect to database '" . htmlspecialchars($selected_db) . "': " . htmlspecialchars($db_error) . "</div>";
                 $selected_db = null; // Reset selected DB if connection failed
                 $link = null;
            }
        }
    ?>

    <!-- Button to Trigger Database Selection -->
    <div class="button-group" style="margin-bottom: 20px;">
         <a href="?action=select_db<?php echo $selected_db ? '&db='.urlencode($selected_db) : ''; ?>" class="button">Select/Change Database</a>
         <?php if ($selected_db && !$show_db_selector): // Show current DB only if one is selected and selector isn't active ?>
             <span>Current DB: <strong><?php echo htmlspecialchars($selected_db); ?></strong></span>
         <?php endif; ?>
    </div>

    <hr>

    <!-- Query Runner Section (Show only if connected) -->
    <?php if ($link && $selected_db): ?>
        <h2>Step 2: Run Queries against '<?php echo htmlspecialchars($selected_db); ?>'</h2>

        <!-- Query Input Form -->
        <form method="GET" action="" class="form-container">
             <!-- Hidden input to keep track of the selected DB -->
             <input type="hidden" name="db" value="<?php echo htmlspecialchars($selected_db); ?>">

            <label for="query_textarea">Enter your SQL query:</label>
            <textarea id="query_textarea" name="query"><?php echo htmlspecialchars($query_to_run); ?></textarea>

            <div class="button-group">
                <input type="submit" name="submit_query" value="Run Query">
                <input type="reset" value="Clear Form">
                <input type="button" value="Sample: Show Tables" onclick="setQuery('SHOW TABLES;')">
                <input type="button" value="Sample: Desc Table (replace 'your_table')" onclick="setQuery('DESCRIBE your_table_name;')">
                <!-- Renamed button for clarity -->
                <input type="submit" name="action" value="Show Tables in Current DB">
            </div>
        </form>

        <script>
            function setQuery(sql) {
                document.getElementById('query_textarea').value = sql;
            }
        </script>

        <!-- Results Area -->
        <div class="results">
            <?php
                $show_tables_action = ($action === 'Show Tables in Current DB');

                // --- Handle Actions (Show Tables in Current DB) ---
                if ($show_tables_action && $link) {
                     echo "<h3>Tables in '" . htmlspecialchars($selected_db) . "':</h3>";
                     // ... (rest of the show tables logic remains the same) ...
                     try {
                         $result = $link->query("SHOW TABLES");
                         if ($result->num_rows > 0) {
                             echo "<ul>";
                             while ($row = $result->fetch_array()) {
                                 echo "<li>" . htmlspecialchars($row[0]) . "</li>";
                             }
                             echo "</ul>";
                         } else {
                             echo "<div class='info'>No tables found in this database.</div>";
                         }
                         $result->free();
                     } catch (mysqli_sql_exception $e) {
                         echo "<div class='error'>Error executing SHOW TABLES: " . htmlspecialchars($e->getMessage()) . "</div>";
                     }
                 }
                 // --- Handle Custom Query Execution ---
                 elseif (!empty($query_to_run) && isset($_GET['submit_query']) && $link) {
                     echo "<h2>Results:</h2>";
                     // ... (rest of the query execution and result display logic remains the same) ...
                     echo "<p>Executing on DB '" . htmlspecialchars($selected_db) . "': <code>" . htmlspecialchars($query_to_run) . "</code></p>";
                     try {
                         $start_time = microtime(true);
                         $result = $link->query($query_to_run);
                         $end_time = microtime(true);
                         $execution_time = round(($end_time - $start_time) * 1000, 2);

                         if ($result instanceof mysqli_result) {
                             if ($result->num_rows > 0) {
                                 echo "<p>Found " . $result->num_rows . " row(s). (Execution time: " . $execution_time . " ms)</p>";
                                 echo "<table>";
                                 $fields = $result->fetch_fields();
                                 echo "<thead><tr>";
                                 foreach ($fields as $field) { echo "<th>" . htmlspecialchars($field->name) . "</th>"; }
                                 echo "</tr></thead>";
                                 echo "<tbody>";
                                 while ($row = $result->fetch_assoc()) {
                                     echo "<tr>";
                                     foreach ($row as $value) { echo "<td>" . ($value === null ? '<i>NULL</i>' : htmlspecialchars($value)) . "</td>"; }
                                     echo "</tr>";
                                 }
                                 echo "</tbody></table>";
                             } else {
                                 echo "<div class='info'>Query executed successfully, 0 rows returned. (Execution time: " . $execution_time . " ms)</div>";
                             }
                             $result->free();
                         } elseif ($result === true) {
                             $affected_rows = $link->affected_rows;
                             echo "<div class='success'>Query executed successfully. " . $affected_rows . " row(s) affected. (Execution time: " . $execution_time . " ms)</div>";
                         } else {
                              echo "<div class='error'>Query failed. Error: " . htmlspecialchars($link->error) . ")</div>";
                         }
                     } catch (mysqli_sql_exception $e) {
                         echo "<div class='error'>SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                     }
                 } elseif (isset($_GET['submit_query']) && empty($query_to_run)) {
                      echo "<div class='info'>Please enter a query to run.</div>";
                 }
            ?>
        </div> <!-- /results -->

    <?php else: ?>
        <?php if (!$show_db_selector && !$selected_db): // Show initial message only if selector isn't active and no DB selected ?>
            <div class="info">Please select a database to begin using the 'Select/Change Database' button.</div>
        <?php endif; ?>
    <?php endif; // End of conditional section for query runner ?>

    <?php
        // Close the connection if it was opened
        if ($link) {
            mysqli_close($link);
        }
    ?>

</div> <!-- /container -->

</body>
</html>