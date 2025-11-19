<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class UpdateHostedCardRequest
{
    private readonly HostedCard $hostedCard;

    public function __construct(
        private readonly string $hostedCardId,
        HostedCard|array $hostedCard,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = $this->hostedCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/hosted-cards/' . $this->hostedCardId)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
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
