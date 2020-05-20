<?php

namespace Src\Helpers;

class Helper
{

    public static function badRequest($string)
    {

        $error = array(
            'error' => utf8_encode($string)
        );
        http_response_code(400);
        echo json_encode($error);
        exit;
    }

    public static function checkToken()
    {

        $header = (getallheaders());
        if (isset($header['Authorization'])) {
            $token= explode('Bearer ', $header['Authorization']);
            return $token[1];
        } else {
            $error = array(
                'error' => 'not find Token'
            );
            http_response_code(401);
            echo json_encode($error);
            exit;
        }
    }
    public static function tokenError(){
        $error=array(
            'error'=>'invalid Token');
            http_response_code(401);
            echo json_encode($error);
            exit;
    }

    public static function getBody(){

        $entityBody = file_get_contents('php://input');

        return json_decode($entityBody);
    }

    public static function validate($var){
        if(isset($var)){
        }else{
            Helper::badRequest('Json Invalid');
            
        }
    }

    public static function validateOptional($var){
        if($var == 0 ){

            return  0;
        }else 

        return $var;
    }
}
