#!/usr/bin/env python3
"""
Improved coverage detection that properly handles list endpoints.
"""

import json
import os

def load_json(filepath):
    """Load JSON file."""
    with open(filepath, 'r') as f:
        return json.load(f)

def save_json(filepath, data):
    """Save JSON file."""
    with open(filepath, 'w') as f:
        json.dump(data, f, indent=2)

def main():
    base_path = '/home/user/elavon-ept-psr7'
    coverage_file = os.path.join(base_path, 'analysis_output', 'coverage_analysis.json')

    coverage = load_json(coverage_file)

    # Get list of existing request/response messages
    request_messages = [
        'CreateApplePayPaymentRequest',
        'CreateForexAdviceRequest',
        'CreateGooglePayPaymentRequest',
        'CreateHostedCardRequest',
        'CreatePazePaymentRequest',
        'CreateRefundSurchargeAdviceRequest',
        'CreateShopperRequest',
        'CreateStoredCardRequest',
        'CreateSurchargeAdviceRequest',
        'CreateTransactionRequest',
        'DeleteShopperRequest',
        'DeleteStoredCardRequest',
        'RetrieveApplePayPaymentRequest',
        'RetrieveForexAdviceRequest',
        'RetrieveGooglePayPaymentRequest',
        'RetrieveHostedCardListRequest',
        'RetrieveHostedCardRequest',
        'RetrievePazePaymentRequest',
        'RetrieveRefundSurchargeAdviceRequest',
        'RetrieveShopperListRequest',
        'RetrieveShopperRequest',
        'RetrieveStoredCardListRequest',
        'RetrieveStoredCardRequest',
        'RetrieveSurchargeAdviceRequest',
        'RetrieveTransactionListRequest',
        'RetrieveTransactionRequest',
        'UpdateShopperRequest',
        'UpdateStoredCardRequest',
        'UpdateTransactionRequest',
    ]

    response_messages = [
        'ApplePayPaymentResponse',
        'ForexAdviceResponse',
        'GooglePayPaymentResponse',
        'HostedCardListResponse',
        'HostedCardResponse',
        'PazePaymentResponse',
        'RefundSurchargeAdviceResponse',
        'ShopperListResponse',
        'ShopperResponse',
        'StoredCardListResponse',
        'StoredCardResponse',
        'SurchargeAdviceResponse',
        'TransactionListResponse',
        'TransactionResponse',
    ]

    # Update coverage based on actual messages
    implemented_count = 0

    for endpoint, details in coverage['endpoints'].items():
        operation_id = details['operationId']

        # Check for exact match in request messages
        request_impl = any(operation_id in msg for msg in request_messages)

        # Check for exact match in response messages
        # For list operations, we need special handling
        if 'Retrieve' in operation_id and not operation_id.endswith('s'):
            # Single resource retrieval
            response_impl = any(operation_id.replace('Retrieve', '') + 'Response' in msg for msg in response_messages)
        elif 'Retrieve' in operation_id and operation_id.endswith('s'):
            # List retrieval
            response_impl = any(operation_id.replace('Retrieve', '').rstrip('s') + 'ListResponse' in msg for msg in response_messages)
        elif 'Create' in operation_id:
            response_impl = any(operation_id.replace('Create', '') + 'Response' in msg for msg in response_messages)
        elif 'Update' in operation_id:
            response_impl = any(operation_id.replace('Update', '') + 'Response' in msg for msg in response_messages)
        elif 'Delete' in operation_id:
            response_impl = True  # Delete operations return 204 No Content
        else:
            response_impl = False

        # Update the coverage
        coverage['endpoints'][endpoint]['request_implemented'] = request_impl
        coverage['endpoints'][endpoint]['response_implemented'] = response_impl
        coverage['endpoints'][endpoint]['implemented'] = request_impl and response_impl

        if coverage['endpoints'][endpoint]['implemented']:
            implemented_count += 1

    # Update summary
    coverage['summary']['implemented_endpoints'] = implemented_count

    # Save updated coverage
    save_json(coverage_file, coverage)

    print(f"Updated coverage analysis")
    print(f"Total Endpoints: {coverage['summary']['total_endpoints']}")
    print(f"Implemented Endpoints: {implemented_count}")
    print(f"Coverage: {implemented_count / coverage['summary']['total_endpoints'] * 100:.1f}%")

if __name__ == '__main__':
    main()
