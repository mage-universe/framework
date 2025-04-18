<?php declare(strict_types=1);

namespace Mage\Framework\Http\Requests;

use Illuminate\Validation\ValidationException as LaravelValidationException;
use Symfony\Component\HttpFoundation\Response;

/** @psalm-suppress PropertyNotSetInConstructor */
class ValidationException extends LaravelValidationException
{
    /** @psalm-var int */
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected static function summarize($validator): string
    {
        /** @psalm-var string */
        return trans('validation.summary');
    }

    public function render(): ?Response
    {
        if (request()->wantsJson()) {
            /** @psalm-var Response */
            return response()->json([
                'status' => Response::$statusTexts[$this->code],
                'message' => $this->message,
                'data' => $this->errors(),
            ], $this->code);
        }
        return null;
    }
}
