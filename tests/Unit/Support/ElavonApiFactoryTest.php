<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Support;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use Academe\Elavon\Epg\Psr7\Support\Request;
use PHPUnit\Framework\TestCase;

class ElavonApiFactoryTest extends TestCase
{
    public function test_apply_addsAcceptHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertTrue($result->hasHeader('Accept'));
        $this->assertSame('application/json;charset=UTF-8', $result->getHeaderLine('Accept'));
    }

    public function test_apply_addsContentTypeHeaderWhenBodyPresent(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions', [], '{"test": "data"}');
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertTrue($result->hasHeader('Content-Type'));
        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
    }

    public function test_apply_doesNotAddContentTypeWhenNoBody(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertFalse($result->hasHeader('Content-Type'));
    }

    public function test_apply_addsDefaultApiVersionHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertTrue($result->hasHeader('Accept-Version'));
        $this->assertSame('1', $result->getHeaderLine('Accept-Version'));
        $this->assertSame('1', $factory->getApiVersion());
    }

    public function test_configure_withCustomApiVersion(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');
        $factory = ElavonApiFactory::configure('2');

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertSame('2', $result->getHeaderLine('Accept-Version'));
        $this->assertSame('2', $factory->getApiVersion());
    }

    public function test_apply_preservesExistingAcceptHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test', [
            'Accept' => 'text/html',
        ]);
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert - Should preserve existing Accept header
        $this->assertSame('text/html', $result->getHeaderLine('Accept'));
    }

    public function test_apply_preservesExistingContentTypeHeader(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/test', [
            'Content-Type' => 'application/xml',
        ], '<xml></xml>');
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert - Should preserve existing Content-Type header
        $this->assertSame('application/xml', $result->getHeaderLine('Content-Type'));
    }

    public function test_withRegion_setsRegion(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('sandbox');

        // Assert
        $this->assertSame('eu', $factory->getRegion());
    }

    public function test_withRegion_throwsOnInvalidRegion(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown region 'invalid'");

        // Act
        ElavonApiFactory::configure()->withRegion('invalid');
    }

    public function test_withEnvironment_setsEnvironment(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('sandbox');

        // Assert
        $this->assertSame('uat', $factory->getEnvironment());
    }

    public function test_withEnvironment_throwsOnInvalidEnvironment(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown environment 'invalid'");

        // Act
        ElavonApiFactory::configure()->withEnvironment('invalid');
    }

    public function test_withEnvironment_normalizesSandboxToUat(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()->withEnvironment('sandbox');

        // Assert
        $this->assertSame('uat', $factory->getEnvironment());
    }

    public function test_withEnvironment_normalizesLiveToProduction(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()->withEnvironment('live');

        // Assert
        $this->assertSame('production', $factory->getEnvironment());
    }

    public function test_withEnvironment_normalizesTestToUat(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()->withEnvironment('test');

        // Assert
        $this->assertSame('uat', $factory->getEnvironment());
    }

    public function test_getBaseUri_returnsEuSandboxUrl(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('sandbox');

        // Assert
        $this->assertSame('https://uat.api.converge.eu.elavonaws.com', $factory->getBaseUri());
    }

    public function test_getBaseUri_returnsEuProductionUrl(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('live');

        // Assert
        $this->assertSame('https://api.eu.convergepay.com', $factory->getBaseUri());
    }

    public function test_getBaseUri_returnsUsSandboxUrl(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()
            ->withRegion('us')
            ->withEnvironment('sandbox');

        // Assert
        $this->assertSame('https://uat.api.convergepay.com', $factory->getBaseUri());
    }

    public function test_getBaseUri_returnsUsProductionUrl(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()
            ->withRegion('us')
            ->withEnvironment('live');

        // Assert
        $this->assertSame('https://api.convergepay.com', $factory->getBaseUri());
    }

    public function test_withBaseUri_overridesRegionAndEnvironment(): void
    {
        // Act
        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('sandbox')
            ->withBaseUri('https://custom.api.example.com');

        // Assert
        $this->assertSame('https://custom.api.example.com', $factory->getBaseUri());
    }

    public function test_apply_replacesBaseUri(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions/123');
        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('sandbox');

        // Act
        $result = $factory->apply($request);

        // Assert
        $uri = $result->getUri();
        $this->assertSame('https://uat.api.converge.eu.elavonaws.com/transactions/123', (string) $uri);
    }

    public function test_apply_preservesPathAndQuery(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions?filter=state_eq_captured');
        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('sandbox');

        // Act
        $result = $factory->apply($request);

        // Assert
        $uri = $result->getUri();
        $this->assertSame('/transactions', $uri->getPath());
        $this->assertSame('filter=state_eq_captured', $uri->getQuery());
    }

    public function test_withAuthentication_addsAuthorizationHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $factory = ElavonApiFactory::configure()
            ->withAuthentication('merchant123', 'secret-key');

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertTrue($result->hasHeader('Authorization'));
        $authHeader = $result->getHeaderLine('Authorization');
        $this->assertStringStartsWith('Basic ', $authHeader);
    }

    public function test_withAuthentication_encodesCredentialsCorrectly(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions');
        $username = 'testmerchant';
        $password = 'testkey123';
        $factory = ElavonApiFactory::configure()
            ->withAuthentication($username, $password);

        // Act
        $result = $factory->apply($request);

        // Assert
        $expected = 'Basic ' . base64_encode("{$username}:{$password}");
        $this->assertSame($expected, $result->getHeaderLine('Authorization'));
    }

    public function test_getUsername_returnsUsername(): void
    {
        // Arrange
        $username = 'mymerchant';
        $factory = ElavonApiFactory::configure()
            ->withAuthentication($username, 'password');

        // Assert
        $this->assertSame($username, $factory->getUsername());
    }

    public function test_getUsername_returnsNullWhenNotAuthenticated(): void
    {
        // Arrange
        $factory = ElavonApiFactory::configure();

        // Assert
        $this->assertNull($factory->getUsername());
    }

    public function test_apply_preservesOriginalRequestMethod(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions');
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertSame('POST', $result->getMethod());
    }

    public function test_apply_preservesOriginalRequestBody(): void
    {
        // Arrange
        $body = '{"amount": "99.99"}';
        $request = new Request('POST', 'https://api.example.com/test', [], $body);
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert
        $this->assertSame($body, (string) $result->getBody());
    }

    public function test_fluentInterface_withAllOptions(): void
    {
        // Arrange
        $request = new Request('POST', 'https://placeholder.example.com/transactions', [], '{"test": "data"}');
        $factory = ElavonApiFactory::configure('2')
            ->withRegion('eu')
            ->withEnvironment('sandbox')
            ->withAuthentication('merchant', 'apikey');

        // Act
        $result = $factory->apply($request);

        // Assert - All headers should be present
        $this->assertTrue($result->hasHeader('Accept'));
        $this->assertTrue($result->hasHeader('Content-Type'));
        $this->assertTrue($result->hasHeader('Accept-Version'));
        $this->assertTrue($result->hasHeader('Authorization'));
        $this->assertSame('2', $result->getHeaderLine('Accept-Version'));
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $result->getUri());
    }

    public function test_authorizationHeader_isValidBase64(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $username = 'merchant';
        $password = 'password';
        $factory = ElavonApiFactory::configure()
            ->withAuthentication($username, $password);

        // Act
        $result = $factory->apply($request);
        $authHeader = $result->getHeaderLine('Authorization');

        // Assert
        $this->assertStringStartsWith('Basic ', $authHeader);
        $encodedPart = substr($authHeader, 6); // Remove 'Basic ' prefix
        $decoded = base64_decode($encodedPart, true);
        $this->assertNotFalse($decoded, 'Authorization header should contain valid base64');
        $this->assertSame("{$username}:{$password}", $decoded);
    }

    public function test_apply_toMultipleRequests(): void
    {
        // Arrange
        $request1 = new Request('POST', 'https://placeholder.example.com/transactions', [], '{"test": "1"}');
        $request2 = new Request('GET', 'https://placeholder.example.com/payments');
        $request3 = new Request('PUT', 'https://placeholder.example.com/refunds', [], '{"test": "3"}');

        $factory = ElavonApiFactory::configure('2')
            ->withRegion('eu')
            ->withEnvironment('sandbox')
            ->withAuthentication('merchant', 'apikey');

        // Act
        $result1 = $factory->apply($request1);
        $result2 = $factory->apply($request2);
        $result3 = $factory->apply($request3);

        // Assert - All should have same configuration
        $this->assertSame('2', $result1->getHeaderLine('Accept-Version'));
        $this->assertSame('2', $result2->getHeaderLine('Accept-Version'));
        $this->assertSame('2', $result3->getHeaderLine('Accept-Version'));

        $this->assertTrue($result1->hasHeader('Authorization'));
        $this->assertTrue($result2->hasHeader('Authorization'));
        $this->assertTrue($result3->hasHeader('Authorization'));

        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $result1->getUri());
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $result2->getUri());
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $result3->getUri());

        // But each should have different paths
        $this->assertStringContainsString('/transactions', (string) $result1->getUri());
        $this->assertStringContainsString('/payments', (string) $result2->getUri());
        $this->assertStringContainsString('/refunds', (string) $result3->getUri());
    }

    public function test_apply_returnsPsr7Request(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $factory = ElavonApiFactory::configure();

        // Act
        $result = $factory->apply($request);

        // Assert - Result should be a plain PSR-7 request, not an ElavonApiFactory
        $this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $result);
        $this->assertNotInstanceOf(ElavonApiFactory::class, $result);
    }

    public function test_withApiVersion_changesVersion(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');

        // Act
        $factory = ElavonApiFactory::configure('1')
            ->withApiVersion('3');
        $result = $factory->apply($request);

        // Assert
        $this->assertSame('3', $factory->getApiVersion());
        $this->assertSame('3', $result->getHeaderLine('Accept-Version'));
    }

    public function test_immutability_withRegion(): void
    {
        // Arrange
        $factory1 = ElavonApiFactory::configure();

        // Act
        $factory2 = $factory1->withRegion('eu');

        // Assert
        $this->assertNotSame($factory1, $factory2);
        $this->assertNull($factory1->getRegion());
        $this->assertSame('eu', $factory2->getRegion());
    }

    public function test_immutability_withEnvironment(): void
    {
        // Arrange
        $factory1 = ElavonApiFactory::configure();

        // Act
        $factory2 = $factory1->withEnvironment('sandbox');

        // Assert
        $this->assertNotSame($factory1, $factory2);
        $this->assertNull($factory1->getEnvironment());
        $this->assertSame('uat', $factory2->getEnvironment());
    }

    public function test_immutability_withAuthentication(): void
    {
        // Arrange
        $factory1 = ElavonApiFactory::configure();

        // Act
        $factory2 = $factory1->withAuthentication('user', 'pass');

        // Assert
        $this->assertNotSame($factory1, $factory2);
        $this->assertNull($factory1->getUsername());
        $this->assertSame('user', $factory2->getUsername());
    }

    public function test_regionConstants_areCorrect(): void
    {
        $this->assertSame('eu', ElavonApiFactory::REGION_EU);
        $this->assertSame('us', ElavonApiFactory::REGION_US);
    }

    public function test_environmentConstants_areCorrect(): void
    {
        $this->assertSame('production', ElavonApiFactory::ENV_PRODUCTION);
        $this->assertSame('uat', ElavonApiFactory::ENV_UAT);
    }
}
