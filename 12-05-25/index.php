<?php
$selectedFile = $_GET['file'] ?? null;
$files = glob('./*.json');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Selector de archivos JSON</title>
</head>
<body>
    <h2>Seleccionar archivo JSON</h2>
    <form method="get">
        <select name="file" onchange="this.form.submit()">
            <option value="">-- Selecciona un archivo --</option>
            <?php foreach ($files as $file): ?>
                <?php $fileName = basename($file); ?>
                <option value="<?= htmlspecialchars($fileName) ?>" <?= ($selectedFile === $fileName) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($fileName) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php
    if ($selectedFile) {
        $path = "./" . basename($selectedFile); // Evitar path traversal

        if (!file_exists($path)) {
            echo "<p style='color:red;'>Archivo no encontrado.</p>";
        } else {
            $json_data = file_get_contents($path);
            if ($json_data === false) {
                echo "<p style='color:red;'>Error al leer el archivo.</p>";
            } else {
                $data = json_decode($json_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo "<p style='color:red;'>Error al parsear JSON: " . json_last_error_msg() . "</p>";
                } else {
                    echo "<h3>Contenido de '$selectedFile':</h3>";
                    echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
                }
            }
        }
    }
    ?>
</body>
</html>
