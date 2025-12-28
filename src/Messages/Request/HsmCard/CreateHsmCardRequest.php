<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HsmCard;

use Academe\Elavon\Epg\Psr7\Dtos\HsmCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create HsmCard Request.
 *
 * Builds a PSR-7 request for creating an HSM card (POST /hsm-cards).
 */
class CreateHsmCardRequest
{
    use HasPsr17Factories;

    /**
     * @param HsmCard $hsmCard HSM card data
     *
     * @throws InvalidArgumentException When required fields are missing
     */
    public function __construct(
        public readonly HsmCard $hsmCard,
    ) {
        $this->validateRequest($this->hsmCard);
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{hsmCard: HsmCard|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('hsmCard', $data)) {
            throw new InvalidArgumentException("Missing required key 'hsmCard' in data");
        }

        $hsmCard = $data['hsmCard'] instanceof HsmCard
            ? $data['hsmCard']
            : HsmCard::fromData($data['hsmCard']);

        return new static($hsmCard);
    }

    public function build(): RequestInterface
    {
        $json = json_encode($this->hsmCard->toData(), JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/hsm-cards')
            ->withBody($this->getStreamFactory()->createStream($json));
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
