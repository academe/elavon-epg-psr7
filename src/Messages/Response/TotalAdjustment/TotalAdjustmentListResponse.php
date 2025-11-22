<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Dtos\TotalAdjustment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class TotalAdjustmentListResponse
{
    use HandlesErrors;

    private readonly ?array $totalAdjustments;
    private readonly ?string $nextPage;
    private readonly ?string $firstPage;

    public function __construct(private readonly ResponseInterface $response)
    {
        if ($this->isSuccessful()) {
            $data = $this->parseSuccessResponse();
            $this->totalAdjustments = $data['items'];
            $this->nextPage = $data['next'];
            $this->firstPage = $data['first'];
            $this->error = null;
        } else {
            $this->totalAdjustments = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    public function getTotalAdjustments(): ?array
    {
        return $this->totalAdjustments;
    }

    public function getNext(): ?string
    {
        return $this->nextPage;
    }

    public function getFirst(): ?string
    {
        return $this->firstPage;
    }

    public function hasMorePages(): bool
    {
        return $this->nextPage !== null;
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    private function parseSuccessResponse(): array
    {
        $data = $this->parseJsonBody();

        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        $adjustments = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }
            $adjustments[] = TotalAdjustment::fromData($itemData);
        }

        return [
            'items' => $adjustments,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }
}
