# ScidApi
Supercell ID API proxy for retrieving the account token. Compatible with all global Supercell Games (tested only for clash royale).
# Usage
This repo is meant to be deployed to a production server so that your app can track scid tokens using a web API. If you just need to get the scid token, read the ["Usage without installation"](#usage-without-installation) section.

* Specify your MySQL database credentials in the method `getDatabaseConnection` located in file `system/ApiRequest.php`.
* Specify the array of api users in the `system/Config.php` file. Don't forget to update the `IDTYPE_MAX_VALUE` variable.
# Usage without installation

1. Perform a `POST` request to `https://ingame.id.supercell.com/api/account/login`.
   
   In the body of your post request there should be following parameters: `email`, `lang`, `game` and `env`.
   
   * The `lang` parameter should be an [ISO 639-1 Code](https://wikipedia.org/wiki/List_of_ISO_639-1_codes) for the language. (It affects only the language in which the mail is sent). Eg : `en` for English.
   
     **Note**: If Supercell ID does not support that language, mail will be sent in English.
   
   * Specify the `env` parameter as `prod` (for production environment of the game).
   
   * For the `game` parameter use the values from the table below.
   
      | Game (Common Name) | Value for `game` (Codename) |
      | ----- | ----- |
      | Hay Day | `soil` |
      | Clash of Clans | `magic` |
      | Boom Beach | `reef` |
      | Clash Royale | `scroll` |
      | Brawl Stars | `laser` |
      
      **Note** : Codenames for other games (such as Clash Mini,Everdale etc) will be added when they launch globally.
   
   **Important**: The values should be serialized as URL parameters but sent via POST body, not as JSON. 
   
   Example: `email=me@example.com&lang=en&game=scroll&env=prod`.
   
   ### Response
   
   A successful response should look like :
   
   ```json
   {"ok":true}
   ```


3. Perform a `POST` request to `https://ingame.id.supercell.com/api/account/login.validate` with the `email` and `pin` parameters in the `POST` body. Just as in the first request, we need to serialize these parameters as if they were URL parameters.

   ### Response

    A successful response should look like :

    ```json
    {"ok" : true, 
     "data" : {
        "email" : "<Email to which code was sent>", 
        "isValid" : true, 
        "isBound" : true, 
        "application" : { 
            "application" : "<game paramater hyphenated by environment>",/*Eg. scroll-prod*/ 
            "account" : "<Player Tag>", // Eg. #8PRLVC0J
            "username" : "<Player Name>", // Eg. OJ 
            "progress" : [ "<XP Level>", "<XP Points>" ] //Eg. [5,820] 
            }, 
        "system" : { 
            "system" : "<Game parameter>", //Eg. scroll 
            "account" : "<Player Tag>", // Eg. #8PRLVC0J
            "username" : "<Player Name>", // Eg. OJ 
            "progress" : [ "<XP Level>", "<XP Points>" ] //Eg. [5,820] 
            } }}```
5. Perform a `POST` request to `https://ingame.id.supercell.com/api/account/login.confirm` with exactly the same `email` and `pin` parameters as in the last request (serialized the same way).

   ### Response

     A successful response should look like :
     ```json
     { "ok" : true, 
       "data" : { 
           "scid" : "<An ES256 OpenID JWT>", // The body contains game (Game codename eg. scroll), pid (Contains high and low components of tag in XXX-YYY format), env (Environment eg. prod), iat (timestamp) & scid claims.
           "scidToken" : "<An ES256 JWT for authentication>", // Practically both the tokens carry same claims but are signed using different keys.
           "email" : "<Email to which the code was sent>" 
           }```


6. In any of the cases above, an unsuccessful response will look like :
    ```json
    {
    "ok":false,
    "error":"<An error message>" //Eg. bad_request,binding_not_found etc
    }
    ```
