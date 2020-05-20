<?php
session_start();
require_once("config.php");

use Src\Model\User;
use Src\Helpers\Helper;
use Src\Model\Drink;

$url = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$request = explode("/", $url);

//MY Routes
if ($method == 'GET') {
    if ($request[1] == 'users' && isset($request[2])) {

        $token = Helper::checkToken();

        if (User::checkLogin($token)) {
            $user = User::getFromSession();
            $user->getUser((int) $request[2]);
        } else {
            Helper::tokenError();
        }
    } elseif ($request[1] == 'users') {

        $token = Helper::checkToken();

        if (User::checkLogin($token)) {
            $user = User::getFromSession();
            $user->getUsers();
        } else {
            Helper::tokenError();
        }
    } elseif ($request[1] == 'drinks' && isset($request[2])) {
        $token = Helper::checkToken();

        if (User::checkLogin($token)) {
            $drink = new Drink;
            $drink->getHistory((int) $request[2]);
        } else {
            Helper::tokenError();
        }
    } elseif ($request[1] == 'ranking') {
        $token = Helper::checkToken();

        if (User::checkLogin($token)) {
            $drink = new Drink;
            $drink->getRanking();
        } else {
            Helper::tokenError();
        }
    }else {
        http_response_code(501);
    }
} elseif ($method == 'POST') {

    if ($request[1] == 'users' && isset($request[2]) && isset($request[3])) {

        if ($request[3] != 'drink') {
            http_response_code(501);
        }

        $token = Helper::checkToken();

        if (User::checkLogin($token)) {
            $body = Helper::getBody();
            $ml = isset($body->drink_ml) ? $body->drink_ml : 0;
            $drink = new Drink;
            $drink->drinkCreate($ml, (int) $request[2]);
            $user = new User;
            $user->getUser((int) $request[2]);
        } else {
            Helper::tokenError();
        }
    } elseif ($request[1] == 'login') {

        $body = Helper::getBody();
        Helper::validate($body->email);
        Helper::validate($body->password);
        User::login($body->email, $body->password);
    } elseif ($request[1] == 'users') {

        $body = Helper::getBody();
        Helper::validate($body->email);
        Helper::validate($body->name);
        Helper::validate($body->password);
        $user = new User();

        $user->setData($body);

        $user->createUser();
    } else {
        http_response_code(501);
    }
} elseif ($method == 'PUT') {
    if ($request[1] == 'users' && isset($request[2])) {
        $token = Helper::checkToken();

        if (User::checkLogin($token)) {
            $user = User::getFromSession();
            $body = Helper::getBody();
            $changes = array(
                "email" => isset($body->email) ? $body->email : 0,
                "name" => isset($body->name) ? $body->name : 0,
                "password" => isset($body->password) ? $body->password : 0
            );
            $user = new User();
            $user->setData($changes);

            $user->updateUser($request[2]);
        } else {
            Helper::tokenError();
        }
    } else {
        http_response_code(501);
    }
} elseif ($method == 'DELETE') {

    if ($request[1] == 'users' && isset($request[2])) {

        $token = Helper::checkToken();

        if (User::checkLogin($token)) {
            $user = new User;

            $user->deleteUser($request[2]);
            $user->logout();
        } else {
            Helper::tokenError();
        }
    } else {
        http_response_code(501);
    }
} else {
    http_response_code(501);
}
