<?php

/*
Questo script, dato il nome utente, l'hash dell'id di un file e un booleano,
genera o elimina un link di download di un file dell'utente
*/

include 'connection.php';

$postData = json_decode(file_get_contents('php://input'));
$response = array();

if(!json_last_error())
{
    if(property_exists($postData, 'username') && property_exists($postData, 'hashedId') && property_exists($postData, 'genLink'))
    {
        $query = "SELECT id FROM users WHERE username = '$postData->username'";
        $result = $mysqli->query($query);

        if($row = $result->fetch_assoc())
        {
            $userid = $row['id'];
            $hashedId = $postData->hashedId;
            $genLink = $postData->genLink;

            $query = "SELECT id FROM files WHERE userid = '$userid'";
            $result = $mysqli->query($query);
            while($row = $result->fetch_assoc())
            {
                $id = $row['id'];
                $hashedRow = hash('tiger192,3', $id);
                if($hashedRow == $hashedId)
                {
                    $query = "UPDATE files SET is_link_generated = $genLink WHERE id = '$id'";
                    if($mysqli->query($query))
                        $response["status"] = 200;
                    else
                    {
                        $response["status"] = 500;
                        $response["message"] = $mysqli->error;
                    }

                }
            }
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

echo json_encode($response);



?>
