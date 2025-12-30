<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * EMV data transfer object.
 *
 * Contains EMV chip card transaction data including TLV request/response tags.
 */
class Emv implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        // TLV request (required for requests)
        public readonly ?string $tlvRequest = null,

        // EMV keys information
        public readonly ?EmvKeys $keys = null,

        // EMV tags (response fields, read-only)
        public readonly ?string $applicationIdentifierTag4F = null,
        public readonly ?string $applicationLabelTag50 = null,
        public readonly ?string $issuerScriptTemplateOneTag71 = null,
        public readonly ?string $issuerScriptTemplateTwoTag72 = null,
        public readonly ?string $applicationInterchangeProfileTag82 = null,
        public readonly ?string $dedicatedFileNameTag84 = null,
        public readonly ?string $authorizationResponseCodeTag8A = null,
        public readonly ?string $issuerAuthenticationDataTag91 = null,
        public readonly ?string $terminalVerificationResultsTag95 = null,
        public readonly ?string $transactionDateTag9A = null,
        public readonly ?string $transactionStatusInformationTag9B = null,
        public readonly ?string $transactionTypeTag9C = null,
        public readonly ?string $applicationExpirationDateTag5F24 = null,
        public readonly ?string $transactionCurrencyCodeTag5F2A = null,
        public readonly ?string $languagePreferenceTag5F2D = null,
        public readonly ?string $applicationPanSequenceNumberTag5F34 = null,
        public readonly ?string $accountTypeTag5F57 = null,
        public readonly ?string $authorizedAmountTag9F02 = null,
        public readonly ?string $otherAmountTag9F03 = null,
        public readonly ?string $applicationIdentifierTerminalTag9F06 = null,
        public readonly ?string $applicationVersionNumberTag9F09 = null,
        public readonly ?string $issuerApplicationDataTag9F10 = null,
        public readonly ?string $issuerApplicationDataTag9F12 = null,
        public readonly ?string $terminalCountryCodeTag9F1A = null,
        public readonly ?string $interfaceDeviceSerialNumberTag9F1E = null,
        public readonly ?string $transactionTimeTag9F21 = null,
        public readonly ?string $applicationCryptogramTag9F26 = null,
        public readonly ?string $cryptogramInformationDataTag9F27 = null,
        public readonly ?string $terminalCapabilitiesTag9F33 = null,
        public readonly ?string $cardholderVerificationMethodResultsTag9F34 = null,
        public readonly ?string $terminalTypeTag9F35 = null,
        public readonly ?string $applicationTransactionCounterTag9F36 = null,
        public readonly ?string $unpredictableNumberTag9F37 = null,
        public readonly ?string $transactionSequenceCounterTag9F41 = null,
        public readonly ?string $transactionCategoryCodeTag9F53 = null,
        public readonly ?string $issuerScriptResultsTag9F5B = null,
        public readonly ?string $thirdPartyDataTag9F6E = null,
        public readonly ?string $customerExclusiveDataTag9F7C = null,
        public readonly ?string $cardholderNameTag5F20 = null,
    ) {
        $this->validate();
    }

    /**
     * Validates EMV data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // TLV request must be at least 6 characters if provided
        if ($this->tlvRequest !== null && strlen($this->tlvRequest) < 6) {
            throw new InvalidArgumentException('TLV request must be at least 6 characters');
        }
    }
}