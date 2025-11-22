<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HsmCard;

use Academe\Elavon\Epg\Psr7\Dtos\HsmCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create HsmCard Request.
 *
 * Builds a PSR-7 request for creating an HSM card (POST /hsm-cards).
 */
class CreateHsmCardRequest
{
    private readonly HsmCard $hsmCard;

    public function __construct(
        HsmCard|array $hsmCard,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->hsmCard = match (true) {
            $hsmCard instanceof HsmCard => $hsmCard,
            is_array($hsmCard) => HsmCard::fromData($hsmCard),
        };

        $this->validateRequest($this->hsmCard);
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = $this->hsmCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', '/hsm-cards')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    public function getHsmCard(): HsmCard
    {
        return $this->hsmCard;
    }

    private function validateRequest(HsmCard $hsmCard): void
    {
        if ($hsmCard->terminal === null) {
            throw new InvalidArgumentException('Terminal is required for creating an HSM card');
        }

        if ($hsmCard->accountEntryMode === null) {
            throw new InvalidArgumentException('Account entry mode is required for creating an HSM card');
        }
    }
}
