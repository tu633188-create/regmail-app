#!/bin/bash

# RegMail API Test GUI Runner
echo "🚀 Starting RegMail API Test GUI..."

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 is not installed. Please install Python3 first."
    exit 1
fi

# Check if requests is installed
python3 -c "import requests" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "📦 Installing required packages..."
    pip3 install -r gui_requirements.txt
fi

# Run the GUI
echo "🎯 Launching GUI..."
python3 api_test_gui.py
