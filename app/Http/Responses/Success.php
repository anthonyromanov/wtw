<?php

namespace App\Http\Responses;

class Success extends Base
{
    /**
     * Формирование содержимого успешного ответа.
     *
     * @return array|null
     */
    protected function makeResponseData(): ?array
    {
        return [
            'data' => $this->prepareData(),
        ];
    }
}
