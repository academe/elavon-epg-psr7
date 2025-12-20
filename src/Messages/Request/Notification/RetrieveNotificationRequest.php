<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Notification;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Notification Request.
 *
 * Builds a PSR-7 request for retrieving a single notification (GET /notifications/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveNotificationRequest
{
    use HasPsr17Factories;

    /**
     * @param string $notificationId Notification ID to retrieve     *
     * @throws InvalidArgumentException When notification ID is empty
     */
    public function __construct(
        private readonly string $notificationId
    ) {
        if (empty($this->notificationId)) {
            throw new InvalidArgumentException('Notification ID cannot be empty');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided
        $requestFactory = $this->getRequestFactory();

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', '/notifications/' . $this->notificationId);
    }

    /**
     * Gets the notification ID being retrieved.
     *
     * @return string
     */
    public function getNotificationId(): string
    {
        return $this->notificationId;
    }
}
