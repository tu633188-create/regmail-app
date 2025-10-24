# RegMail GUI Client

A simple Python GUI application to test RegMail API endpoints.

## Features

- **Login Tab**: Authenticate with the RegMail API
- **Email Submission Tab**: Submit email registration data
- **Log Tab**: View API request/response logs
- **Device Fingerprinting**: Automatic device fingerprint generation

## Requirements

- Python 3.6+
- tkinter (usually included with Python)
- requests library

## Installation

1. Install Python dependencies:
```bash
pip3 install -r requirements.txt
```

## Usage

### Option 1: Using the launcher script
```bash
./run_gui.sh
```

### Option 2: Direct execution
```bash
python3 regmail_gui.py
```

## How to Use

1. **Start the Laravel server** (if not already running):
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

2. **Launch the GUI**:
```bash
python3 regmail_gui.py
```

3. **Login**:
   - Enter username: `admin`
   - Enter password: `admin123`
   - Device name will be auto-filled
   - Click "Login"

4. **Submit Email**:
   - Fill in email details
   - Set registration time (in seconds)
   - Configure proxy settings
   - Click "Submit Email"

5. **View Logs**:
   - Switch to "Log" tab to see API requests/responses

## Device Fingerprinting

The GUI automatically generates a device fingerprint using:
- Platform information
- Machine architecture
- MAC address

This fingerprint is used to track which device registered each email.

## API Endpoints Used

- `POST /api/auth/login` - User authentication
- `POST /api/email/submit` - Email registration submission

## Troubleshooting

- **Connection Error**: Make sure the Laravel server is running on `http://127.0.0.1:8000`
- **Login Failed**: Check username/password (default: admin/admin123)
- **Submission Failed**: Ensure you're logged in first

## Example Usage Flow

1. Start Laravel server: `php artisan serve`
2. Run GUI: `python3 regmail_gui.py`
3. Login with admin credentials
4. Submit test email with 1 hour registration time
5. Check Filament admin panel to see the submitted data
