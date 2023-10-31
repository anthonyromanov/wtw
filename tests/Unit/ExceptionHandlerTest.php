<?php

namespace Tests\Unit;

use App\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    public function testPrepareJsonResponse()
    {
        $handler = new Handler($this->app);
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept', 'application/json');
        $exception = new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Проверка. Тестовое исключение');

        $response = $handler->render($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertEquals('Проверка. Тестовое исключение', $response->getData(true)['message']);
    }
}
