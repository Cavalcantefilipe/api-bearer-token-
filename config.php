<?php

    spl_autoload_register(function($nameClass){

        $filename = $nameClass.".php";

        if(file_exists($filename)){

            require_once($filename);
        }

    });