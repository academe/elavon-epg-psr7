<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Money\Money;

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
            'money' => [
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
     * @param Money|null $total The cumulative new total amount
     * @param Money|null $totalAdjustment The positive or negative adjustment to the prior authorized amount
     * @param Money|null $salesTax The cumulative new sales tax
     * @param Money|null $salesTaxAdjustment The positive or negative adjustment to the prior salesTax amount
     * @param Money|null $tip Tip amount
     * @param Money|null $tipAdjustment The positive or negative adjustment to the prior tip amount
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
        public readonly ?Money $total = null,
        public readonly ?Money $totalAdjustment = null,
        public readonly ?Money $salesTax = null,
        public readonly ?Money $salesTaxAdjustment = null,
        public readonly ?Money $tip = null,
        public readonly ?Money $tipAdjustment = null,
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
