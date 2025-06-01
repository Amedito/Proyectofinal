<?php
// import_csv.php — Importa datos desde CSV

require 'config/db.php';

$csvFile = __DIR__ . '/datos/reportes.csv';

if (!file_exists($csvFile)) {
    die("No se encontró el archivo CSV en $csvFile\n");
}

if (($handle = fopen($csvFile, 'r')) === false) {
    die("No se puede abrir $csvFile\n");
}

// Lee la primera fila (encabezados) y la ignora
$headers = fgetcsv($handle, 1000, ',');

// Prepara las consultas
$insertSupervisor = $pdo->prepare("
    INSERT IGNORE INTO supervisores (tienda, supervisor)
    VALUES (:tienda, :supervisor)
");
$insertCircuito = $pdo->prepare("
    INSERT IGNORE INTO circuitos (aplicativo, circuito)
    VALUES (:aplicativo, :circuito)
");
$insertReporte = $pdo->prepare("
    INSERT INTO reportes
      (tienda, tipo, fecha, hora, estado, severidad,
       aplicativo, circuito, supervisor, detalle)
    VALUES
      (:tienda, :tipo, :fecha, :hora, :estado, :severidad,
       :aplicativo, :circuito, :supervisor, :detalle)
");

// Asume que el CSV tiene columnas en este orden:
// Tienda,Tipo,Fecha,Hora,Estado,Severidad,Aplicativo,Circuito,Supervisor,Detalle
while (($row = fgetcsv($handle, 1000, ',')) !== false) {
    // Salta filas vacías
    if (count($row) < 10 || trim($row[0]) === '') {
        continue;
    }

    // Asigna valores
    list($tienda, $tipo, $fechaRaw, $horaRaw, $estado,
         $severidad, $aplicativo, $circuito,
         $supervisor, $detalle) = $row;

    // Convierte fecha y hora a formatos MySQL
    $fecha = date('Y-m-d', strtotime($fechaRaw));
    $hora  = date('H:i:s', strtotime($horaRaw));

    // Inserta en supervisores y circuitos (ignore duplicados)
    $insertSupervisor->execute([
        ':tienda'     => $tienda,
        ':supervisor' => $supervisor,
    ]);
    $insertCircuito->execute([
        ':aplicativo' => $aplicativo,
        ':circuito'   => $circuito,
    ]);

    // Inserta en reportes
    $insertReporte->execute([
        ':tienda'     => $tienda,
        ':tipo'       => $tipo,
        ':fecha'      => $fecha,
        ':hora'       => $hora,
        ':estado'     => $estado,
        ':severidad'  => $severidad,
        ':aplicativo' => $aplicativo,
        ':circuito'   => $circuito,
        ':supervisor' => $supervisor,
        ':detalle'    => $detalle,
    ]);
}

fclose($handle);

echo "Importación completada con éxito.\n";

