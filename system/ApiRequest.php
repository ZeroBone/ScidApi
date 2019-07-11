<?php

namespace Api\Request;

use Api\Config\Config;
use Api\Error\Errors;
use Api\Utils\Utils;
use PDO;

class ApiRequest {

    private $methodName;

    private $requiredParams;

    public $userId = null;

    public $userToken = null;

    public function __construct($methodName) {

        $this->methodName = $methodName;

    }

    public function setRequiredParameters($params) {

        $this->requiredParams = $params;

    }

    public function validate() {

        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {

            Errors::returnError(Errors::PARAMETERS_INVALID_OR_UNDEFINED, Errors::DESCIPRION_CREDENTIALS_INVALID);

        }

        $authorizationHeaderValue = (string)$_SERVER['HTTP_AUTHORIZATION'];

        if (strlen($authorizationHeaderValue) > 500) {

            Errors::returnError(Errors::CREDENTIALS_INVALID, Errors::DESCIPRION_CREDENTIALS_INVALID);

        }

        if (Utils::stringStartsWith($authorizationHeaderValue, 'Bearer ')) {

            $authorizationHeaderValue = str_replace('Bearer ', '', $authorizationHeaderValue);

        }

        $rawToken = explode(':', $authorizationHeaderValue,2);

        if (count($rawToken) !== 2) {

            Errors::returnError(Errors::CREDENTIALS_INVALID, Errors::DESCIPRION_CREDENTIALS_INVALID);

        }

        $userId = (int)$rawToken[0];

        $userToken = isset($rawToken[1]) ? (string)$rawToken[1] : '';

        unset($rawToken);

        if ($userId <= 0 || $userToken === '') {

            Errors::returnError(Errors::CREDENTIALS_INVALID, Errors::DESCIPRION_CREDENTIALS_INVALID);

        }

        if (!isset(Config::API_USERS[$userId - 1])) {

            Errors::returnError(Errors::CREDENTIALS_INVALID, Errors::DESCIPRION_CREDENTIALS_INVALID);

        }

        $user = Config::API_USERS[$userId - 1];

        if ($user['token'] === $userToken) {

            // token valid, check permissions

            if (isset($user['permissions'][$this->methodName]) && $user['permissions'][$this->methodName] === true) {

                // valid credentials and permissions

                $params = [];

                foreach ($this->requiredParams as $requiredParam) {

                    $currentParam = $this->getParamValue($requiredParam);

                    if ($currentParam === null) {

                        Errors::returnError(
                            Errors::PARAMETERS_INVALID_OR_UNDEFINED,
                            'Parameter "' . $requiredParam['key'] . '" is invalid or undefined.'
                        );

                        return false;

                    }

                    $params[$requiredParam['key']] = $currentParam;

                }

                $this->userToken = $user['token'];

                $this->userId = $userId;

                return $params;

            }
            else {

                Errors::returnError(Errors::NOT_ENOUPH_PERMISSIONS, Errors::DESCIPRION_NOT_ENOUPH_PERMISSIONS);

            }


        }
        else {

            Errors::returnError(Errors::CREDENTIALS_INVALID, Errors::DESCIPRION_CREDENTIALS_INVALID);

        }

        return false;

    }

    private function getParamValue($description) {

        if (!isset($_REQUEST[$description['key']])) {

            return null;

        }

        $value = (string)$_REQUEST[$description['key']];

        if ($description['type'] === 'string') {

            $value = urldecode($value);

            if (isset($description['maxlength']) && Utils::getStringLength($value) > $description['maxlength']) {

                return null;

            }

            if (isset($description['minlength']) && Utils::getStringLength($value) < $description['minlength']) {

                return null;

            }

        }
        elseif ($description['type'] === 'integer') {

            $value = (int)$value;

            if (isset($description['min']) && $value < $description['min']) {

                return null;

            }

            if (isset($description['max']) && $value > $description['max']) {

                return null;

            }

        }
        elseif ($description['type'] === 'boolean') {

            $value = $value === '1' ? true : false;

        }
        else {

            return null;

        }

        if (isset($description['preparer']) && is_callable($description['preparer'])) {

            $value = call_user_func($description['preparer'], $value); // not sure this is the right php api

        }

        if (isset($description['validator']) && is_callable($description['validator'])) {

            if (!call_user_func($description['validator'], $value)) {

                return null;

            }

        }

        return $value;

    }

    public function sendResponse($data) {

        header('Content-Type: application/json');

        echo json_encode([
            'ok' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);

        exit(0);

    }

    public function sendError($errorCode, $errorDescription) {

        Errors::returnError($errorCode, $errorDescription);

    }

    public static function getDatabaseConnection() {

        return new PDO(
            'mysql:host=localhost:<db_port>;dbname=scidapi;charset=utf8',
            '<db_user>',
            '<db_pass>'
        );

    }

}