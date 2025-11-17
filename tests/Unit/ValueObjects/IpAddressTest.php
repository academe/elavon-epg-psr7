<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\IpAddress;
use PHPUnit\Framework\TestCase;

class IpAddressTest extends TestCase
{
    public function test_construct_withValidIPv4_createsInstance(): void
    {
        $ip = new IpAddress('192.168.1.1');

        $this->assertSame('192.168.1.1', $ip->address);
        $this->assertTrue($ip->isIPv4());
        $this->assertFalse($ip->isIPv6());
    }

    public function test_construct_withValidIPv6_createsInstance(): void
    {
        $ip = new IpAddress('2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $this->assertSame('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $ip->address);
        $this->assertTrue($ip->isIPv6());
        $this->assertFalse($ip->isIPv4());
    }

    public function test_fromData_withValidIPv4String_createsInstance(): void
    {
        $ip = IpAddress::fromData('10.9.234.22');

        $this->assertSame('10.9.234.22', $ip->address);
        $this->assertTrue($ip->isIPv4());
    }

    public function test_fromData_withValidIPv6String_createsInstance(): void
    {
        $ip = IpAddress::fromData('::1');

        $this->assertSame('::1', $ip->address);
        $this->assertTrue($ip->isIPv6());
    }

    public function test_fromData_withNonString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IP address must be a string');

        IpAddress::fromData(['ip' => '192.168.1.1']);
    }

    public function test_toData_returnsString(): void
    {
        $ip = new IpAddress('192.168.1.1');

        $this->assertSame('192.168.1.1', $ip->toData());
    }

    public function test_toString_returnsIpAddress(): void
    {
        $ip = new IpAddress('10.0.0.1');

        $this->assertSame('10.0.0.1', (string) $ip);
    }

    public function test_construct_withEmptyString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IP address cannot be empty');

        new IpAddress('');
    }

    public function test_construct_withInvalidFormat_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid IP address format: 'not-an-ip'");

        new IpAddress('not-an-ip');
    }

    /**
     * @dataProvider validIPv4Provider
     */
    public function test_construct_withValidIPv4Formats_succeeds(string $ip): void
    {
        $ipObject = new IpAddress($ip);

        $this->assertSame($ip, $ipObject->address);
        $this->assertTrue($ipObject->isIPv4());
    }

    public static function validIPv4Provider(): array
    {
        return [
            'localhost' => ['127.0.0.1'],
            'private class A' => ['10.0.0.1'],
            'private class B' => ['172.16.0.1'],
            'private class C' => ['192.168.1.1'],
            'public' => ['8.8.8.8'],
            'broadcast' => ['255.255.255.255'],
            'zero' => ['0.0.0.0'],
        ];
    }

    /**
     * @dataProvider validIPv6Provider
     */
    public function test_construct_withValidIPv6Formats_succeeds(string $ip): void
    {
        $ipObject = new IpAddress($ip);

        $this->assertSame($ip, $ipObject->address);
        $this->assertTrue($ipObject->isIPv6());
    }

    public static function validIPv6Provider(): array
    {
        return [
            'loopback' => ['::1'],
            'full format' => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            'compressed' => ['2001:db8:85a3::8a2e:370:7334'],
            'IPv4 mapped' => ['::ffff:192.0.2.1'],
            'link local' => ['fe80::1'],
            'all zeros' => ['::'],
        ];
    }

    /**
     * @dataProvider invalidIpProvider
     */
    public function test_construct_withInvalidFormats_throwsException(string $ip): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid IP address format');

        new IpAddress($ip);
    }

    public static function invalidIpProvider(): array
    {
        return [
            'text' => ['not-an-ip'],
            'IPv4 out of range' => ['256.1.1.1'],
            'IPv4 incomplete' => ['192.168.1'],
            'IPv4 too many octets' => ['192.168.1.1.1'],
            'IPv6 invalid' => ['gggg::1'],
            'mixed invalid' => ['192.168.1.1::1'],
        ];
    }

    public function test_serializationRoundTrip_preservesIPv4Value(): void
    {
        $original = new IpAddress('10.9.234.22');
        $data = $original->toData();
        $restored = IpAddress::fromData($data);

        $this->assertSame($original->address, $restored->address);
        $this->assertSame('10.9.234.22', $data);
        $this->assertTrue($restored->isIPv4());
    }

    public function test_serializationRoundTrip_preservesIPv6Value(): void
    {
        $original = new IpAddress('2001:db8::1');
        $data = $original->toData();
        $restored = IpAddress::fromData($data);

        $this->assertSame($original->address, $restored->address);
        $this->assertSame('2001:db8::1', $data);
        $this->assertTrue($restored->isIPv6());
    }
}
