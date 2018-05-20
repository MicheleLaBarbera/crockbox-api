<?php

/*
Questo script legge il codice inserito nell'URL e cerca il suo corrispondente
tra i file nel database. Se il boolean is_link_generated è uguale a 1, vuol dire
che il proprietario del file ha attivato il download tramite link, altrimenti
verrà restituito un errore 404 - non trovato. Nel caso in cui il richiedente di un
file sia il proprietario di quest'ultimo, invece, gli sarà SEMPRE possibile scaricarlo,
anche se il boolean dovesse essere settato a 0
*/

include 'connection.php';

$wildcard = substr($request_uri[0], 6);

/*controlla se l'utente è stato specificato: se sì, cerca il suo id nel database,
altrimenti imposta la variable userId a -1*/
$postData = json_decode(file_get_contents('php://input'));
if($postData != null && property_exists($postData, 'username'))
{
    $username = $postData->username;
    $query = "SELECT id FROM users WHERE username='$username'";
    $user = $mysqli->query($query);

    if($user->num_rows > 0)
        $userId = $user->fetch_assoc()['id'];
    else
        $userId = -1;
}
else
{
    $userId = -1;
}


$query = "SELECT * FROM files";
$result = $mysqli->query($query);

if($result->num_rows > 0)
{
    //trasforma il risultato in un array associativo
    while($filesRow = $result->fetch_assoc())
    {
        //controllo: se il file è pubblico, o se appartiene all'utente che ha fatto la richiesta
        if($filesRow['is_link_generated'] == 1 || $filesRow['userid'] == $userId)
        {

            $hashedId = hash('tiger192,3', $filesRow['id']);

            if($hashedId == $wildcard)
            {
                $query = "SELECT username FROM users WHERE id = '". $filesRow['userid'] . "'";
                $username = $mysqli->query($query);
                $userRow = $username->fetch_assoc();

                $file = "./uploads/" . $userRow['username'] . "/" . $filesRow['name'];
                $fileName = $filesRow['name'];

                if(!file_exists($file))
                {
                    unset($file);
                    unset($fileName);
                }

                break;
            }
        }
    }
}

if(isset($file))
{
    $fileContent = file_get_contents($file);

    echo json_encode(array(
        "status" => 200,
        "fileName" => $fileName,
        "fileContent" => base64_encode($fileContent)
    ));

}

else
{
    echo json_encode(array(
        "status" => 404,
        "message" => "File not found",
    ));
}

?>
