<?php declare(strict_types=1);

namespace Mage\Framework\Exceptions;

use ArrayObject;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Exception extends \Exception
{
    /**
     * @psalm-param string $message
     * @psalm-param int $code
     */
    public function __construct(protected $message, protected $code, protected mixed $error = null)
    {
        /** @psalm-var string $message */
        $message = trans($this->message);
        parent::__construct($message, $this->code, $this->getPrevious());
    }

    public function errors(): mixed
    {
        $errors = is_array($this->error) ? $this->error : [$this->error];
        $errors = array_filter($errors);
        return count($errors) < 1 ? new ArrayObject() : $errors;
    }

    public function render(): Response
    {
        /** @psalm-var Request $request */
        $request = request();
        if (!$request->wantsJson()) {
            abort($this->code, $this->message);
        }

        /** @psalm-var Response */
        return response()->json([
            'status' => Response::$statusTexts[$this->code],
            'message' => $this->message,
            'data' => $this->errors(),
        ], $this->code);
    }
}
