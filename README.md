# DESCRIZIONE REST #

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
    "fileName":"nome del file",
    "fileExtension":"estensione del file tipo .txt"
    "fileContent":"contenuto in base64"
}

### /setLink ###
{
    "username":"username",
    "hashedId":"l'hash dell'id del file, dato al login",
    "genLink":"booleano, 1 per settare il link, 0 per disattivarlo"
}

### /file/{hashedId} ###
hashedId rappresenta l'hash dell'id del file, dato al login
