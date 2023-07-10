<?php

namespace App\Helpers;

class APIHelper
{
    public static $resKeys = [
        'msg' => 'message',
        'suc' => "success",
        'data' => 'data'
    ];

    public static function generateResponseArray($success, $message, $dataArray = [])
    {
        return [self::$resKeys['suc'] => $success, self::$resKeys['msg'] => $message, self::$resKeys['data'] => $dataArray];
    }

    
}
