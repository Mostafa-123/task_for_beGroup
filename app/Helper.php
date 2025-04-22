<?php

namespace App;

use Illuminate\Auth\AuthenticationException;

if (!function_exists('apiResponse')) {
    function apiResponse($status = null, $data = null, $message = null)
    {
        $array = [
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
        return response($array, $status);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'Y-m-d')
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}
