<?php
$host = 'localhost';
$db   = 'tu_base_de_datos';
$user = 'tu_usuario';
$pass = 'tu_contraseña';
$charset = 'utf8mb4';

// Configuración de conexión PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}

// Matriz de ejemplo
$nuevo_registro = [
    "id" => 3,
    "nombre" => "Ana",
    "apellido" => "Gomez",
    "telefono" => "123456789",
    "activo" => true,
    "direccion" => [
        "calle" => "Mitre",
        "altura" => "456"
    ]
];

// Convertir a JSON
$json_data = json_encode($nuevo_registro, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Insertar en la base de datos
$sql = "INSERT INTO registros_json (datos) VALUES (:datos)";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':datos', $json_data, PDO::PARAM_STR);

if ($stmt->execute()) {
    echo "Registro insertado correctamente.";
} else {
    echo "Error al insertar el registro.";
}
?>
