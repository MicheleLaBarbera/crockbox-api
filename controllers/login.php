<?php

/*
Questo script restituisce dati a un utente, dati username e password dello
stesso. I dati restituiti sono: nome utente, lista dei file sottoforma di Hash
degli id degli stessi.
*/

include 'connection.php';

//prende il JSON e lo converte in oggetto
$postData = json_decode(file_get_contents('php://input'));
$response = array();

if(!json_last_error())
{
    if(property_exists($postData, 'username') && property_exists($postData, 'password'))
    {
        $query = "SELECT id, username, password FROM users WHERE username = '$postData->username'";
        $result = $mysqli->query($query);
        if($result->num_rows > 0)
        {
            //trasforma il risultato in un array associativo
            $row = $result->fetch_assoc();
            if(password_verify($postData->password, $row['password']))
            {
                $id = $row['id'];
                $username = $row["username"];

                $query = "SELECT id FROM files WHERE userid = '$id'";
                $result = $mysqli->query($query);

                $idArray = array();
                while ($row = $result->fetch_assoc())
                {
                    $hashedId = hash('tiger192,3', $row['id']);

                    array_push($idArray, $hashedId);
                }

                $response["status"] = 200;
                $response["body"] = new stdClass();
                $response["body"]->username = $username;
                $response["body"]->files = $idArray;
            }
            else
            {
                $response["status"] = 404;
                $response["message"] = "Not found";
            }
        }
        else
        {
            $response["status"] = 404;
            $response["message"] = "Not found";
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
