<!DOCTYPE html>
<html>
    <head></head>
<body>

            <?php

            $server = "localhost";
            $user = "root";
            $pass = "";
            $base = "tup2_bd";

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $link = mysqli_connect($server, $user, $pass, $base);

            if ($link->connect_errno) {
                die("Error de conexión: " . $link->connect_error);
            }

            // $link = mysqli_connect("localhost", "root", "", "tup2_bd") or die("error");
            
            // echo "it works!";

            ?>

            <form method="GET" style="display: flex; flex-direction: column; align-items: flex-start; gap: 10px;">
                <input type="text" name="query" 
                    value="<?php if (isset($_GET['query'])) echo $_GET['query']; ?>" 
                    style="width: 600px; height: 100px; font-size: 16px;">
                
                <input type="submit" name="boton" value="Consultar" 
                    style="width: 150px; height: 34px; font-size: 16px;">
            </form>

            <?php

            if (isset($_GET['query'])) {
                $q = $_GET['query'];

                $sql1 = "SELECT persona.nombre AS persona, materia.nombre AS materia, nota.valor
                        FROM nota
                        INNER JOIN cursada ON nota.cursada_id = cursada.id
                        INNER JOIN persona ON cursada.persona_id = persona.id
                        INNER JOIN materia ON cursada.materia_id = materia.id";

                $res = $link->query($q);

                if ($res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) { 
                        foreach ($row as $col => $valor) {
                            echo "$col: $valor ";
                        }
                        echo "<br>";
                    }
                } else {
                    echo "No hay resultados.";
                }
            }

            ?>

            <!-- <form action="" method="post">
            <table border="1" width="80%">
                <tr>
                    <td colspan="2">cursos</td>
                </tr>
                <tr>
                    <td>clave</td>
                    <td><input type="text" name="codcurso"</td>
                </tr>
                <tr>
                    <td>nombre</td>
                    <td><input type="text" name="nomcurso"</td>
                </tr>
                <tr>
                    <td colspan="2">
                    
                    <input type="submit" name="boton" value="Agregar">
                    <input type="submit" name="boton" value="Borrar">
                    <input type="submit" name="boton" value="Modificar">
                    <input type="submit" name="boton" value="Buscar">
        
                </tr>

            </table> -->

</body>