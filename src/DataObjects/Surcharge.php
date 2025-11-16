<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

/**
 * Surcharge data transfer object.
 *
 * Surcharge information if surchargeAdvice or refundSurchargeAdvice was created for the transaction.
 * All properties are read-only.
 */
class Surcharge
{
    // Normalized Money properties
    public readonly ?Money $unadjustedTotal;
    public readonly ?Money $unadjustedRefundableTotal;
    public readonly ?Money $surchargeTotal;

    /**
     * @param Money|array{amount: string, currencyCode: string}|null $unadjustedTotal Transaction total before adding surcharge
     * @param Money|array{amount: string, currencyCode: string}|null $unadjustedRefundableTotal Amount of original unadjusted transaction still available for refund
     * @param Money|array{amount: string, currencyCode: string}|null $surchargeTotal Surcharge total
     * @param string|null $rate Merchant's surcharge rate (e.g., "0.035" = 3.5%)
     */
    public function __construct(
        Money|array|null $unadjustedTotal = null,
        Money|array|null $unadjustedRefundableTotal = null,
        Money|array|null $surchargeTotal = null,
        public readonly ?string $rate = null,
    ) {
        // Normalize Money objects
        $this->unadjustedTotal = match (true) {
            $unadjustedTotal instanceof Money => $unadjustedTotal,
            is_array($unadjustedTotal) => Money::fromArray($unadjustedTotal),
            default => null,
        };

        $this->unadjustedRefundableTotal = match (true) {
            $unadjustedRefundableTotal instanceof Money => $unadjustedRefundableTotal,
            is_array($unadjustedRefundableTotal) => Money::fromArray($unadjustedRefundableTotal),
            default => null,
        };

        $this->surchargeTotal = match (true) {
            $surchargeTotal instanceof Money => $surchargeTotal,
            is_array($surchargeTotal) => Money::fromArray($surchargeTotal),
            default => null,
        };
    }

    /**
     * Creates a Surcharge instance from an array representation.
     *
     * @param array<string, mixed> $data Array with surcharge data
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromArray(array $data): self
    {
        return new self(
            unadjustedTotal: $data['unadjustedTotal'] ?? null,
            unadjustedRefundableTotal: $data['unadjustedRefundableTotal'] ?? null,
            surchargeTotal: $data['surchargeTotal'] ?? null,
            rate: isset($data['rate']) ? (string) $data['rate'] : null,
        );
    }

    /**
     * Converts the Surcharge to an array representation.
     *
     * Only includes non-null values for cleaner JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->unadjustedTotal !== null) {
            $data['unadjustedTotal'] = $this->unadjustedTotal->toArray();
        }

        if ($this->unadjustedRefundableTotal !== null) {
            $data['unadjustedRefundableTotal'] = $this->unadjustedRefundableTotal->toArray();
        }

        if ($this->surchargeTotal !== null) {
            $data['surchargeTotal'] = $this->surchargeTotal->toArray();
        }

        if ($this->rate !== null) {
            $data['rate'] = $this->rate;
        }

        return $data;
    }
}
