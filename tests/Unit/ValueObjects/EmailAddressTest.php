<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\EmailAddress;
use PHPUnit\Framework\TestCase;

class EmailAddressTest extends TestCase
{
    public function test_construct_withValidEmail_createsInstance(): void
    {
        $email = new EmailAddress('user@example.com');

        $this->assertSame('user@example.com', $email->address);
    }

    public function test_fromData_withValidString_createsInstance(): void
    {
        $email = EmailAddress::fromData('user@example.com');

        $this->assertSame('user@example.com', $email->address);
    }

    public function test_fromData_withNonString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email address must be a string');

        EmailAddress::fromData(['email' => 'user@example.com']);
    }

    public function test_toData_returnsString(): void
    {
        $email = new EmailAddress('user@example.com');

        $this->assertSame('user@example.com', $email->toData());
    }

    public function test_toString_returnsEmailAddress(): void
    {
        $email = new EmailAddress('user@example.com');

        $this->assertSame('user@example.com', (string) $email);
    }

    public function test_construct_withEmptyString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email address cannot be empty');

        new EmailAddress('');
    }

    public function test_construct_withInvalidFormat_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid email address format: 'not-an-email'");

        new EmailAddress('not-an-email');
    }

    public function test_construct_withTooLongEmail_throwsException(): void
    {
        // Create an email longer than 254 characters
        $longEmail = str_repeat('a', 245) . '@example.com'; // Total: 257 chars

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email address cannot exceed 254 characters');

        new EmailAddress($longEmail);
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function test_construct_withValidFormats_succeeds(string $email): void
    {
        $emailAddress = new EmailAddress($email);

        $this->assertSame($email, $emailAddress->address);
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple' => ['user@example.com'],
            'with plus' => ['user+tag@example.com'],
            'with dots' => ['first.last@example.com'],
            'with subdomain' => ['user@mail.example.com'],
            'with numbers' => ['user123@example123.com'],
            'with dash' => ['user-name@example-domain.com'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function test_construct_withInvalidFormats_throwsException(string $email): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address format');

        new EmailAddress($email);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'missing @' => ['userexample.com'],
            'missing domain' => ['user@'],
            'missing local' => ['@example.com'],
            'double @' => ['user@@example.com'],
            'spaces' => ['user name@example.com'],
        ];
    }

    public function test_serializationRoundTrip_preservesValue(): void
    {
        $original = new EmailAddress('user@example.com');
        $data = $original->toData();
        $restored = EmailAddress::fromData($data);

        $this->assertSame($original->address, $restored->address);
        $this->assertSame('user@example.com', $data);
    }
}
