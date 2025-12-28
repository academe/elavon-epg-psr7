<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PanToken;

use Academe\Elavon\Epg\Psr7\Dtos\PanToken;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreatePanTokenRequest
{
    use HasPsr17Factories;

    /** @var PanToken[] */
    public readonly array $panTokens;

    /**
     * @param PanToken[] $panTokens
     */
    public function __construct(
        array $panTokens
    ) {
        $this->panTokens = array_map(
            fn($token) => $token instanceof PanToken ? $token : PanToken::fromData($token),
            $panTokens
        );
    }

    /**
     * @param array{panTokens: array<PanToken|array<string, mixed>>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('panTokens', $data)) {
            throw new InvalidArgumentException("Missing required key 'panTokens' in data");
        }

        return new static($data['panTokens']);
    }

    public function build(): RequestInterface
    {
        $data = array_map(fn($token) => $token->toData(), $this->panTokens);
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/pan-tokens')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
