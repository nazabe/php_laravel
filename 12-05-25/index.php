<?php

    $file = './usuarios.json';
    $json_data = file_get_contents($file);
    
    if($json_data == false)
    {
        die("Error reading file.");
    }

    $users = json_decode($json_data, true);

    if(json_last_error() !== JSON_ERROR_NONE)
    {
        die("Error parsing file.".json_last_error_msg());
    }

    echo '<pre>' . htmlspecialchars(json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    
?>