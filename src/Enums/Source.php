<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Transaction source enumeration.
 *
 * Identifies how the transaction was submitted. Defaults to 'directApiCall' when not specified.
 */
enum Source: string
{
    case DIRECT_API_CALL = 'directApiCall';
    case HPP_SUBMIT_REDIRECT = 'hppSubmitRedirect';
    case HPP_IFRAME_LIGHTBOX = 'hppIframeLightbox';
    case HPP_IFRAME_EMBEDDED = 'hppIframeEmbedded';
    case HPP_SDK = 'hppSdk';
    case VIRTUAL_TERMINAL = 'virtualTerminal';
    case GATEWAY_RECURRING = 'gatewayRecurring';
    case PAY_BY_LINK = 'payByLink';
    case MONITORING = 'monitoring';
    case HPP_FIELDS = 'hppFields';
    case PHYSICAL_TERMINAL = 'physicalTerminal';
    case UNKNOWN = 'unknown';
}
