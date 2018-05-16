<?php

/*
Questo script prende da un JSON il nome dell'utente che sta uppando il file,
il nome del file e il suo contenuto codificato in base 64
in BASE64
*/

include 'connection.php';
include $_SERVER['DOCUMENT_ROOT'] . '\checkStructure.php';

//prende il JSON e lo converte in oggetto
$postData = json_decode(file_get_contents('php://input'));
$response = array();


if(!json_last_error())
{
    //controlla se nell'oggetto ci sono i campi descritti prima
    if(property_exists($postData, 'username'))
    {
        $username = $postData->username;

        $query = "SELECT id FROM users WHERE username='$username'";
        $result = $mysqli->query($query);

        if($result->num_rows > 0)
        {
            $userId = $result->fetch_assoc()['id'];

            $query = "SELECT id, name, size, uploaded_at, is_link_generated FROM files WHERE userid='$userId' ORDER BY uploaded_at DESC";
            $result = $mysqli->query($query);

            if($result->num_rows > 0)
            {
                $files = array();

                while($file = $result->fetch_assoc())
                {
                    $file['id'] = hash('tiger192,3', $file['id']);
                    $file['size'] = bytes($file['size'], NULL, NULL, false);
                    array_push($files, $file);
                }

                $response["status"] = 200;
                $response["files"] = $files;
            }
            else
            {
                $response["status"] = 404;
                $response["files"] = "Files not found";
            }
        }
        else
        {
            $response["status"] = 404;
            $response["files"] = "User not found";
        }

    }
    else
    {
        $response["status"] = 400;
        $response["message"] = "Missing params";
    }

}
else
{
    $response["status"] = 400;
    $response["message"] = "Wrong JSON structure";
}

//manda un JSON con gli eventuali attributi settati
echo json_encode($response);

function bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
{
    // Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

    // IEC prefixes (binary)
    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    }
    // SI prefixes (decimal)
    else
    {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE)
    {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}

?>
