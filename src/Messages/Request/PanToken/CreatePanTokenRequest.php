<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PanToken;

use Academe\Elavon\Epg\Psr7\Dtos\PanToken;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CreatePanTokenRequest
{
    private readonly array $panTokens;

    public function __construct(
        array $panTokens,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->panTokens = array_map(
            fn($token) => $token instanceof PanToken ? $token : PanToken::fromData($token),
            $panTokens
        );
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = array_map(fn($token) => $token->toData(), $this->panTokens);
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', '/pan-tokens')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    public function getPanTokens(): array
    {
        return $this->panTokens;
    }
}
