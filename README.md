# Developed by Gabriele Palmeri #

## Routes ##

### /register ###
{
    "username":"username",
    "password":"password"
}

### /login ###
{
    "username":"username",
    "password":"password"
}

### /upload ###
{
    "username":"username",
    "fileName":"file name",
    "fileExtension":"file extension"
    "fileContent":"base64 encoded content"
}

### /setLink ###
{
    "username":"username",
    "hashedId":"file's hashed id",
    "genLink":"boolean, 1 to set the link, 0 to delete it"
}

### /file/{hashedId} ###
hashedId represents the file's hashed id
