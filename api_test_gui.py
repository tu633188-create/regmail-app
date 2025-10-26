#!/usr/bin/env python3
"""
RegMail API Test GUI
Simple GUI application to test RegMail API login functionality
"""

import tkinter as tk
from tkinter import ttk, scrolledtext, messagebox
import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry
import json
import hashlib
import platform
import uuid
from datetime import datetime
import urllib3


class RegMailAPITester:
    def __init__(self, root):
        self.root = root
        self.root.title("RegMail API Tester")
        self.root.geometry("800x600")
        self.root.configure(bg='#f0f0f0')
        
        # API Configuration
        self.api_base_url = "https://trananhtu.vn/api"
        self.jwt_token = None
        
        # Create session with retry strategy
        self.session = self.create_session()
        
        self.setup_ui()
        
    def setup_ui(self):
        """Setup the user interface"""
        # Main frame
        main_frame = ttk.Frame(self.root, padding="10")
        main_frame.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Configure grid weights
        self.root.columnconfigure(0, weight=1)
        self.root.rowconfigure(0, weight=1)
        main_frame.columnconfigure(1, weight=1)
        
        # Title
        title_label = ttk.Label(main_frame, text="RegMail API Tester", 
                               font=('Arial', 16, 'bold'))
        title_label.grid(row=0, column=0, columnspan=2, pady=(0, 20))
        
        # Login Section
        login_frame = ttk.LabelFrame(main_frame, text="Login", padding="10")
        login_frame.grid(row=1, column=0, columnspan=2, sticky=(tk.W, tk.E), pady=(0, 10))
        login_frame.columnconfigure(1, weight=1)
        
        # Username
        ttk.Label(login_frame, text="Username:").grid(row=0, column=0, sticky=tk.W, pady=2)
        self.username_var = tk.StringVar(value="admin")
        username_entry = ttk.Entry(login_frame, textvariable=self.username_var, width=30)
        username_entry.grid(row=0, column=1, sticky=(tk.W, tk.E), pady=2, padx=(10, 0))
        
        # Password
        ttk.Label(login_frame, text="Password:").grid(row=1, column=0, sticky=tk.W, pady=2)
        self.password_var = tk.StringVar(value="admin123")
        password_entry = ttk.Entry(login_frame, textvariable=self.password_var, show="*", width=30)
        password_entry.grid(row=1, column=1, sticky=(tk.W, tk.E), pady=2, padx=(10, 0))
        
        # Device Name
        ttk.Label(login_frame, text="Device Name:").grid(row=2, column=0, sticky=tk.W, pady=2)
        self.device_name_var = tk.StringVar(value="Test Device")
        device_name_entry = ttk.Entry(login_frame, textvariable=self.device_name_var, width=30)
        device_name_entry.grid(row=2, column=1, sticky=(tk.W, tk.E), pady=2, padx=(10, 0))
        
        # Device Fingerprint
        ttk.Label(login_frame, text="Device Fingerprint:").grid(row=3, column=0, sticky=tk.W, pady=2)
        fingerprint_frame = ttk.Frame(login_frame)
        fingerprint_frame.grid(row=3, column=1, sticky=(tk.W, tk.E), pady=2, padx=(10, 0))
        fingerprint_frame.columnconfigure(0, weight=1)
        
        self.device_fingerprint_var = tk.StringVar()
        fingerprint_entry = ttk.Entry(fingerprint_frame, textvariable=self.device_fingerprint_var, width=25)
        fingerprint_entry.grid(row=0, column=0, sticky=(tk.W, tk.E))
        
        # Generate fingerprint button
        ttk.Button(fingerprint_frame, text="Generate", 
                  command=self.generate_fingerprint).grid(row=0, column=1, padx=(5, 0))
        
        # Login buttons
        button_frame = ttk.Frame(login_frame)
        button_frame.grid(row=4, column=0, columnspan=2, pady=10)
        
        ttk.Button(button_frame, text="Test Connection", 
                  command=self.test_connection).pack(side=tk.LEFT, padx=(0, 10))
        ttk.Button(button_frame, text="Login", 
                  command=self.login).pack(side=tk.LEFT)
        
        # Token display
        token_frame = ttk.LabelFrame(main_frame, text="JWT Token", padding="10")
        token_frame.grid(row=2, column=0, columnspan=2, sticky=(tk.W, tk.E), pady=(0, 10))
        token_frame.columnconfigure(0, weight=1)
        
        self.token_var = tk.StringVar()
        token_entry = ttk.Entry(token_frame, textvariable=self.token_var, state="readonly", width=50)
        token_entry.grid(row=0, column=0, sticky=(tk.W, tk.E))
        
        # Copy token button
        ttk.Button(token_frame, text="Copy Token", 
                  command=self.copy_token).grid(row=0, column=1, padx=(10, 0))
        
        # Test API Section
        test_frame = ttk.LabelFrame(main_frame, text="Test API Endpoints", padding="10")
        test_frame.grid(row=3, column=0, columnspan=2, sticky=(tk.W, tk.E), pady=(0, 10))
        test_frame.columnconfigure(0, weight=1)
        
        # Test buttons
        button_frame = ttk.Frame(test_frame)
        button_frame.grid(row=0, column=0, sticky=(tk.W, tk.E), pady=(0, 10))
        
        ttk.Button(button_frame, text="Get Profile", 
                  command=self.test_profile).pack(side=tk.LEFT, padx=(0, 5))
        ttk.Button(button_frame, text="Get Quota", 
                  command=self.test_quota).pack(side=tk.LEFT, padx=5)
        ttk.Button(button_frame, text="Get Devices", 
                  command=self.test_devices).pack(side=tk.LEFT, padx=5)
        ttk.Button(button_frame, text="Validate Token", 
                  command=self.test_validate).pack(side=tk.LEFT, padx=5)
        
        # Response display
        response_frame = ttk.LabelFrame(main_frame, text="API Response", padding="10")
        response_frame.grid(row=4, column=0, columnspan=2, sticky=(tk.W, tk.E, tk.N, tk.S), pady=(0, 10))
        response_frame.columnconfigure(0, weight=1)
        response_frame.rowconfigure(0, weight=1)
        main_frame.rowconfigure(4, weight=1)
        
        self.response_text = scrolledtext.ScrolledText(response_frame, height=15, width=80)
        self.response_text.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Clear button
        ttk.Button(response_frame, text="Clear", 
                  command=self.clear_response).grid(row=1, column=0, pady=(10, 0))
        
        # Auto-generate fingerprint on startup
        self.generate_fingerprint()
    
    def create_session(self):
        """Create requests session with retry strategy"""
        session = requests.Session()
        
        # Configure retry strategy
        retry_strategy = Retry(
            total=3,
            backoff_factor=1,
            status_forcelist=[429, 500, 502, 503, 504],
            allowed_methods=["HEAD", "GET", "POST", "PUT", "DELETE", "OPTIONS", "TRACE"]
        )
        
        adapter = HTTPAdapter(max_retries=retry_strategy)
        session.mount("http://", adapter)
        session.mount("https://", adapter)
        
        # Set default headers
        session.headers.update({
            "User-Agent": "RegMail-API-Tester/1.0",
            "Accept": "application/json",
            "Connection": "keep-alive",
            "X-API-Version": "1.0.0"
        })
        
        return session
        
    def generate_fingerprint(self):
        """Generate device fingerprint"""
        try:
            system_info = platform.platform() + platform.machine() + str(uuid.getnode())
            fingerprint = 'device_' + hashlib.sha256(system_info.encode()).hexdigest()[:12]
            self.device_fingerprint_var.set(fingerprint)
        except Exception as e:
            self.device_fingerprint_var.set("device_manual_test")
            self.log_response(f"Error generating fingerprint: {str(e)}")
    
    def log_response(self, message):
        """Log message to response text area"""
        timestamp = datetime.now().strftime("%H:%M:%S")
        self.response_text.insert(tk.END, f"[{timestamp}] {message}\n")
        self.response_text.see(tk.END)
        self.root.update()
    
    def clear_response(self):
        """Clear response text area"""
        self.response_text.delete(1.0, tk.END)
    
    def copy_token(self):
        """Copy JWT token to clipboard"""
        if self.jwt_token:
            self.root.clipboard_clear()
            self.root.clipboard_append(self.jwt_token)
            self.log_response("Token copied to clipboard!")
        else:
            messagebox.showwarning("Warning", "No token available to copy!")
    
    def test_connection(self):
        """Test basic connection to server"""
        try:
            self.log_response("Testing connection to server...")
            
            # Test basic connectivity
            response = self.session.get(
                f"{self.api_base_url.replace('/api', '')}/up",
                timeout=10,
                verify=True
            )
            
            self.log_response(f"Health check status: {response.status_code}")
            
            if response.status_code == 200:
                self.log_response("✅ Server is reachable!")
                messagebox.showinfo("Connection Test", "Server is reachable!")
            else:
                self.log_response(f"⚠️ Server responded with status: {response.status_code}")
                messagebox.showwarning("Connection Test", f"Server responded with status: {response.status_code}")
                
        except requests.exceptions.RequestException as e:
            error_msg = f"Connection failed: {str(e)}"
            self.log_response(f"❌ {error_msg}")
            messagebox.showerror("Connection Test Failed", error_msg)
        except Exception as e:
            error_msg = f"Unexpected error: {str(e)}"
            self.log_response(f"❌ {error_msg}")
            messagebox.showerror("Connection Test Failed", error_msg)
    
    def login(self):
        """Perform login request"""
        try:
            self.log_response("Attempting login...")
            
            # Prepare login data
            login_data = {
                "username": self.username_var.get(),
                "password": self.password_var.get(),
                "device_name": self.device_name_var.get(),
                "device_fingerprint": self.device_fingerprint_var.get()
            }
            
            # Make login request with session
            response = self.session.post(
                f"{self.api_base_url}/auth/login",
                json=login_data,
                headers={"Content-Type": "application/json"},
                timeout=30,
                verify=True
            )
            
            # Log response
            self.log_response(f"Status Code: {response.status_code}")
            self.log_response(f"Response: {response.text}")
            
            if response.status_code == 200:
                data = response.json()
                if data.get('success') and 'data' in data and 'token' in data['data']:
                    self.jwt_token = data['data']['token']
                    self.token_var.set(self.jwt_token[:50] + "..." if len(self.jwt_token) > 50 else self.jwt_token)
                    self.log_response("✅ Login successful! Token saved.")
                    messagebox.showinfo("Success", "Login successful! Token saved.")
                else:
                    self.log_response("❌ Login failed: Invalid response format")
                    messagebox.showerror("Error", "Login failed: Invalid response format")
            else:
                self.log_response(f"❌ Login failed: HTTP {response.status_code}")
                messagebox.showerror("Error", f"Login failed: HTTP {response.status_code}")
                
        except requests.exceptions.RequestException as e:
            error_msg = f"Network error: {str(e)}"
            self.log_response(f"❌ {error_msg}")
            messagebox.showerror("Network Error", error_msg)
        except Exception as e:
            error_msg = f"Unexpected error: {str(e)}"
            self.log_response(f"❌ {error_msg}")
            messagebox.showerror("Error", error_msg)
    
    def make_authenticated_request(self, endpoint, method="GET"):
        """Make authenticated API request"""
        if not self.jwt_token:
            self.log_response("❌ No JWT token available. Please login first.")
            messagebox.showwarning("Warning", "No JWT token available. Please login first.")
            return
        
        try:
            headers = {
                "Authorization": f"Bearer {self.jwt_token}",
                "Content-Type": "application/json"
            }
            
            self.log_response(f"Making {method} request to {endpoint}...")
            
            if method == "GET":
                response = self.session.get(f"{self.api_base_url}{endpoint}", headers=headers, timeout=30, verify=True)
            elif method == "POST":
                response = self.session.post(f"{self.api_base_url}{endpoint}", headers=headers, timeout=30, verify=True)
            
            self.log_response(f"Status Code: {response.status_code}")
            self.log_response(f"Response: {response.text}")
            
            if response.status_code == 200:
                self.log_response("✅ Request successful!")
            else:
                self.log_response(f"❌ Request failed: HTTP {response.status_code}")
                
        except requests.exceptions.RequestException as e:
            error_msg = f"Network error: {str(e)}"
            self.log_response(f"❌ {error_msg}")
        except Exception as e:
            error_msg = f"Unexpected error: {str(e)}"
            self.log_response(f"❌ {error_msg}")
    
    def test_profile(self):
        """Test get profile endpoint"""
        self.make_authenticated_request("/users/profile")
    
    def test_quota(self):
        """Test get quota endpoint"""
        self.make_authenticated_request("/users/quota")
    
    def test_devices(self):
        """Test get devices endpoint"""
        self.make_authenticated_request("/auth/devices")
    
    def test_validate(self):
        """Test validate token endpoint"""
        self.make_authenticated_request("/auth/validate")


def main():
    """Main function to run the application"""
    root = tk.Tk()
    app = RegMailAPITester(root)
    
    # Center the window
    root.update_idletasks()
    x = (root.winfo_screenwidth() // 2) - (root.winfo_width() // 2)
    y = (root.winfo_screenheight() // 2) - (root.winfo_height() // 2)
    root.geometry(f"+{x}+{y}")
    
    root.mainloop()


if __name__ == "__main__":
    main()
