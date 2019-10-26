# ScidApi
Supercell id API proxy for retrieving the account token. Compatible with Clash Royale and Brawl Stars (tested only for clash royale, for brawl stars compatibility you need to change the `scroll` parameter).
# Usage
* Specify your MySQL database credentials in the method `getDatabaseConnection` located in file `system/ApiRequest.php`.
* Specify the array of api users in the `system/Config.php` file. Don't forget to update the `IDTYPE_MAX_VALUE` variable.
