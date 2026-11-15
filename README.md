# Hospital Patient Registration System

A web app for registering first-time hospital patients, with a staff admin area.

## Technologies
HTML5, CSS3, JavaScript, PHP 8, MySQL (XAMPP)

## Features
- Patient sign-up with JavaScript validation and referral letter upload (with image preview)
- Secure login with hashed passwords and sessions
- Patient card showing personal details and referral letter
- Staff admin page: search, edit and delete patients
- Forgot-password flow with one-time, expiring reset links
- Role-based access (patient / staff)

## Setup
1. Install XAMPP and start **Apache** and **MySQL**.
2. Copy the `hospital` folder to `C:\xampp\htdocs\`.
3. Open `http://localhost/phpmyadmin`, create a database named `hospital_db`, and **Import** `hospital_db.sql`.
4. Make sure the `uploads` folder exists inside `hospital`.
5. Open `http://localhost/hospital/signup.html`.

## Creating a staff account
Register a normal account on signup.html, then in phpMyAdmin run:
UPDATE patients SET role = 'staff' WHERE email = 'your-email@example.com';

## File overview
| File | Purpose |
|---|---|
| signup.html, script.js | Sign-up form and validation |
| register.php | Validates and saves a patient |
| login.html, login.php | Login |
| welcome.php | Patient card |
| admin.php, edit_patient.php, delete_patient.php | Staff tools |
| forgot_password.php, reset_password.php | Password reset |
| db.php | Database connection |
| style.css, toggle.js | Styling and show-password button |

## Security measures
Prepared statements (SQL injection), `password_hash` (password storage), `htmlspecialchars` (XSS),
session role checks, CSRF token on delete/edit, file type and size checks, random file names,
single-use expiring reset tokens.

## Known limitations
Reset links are shown on screen in demo mode (a real system emails them). Uploaded files can be opened by anyone
who knows the URL; a production system would store them outside the web root.