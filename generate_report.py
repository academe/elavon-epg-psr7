#!/usr/bin/env python3
"""
Generates comprehensive coverage and validation reports from OpenAPI analysis.
"""

import json
import os
from collections import defaultdict

def load_json(filepath):
    """Load JSON file."""
    with open(filepath, 'r') as f:
        return json.load(f)

def generate_coverage_report(coverage_data):
    """Generate a detailed coverage report."""

    report = []
    report.append("=" * 100)
    report.append("ELAVON EPG PSR-7 - OPENAPI IMPLEMENTATION COVERAGE ANALYSIS")
    report.append("=" * 100)
    report.append("")

    # Summary
    summary = coverage_data['summary']
    report.append("EXECUTIVE SUMMARY")
    report.append("-" * 100)
    report.append(f"Total Endpoints in Spec: {summary['total_endpoints']}")
    report.append(f"Implemented Endpoints: {summary['implemented_endpoints']}")
    report.append(f"Endpoint Coverage: {summary['implemented_endpoints'] / summary['total_endpoints'] * 100:.1f}%")
    report.append("")
    report.append(f"Total Schemas in Spec: {summary['total_schemas']}")
    report.append(f"Implemented DTOs: {summary['implemented_schemas']}")
    report.append(f"Schema Coverage: {summary['implemented_schemas'] / summary['total_schemas'] * 100:.1f}%")
    report.append("")
    report.append("")

    # Group endpoints by resource
    endpoints_by_resource = defaultdict(list)
    for endpoint, details in coverage_data['endpoints'].items():
        resource = details['tags'][0] if details['tags'] else 'Unknown'
        endpoints_by_resource[resource].append((endpoint, details))

    # Implemented endpoints
    report.append("IMPLEMENTED ENDPOINTS (FULLY IMPLEMENTED)")
    report.append("-" * 100)
    implemented_count = 0
    for resource in sorted(endpoints_by_resource.keys()):
        endpoints = endpoints_by_resource[resource]
        implemented = [ep for ep in endpoints if ep[1]['implemented']]

        if implemented:
            report.append(f"\n{resource}:")
            for endpoint, details in sorted(implemented):
                implemented_count += 1
                method, path = endpoint.split(' ', 1)
                report.append(f"  ✓ {method:6s} {path:50s} [{details['operationId']}]")

    report.append(f"\nTotal Fully Implemented: {implemented_count}")
    report.append("")
    report.append("")

    # Partially implemented endpoints
    report.append("PARTIALLY IMPLEMENTED ENDPOINTS")
    report.append("-" * 100)
    partial_count = 0
    for resource in sorted(endpoints_by_resource.keys()):
        endpoints = endpoints_by_resource[resource]
        partial = [ep for ep in endpoints if not ep[1]['implemented'] and (ep[1]['request_implemented'] or ep[1]['response_implemented'])]

        if partial:
            report.append(f"\n{resource}:")
            for endpoint, details in sorted(partial):
                partial_count += 1
                method, path = endpoint.split(' ', 1)
                req_status = "✓" if details['request_implemented'] else "✗"
                res_status = "✓" if details['response_implemented'] else "✗"
                report.append(f"  {req_status} Request | {res_status} Response | {method:6s} {path:50s} [{details['operationId']}]")

    report.append(f"\nTotal Partially Implemented: {partial_count}")
    report.append("")
    report.append("")

    # Not implemented endpoints
    report.append("NOT IMPLEMENTED ENDPOINTS")
    report.append("-" * 100)
    not_impl_count = 0
    for resource in sorted(endpoints_by_resource.keys()):
        endpoints = endpoints_by_resource[resource]
        not_impl = [ep for ep in endpoints if not ep[1]['implemented'] and not ep[1]['request_implemented'] and not ep[1]['response_implemented']]

        if not_impl:
            report.append(f"\n{resource}:")
            for endpoint, details in sorted(not_impl):
                not_impl_count += 1
                method, path = endpoint.split(' ', 1)
                report.append(f"  ✗ {method:6s} {path:50s} [{details['operationId']}]")

    report.append(f"\nTotal Not Implemented: {not_impl_count}")
    report.append("")
    report.append("")

    # Schema coverage
    report.append("SCHEMA/DTO MAPPING")
    report.append("-" * 100)

    implemented_schemas = []
    not_implemented_schemas = []

    for schema_name, schema_info in sorted(coverage_data['schemas'].items()):
        if schema_info['implemented']:
            implemented_schemas.append((schema_name, schema_info))
        else:
            not_implemented_schemas.append((schema_name, schema_info))

    report.append(f"\nImplemented Schemas ({len(implemented_schemas)}):")
    for schema_name, schema_info in implemented_schemas:
        report.append(f"  ✓ {schema_name:40s} → {schema_info['php_class']}")

    report.append(f"\nNot Implemented Schemas ({len(not_implemented_schemas)}):")
    for schema_name, schema_info in not_implemented_schemas[:50]:  # Limit to first 50
        report.append(f"  ✗ {schema_name}")

    if len(not_implemented_schemas) > 50:
        report.append(f"  ... and {len(not_implemented_schemas) - 50} more")

    report.append("")
    report.append("=" * 100)

    return "\n".join(report)

