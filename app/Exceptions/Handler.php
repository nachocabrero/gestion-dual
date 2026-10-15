<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Capturar Error 413 (Payload Too Large) cuando el archivo supera el límite de subida
        $this->renderable(function (PostTooLargeException $e, $request) {
            $errorMessage = 'Error 413 (Archivo demasiado grande): El archivo seleccionado supera el límite máximo permitido por el servidor o su formato no es el adecuado. Tamaño máximo permitido: 2 MB para foto de perfil (avatar) y 5 MB para curriculum/documentos (formatos válidos: JPG, PNG, WebP, PDF).';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $errorMessage,
                    'error_code' => 413,
                    'error' => 'Payload Too Large',
                    'details' => [
                        'max_avatar_size' => '2 MB (2048 KB)',
                        'max_cv_size' => '5 MB (5120 KB)',
                        'allowed_formats_avatar' => ['JPG', 'JPEG', 'PNG', 'WebP'],
                        'allowed_formats_cv' => ['PDF', 'JPG', 'JPEG', 'PNG', 'WebP'],
                    ],
                ], 413);
            }

            return redirect()->back()->withInput($request->except(['avatar', 'cv']))->withErrors([
                'avatar' => 'El archivo supera el tamaño máximo permitido de 2 MB o el formato no es válido (Error 413).',
                'cv' => 'El documento supera el tamaño máximo permitido de 5 MB o el formato no es válido (Error 413).',
                'file_upload_error' => $errorMessage,
            ])->with('error_413', $errorMessage);
        });

        $this->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 413) {
                $errorMessage = 'Error 413 (Payload Too Large): El archivo supera el tamaño máximo de subida permitido por el servidor o el formato no es el adecuado.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => $errorMessage,
                        'error_code' => 413,
                        'error' => 'Payload Too Large',
                    ], 413);
                }

                return redirect()->back()->withInput($request->except(['avatar', 'cv']))->withErrors([
                    'file_upload_error' => $errorMessage,
                ])->with('error_413', $errorMessage);
            }
        });
    }
}
