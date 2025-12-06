<?php

namespace App\Exceptions;

use App\Models\TelescopeReport;
use Open\RestAPI\Exceptions\ApiException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Throwable;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ApiException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
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

        $this->renderable(function (ApiException $e, $request) {
            return response()->json($e, 403);
        });

        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (\Exception $e) {
            if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->route('login');
            }
        });

        $this->renderable(function (InvalidSignatureException $e) {
            return response()->view('errors.link-expired', [], 403);
        });
    }

    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception) && config('services.sentry.enabled')) {
            app('sentry')->captureException($exception);
        }

        try {
            if ($this->shouldReport($exception)) {
                $message = $exception->getMessage();
                $context = [
                    'class' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];

                $existing = TelescopeReport::where('type', 'exception')
                    ->where('message', $message)
                    ->first();

                if ($existing) {
                    $existing->count = ($existing->count ?? 1) + 1;
                    $existing->last_seen_at = now();
                    $existing->save();
                } else {
                    TelescopeReport::create([
                        'type' => 'exception',
                        'level' => 'error',
                        'message' => $message,
                        'context' => $context,
                        'count' => 1,
                        'first_seen_at' => now(),
                        'last_seen_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }

        parent::report($exception);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Validation\ValidationException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => __('validation.givenDataInvalid'),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {

            return redirect(route('login'))->with('message', 'You page session expired. Please try again');
        }

        return parent::render($request, $exception);
    }

}
