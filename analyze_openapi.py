#!/usr/bin/env python3
"""
Analyzes the OpenAPI specification to extract:
1. All endpoints and operations
2. All schemas with validation rules
3. Coverage analysis comparing spec to implementation
"""

import json
import os
import re
from pathlib import Path
from typing import Dict, List, Any, Set

def load_openapi_spec(path: str) -> Dict:
    """Load the OpenAPI specification."""
    with open(path, 'r') as f:
        return json.load(f)

def get_existing_dtos(base_path: str) -> Set[str]:
    """Get list of existing DTO class names."""
    dto_path = Path(base_path) / 'src' / 'Dtos'
    dtos = set()
    for file in dto_path.glob('*.php'):
        # Extract class name from filename
        class_name = file.stem
        dtos.add(class_name)
    return dtos

def get_existing_messages(base_path: str) -> Dict[str, List[str]]:
    """Get list of existing message classes."""
    messages = {'request': [], 'response': []}

    request_path = Path(base_path) / 'src' / 'Messages' / 'Request'
    for file in request_path.glob('*.php'):
        messages['request'].append(file.stem)

    response_path = Path(base_path) / 'src' / 'Messages' / 'Response'
    for file in response_path.glob('*.php'):
        if file.stem != 'Concerns':  # Skip the Concerns directory
            messages['response'].append(file.stem)

    return messages

def extract_paths_and_operations(spec: Dict) -> Dict[str, Any]:
    """Extract all paths and operations from the spec."""
    paths_info = {}

    for path, path_item in spec.get('paths', {}).items():
        paths_info[path] = {}

        for method in ['get', 'post', 'put', 'patch', 'delete']:
            if method in path_item:
                operation = path_item[method]
                operation_id = operation.get('operationId', '')
                tags = operation.get('tags', [])
                summary = operation.get('summary', '')

                paths_info[path][method] = {
                    'operationId': operation_id,
                    'tags': tags,
                    'summary': summary,
                    'requestBody': operation.get('requestBody'),
                    'responses': operation.get('responses', {}),
                    'parameters': operation.get('parameters', [])
                }

    return paths_info

def extract_validation_rules(schema: Dict, schema_name: str = None) -> Dict[str, Any]:
    """Extract validation rules from a schema."""
    rules = {}

    if 'properties' in schema:
        for prop_name, prop_schema in schema['properties'].items():
            prop_rules = {}

            # Type
            if 'type' in prop_schema:
                prop_rules['type'] = prop_schema['type']
            elif '$ref' in prop_schema:
                prop_rules['$ref'] = prop_schema['$ref']
            elif 'oneOf' in prop_schema:
                prop_rules['oneOf'] = prop_schema['oneOf']
            elif 'anyOf' in prop_schema:
                prop_rules['anyOf'] = prop_schema['anyOf']
            elif 'allOf' in prop_schema:
                prop_rules['allOf'] = prop_schema['allOf']

            # String validations
            if 'minLength' in prop_schema:
                prop_rules['minLength'] = prop_schema['minLength']
            if 'maxLength' in prop_schema:
                prop_rules['maxLength'] = prop_schema['maxLength']
            if 'pattern' in prop_schema:
                prop_rules['pattern'] = prop_schema['pattern']

            # Number validations
            if 'minimum' in prop_schema:
                prop_rules['minimum'] = prop_schema['minimum']
            if 'maximum' in prop_schema:
                prop_rules['maximum'] = prop_schema['maximum']
            if 'exclusiveMinimum' in prop_schema:
                prop_rules['exclusiveMinimum'] = prop_schema['exclusiveMinimum']
            if 'exclusiveMaximum' in prop_schema:
                prop_rules['exclusiveMaximum'] = prop_schema['exclusiveMaximum']
            if 'multipleOf' in prop_schema:
                prop_rules['multipleOf'] = prop_schema['multipleOf']

            # Array validations
            if 'minItems' in prop_schema:
                prop_rules['minItems'] = prop_schema['minItems']
            if 'maxItems' in prop_schema:
                prop_rules['maxItems'] = prop_schema['maxItems']
            if 'uniqueItems' in prop_schema:
                prop_rules['uniqueItems'] = prop_schema['uniqueItems']
            if 'items' in prop_schema:
                prop_rules['items'] = prop_schema['items']

            # Enum
            if 'enum' in prop_schema:
                prop_rules['enum'] = prop_schema['enum']

            # Format
            if 'format' in prop_schema:
                prop_rules['format'] = prop_schema['format']

            # Description
            if 'description' in prop_schema:
                prop_rules['description'] = prop_schema['description']

            # Default
            if 'default' in prop_schema:
                prop_rules['default'] = prop_schema['default']

            # Nullable
            if 'nullable' in prop_schema:
                prop_rules['nullable'] = prop_schema['nullable']

            # Read only / Write only
            if 'readOnly' in prop_schema:
                prop_rules['readOnly'] = prop_schema['readOnly']
            if 'writeOnly' in prop_schema:
                prop_rules['writeOnly'] = prop_schema['writeOnly']

            # Check if required
            required_fields = schema.get('required', [])
            if prop_name in required_fields:
                prop_rules['required'] = True
            else:
                prop_rules['required'] = False

            rules[prop_name] = prop_rules

    # Include required array at schema level
    if 'required' in schema:
        rules['__required_fields__'] = schema['required']

    return rules

