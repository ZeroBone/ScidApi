<?php

use Api\Config\Config;
use Api\Error\Errors;
use Api\Request\ApiRequest;
use Api\Utils\TagUtils;

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/autoload.php';

$request = new ApiRequest('loginConfirm');

$request->setRequiredParameters([
    [
        'key' => 'idType',
        'type' => 'integer',
        'min' => Config::IDTYPE_MIN_VALUE,
        'max' => Config::IDTYPE_MAX_VALUE
    ],
    [
        'key' => 'id',
        'type' => 'integer',
        'min' => 1,
        'max' => 18446744073709551615,
    ],
    [
        'key' => 'pin',
        'type' => 'integer',
        'min' => 100000,
        'max' => 999999
    ]
]);

$parameters = $request->validate();

if ($parameters) {

    $db = ApiRequest::getDatabaseConnection();

    $query = $db->prepare('SELECT `email` FROM `playersPending` WHERE `idType` = ? AND `id` = ? LIMIT 1;');

    $query->execute([
        $parameters['idType'],
        $parameters['id']
    ]);

    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!isset($result['email'])) {

        $request->sendError(Errors::HANDSHAKE_NOT_FOUND, 'Handshake not found. Please start the login process before confirming it.');

    }
    else {

        $email = trim($result['email']);

        unset($result);

        unset($query);

        // loginValidate

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://ingame.id.supercell.com/api/account/login.validate');

        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

        curl_setopt($curl, CURLOPT_POST, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'email' => $email,
            'pin' => $parameters['pin']
        ]));

        $loginValidateResult = json_decode(curl_exec($curl), true);

        curl_close($curl);

        unset($curl);

        if (
            $loginValidateResult &&
            isset($loginValidateResult['ok']) &&
            isset($loginValidateResult['data']) &&
            isset($loginValidateResult['data']['isValid']) &&
            isset($loginValidateResult['data']['isBound']) &&
            $loginValidateResult['ok'] === true &&
            $loginValidateResult['data']['isValid'] === true &&
            $loginValidateResult['data']['isBound'] === true
        ) {

            unset($loginValidateResult);

            // loginConfirm

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, 'https://ingame.id.supercell.com/api/account/login.confirm');

            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

            curl_setopt($curl, CURLOPT_POST, true);

            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
                'email' => $email,
                'pin' => $parameters['pin']
            ]));

            $loginConfirmResult = json_decode(curl_exec($curl), true);

            curl_close($curl);

            unset($curl);

            if (
                $loginConfirmResult &&
                isset($loginConfirmResult['ok']) &&
                $loginConfirmResult['ok'] === true &&
                isset($loginConfirmResult['data']) &&
                isset($loginConfirmResult['data']['scid']) &&
                isset($loginConfirmResult['data']['pid'])
            ) {

                $scid = (string)$loginConfirmResult['data']['scid'];

                $tagStructure = explode('-', (string)$loginConfirmResult['data']['pid'], 2);

                $tagHigh = (int)$tagStructure[0];

                $tagLow = (int)$tagStructure[1];

                unset($tagStructure);

                $tag = TagUtils::idToTag($tagHigh, $tagLow);

                unset($tagHigh);
                unset($tagLow);

                $query = $db->prepare('DELETE FROM `playersPending` WHERE `email` = ?;');

                $query->execute([
                    $email
                ]);

                $query = $db->prepare('INSERT INTO `players` (`keyId`, `vkId`, `tag`, `email`, `scidToken`) VALUES (?,?,?,?,?);');

                $query->execute([
                    $request->userId,
                    $parameters['idType'] === Config::IDTYPE_VK_AUTV || $parameters['idType'] === Config::IDTYPE_VK_SCSTUDIO ? $parameters['id'] : 0,
                    $tag,
                    $email,
                    $scid
                ]);

                $request->sendResponse([
                    'id' => (int)$db->lastInsertId()
                ]);

            }
            else {

                $request->sendError(Errors::SUPERCELL_ERROR, 'The handshake has been revoked by supercell. The pin is probably incorrect (2).');

            }

        }
        else {

            $request->sendError(Errors::SUPERCELL_ERROR, 'The handshake has been revoked by supercell. The pin is probably incorrect (1).');

        }

    }

}