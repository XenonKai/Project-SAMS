# Project-SAMS
Student Attendance Management System

## Demo accounts

Run the following command from the project directory after configuring the database connection:

```bash
php seed_demo_users.php
```

All demo accounts use the password `1029384756`:

| Role | Phone | Email |
| --- | --- | --- |
| Admin | `09161196693` | — |
| Student | `09123789456` | `ivanchristmas@gmail.com` |
| Faculty | `09456789123` | `laosna@gmail.com` |

Log in with either the phone number or email address. The seeder creates missing accounts and repairs existing demo accounts by resetting their password and `Registered` status.
