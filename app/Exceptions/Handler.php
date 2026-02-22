<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
public function register()
{
    $this->renderable(function (Throwable $e, $request) {
        // استخرج كود الحالة (في بعض الحالات الاستثنائية يكون 500 افتراضياً)
        $status = method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : 500;

        // فقط لروابط admin/*
        // if ($request->is('admin/*')) {
        //     // إذا كان الخطأ 500 أو 400
        //     if (in_array($status, [500, 404], true)) {
        //         // اختر البلسيد المناسب
        //         $view = $status === 404
        //             ? 'admin-views.errors.404'
        //             : 'admin-views.errors.500';

        //         return response()->view($view, [], $status);
        //     }
        // }
    });
}
}
