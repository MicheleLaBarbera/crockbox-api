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
    if(property_exists($postData, 'username') && property_exists($postData, 'fileName') && property_exists($postData, 'fileExtension') && property_exists($postData, 'fileContent') && property_exists($postData, 'fileSize'))
    {
        //cerca un utente con il nome fornito nel JSON
        $query = "SELECT id FROM users WHERE username = '$postData->username'";
        $result = $mysqli->query($query);

        if($result->num_rows > 0)
        {

            $fileName = $postData->fileName;
            $fileExtension = $postData->fileExtension;
            $fileSize = $postData->fileSize;

            //decodifica il contenuto del file
            $fileContent = base64_decode($postData->fileContent);

            //se il file è un XML e ha la stessa struttura del nostro, l'estensione sarà txt per poter salvare gli attributi
            libxml_use_internal_errors(true);
            $actualStructure =  simplexml_load_string($fileContent);
            $xmlStructure = new SimpleXMLElement(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '\structure.xml'));

            if($actualStructure && xml_is_equal($actualStructure, $xmlStructure))
                $fileExtension = "txt";

            //trasforma il risultato in un array associativo, prendendo solo l'ID
            $id = $result->fetch_assoc()['id'];

            //controlla se il file esiste già
            if(file_exists("uploads/$postData->username/$fileName.$fileExtension"))
            {
                /*
                Se il file esiste già, appende al nome del file (n), incrementando
                n fino a quando è possibile salvare il file.
                Se ad esempio esistono i file test.txt e test(2).txt, il codice
                si fermerà non appena $n sarà 3, generando il file test(3).txt
                */
                $n = 2;
                do
                {
                    $complete = false;

                    $checkFile = $fileName . "($n).$fileExtension";
                    if(file_exists("uploads/$postData->username/$checkFile"))
                        $n++;
                    else
                    {
                        $completeFile = $checkFile;
                        $complete = true;
                    }

                }while(!$complete);
            }
            else
                $completeFile = "$fileName.$fileExtension";

            /*inserisce il file nel database specificando il nome del file, l'id dell'utente che lo sta uppando,
            la dimensione del file e la data di upload*/
            $uploaded_at = date("d/m/Y - H:i:s");
            $query = "INSERT INTO files(name, userid, size, uploaded_at) VALUES ('$completeFile', $id, '$fileSize', '$uploaded_at')";
            if($mysqli->query($query))
            {
                /*
                se la query è andata a buon fine, controlla se la cartella esiste:
                se non esiste, la crea
                */
                if(!file_exists("uploads/$postData->username"))
                    mkdir("uploads/$postData->username");

                if($actualStructure) //vuol dire che è un file XML
                {
                    $isEqual = xml_is_equal($xmlStructure, $actualStructure);
                    if($isEqual === true) //vuol dire che ha la stessa struttura del nostro file XML
                    {

                        $data = null;

                        $xmlIterator = new SimpleXMLIterator($actualStructure->asXML());
                        $xmlIterator->rewind();
                        do
                        {
                            if(trim((string)$xmlIterator->current()) != "")
                                $data .= $xmlIterator->key() . ": " . $xmlIterator->current() . "\r\n";

                            $xmlIterator->current()->rewind();
                            if(trim((string)$xmlIterator->current()->getChildren()) != "")
                            {

                                $data .= strtoupper($xmlIterator->key()) . "\r\n";
                                do
                                {
                                    if(trim((string)$xmlIterator->current()->current()) != "")
                                        $data .= "\t" . $xmlIterator->current()->key() . ": " . $xmlIterator->current()->current() . "\r\n";
                                    $xmlIterator->current()->next();
                                }while($xmlIterator->current()->current() != null);
                            }

                            $xmlIterator->next();
                        }while($xmlIterator->current() != null);

                        //salva il file in memoria
                        if($data != null)
                            file_put_contents("uploads/$postData->username/$completeFile", $data);
                        else
                        {
                            $query = "DELETE FROM files WHERE name='$completeFile'";
                            $mysqli->query($query);
                        }
                    }
                    else //non ha la stessa struttura
                    {
                        file_put_contents("uploads/$postData->username/$completeFile", $fileContent);
                    }
                }
                else //non è un file XML
                    file_put_contents("uploads/$postData->username/$completeFile", $fileContent);


                //allega l'id hashato del file, per poterlo scaricare successivamente
                $query = "SELECT id FROM files WHERE name='$completeFile'";
                $result = $mysqli->query($query);
                $id = $result->fetch_assoc()['id'];
                $hashedId = hash('tiger192,3', $id);

                $response["status"] = 200;
                $response["id"] = $hashedId;
            }
            else
            {
                $response["status"] = 500;
                $response["message"] = "Cannot upload file";
                $response["error"] = $mysqli->error;
            }
        }
        else
        {
            $response["status"] = 404;
            $response["message"] = "User not found";
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



?>
