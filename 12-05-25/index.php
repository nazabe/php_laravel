<?php

$file = './usuarios.json';
$json_data = file_get_contents($file);

if ($json_data === false) {
    die("Error reading file.");
}

$users = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error parsing file: " . json_last_error_msg());
}

echo '<pre>' . htmlspecialchars(json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';

foreach ($users as $user) {
    echo "<li>" . $user['nombre'] . " (ID: " . $user['id'] . ")</li>";
}

$new_user = [
    "id" => "3",
    "nombre" => "Juan",
    "apellido" => "Perez",
    "telefono" => "12345678",
    "activo" => true,
    "direccion" => [
        "calle" => "French",
        "altura" => "400"
    ]
];

$users[] = $new_user;
$json_final = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (is_writable($file)) {
    echo "El archivo es escribible.";
} else {
    echo "El archivo NO es escribible.";
}

// Verificar si se pudo escribir el archivo
if (file_put_contents($file, $json_final) === false) {
    var_dump(error_get_last());
    die("Error al guardar en el archivo. " . var_export(error_get_last(), true));
} else {
    echo "¡Guardado exitosamente!";
}

echo '<pre>' . htmlspecialchars($json_final) . '</pre>';
?>
