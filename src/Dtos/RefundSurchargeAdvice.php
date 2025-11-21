<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

/**
 * Refund Surcharge Advice data transfer object.
 *
 * Surcharge calculation advice for refund transactions.
 * All properties are read-only.
 */
class RefundSurchargeAdvice implements DataTransferObject
{
    use SerializesData;

    // Normalized Money properties
    public readonly ?Money $total;
    public readonly ?Money $surchargeTotal;
    public readonly ?Money $adjustedTotal;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['total', 'surchargeTotal', 'adjustedTotal'],
            'string' => [
                'href', 'id', 'createdAt', 'expiresAt', 'merchant', 'processorAccount',
                'parentTransaction', 'surchargeRate', 'customReference',
            ],
            'array' => ['customFields'],
        ];
    }

    /**
     * @param string|null $href RefundSurchargeAdvice Resource URL (self link)
     * @param string|null $id RefundSurchargeAdvice Resource ID
     * @param string|null $createdAt Creation timestamp
     * @param string|null $expiresAt Expiration timestamp
     * @param string|null $merchant Merchant Resource URL
     * @param string|null $processorAccount ProcessorAccount Resource URL
     * @param string|null $parentTransaction Parent Transaction Resource URL
     * @param Money|array{amount: string, currencyCode: string}|null $total Refund total before surcharge
     * @param string|null $surchargeRate Surcharge rate (e.g., "0.035" = 3.5%)
     * @param Money|array{amount: string, currencyCode: string}|null $surchargeTotal Surcharge amount
     * @param Money|array{amount: string, currencyCode: string}|null $adjustedTotal Total after adding surcharge
     * @param string|null $customReference Custom reference
     * @param array<string, mixed>|null $customFields Custom fields
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $parentTransaction = null,
        Money|array|null $total = null,
        public readonly ?string $surchargeRate = null,
        Money|array|null $surchargeTotal = null,
        Money|array|null $adjustedTotal = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize Money objects
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromData($total),
            default => null,
        };

        $this->surchargeTotal = match (true) {
            $surchargeTotal instanceof Money => $surchargeTotal,
            is_array($surchargeTotal) => Money::fromData($surchargeTotal),
            default => null,
        };

        $this->adjustedTotal = match (true) {
            $adjustedTotal instanceof Money => $adjustedTotal,
            is_array($adjustedTotal) => Money::fromData($adjustedTotal),
            default => null,
        };
    }
}