def map_openapi_to_php_class(schema_name: str, existing_dtos: Set[str]) -> str:
    """Map OpenAPI schema name to PHP class name."""
    # Direct mapping first
    if schema_name in existing_dtos:
        return f"Academe\\Elavon\\Epg\\Psr7\\Dtos\\{schema_name}"

    # Try some common transformations
    # Remove common suffixes/prefixes
    cleaned = schema_name.replace('Request', '').replace('Response', '').replace('Body', '')
    if cleaned in existing_dtos:
        return f"Academe\\Elavon\\Epg\\Psr7\\Dtos\\{cleaned}"

    return None

def analyze_coverage(spec: Dict, existing_dtos: Set[str], existing_messages: Dict[str, List[str]]) -> Dict[str, Any]:
    """Analyze implementation coverage."""
    coverage = {
        'endpoints': {},
        'schemas': {},
        'summary': {
            'total_endpoints': 0,
            'implemented_endpoints': 0,
            'total_schemas': 0,
            'implemented_schemas': 0
        }
    }

    # Analyze endpoints
    paths = extract_paths_and_operations(spec)
    for path, methods in paths.items():
        for method, operation in methods.items():
            operation_id = operation['operationId']
            coverage['endpoints'][f"{method.upper()} {path}"] = {
                'operationId': operation_id,
                'tags': operation['tags'],
                'summary': operation['summary'],
                'implemented': False,
                'request_implemented': False,
                'response_implemented': False
            }

            coverage['summary']['total_endpoints'] += 1

            # Check if request message exists
            for msg in existing_messages['request']:
                if operation_id.replace('Retrieve', 'Retrieve').replace('Create', 'Create').replace('Update', 'Update').replace('Delete', 'Delete') in msg:
                    coverage['endpoints'][f"{method.upper()} {path}"]['request_implemented'] = True
                    break

            # Check if response message exists
            for msg in existing_messages['response']:
                resource_name = operation['tags'][0] if operation['tags'] else ''
                if resource_name.replace(' ', '') in msg or operation_id.replace('Retrieve', '').replace('Create', '').replace('Update', '').replace('Delete', '') in msg:
                    coverage['endpoints'][f"{method.upper()} {path}"]['response_implemented'] = True
                    break

            if coverage['endpoints'][f"{method.upper()} {path}"]['request_implemented'] and \
               coverage['endpoints'][f"{method.upper()} {path}"]['response_implemented']:
                coverage['endpoints'][f"{method.upper()} {path}"]['implemented'] = True
                coverage['summary']['implemented_endpoints'] += 1

    # Analyze schemas
    schemas = spec.get('components', {}).get('schemas', {})
    for schema_name, schema_def in schemas.items():
        php_class = map_openapi_to_php_class(schema_name, existing_dtos)
        coverage['schemas'][schema_name] = {
            'php_class': php_class,
            'implemented': php_class is not None,
            'has_properties': 'properties' in schema_def
        }
        coverage['summary']['total_schemas'] += 1
        if php_class:
            coverage['summary']['implemented_schemas'] += 1

    return coverage

def main():
    base_path = '/home/user/elavon-ept-psr7'
    spec_path = os.path.join(base_path, 'docs', 'openapi.json')

    print("Loading OpenAPI specification...")
    spec = load_openapi_spec(spec_path)

    print("Scanning existing DTOs and Messages...")
    existing_dtos = get_existing_dtos(base_path)
    existing_messages = get_existing_messages(base_path)

    print(f"Found {len(existing_dtos)} DTOs")
    print(f"Found {len(existing_messages['request'])} Request messages")
    print(f"Found {len(existing_messages['response'])} Response messages")

    print("\nAnalyzing coverage...")
    coverage = analyze_coverage(spec, existing_dtos, existing_messages)

    print("\nExtracting validation rules...")
    validation_rules = {}
    schemas = spec.get('components', {}).get('schemas', {})

    for schema_name, schema_def in schemas.items():
        php_class = map_openapi_to_php_class(schema_name, existing_dtos)
        if php_class:
            # Extract validation rules for this schema
            rules = extract_validation_rules(schema_def, schema_name)
            if rules:
                validation_rules[php_class] = rules
        else:
            # Still extract rules but use OpenAPI schema name
            rules = extract_validation_rules(schema_def, schema_name)
            if rules:
                validation_rules[f"OpenAPI::{schema_name}"] = rules

    # Save results
    output_dir = os.path.join(base_path, 'analysis_output')
    os.makedirs(output_dir, exist_ok=True)

    coverage_file = os.path.join(output_dir, 'coverage_analysis.json')
    with open(coverage_file, 'w') as f:
        json.dump(coverage, f, indent=2)
    print(f"\nCoverage analysis saved to: {coverage_file}")

    validation_file = os.path.join(output_dir, 'validation_rules.json')
    with open(validation_file, 'w') as f:
        json.dump(validation_rules, f, indent=2)
    print(f"Validation rules saved to: {validation_file}")

    # Print summary
    print("\n" + "="*80)
    print("COVERAGE SUMMARY")
    print("="*80)
    print(f"Total Endpoints: {coverage['summary']['total_endpoints']}")
    print(f"Implemented Endpoints: {coverage['summary']['implemented_endpoints']}")
    print(f"Coverage: {coverage['summary']['implemented_endpoints'] / coverage['summary']['total_endpoints'] * 100:.1f}%")
    print()
    print(f"Total Schemas: {coverage['summary']['total_schemas']}")
    print(f"Implemented Schemas (DTOs): {coverage['summary']['implemented_schemas']}")
    print(f"Coverage: {coverage['summary']['implemented_schemas'] / coverage['summary']['total_schemas'] * 100:.1f}%")
    print("="*80)

    return coverage, validation_rules

if __name__ == '__main__':
    main()
