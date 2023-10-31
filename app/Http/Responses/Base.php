<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Base implements Responsable
{
    /**
     * Конструктор класса BaseResponse.
     *
     * @param mixed $data Данные ответа.
     * @param int $statusCode Код состояния HTTP.
     */
    public function __construct(
        protected mixed $data = [],
        public int $statusCode = Response::HTTP_OK
    ) {
    }

    /**
     * Создание объекта Response в зависимости от типа возвращаемых данных.
     *
     * @param Request $request.
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json($this->makeResponseData(), $this->statusCode);
    }

    /**
     * Преобразование возвращаемых данных к массиву.
     *
     * @return array
     */
    protected function prepareData(): ?array
    {
        if ($this->data instanceof Arrayable) {
            return $this->data->toArray();
        }

        return $this->data;
    }

    /**
     * Формирование содержимого ответа.
     *
     * @return array|null
     */
    abstract protected function makeResponseData(): ?array;
}
