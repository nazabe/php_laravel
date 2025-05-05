<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP SQL Query Runner Example</title>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Align items to the start */
            gap: 15px; /* Space between elements */
            margin-bottom: 20px;
        }
        textarea[name="query"] {
            width: 100%; /* Make textarea full width */
            height: 150px;
            font-size: 16px;
            padding: 10px;
            box-sizing: border-box; /* Include padding in width */
        }
        .button-group {
            display: flex;
            flex-wrap: wrap; /* Allow buttons to wrap on small screens */
            gap: 10px; /* Space between buttons */
        }
        .button-group input[type="submit"],
        .button-group input[type="button"],
        .button-group input[type="reset"] {
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
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
        .error {
            color: red;
            font-weight: bold;
            border: 1px solid red;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #ffecec;
        }
        .warning {
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .info {
            color: #004085;
            background-color: #cce5ff;
            border: 1px solid #b8daff;
            padding: 10px;
            margin-bottom: 15px;
        }
        .action-link {
             margin-left: 15px;
             font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>SQL Query Runner</h1>

    <div class="warning">
        <strong>Security Warning:</strong> Executing raw SQL queries directly from user input is extremely dangerous and can lead to SQL injection attacks. This tool is for demonstration purposes only on a trusted, local environment. <strong>NEVER use this approach in a production application.</strong> Always use prepared statements or an ORM.
    </div>

    <?php
        // --- Database Configuration ---
        $server = "localhost";
        $user   = "root"; // Use environment variables or config files in real apps
        $pass   = "";     // Use environment variables or config files in real apps
        $base   = "tup2_bd"; // Your database name

        // Enable error reporting for mysqli
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $link = null; // Initialize link variable
        $db_error = null; // Variable to store connection error

        try {
            // Establish connection
            $link = mysqli_connect($server, $user, $pass, $base);
            // Set charset for proper encoding handling
            mysqli_set_charset($link, "utf8mb4");
        } catch (mysqli_sql_exception $e) {
            // Catch connection errors
            $db_error = "Database Connection Error: " . $e->getMessage();
            // Display connection error prominently
            echo "<div class='error'>" . htmlspecialchars($db_error) . "</div>";
            // No point continuing if DB connection failed
            exit; // Or handle more gracefully
        }

        // --- Default values ---
        $query_to_run = isset($_GET['query']) ? trim($_GET['query']) : '';
        $action = isset($_GET['action']) ? $_GET['action'] : null;

    ?>

    <!-- Query Input Form -->
    <form method="GET" action="" class="form-container">
        <label for="query_textarea">Enter your SQL query:</label>
        <textarea id="query_textarea" name="query"><?php echo htmlspecialchars($query_to_run); ?></textarea>

        <div class="button-group">
            <input type="submit" name="submit_query" value="Run Query">
            <input type="reset" value="Clear Form">
            <!-- Sample Query Buttons -->
            <input type="button" value="Sample: Show People" onclick="setQuery('SELECT id, nombre, apellido FROM persona LIMIT 10;')">
            <input type="button" value="Sample: Show Subjects" onclick="setQuery('SELECT id, nombre FROM materia;')">
            <input type="button" value="Sample: Grades" onclick="setQuery('SELECT p.nombre AS student, m.nombre AS subject, n.valor AS grade\nFROM nota n\nJOIN cursada c ON n.cursada_id = c.id\nJOIN persona p ON c.persona_id = p.id\nJOIN materia m ON c.materia_id = m.id\nORDER BY p.nombre, m.nombre\nLIMIT 20;')">
             <!-- Show Tables Button (submits with an 'action' parameter) -->
            <input type="submit" name="action" value="Show Tables">
        </div>
    </form>

    <!-- JavaScript for Sample Query Buttons -->
    <script>
        function setQuery(sql) {
            document.getElementById('query_textarea').value = sql;
        }
    </script>

    <!-- Results Area -->
    <div class="results">
        <h2>Results:</h2>
        <?php

        // --- Handle Actions (like Show Tables) ---
        if ($action === 'Show Tables' && $link) {
            echo "<h3>Available Tables:</h3>";
            try {
                $result = $link->query("SHOW TABLES");
                if ($result->num_rows > 0) {
                    echo "<ul>";
                    while ($row = $result->fetch_array()) { // fetch_array gets numeric index
                        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<div class='info'>No tables found in the database '" . htmlspecialchars($base) . "'.</div>";
                }
                // Free result set
                $result->free();
            } catch (mysqli_sql_exception $e) {
                echo "<div class='error'>Error executing SHOW TABLES: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        // --- Handle Custom Query Execution ---
        // Check if a query was submitted via the 'Run Query' button or if query param exists
        // and it's not the 'Show Tables' action
        elseif (!empty($query_to_run) && isset($_GET['submit_query']) && $link) {
             echo "<p>Executing: <code>" . htmlspecialchars($query_to_run) . "</code></p>";
             try {
                $start_time = microtime(true); // Start timer
                $result = $link->query($query_to_run);
                $end_time = microtime(true); // End timer
                $execution_time = round(($end_time - $start_time) * 1000, 2); // Time in milliseconds

                // Check if the query was successful and produced a result set (typical for SELECT)
                if ($result instanceof mysqli_result) {
                    if ($result->num_rows > 0) {
                        echo "<p>Found " . $result->num_rows . " row(s). (Execution time: " . $execution_time . " ms)</p>";
                        echo "<table>";
                        // Fetch field names for the header
                        $fields = $result->fetch_fields();
                        echo "<thead><tr>";
                        foreach ($fields as $field) {
                            echo "<th>" . htmlspecialchars($field->name) . "</th>";
                        }
                        echo "</tr></thead>";

                        // Fetch and display data rows
                        echo "<tbody>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                // Display NULL explicitly, escape others
                                echo "<td>" . ($value === null ? '<i>NULL</i>' : htmlspecialchars($value)) . "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<div class='info'>Query executed successfully, but returned 0 rows. (Execution time: " . $execution_time . " ms)</div>";
                    }
                    // Free result set memory
                    $result->free();
                }
                // Check if the query was successful but didn't produce a result set (e.g., INSERT, UPDATE, DELETE)
                elseif ($result === true) {
                    $affected_rows = $link->affected_rows;
                    echo "<div class='info'>Query executed successfully. " . $affected_rows . " row(s) affected. (Execution time: " . $execution_time . " ms)</div>";
                }
                // If $result is false, an error occurred, but mysqli_report should have thrown an exception
                // This else block might be redundant with mysqli_report enabled, but kept for clarity.
                else {
                     echo "<div class='error'>Query failed. Please check syntax. (Error: " . htmlspecialchars($link->error) . ")</div>";
                }

             } catch (mysqli_sql_exception $e) {
                 // Catch query execution errors
                 echo "<div class='error'>SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>";
             }

        } elseif (isset($_GET['submit_query']) && empty($query_to_run)) {
             echo "<div class='info'>Please enter a query to run.</div>";
        } elseif (!isset($_GET['submit_query']) && !isset($_GET['action'])) {
            // Initial page load without any action
             echo "<div class='info'>Enter a query or use a sample button.</div>";
        }

        // Close the connection when done
        if ($link) {
            mysqli_close($link);
        }
        ?>
    </div> <!-- /results -->

</div> <!-- /container -->

</body>
</html>