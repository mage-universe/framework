<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Exceptions;

use ArrayObject;
use Mage\Framework\Auth\Exceptions\AuthException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Framework\Auth\Data\Exceptions\ErrorException;
use Tests\TestCase;

class ExceptionTest extends TestCase
{
    public function test_exception_render_throw_http_exception_correctly(): void
    {
        $this->expectException(HttpException::class);
        $exception = AuthException::invalidCredentials();
        $this->assertEquals(new ArrayObject(), $exception->errors());
        $exception->render();
    }

    public function test_when_json_request_exception_raised_as_json_response(): void
    {
        $request = request();
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');
        $exception = AuthException::invalidCredentials();
        $this->assertEquals(new ArrayObject(), $exception->errors());
        $response = $exception->render();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([
            'status' => 'Unauthorized',
            'message' => $exception->getMessage(),
            'data' => [],
        ], json_decode((string) $response->getContent(), true));
    }

    public function test_when_json_request_on_multiple_errors_exception(): void
    {
        $request = request();
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');
        $exception = ErrorException::multiple();
        $this->assertEquals(['error.1', 'error.2', 'error.3'], $exception->errors());
        $response = $exception->render();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals([
            'status' => 'Unprocessable Content',
            'message' => $exception->getMessage(),
            'data' => $exception->errors(),
        ], json_decode((string) $response->getContent(), true));
    }

    public function test_when_json_request_on_single_error_exception(): void
    {
        $request = request();
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');
        $exception = ErrorException::single();
        $this->assertEquals(['error.1'], $exception->errors());
        $response = $exception->render();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals([
            'status' => 'Unprocessable Content',
            'message' => $exception->getMessage(),
            'data' => $exception->errors(),
        ], json_decode((string) $response->getContent(), true));
    }
}
