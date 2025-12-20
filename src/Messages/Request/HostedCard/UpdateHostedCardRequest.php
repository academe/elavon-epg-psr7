<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class UpdateHostedCardRequest
{
    use HasPsr17Factories;

    private readonly HostedCard $hostedCard;

    public function __construct(
        private readonly string $hostedCardId,
        HostedCard|array $hostedCard
    ) {
        if (empty($this->hostedCardId)) {
            throw new InvalidArgumentException('HostedCard ID cannot be empty');
        }

        $this->hostedCard = match (true) {
            $hostedCard instanceof HostedCard => $hostedCard,
            is_array($hostedCard) => HostedCard::fromData($hostedCard),
        };
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->getRequestFactory();
        $streamFactory = $this->getStreamFactory();

        $data = $this->hostedCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', '/hosted-cards/' . $this->hostedCardId)
            ->withBody($streamFactory->createStream($json));
    }

    public function getHostedCardId(): string
    {
        return $this->hostedCardId;
    }

    public function getHostedCard(): HostedCard
    {
        return $this->hostedCard;
    }
}
