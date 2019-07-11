<?php

namespace Api\Config;


class Config {

    const API_USERS = [
        [ // user id 1
            'token' => '<token_1>',
            'permissions' => [
                'loginStart' => true,
                'loginConfirm' => true
            ]
        ],
        [ // user id 2
            'token' => '<token_2>',
            'permissions' => [
                'loginStart' => true,
                'loginConfirm' => true
            ]
        ]
    ];

    const IDTYPE_MIN_VALUE = 1;

    const IDTYPE_MAX_VALUE = 2;

}