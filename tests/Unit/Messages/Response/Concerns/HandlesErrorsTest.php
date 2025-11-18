<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Concerns;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use PHPUnit\Framework\TestCase;

/**
 * Tests for HandlesErrors trait.
 */
class HandlesErrorsTest extends TestCase
{
    public function test_hasError_withError_returnsTrue(): void
    {
        // Arrange
        $mock = new class {
            use HandlesErrors;

            public function __construct()
            {
                $this->error = ErrorResponse::fromData([
                    'status' => 401,
                    'failures' => [
                        ['code' => 'unauthorized', 'description' => 'Invalid API key'],
                    ],
                ]);
            }

            public function getStatusCode(): int
            {
                return 401;
            }

            private function parseJsonBody(): array
            {
                return [];
            }
        };

        // Act & Assert
        $this->assertTrue($mock->hasError());
        $this->assertInstanceOf(ErrorResponse::class, $mock->getError());
        $this->assertFalse($mock->isSuccessful());
    }

    public function test_hasError_withoutError_returnsFalse(): void
    {
        // Arrange
        $mock = new class {
            use HandlesErrors;

            public function __construct()
            {
                $this->error = null;
            }

            public function getStatusCode(): int
            {
                return 200;
            }

            private function parseJsonBody(): array
            {
                return [];
            }
        };

        // Act & Assert
        $this->assertFalse($mock->hasError());
        $this->assertNull($mock->getError());
        $this->assertTrue($mock->isSuccessful());
    }

    public function test_isSuccessful_with200StatusCode_returnsTrue(): void
    {
        // Arrange
        $mock = new class {
            use HandlesErrors;

            public function __construct()
            {
                $this->error = null;
            }

            public function getStatusCode(): int
            {
                return 200;
            }

            private function parseJsonBody(): array
            {
                return [];
            }
        };

        // Act & Assert
        $this->assertTrue($mock->isSuccessful());
    }

    public function test_isSuccessful_with299StatusCode_returnsTrue(): void
    {
        // Arrange
        $mock = new class {
            use HandlesErrors;

            public function __construct()
            {
                $this->error = null;
            }

            public function getStatusCode(): int
            {
                return 299;
            }

            private function parseJsonBody(): array
            {
                return [];
            }
        };

        // Act & Assert
        $this->assertTrue($mock->isSuccessful());
    }

    public function test_isSuccessful_with400StatusCode_returnsFalse(): void
    {
        // Arrange
        $mock = new class {
            use HandlesErrors;

            public function __construct()
            {
                $this->error = null;
            }

            public function getStatusCode(): int
            {
                return 400;
            }

            private function parseJsonBody(): array
            {
                return [];
            }
        };

        // Act & Assert
        $this->assertFalse($mock->isSuccessful());
    }

    public function test_isSuccessful_with500StatusCode_returnsFalse(): void
    {
        // Arrange
        $mock = new class {
            use HandlesErrors;

            public function __construct()
            {
                $this->error = null;
            }

            public function getStatusCode(): int
            {
                return 500;
            }

            private function parseJsonBody(): array
            {
                return [];
            }
        };

        // Act & Assert
        $this->assertFalse($mock->isSuccessful());
    }
}
