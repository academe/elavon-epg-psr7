<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Hosted Payment Page (HPP) type enumeration.
 *
 * Indicates the type of hosted payments page for the payment session.
 */
enum HppType: string
{
    /** Lightbox integration where the HPP is displayed in a modal overlay. */
    case LIGHTBOX = 'lightbox';

    /** Payment link integration where a shareable URL is generated. */
    case PAYMENT_LINK = 'paymentLink';

    /** Full page redirect integration where the shopper is redirected to the HPP. */
    case FULL_PAGE_REDIRECT = 'fullPageRedirect';

    /** Hosted payment fields integration where individual form fields are embedded. */
    case HOSTED_PAYMENT_FIELDS = 'hostedPaymentFields';
}
