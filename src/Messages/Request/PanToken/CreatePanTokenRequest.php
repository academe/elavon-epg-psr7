<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PanToken;

use Academe\Elavon\Epg\Psr7\Dtos\PanToken;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreatePanTokenRequest
{
    use HasPsr17Factories;

    private readonly array $panTokens;

    public function __construct(
        array $panTokens
    ) {
        $this->panTokens = array_map(
            fn($token) => $token instanceof PanToken ? $token : PanToken::fromData($token),
            $panTokens
        );
    }

    public function build(): RequestInterface
    {

        $data = array_map(fn($token) => $token->toData(), $this->panTokens);
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/pan-tokens')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    public function getPanTokens(): array
    {
        return $this->panTokens;
    }
}
