<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Concerns;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides PSR-7 response parsing, error handling, and common accessor methods.
 *
 * Classes using this trait can be instantiated either:
 * - From raw data: new Response($data, $statusCode)
 * - From PSR-7: Response::fromPsr7Response($psrResponse)
 *
 * The trait provides:
 * - Status code and success checking
 * - Error response handling
 * - Static factory for PSR-7 parsing
 */
trait ParsesPsr7Response
{
    public readonly int $statusCode;
    public readonly ?ErrorResponse $error;

    /**
     * Creates an instance from a PSR-7 response.
     *
     * Parses the response body as JSON and extracts the status code,
     * then delegates to the constructor.
     *
     * @throws InvalidArgumentException When JSON is invalid or not an object
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        $statusCode = $response->getStatusCode();
        $data = self::parsePsr7Body($response);

        return new static($data, $statusCode);
    }

    /**
     * Checks if the response was successful (2xx status code).
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Checks if the response has an error.
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Parses the JSON body from a PSR-7 response.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException When JSON is invalid or not an object
     */
    private static function parsePsr7Body(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            throw new InvalidArgumentException('Response body is empty');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException(
                'Failed to decode JSON response: ' . $e->getMessage(),
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Response body is not a JSON object');
        }

        // Check if it's an indexed array (JSON array) vs associative array (JSON object)
        if ($data === [] || array_keys($data) === range(0, count($data) - 1)) {
            throw new InvalidArgumentException('Response body is not a JSON object');
        }

        return $data;
    }

    /**
     * Parses error data into an ErrorResponse object.
     *
     * @param array<string, mixed> $data
     */
    private static function parseErrorData(array $data): ErrorResponse
    {
        return ErrorResponse::fromData($data);
    }
}
