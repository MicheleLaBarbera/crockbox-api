<?php

$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);


switch ($request_uri[0]) {
    case '/register':
        require './controllers/register.php';
        break;
    case '/login':
        require './controllers/login.php';
        break;
    case '/upload':
        require './controllers/uploadByUser.php';
        break;
    case '/uploadAnonymously':
        require './controllers/uploadAnonymously.php';
        break;
    case '/setLink':
        require './controllers/setLink.php';
        break;
    case '/getFiles':
        require './controllers/getFiles.php';
        break;
}

if(strpos($request_uri[0], "/file/") !== false)
    require './controllers/downloadFile.php';



?>
