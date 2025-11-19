#!/usr/bin/env python3
"""
Extracts validation rules for only implemented DTOs.
"""

import json
import os

def main():
    base_path = '/home/user/elavon-ept-psr7/analysis_output'

    # Load full validation rules
    with open(os.path.join(base_path, 'validation_rules.json'), 'r') as f:
        all_rules = json.load(f)

    # Filter to only implemented DTOs (not OpenAPI::*)
    implemented_rules = {
        class_name: rules
        for class_name, rules in all_rules.items()
        if not class_name.startswith('OpenAPI::')
    }

    # Save filtered rules
    output_file = os.path.join(base_path, 'validation_rules_implemented_only.json')
    with open(output_file, 'w') as f:
        json.dump(implemented_rules, f, indent=2)

    print(f"Extracted validation rules for {len(implemented_rules)} implemented DTOs")
    print(f"Saved to: {output_file}")

    # Print summary
    print("\nImplemented DTOs with validation rules:")
    for class_name in sorted(implemented_rules.keys()):
        field_count = len([k for k in implemented_rules[class_name].keys() if not k.startswith('__')])
        short_name = class_name.split('\\')[-1]
        print(f"  - {short_name:30s} ({field_count} fields)")

if __name__ == '__main__':
    main()
