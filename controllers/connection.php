<?php
$mysqli = new mysqli("localhost", "root", "1234", "fileupload");
if ($mysqli->connect_errno) {
    echo "Errore: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

?>
