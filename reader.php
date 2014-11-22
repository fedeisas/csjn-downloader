<?php

foreach ($locations as $location) {
    $dir = join('/', ['.', $location, 'output']);
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (!in_array($file, ['.', '..']) && is_string($file)) {
                    $filename = join('/', [$dir, $file]);
                    $type = (strpos($file, 'V') !== false ? 'victima' : 'imputado');
                    parse_str(file_get_contents($filename), $content);
                    $content['tipo'] = $type;
                    $content['ubicacion'] = $location;
                    $content['id'] = join('-', [$location, str_replace('.txt', '', $file)]);

                    $rows[] = sanitize($content);
                }
            }
            closedir($dh);
        }
    }
}

$output = fopen("output.csv", 'w');
fputcsv($output, ['id', 'tipo', 'ubicacion', 'sexo', 'edad', 'nacionalidad', 'fecha', 'hora', 'lugar', 'comuna', 'barrio', 'villa', 'movil', 'arma']);
foreach($rows as $row) {
    fputcsv($output, $row);
}
fclose($output);