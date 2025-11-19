<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

/**
 * TotalAdjustment data transfer object.
 *
 * An adjustment to the total and/or tip of an existing transaction.
 *
 * Check the 'isAuthorized' field to see whether or not the adjustment was authorized.
 * If not authorized, check the 'failures' array to determine why.
 */
class TotalAdjustment implements DataTransferObject
{
    use SerializesData;

    // Normalized Money properties
    public readonly ?Money $total;
    public readonly ?Money $totalAdjustment;
    public readonly ?Money $salesTax;
    public readonly ?Money $salesTaxAdjustment;
    public readonly ?Money $tip;
    public readonly ?Money $tipAdjustment;

    /** @var array<Failure>|null */
    public readonly ?array $failures;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => [
                'total', 'totalAdjustment', 'salesTax', 'salesTaxAdjustment',
                'tip', 'tipAdjustment',
            ],
            'array' => ['failures', 'customFields'],
            'string' => [
                'href', 'id', 'transaction', 'createdAt', 'processorReference',
                'issuerReference', 'authorizationCode', 'issuerResponseCode',
                'rawProcessorResponseInfo', 'customReference',
            ],
            'boolean' => ['doCapture', 'isAuthorized'],
        ];
    }

    /**
     * @param string|null $href TotalAdjustment Resource URL (self link)
     * @param string|null $id TotalAdjustment Resource ID assigned by server
     * @param string|null $transaction Transaction Resource URL (suppressed when public API key is used)
     * @param string|null $createdAt Creation timestamp
     * @param Money|array{amount: string, currencyCode: string}|null $total The cumulative new total amount
     * @param Money|array{amount: string, currencyCode: string}|null $totalAdjustment The positive or negative adjustment to the prior authorized amount
     * @param Money|array{amount: string, currencyCode: string}|null $salesTax The cumulative new sales tax
     * @param Money|array{amount: string, currencyCode: string}|null $salesTaxAdjustment The positive or negative adjustment to the prior salesTax amount
     * @param Money|array{amount: string, currencyCode: string}|null $tip Tip amount
     * @param Money|array{amount: string, currencyCode: string}|null $tipAdjustment The positive or negative adjustment to the prior tip amount
     * @param string|null $processorReference Reference assigned by the processor
     * @param string|null $issuerReference Reference assigned by the issuer
     * @param bool|null $doCapture If false, authorize only; if true (default), authorize and capture funds for settlement
     * @param bool|null $isAuthorized Transaction is authorized?
     * @param string|null $authorizationCode Authorization code
     * @param string|null $issuerResponseCode Issuer response code
     * @param string|null $rawProcessorResponseInfo Raw response data from the processor
     * @param array<Failure|array<string, mixed>>|null $failures Failure details if the transaction was not authorized
     * @param string|null $customReference Optional reference provided by the merchant
     * @param array<string, mixed>|null $customFields Custom fields containing arbitrary string values
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $transaction = null,
        public readonly ?string $createdAt = null,
        Money|array|null $total = null,
        Money|array|null $totalAdjustment = null,
        Money|array|null $salesTax = null,
        Money|array|null $salesTaxAdjustment = null,
        Money|array|null $tip = null,
        Money|array|null $tipAdjustment = null,
        public readonly ?string $processorReference = null,
        public readonly ?string $issuerReference = null,
        public readonly ?bool $doCapture = null,
        public readonly ?bool $isAuthorized = null,
        public readonly ?string $authorizationCode = null,
        public readonly ?string $issuerResponseCode = null,
        public readonly ?string $rawProcessorResponseInfo = null,
        array|null $failures = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize Money objects
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromData($total),
            default => null,
        };

        $this->totalAdjustment = match (true) {
            $totalAdjustment instanceof Money => $totalAdjustment,
            is_array($totalAdjustment) => Money::fromData($totalAdjustment),
            default => null,
        };

        $this->salesTax = match (true) {
            $salesTax instanceof Money => $salesTax,
            is_array($salesTax) => Money::fromData($salesTax),
            default => null,
        };

        $this->salesTaxAdjustment = match (true) {
            $salesTaxAdjustment instanceof Money => $salesTaxAdjustment,
            is_array($salesTaxAdjustment) => Money::fromData($salesTaxAdjustment),
            default => null,
        };

        $this->tip = match (true) {
            $tip instanceof Money => $tip,
            is_array($tip) => Money::fromData($tip),
            default => null,
        };

        $this->tipAdjustment = match (true) {
            $tipAdjustment instanceof Money => $tipAdjustment,
            is_array($tipAdjustment) => Money::fromData($tipAdjustment),
            default => null,
        };

        // Normalize Failure array
        $this->failures = $this->normalizeFailures($failures);
    }

    /**
     * Normalize an array of Failure objects.
     *
     * @param array<Failure|array<string, mixed>>|null $items
     * @return array<Failure>|null
     */
    private function normalizeFailures(?array $items): ?array
    {
        if ($items === null) {
            return null;
        }

        return array_map(
            fn ($item) => $item instanceof Failure
                ? $item
                : Failure::fromData($item),
            $items
        );
    }
}
