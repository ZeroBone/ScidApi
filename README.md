# ScidApi
Supercell ID API proxy for retrieving the account token. Compatible with all global Supercell Games (tested only for clash royale).
# Usage
This repo is meant to be deployed to a production server so that your app can track scid tokens using a web API. If you just need to get the scid token, read the ["Documentation"](#documentation) section.

* Specify your MySQL database credentials in the method `getDatabaseConnection` located in file `system/ApiRequest.php`.
* Specify the array of api users in the `system/Config.php` file. Don't forget to update the `IDTYPE_MAX_VALUE` variable.

# Documentation

All the documentation about Supercell ID API is available [here](/APIDoc.MD)
