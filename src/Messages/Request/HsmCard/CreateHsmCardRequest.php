<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HsmCard;

use Academe\Elavon\Epg\Psr7\Dtos\HsmCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create HsmCard Request.
 *
 * Builds a PSR-7 request for creating an HSM card (POST /hsm-cards).
 */
class CreateHsmCardRequest
{
    use HasPsr17Factories;

    private readonly HsmCard $hsmCard;

    public function __construct(
        HsmCard|array $hsmCard
    ) {
        $this->hsmCard = match (true) {
            $hsmCard instanceof HsmCard => $hsmCard,
            is_array($hsmCard) => HsmCard::fromData($hsmCard),
        };

        $this->validateRequest($this->hsmCard);
    }

    public function build(): RequestInterface
    {

        $data = $this->hsmCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/hsm-cards')
            ->withBody($this->getStreamFactory()->createStream($json));
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
