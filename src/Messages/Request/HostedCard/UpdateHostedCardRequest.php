<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class UpdateHostedCardRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $hostedCardId Hosted card ID to update
     * @param HostedCard $hostedCard Hosted card update data
     *
     * @throws InvalidArgumentException When hosted card ID is empty
     */
    public function __construct(
        public readonly string $hostedCardId,
        public readonly HostedCard $hostedCard,
    ) {
        if (empty($this->hostedCardId)) {
            throw new InvalidArgumentException('HostedCard ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{hostedCardId: string, hostedCard: HostedCard|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('hostedCardId', $data)) {
            throw new InvalidArgumentException("Missing required key 'hostedCardId' in data");
        }

        if (! array_key_exists('hostedCard', $data)) {
            throw new InvalidArgumentException("Missing required key 'hostedCard' in data");
        }

        $hostedCard = $data['hostedCard'] instanceof HostedCard
            ? $data['hostedCard']
            : HostedCard::fromData($data['hostedCard']);

        return new static($data['hostedCardId'], $hostedCard);
    }

    public function build(): RequestInterface
    {
        $json = json_encode($this->hostedCard->toData(), JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/hosted-cards/' . $this->hostedCardId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
