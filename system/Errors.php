<?php

namespace Api\Error;


class Errors {

    const UNKNOWN = 1;

    const PARAMETERS_INVALID_OR_UNDEFINED = 2;

    const CREDENTIALS_INVALID = 3;

    const NOT_ENOUPH_PERMISSIONS = 4;

    const SUPERCELL_ERROR = 5;

    const LOGIN_ALREADY_PENDING = 6;

    const HANDSHAKE_NOT_FOUND = 7;

    const ACCOUNT_ALREADY_BOUND = 8;

    const DESCRIPRION_DEFAULT = 'There is no description. Please contact the administrator for more information about this error.';

    const DESCIPRION_CREDENTIALS_INVALID = 'Access denied: Authentication credentials are invalid or undefined!';

    const DESCIPRION_NOT_ENOUPH_PERMISSIONS = 'Access denied: You do not have enouph permission to execute this method!';

    public static function returnError($errorCode = self::UNKNOWN, $errorDescription=self::DESCRIPRION_DEFAULT) {

        header('Content-Type: application/json');

        echo json_encode([
            'ok' => false,
            'error' => [
                'code' => $errorCode,
                'description' => $errorDescription
            ]
        ], JSON_UNESCAPED_UNICODE);

        exit(0);

    }

}