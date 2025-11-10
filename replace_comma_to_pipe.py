#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Script to replace all commas (,) with pipes (|) in account.txt file
"""

import sys
import os

def replace_comma_to_pipe(input_file, output_file=None):
    """
    Replace all commas with pipes in the file
    
    Args:
        input_file: Path to input file
        output_file: Path to output file (if None, overwrites input file)
    """
    if not os.path.exists(input_file):
        print(f"Error: File '{input_file}' not found!")
        return False
    
    try:
        # Read the file
        print(f"Reading file: {input_file}")
        with open(input_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Count commas before replacement
        comma_count = content.count(',')
        print(f"Found {comma_count:,} commas to replace")
        
        # Replace all commas with pipes
        new_content = content.replace(',', '|')
        
        # Determine output file
        if output_file is None:
            output_file = input_file
            # Create backup
            backup_file = input_file + '.backup'
            print(f"Creating backup: {backup_file}")
            with open(backup_file, 'w', encoding='utf-8') as f:
                f.write(content)
        
        # Write the new content
        print(f"Writing to: {output_file}")
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print(f"✅ Successfully replaced {comma_count:,} commas with pipes!")
        print(f"Output file: {output_file}")
        if output_file == input_file:
            print(f"Backup saved as: {input_file}.backup")
        
        return True
        
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

if __name__ == "__main__":
    input_file = "account.txt"
    
    # Check if output file is provided as argument
    output_file = None
    if len(sys.argv) > 1:
        output_file = sys.argv[1]
    
    # Confirm before proceeding
    print("=" * 60)
    print("Replace Comma to Pipe Script")
    print("=" * 60)
    print(f"Input file: {input_file}")
    if output_file:
        print(f"Output file: {output_file}")
    else:
        print(f"Output file: {input_file} (will overwrite, backup will be created)")
    print("=" * 60)
    
    response = input("\nProceed? (yes/no): ").strip().lower()
    if response not in ['yes', 'y']:
        print("Cancelled.")
        sys.exit(0)
    
    # Execute replacement
    success = replace_comma_to_pipe(input_file, output_file)
    
    if success:
        print("\n✅ Done!")
    else:
        print("\n❌ Failed!")
        sys.exit(1)

