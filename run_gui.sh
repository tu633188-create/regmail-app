#!/bin/bash

# RegMail GUI Launcher
echo "Starting RegMail GUI Client..."

# Check if Python 3.9 is installed
if ! command -v python3.9 &> /dev/null; then
    echo "Error: Python 3.9 is not installed"
    echo "Please install Python 3.9: brew install python@3.9"
    exit 1
fi

# Install requirements if needed
if [ -f "requirements.txt" ]; then
    echo "Installing Python dependencies..."
    python3.9 -m pip install -r requirements.txt
fi

# Run the GUI
echo "Launching RegMail GUI..."
python3.9 regmail_gui.py
