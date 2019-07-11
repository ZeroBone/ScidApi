<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/autoload.php';

use Api\Config\Config;
use Api\Error\Errors;
use Api\Request\ApiRequest;
use Api\Utils\Utils;

$request = new ApiRequest('loginStart');

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
        'key' => 'email',
        'type' => 'string',
        'maxlength' => 30,
        'minlength' => 6,
        'preparer' => function ($value) {

            return trim($value);

        },
        'validator' => function ($value) {

            return Utils::isValidEmail($value);

        }
    ]
]);

$parameters = $request->validate();

if ($parameters) {

    $db = ApiRequest::getDatabaseConnection();

    $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `players` WHERE `email` = ? LIMIT 1;');

    $query->execute([
        $parameters['email']
    ]);

    $playersWithThisEmail = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

    if ($playersWithThisEmail === 0) {

        unset($playersWithThisEmail);

        $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `playersPending` WHERE `idType` = ? AND `id` = ? LIMIT 1;');

        $query->execute([
            $parameters['idType'],
            $parameters['id']
        ]);

        if ((int)$query->fetch(PDO::FETCH_ASSOC)['count'] === 0) {

            unset($query);

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, 'https://ingame.id.supercell.com/api/account/login');

            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

            curl_setopt($curl, CURLOPT_POST, true);

            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
                'email' => $parameters['email'],
                'lang' => 'ru',
                'game' => 'scroll',
                'env' => 'prod'
            ]));

            $scApiResult = json_decode(curl_exec($curl), true);

            curl_close($curl);

            unset($curl);

            if ($scApiResult && isset($scApiResult['ok']) && $scApiResult['ok'] === true) {

                $query = $db->prepare('INSERT INTO `playersPending` (`idType`, `id`, `email`) VALUES (?,?,?);');

                $query->execute([
                    $parameters['idType'],
                    $parameters['id'],
                    $parameters['email']
                ]);

                $request->sendResponse([
                    'recorded' => true
                ]);

            }
            else {

                $request->sendError(Errors::SUPERCELL_ERROR, 'Supercell did not accept the handshake.');

            }

        }
        else {

            $request->sendError(Errors::LOGIN_ALREADY_PENDING, 'The login process for this account has already begun. Please confirm it or wait till it gets expired.');

        }

    }
    else {

        $request->sendError(Errors::ACCOUNT_ALREADY_BOUND, 'The account with this email already exists.');

    }

}