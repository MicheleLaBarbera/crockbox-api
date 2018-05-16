<?php

/*
Questo script salva un utente sul database, dati il suo nome e la sua password.
La password sarà salvata utilizzando la codifica blowfish
*/

include 'connection.php';

//prende il JSON e lo converte in oggetto
$postData = json_decode(file_get_contents('php://input'));
$response = array();

if(!json_last_error())
{
    if(property_exists($postData, 'username') && property_exists($postData, 'password'))
    {
        $password = password_hash($postData->password, PASSWORD_BCRYPT);
        $query = "INSERT INTO users (username, password) VALUES ('" . $postData->username . "', '" . $password . "')";
        if(!$mysqli->query($query))
        {
            $response["status"] = 409;
            $response["message"] = $mysqli->error;
        }
        else
            $response["status"] = 200;
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
