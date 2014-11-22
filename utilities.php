<?php

/**
 * Type key to type string
 */
function getRowType($key)
{
    if (strtolower($key) == 'v') {
        return 'victima';
    }
    if (strtolower($key) == 'i') {
        return 'imputado';
    }
}

/**
 * Remove special characters
 * @param string $string
 * @return string $string
 */
function clean_string($string)
{
    $string = ereg_replace("[áàâãª]", "a", $string);
    $string = ereg_replace("[ÁÀÂÃ]", "A", $string);
    $string = ereg_replace("[éèê]", "e", $string);
    $string = ereg_replace("[ÉÈÊ]", "E", $string);
    $string = ereg_replace("[íìî]", "i", $string);
    $string = ereg_replace("[ÍÌÎ]", "I", $string);
    $string = ereg_replace("[óòôõº]", "o", $string);
    $string = ereg_replace("[ÓÒÔÕ]", "O", $string);
    $string = ereg_replace("[úùû]", "u", $string);
    $string = ereg_replace("[ÚÙÛ]", "U", $string);
    $string = str_replace("ñ", "n", $string);
    $string = str_replace("Ñ", "N", $string);

    return $string;
}

/**
 * Sanitize row into useful data
 * @param array $row
 * @return array $data
 */
function sanitize($row)
{
    $data = [
        'id' => (array_key_exists('id', $row) ? trim($row['id']) : null),
        'tipo' => $row['tipo'],
        'ubicacion' => $row['ubicacion'],
        'sexo' => (array_key_exists('sexo', $row) ? trim($row['sexo']) : null),
        'edad' => (array_key_exists('edad', $row) ? (int) trim($row['edad']) : null),
        'nacionalidad' => (array_key_exists('nacionalidad', $row) ? trim($row['nacionalidad']) : null),
        'fecha' => (array_key_exists('fecha', $row) ? trim($row['fecha']) : null),
        'hora' => (array_key_exists('hora', $row) ? trim($row['hora']) : null),
        'lugar' => (array_key_exists('lugar', $row) ? trim($row['lugar']) : null),
        'comuna' => (array_key_exists('comuna', $row) ? trim($row['comuna']) : null),
        'barrio' => (array_key_exists('barrio', $row) ? trim($row['barrio']) : null),
        'villa' => (array_key_exists('villa', $row) ? trim($row['villa']) : null),
        'movil' => (array_key_exists('movil', $row) ? trim($row['movil']) : null),
        'arma' => (array_key_exists('arma', $row) ? trim($row['arma']) : null),
    ];

    foreach ($data as $key => $value) {
        $data[$key] = utf8_decode(
            mb_strtolower(
                trim(
                    str_replace('"', '', $value)
                ),
                'UTF-8'
            )
        );
    }

    if ($data['edad'] == 0) {
        $data['edad'] = null;
    } else {
        $data['edad'] = (int) $data['edad'];
    }

    if (in_array($data['sexo'], ['s/d', ''])) {
        $data['sexo'] = null;
    } else {
        $data['sexo'] = (string) $data['sexo'];
    }

    $data['comuna'] = (int) $data['comuna'];

    if ($data['nacionalidad'] === 'sindatos') {
        $data['nacionalidad'] = null;
    } else {
        $data['nacionalidad'] = (string) $data['nacionalidad'];
    }

    $data['villa'] = (int) ($data['villa'] == 'si') ? 1 : 0;

    if ($data['movil'] === 's/d') {
        $data['movil'] = null;
    } else {
        $data['movil'] = (string) $data['movil'];
    }

    $data['fecha'] = DateTime::createFromFormat('d/m/Y', $data['fecha'])->format('Y-m-d');

    return $data;
}
