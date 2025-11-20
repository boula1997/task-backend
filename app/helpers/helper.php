<?php

function successResponse($data = [], $message = "success", $status = 200)
{
    return response()->json(
        [
            "status" => $status,
            "message" => $message,
            "data" => $data,
        ],
        $status
    );
}


function failedResponse($data = [], $message = "error", $status = 400)
{
    return response()->json(
        [
            "status" => $status,
            "message" => $message,
            "data" => $data,
        ],
        $status
    );
}