
# API Documentation

This document contains all the endpoints available with Supercell ID API.

## Root Endpoint

All the endpoints below are on the root url `https://ingame.id.supercell.com/api`.

## Authentication
A small note on authentication here, Supercell ID requires you to authenticate using a JWT for every endpoint below and that JWT is passed on by the game client (using `supercellid://` URL Scheme). As we do not have the private key to generate the JWTs, only those endpoints are well documented which work without authentication/work on the principle of least privileged authentication.
## Endpoints

1. `POST /account/login`

    ### Request
    **Headers** : None other than the default ones.
    **Body**    : URL Encoded body (`application/x-www-form-urlencoded`) with the following parameters. Example: `email=me@example.com&env=prod&game=soil&lang=en`

    | Parameter | Possible Value |
      | ----- | ----- |
      | `game` | Refer the table below. |
      | `email` | The email address on which the code is to be sent. Has the same use in all the endpoints below. |
      | `env` | The game environment. For production environment use `prod` |
      | `lang` | An [ISO 639-1 Language Code](https://wikipedia.org/wiki/List_of_ISO_639-1_codes) in which the mail is to be sent. If the language is not supported, English will be used as fallback language. |

    Possible values for `game` parameter.

    | Game (Common Name) | Value for `game` (Codename) |
      | ----- | ----- |
      | Hay Day | `soil` |
      | Clash of Clans | `magic` |
      | Boom Beach | `reef` |
      | Clash Royale | `scroll` |
      | Brawl Stars | `laser` |

      ### Successful Response
    **Body** : 
    ```json
    {"ok":true}
    ```
2. `POST /account/login.validate`  
    
    ### Request
    **Headers** : None except default ones.
    
    **Body** : URL Encoded body (`application/x-www-form-urlencoded`) with the `email` & `pin` parametres. (`pin` is the six-digit code you receive on your email)

    ### Successful Response
    **Body**:
    ```javascript
    {"ok" : true, 
    "data" : { 
        "email" : "<Email to which code was sent>", 
        "isValid" : true, 
        "isBound" : true, 
        "application" : { 
            "application" : "<game paramater hyphenated by environment>", // Eg. scroll-prod 
            "account" : "<Player Tag>", // Eg. #8PRLVC0J
            "username" : "<Player Name>", // Eg. OJ 
            "progress" : [ "<XP Level>", "<XP Points>" ] // Eg. [5,820] 
            }, 
        "system" : { 
            "system" : "<Game parameter>", //Eg. scroll 
            "account" : "<Player Tag>", // Eg. #8PRLVC0J
            "username" : "<Player Name>", // Eg. OJ 
            "progress" : [ "<XP Level>", "<XP Points>" ] // Eg. [5,820] 
            } }}
            ```
3. `POST /account/login.confirm`

    ### Request
    **Headers** : Default.
    
    **Body** : Same as in `/account/login.validate`.

    ### Successful Response
    **Body** :
    ```javascript
     { "ok" : true, 
       "data" : { 
           "scid" : "<An ES256 JWT for authentication>", // The body contains game (Game codename eg. scroll), pid (Contains high and low components of tag in XXX-YYY format), env (Environment eg. prod), iat (timestamp) & scid claims.
           "scidToken" : "<An ES256 OpenID JWT>", // Practically both the tokens carry same claims but are signed using different keys.
           "bindToken" : "<A token which is used to login on the game client> (Requires Authentication)", // Not much information is available on the type and kind of this token because it requires authentication. The scid JWT does not have required privileges to get this token.
           "email" : "<Email to which the code was sent>" 
           }
           ```
4. `POST /account/links.get`

    ### Request
    **Headers** : Requires at least the `scid` Bearer token obtained using `/account/login.confirm` endpoint which is used for authentication using the `Authorization` header. Example : `Authorization: Bearer <scid>`
    
    **Body** : URL Encoded body (`application/x-www-form-urlencoded`) with the `scid_token` parameter containing `scidToken` from the `/account/login.confirm` response.

    ### Successful Response
    **Body**:
    ```javascript
    { "ok" : true, 
      "data" : 
      { 
          "attributionId" : "<A GUID/UUID without hyphens for internal use>", // Eg. 835dd13bb5b846279b0d30c9bf41746d  
          "system" : "<The name of the game on whose behalf the request was made>", // Eg. Clash Royale 
          "links" : [  // An array of 5 objects(as of now)
              { 
                  "system" : "Unknown/<Game Name> (If Unknown, the player has not linked his account with the particular game, if a game name appears, the player has linked his account with the particular game.)" // Eg. Clash Royale
              } 
                   ] 
        }
    ```

    **Note** : The above endpoint requires auth. Since, `scid` token is not a fully privileged token, some fields in the response body *may* be missing.

5. `POST /account/settings.get`

    ### Request

    **Headers** : Requires at least the `scid` Bearer token obtained using `/account/login.confirm` endpoint which is used for authentication using the `Authorization` header. Example : `Authorization: Bearer <scid>`

    **Body** : Same as `/account/links.get`

    ### Successful Response

    **Body**:
    ```javascript
    {
    "ok": true,
    "data": {
        "created": "<An ISO 8601 timestamp indicating the time when the Supercell ID was created>", // Eg. 2022-03-21T16:02:35.560Z
        "lang": "<An ISO 639-1 Language Code>",
        "email_marketing_permission": false,
        "email_marketing_timestamp": "<An ISO 8601 timestamp indicating the time when the email marketing setting was last changed>", // Eg. 2022-03-21T16:02:35.560Z
        "marketing_scope_consents": [
            {
                "scope_id": "beatstar",
                "scope_localized_name": "Beatstar",
                "scope_consent": true
            },
            {
                "scope_id": "boombeach",
                "scope_localized_name": "Boom Beach",
                "scope_consent": false
            },
            {
                "scope_id": "brawlstars",
                "scope_localized_name": "Brawl Stars",
                "scope_consent": true
            },
            {
                "scope_id": "clashofclans",
                "scope_localized_name": "Clash of Clans",
                "scope_consent": false
            },
            {
                "scope_id": "clashroyale",
                "scope_localized_name": "Clash Royale",
                "scope_consent": true
            },
            {
                "scope_id": "hayday",
                "scope_localized_name": "Hay Day",
                "scope_consent": false
            },
            {
                "scope_id": "supercell",
                "scope_localized_name": "Supercell News",
                "scope_consent": true
            }
        ] // All the booleans above can be either true/false.
    } 
    }
    ```
 6. `POST /account/settings.set`

    ### Request

    **Headers** : Requires at least the `scid` Bearer token obtained using `/account/login.confirm` endpoint which is used for authentication using the `Authorization` header. Example : `Authorization: Bearer <scid>`

    **Body** : URL Encoded body (`application/x-www-form-urlencoded`) with the `scid_token` parameter containing `scidToken` from the `/account/login.confirm` response and `accept_marketing` parameter which is either `true` or `false` to change the email marketing setting.

    ### Successful Response
    **Body** : Same as in `/account/settings.get`

## Unsuccessful Response

In any of the cases above, you will get `Unauthorized` response from the endpoints which require authentication and you don't authenticate.

If there is some other error you will get a response body like the below one.
```javascript
{
    "ok":false,
    "error":"<An error message>" //Eg. bad_request,binding_not_found etc
    }
```

## Additional Endpoints

In addition to the above all endpoints, there are 2 more endpoints as listed below.

    * `POST /account/create`
    * `POST /account/create.confirm`

The endpoints above are used to create new Supercell IDs. These have not been documented as they require authentication and the `scid` JWT does not carry enough privileges to authenticate with the above endpoints. Most probably the JWT received from the game client is the one which can be used to successfully authenticate with them. Also, the `/account/create` endpoint is protected by Google's Recaptcha (that does not matter much anyways because it is easy to get the captcha API token, the main difficulty is obtaining a JWT 😅).