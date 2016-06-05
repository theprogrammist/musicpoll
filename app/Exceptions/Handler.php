<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use ReflectionException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //catch unimplemented rest-api actions
        if ($e instanceof ReflectionException) {
            //url segments to get intended route / [0]=> string(3) "api" [1]=> string(2) "v1" [2]=> string(4) "vote" [3]=> string(6) "create"
            $arr = $request->segments();

            if($arr[0] == 'api' && $arr[1] == 'v1' && $arr[2] == 'vote') {
                switch($request->method()) {
                    case 'GET':
                        if($arr[3] == 'create' || is_numeric($arr[3])) {
                            return response()->json(['status'=>'error','message'=>'Method Not Allowed (unimplemented for the resource)'],405);
                        }
                        break;
                    case 'PUT':
                    case 'DELETE':
                    case 'PATCH':
                        return response()->json(['status'=>'error','message'=>'Method Not Allowed (unimplemented for the resource)'],405);
                        break;
                }
            }
        }
        return parent::render($request, $e);
    }
}