def generate_validation_summary(validation_data):
    """Generate a summary of validation rules."""

    report = []
    report.append("")
    report.append("=" * 100)
    report.append("VALIDATION RULES SUMMARY")
    report.append("=" * 100)
    report.append("")

    total_classes = len(validation_data)
    total_fields = sum(len([k for k in v.keys() if not k.startswith('__')]) for v in validation_data.values())

    report.append(f"Total Classes with Validation Rules: {total_classes}")
    report.append(f"Total Fields with Validation Rules: {total_fields}")
    report.append("")

    # Count validation types
    validation_types = defaultdict(int)

    for class_name, fields in validation_data.items():
        for field_name, rules in fields.items():
            if field_name.startswith('__'):
                continue

            for rule_type in rules.keys():
                if rule_type not in ['description', 'type', '$ref', 'oneOf', 'anyOf', 'allOf']:
                    validation_types[rule_type] += 1

    report.append("Validation Rule Types Used:")
    for rule_type, count in sorted(validation_types.items(), key=lambda x: x[1], reverse=True):
        report.append(f"  {rule_type:20s}: {count:4d} occurrences")

    report.append("")
    report.append("Sample Validation Rules by DTO:")
    report.append("-" * 100)

    # Show sample for implemented DTOs only
    for class_name, fields in sorted(validation_data.items()):
        if not class_name.startswith("OpenAPI::"):
            report.append(f"\n{class_name}:")

            field_count = 0
            for field_name, rules in sorted(fields.items()):
                if field_name.startswith('__'):
                    continue

                field_count += 1
                if field_count > 5:  # Limit to first 5 fields per class
                    remaining = len([k for k in fields.keys() if not k.startswith('__')]) - 5
                    report.append(f"  ... and {remaining} more fields")
                    break

                rule_summary = []
                if 'type' in rules:
                    rule_summary.append(f"type={rules['type']}")
                if rules.get('required'):
                    rule_summary.append("required")
                if 'maxLength' in rules:
                    rule_summary.append(f"maxLength={rules['maxLength']}")
                if 'minLength' in rules:
                    rule_summary.append(f"minLength={rules['minLength']}")
                if 'pattern' in rules:
                    rule_summary.append(f"pattern={rules['pattern'][:30]}...")
                if 'enum' in rules:
                    rule_summary.append(f"enum={len(rules['enum'])} values")
                if 'minimum' in rules:
                    rule_summary.append(f"min={rules['minimum']}")
                if 'maximum' in rules:
                    rule_summary.append(f"max={rules['maximum']}")
                if 'format' in rules:
                    rule_summary.append(f"format={rules['format']}")

                report.append(f"  • {field_name:30s} [{', '.join(rule_summary)}]")

    report.append("")
    report.append("=" * 100)

    return "\n".join(report)

def main():
    base_path = '/home/user/elavon-ept-psr7/analysis_output'

    # Load data
    coverage_data = load_json(os.path.join(base_path, 'coverage_analysis.json'))
    validation_data = load_json(os.path.join(base_path, 'validation_rules.json'))

    # Generate reports
    coverage_report = generate_coverage_report(coverage_data)
    validation_summary = generate_validation_summary(validation_data)

    # Print to console
    print(coverage_report)
    print(validation_summary)

    # Save to file
    report_file = os.path.join(base_path, 'comprehensive_report.txt')
    with open(report_file, 'w') as f:
        f.write(coverage_report)
        f.write("\n\n")
        f.write(validation_summary)

    print(f"\n\nFull report saved to: {report_file}")

if __name__ == '__main__':
    main()
