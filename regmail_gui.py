#!/usr/bin/env python3.9
"""
RegMail GUI Client
Simple GUI application to test RegMail API endpoints
"""

import tkinter as tk
from tkinter import ttk, messagebox, scrolledtext
import requests
import json
import hashlib
import platform
import uuid
from datetime import datetime

class RegMailGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("RegMail API Client")
        self.root.geometry("800x600")
        
        # API Configuration
        self.base_url = "http://127.0.0.1:8000/api"
        self.token = None
        self.device_fingerprint = self.generate_device_fingerprint()
        
        self.setup_ui()
        
    def generate_device_fingerprint(self):
        """Generate device fingerprint using system info"""
        system_info = platform.platform() + platform.machine() + str(uuid.getnode())
        return 'device_' + hashlib.sha256(system_info.encode()).hexdigest()[:12]
    
    def setup_ui(self):
        """Setup the user interface"""
        # Create notebook for tabs
        notebook = ttk.Notebook(self.root)
        notebook.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # Login Tab
        self.setup_login_tab(notebook)
        
        # Email Submission Tab
        self.setup_email_tab(notebook)
        
        # Log Tab
        self.setup_log_tab(notebook)
        
    def setup_login_tab(self, notebook):
        """Setup login tab"""
        login_frame = ttk.Frame(notebook)
        notebook.add(login_frame, text="Login")
        
        # Login form
        ttk.Label(login_frame, text="Username:").grid(row=0, column=0, sticky=tk.W, padx=5, pady=5)
        self.username_entry = ttk.Entry(login_frame, width=30)
        self.username_entry.grid(row=0, column=1, padx=5, pady=5)
        self.username_entry.insert(0, "admin")
        
        ttk.Label(login_frame, text="Password:").grid(row=1, column=0, sticky=tk.W, padx=5, pady=5)
        self.password_entry = ttk.Entry(login_frame, width=30, show="*")
        self.password_entry.grid(row=1, column=1, padx=5, pady=5)
        self.password_entry.insert(0, "admin123")
        
        ttk.Label(login_frame, text="Device Name:").grid(row=2, column=0, sticky=tk.W, padx=5, pady=5)
        self.device_name_entry = ttk.Entry(login_frame, width=30)
        self.device_name_entry.grid(row=2, column=1, padx=5, pady=5)
        self.device_name_entry.insert(0, "Python GUI Client")
        
        # Device fingerprint display
        ttk.Label(login_frame, text="Device Fingerprint:").grid(row=3, column=0, sticky=tk.W, padx=5, pady=5)
        fingerprint_label = ttk.Label(login_frame, text=self.device_fingerprint, foreground="blue")
        fingerprint_label.grid(row=3, column=1, sticky=tk.W, padx=5, pady=5)
        
        # Login button
        login_btn = ttk.Button(login_frame, text="Login", command=self.login)
        login_btn.grid(row=4, column=1, padx=5, pady=10)
        
        # Status
        self.login_status = ttk.Label(login_frame, text="Not logged in", foreground="red")
        self.login_status.grid(row=5, column=0, columnspan=2, padx=5, pady=5)
        
    def setup_email_tab(self, notebook):
        """Setup email submission tab"""
        email_frame = ttk.Frame(notebook)
        notebook.add(email_frame, text="Email Submission")
        
        # Email form
        ttk.Label(email_frame, text="Email:").grid(row=0, column=0, sticky=tk.W, padx=5, pady=5)
        self.email_entry = ttk.Entry(email_frame, width=40)
        self.email_entry.grid(row=0, column=1, padx=5, pady=5)
        self.email_entry.insert(0, "testuser123@gmail.com")
        
        ttk.Label(email_frame, text="Recovery Email:").grid(row=1, column=0, sticky=tk.W, padx=5, pady=5)
        self.recovery_email_entry = ttk.Entry(email_frame, width=40)
        self.recovery_email_entry.grid(row=1, column=1, padx=5, pady=5)
        self.recovery_email_entry.insert(0, "recovery123@gmail.com")
        
        ttk.Label(email_frame, text="Password:").grid(row=2, column=0, sticky=tk.W, padx=5, pady=5)
        self.email_password_entry = ttk.Entry(email_frame, width=40, show="*")
        self.email_password_entry.grid(row=2, column=1, padx=5, pady=5)
        self.email_password_entry.insert(0, "SecurePass123!")
        
        ttk.Label(email_frame, text="Registration Time (seconds):").grid(row=3, column=0, sticky=tk.W, padx=5, pady=5)
        self.reg_time_entry = ttk.Entry(email_frame, width=40)
        self.reg_time_entry.grid(row=3, column=1, padx=5, pady=5)
        self.reg_time_entry.insert(0, "3600")
        
        # Proxy info
        ttk.Label(email_frame, text="Proxy IP:").grid(row=4, column=0, sticky=tk.W, padx=5, pady=5)
        self.proxy_ip_entry = ttk.Entry(email_frame, width=40)
        self.proxy_ip_entry.grid(row=4, column=1, padx=5, pady=5)
        self.proxy_ip_entry.insert(0, "192.168.1.100")
        
        ttk.Label(email_frame, text="Proxy Port:").grid(row=5, column=0, sticky=tk.W, padx=5, pady=5)
        self.proxy_port_entry = ttk.Entry(email_frame, width=40)
        self.proxy_port_entry.grid(row=5, column=1, padx=5, pady=5)
        self.proxy_port_entry.insert(0, "8080")
        
        # Submit button
        submit_btn = ttk.Button(email_frame, text="Submit Email", command=self.submit_email)
        submit_btn.grid(row=6, column=1, padx=5, pady=10)
        
        # Status
        self.email_status = ttk.Label(email_frame, text="Ready to submit", foreground="blue")
        self.email_status.grid(row=7, column=0, columnspan=2, padx=5, pady=5)
        
    def setup_log_tab(self, notebook):
        """Setup log tab"""
        log_frame = ttk.Frame(notebook)
        notebook.add(log_frame, text="Log")
        
        # Log text area
        self.log_text = scrolledtext.ScrolledText(log_frame, height=20, width=80)
        self.log_text.pack(fill=tk.BOTH, expand=True, padx=5, pady=5)
        
        # Clear button
        clear_btn = ttk.Button(log_frame, text="Clear Log", command=self.clear_log)
        clear_btn.pack(pady=5)
        
    def log_message(self, message):
        """Add message to log"""
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        log_text = f"[{timestamp}] {message}"
        print(log_text)  # Print to terminal
        self.log_text.insert(tk.END, f"{log_text}\n")
        self.log_text.see(tk.END)
        
    def clear_log(self):
        """Clear log text"""
        self.log_text.delete(1.0, tk.END)
        
    def login(self):
        """Login to API"""
        username = self.username_entry.get()
        password = self.password_entry.get()
        device_name = self.device_name_entry.get()
        
        if not username or not password:
            messagebox.showerror("Error", "Please enter username and password")
            return
            
        self.log_message(f"Attempting login for user: {username}")
        
        try:
            response = requests.post(
                f"{self.base_url}/auth/login",
                json={
                    "username": username,
                    "password": password,
                    "device_name": device_name,
                    "device_fingerprint": self.device_fingerprint
                },
                headers={
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                }
            )
            
            if response.status_code == 200:
                data = response.json()
                if data.get("success"):
                    self.token = data["data"]["token"]
                    self.login_status.config(text="Logged in successfully", foreground="green")
                    self.log_message(f"Login successful! Token: {self.token[:20]}...")
                    self.log_message(f"Device info: {data['data'].get('device', {})}")
                else:
                    self.login_status.config(text="Login failed", foreground="red")
                    self.log_message(f"Login failed: {data.get('message', 'Unknown error')}")
            else:
                self.login_status.config(text="Login failed", foreground="red")
                self.log_message(f"Login failed with status {response.status_code}: {response.text}")
                
        except requests.exceptions.RequestException as e:
            self.login_status.config(text="Connection error", foreground="red")
            self.log_message(f"Connection error: {str(e)}")
            
    def submit_email(self):
        """Submit email registration"""
        if not self.token:
            messagebox.showerror("Error", "Please login first")
            return
            
        email = self.email_entry.get()
        recovery_email = self.recovery_email_entry.get()
        password = self.email_password_entry.get()
        reg_time = self.reg_time_entry.get()
        proxy_ip = self.proxy_ip_entry.get()
        proxy_port = self.proxy_port_entry.get()
        
        if not email or not password:
            messagebox.showerror("Error", "Please enter email and password")
            return
            
        self.log_message(f"Submitting email: {email}")
        
        try:
            # Convert registration time to integer
            reg_time_seconds = int(reg_time) if reg_time else 0
            
            response = requests.post(
                f"{self.base_url}/email/submit",
                json={
                    "email": email,
                    "recovery_email": recovery_email if recovery_email else None,
                    "password": password,
                    "registration_time": reg_time_seconds,
                    "device_fingerprint": self.device_fingerprint,
                    "proxy_info": {
                        "ip": proxy_ip,
                        "port": int(proxy_port) if proxy_port else 8080,
                        "username": None,
                        "password": None
                    },
                    "metadata": {
                        "user_agent": "RegMail Python GUI Client",
                        "creation_time": datetime.now().isoformat(),
                        "verification_status": "verified",
                        "notes": "Submitted via Python GUI"
                    }
                },
                headers={
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": f"Bearer {self.token}"
                }
            )
            
            if response.status_code == 200:
                data = response.json()
                if data.get("success"):
                    self.email_status.config(text="Email submitted successfully", foreground="green")
                    self.log_message(f"Email submitted successfully! ID: {data['data'].get('registration_id')}")
                    self.log_message(f"Registration time: {data['data'].get('registration_time')} seconds")
                else:
                    self.email_status.config(text="Submission failed", foreground="red")
                    self.log_message(f"Submission failed: {data.get('message', 'Unknown error')}")
            else:
                self.email_status.config(text="Submission failed", foreground="red")
                self.log_message(f"Submission failed with status {response.status_code}: {response.text}")
                
        except requests.exceptions.RequestException as e:
            self.email_status.config(text="Connection error", foreground="red")
            self.log_message(f"Connection error: {str(e)}")
        except ValueError as e:
            self.email_status.config(text="Invalid input", foreground="red")
            self.log_message(f"Invalid input: {str(e)}")

def main():
    root = tk.Tk()
    app = RegMailGUI(root)
    root.mainloop()

if __name__ == "__main__":
    main()
