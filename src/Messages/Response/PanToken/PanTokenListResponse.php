<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PanToken;

use Academe\Elavon\Epg\Psr7\Dtos\PanToken;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class PanTokenListResponse
{
    use HandlesErrors;

    private readonly ?array $panTokens;

    public function __construct(private readonly ResponseInterface $response)
    {
        if ($this->isSuccessful()) {
            $this->panTokens = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->panTokens = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    public function getPanTokens(): ?array
    {
        return $this->panTokens;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): array
    {
        $data = $this->parseJsonBody();

        if (!is_array($data)) {
            throw new InvalidArgumentException('Response must be an array');
        }

        return array_map(fn($item) => PanToken::fromData($item), $data);
    }

    private function parseJsonBody(): array
    {
        $body = (string) $this->response->getBody();

        if ($body === '') {
            throw new InvalidArgumentException('Response body is empty');
        }

        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException(
                'Failed to decode JSON response: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
