# ScidApi
Supercell id API proxy for retrieving the account token. Compatible with Clash Royale and Brawl Stars (tested only for clash royale, for brawl stars compatibility you need to change the `scroll` parameter to `laser`).
# Usage
This repo is meant to be deployed to a production server so that your app can track scid tokens using a web API. If you just need to get the scid token, read the "Usage without installation" section.

* Specify your MySQL database credentials in the method `getDatabaseConnection` located in file `system/ApiRequest.php`.
* Specify the array of api users in the `system/Config.php` file. Don't forget to update the `IDTYPE_MAX_VALUE` variable.
# Usage without installation

1. Perform a `POST` request to `https://ingame.id.supercell.com/api/account/login`.
   
   In the body of your post request there should be following parameters: `email`, `lang`, `game` and `env`.
   
   The `lang` parameter can be e.g. `en` or `ru` (It affects only the web interface language).
   
   Specify the `env` parameter as `prod` (for production).
   
   For Clash Royale, specify the `game` parameter to be `scroll`, for Brawl Stars: `laser`.
   
   **Important**: The values should be serialized as URL parameters but sent via POST body, not as JSON. Example: `email=example@gmail.com&lang=en&game=scroll&env=prod`.
   
2. The supercell server should respond with `{"ok": true}`. If so, you will receive an email with the pin code shortly.
3. Perform a `POST` request to `https://ingame.id.supercell.com/api/account/login.validate` with the `email` and `pin` parameters in the `POST` body. Just as in the first request, we need to serialize these parameters as if they were URL parameters.
4. The supercell server should respond with the following json-object:
   ```json
   {
       "ok": true,
       "data": {
           "isValid": true,
           "isBound": true
       }
   }
   ```
5. Perform a `POST` request to `https://ingame.id.supercell.com/api/account/login.confirm` with exactly the same `email` and `pin` parameters as in the last request (serialized the same way).
6. The supercell server should respond with:
   ```javascript
   {
       "ok": true,
       "data": {
           "scid": "<YOUR SCID TOKEN>",
           "pid": "XXXXX-YYYYY" // XXXXX is the tag's high component, YYYYY is the tag's low component
       }
   }
   ```
