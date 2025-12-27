<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PazePayment;

use Academe\Elavon\Epg\Psr7\Dtos\PazePayment;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class PazePaymentResponse
{
    use ParsesPsr7Response;

    public readonly ?PazePayment $pazePayment;

    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->pazePayment = PazePayment::fromData($data);
            $this->error = null;
        } else {
            $this->pazePayment = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
