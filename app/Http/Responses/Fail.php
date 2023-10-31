<?php

namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Fail extends Base
{
    private string $errorMessage;

    /**
     * Конструктор класса Fail.
     *
     * @param string|null $errorMessage Сообщение об ошибке.
     * @param int|null $statusCode Код состояния HTTP.
     * @param Throwable|null $exception Исключение.
     */
    public function __construct(
        string $errorMessage = null,
        int $statusCode = null,
        Throwable $exception = null
    ) {
        if ($exception) {
            $this->errorMessage = $errorMessage ?? $exception->getMessage();
            $statusCode = $statusCode ?? ($exception->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $this->errorMessage = $errorMessage ?? 'Ошибка в теле запроса';
            $statusCode = $statusCode ?? Response::HTTP_BAD_REQUEST;
        }

        parent::__construct(null, $statusCode);
    }

    /**
     * Формирование содержимого ответа с текстом ошибки.
     *
     * @return array|null
     */
    protected function makeResponseData(): ?array
    {
        return [
            'message' => $this->errorMessage,
        ];
    }
}
