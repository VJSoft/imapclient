<?php

namespace Vjsoft\Imapclient\Exceptions;

use \Exception;

class ConnectionFailedException extends Exception
{
    public
    public function render($request, Exception $exception)
    {
        dd($request);
        return response()->json(['',$message=>$exception],404);

        //return parent::render($request, $exception);
    }
}
