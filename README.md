# AdminHub – Business Management & Billing System

A complete web management platform for service-based businesses that includes authentication with Google, appointment scheduling, invoicing, inventory control, sales tracking, and administrative dashboards.

This project was built following an MVC architecture and is designed as a professional portfolio demo showcasing full-stack web development for real business use cases.

---

## 🚀 Features

- Email & password authentication  
- Google OAuth login  
- User registration & password recovery  
- Admin dashboard with KPIs  
- Appointment scheduling (Citas)  
- Client management  
- Services catalog  
- Inventory control  
- Sales module  
- Reports & analytics  
- Electronic invoice creation  
- Role-based admin panel  
- Responsive modern UI  

---

## 🧰 Tech Stack

- PHP (MVC Architecture)  
- MySQL  
- HTML / CSS  
- JavaScript  
- jQuery  
- Bootstrap  
- Google OAuth  
- Apache (XAMPP / Laragon)

---

## 🏗️ Architecture

adminhub/
├── auth/                 # Authentication & OAuth
├── config/               # App configuration
├── controller/           # Main controllers
├── dashboard/
│   ├── controller/       # Admin module controllers
│   ├── css/
│   ├── js/
│   └── views/
├── css/                  # Global styles
├── js/                   # Global JS
├── views/                # Public views
├── documents/            # Generated invoices / PDFs
├── vendor/               # Composer dependencies
├── database.sql          # DB schema
├── composer.json
├── composer.lock
├── index.html            # Login UI
├── index2.html           # Register UI
└── README.md


This project follows the MVC pattern:

- **Models** – Database access and business logic  
- **Views** – UI templates and components  
- **Controllers** – Request handling and workflows  

---

## 📁 Project Structure


---

## ⚙️ Installation

1. Clone the repository  
2. Place the project inside your web server root (htdocs or www)  
3. Create a database:

adminhub


4. Import SQL from:


sql/schema.sql

5. Configure database credentials in:

config/database.php

6. Configure Google OAuth credentials in:

config/google.php

7. Start Apache & MySQL  
8. Access the system:

/public

---

## 🔐 Authentication

Users can:

- Register with email  
- Log in using Google  
- Recover passwords  
- Maintain secure sessions  

---

## 📌 Business Use Case

AdminHub is ideal for service businesses such as:

- Barbershops  
- Salons  
- Clinics  
- Repair services  
- Consulting firms  

It allows owners to:

- Manage customers  
- Schedule appointments  
- Track sales  
- Generate invoices  
- Control inventory  
- View reports in real time  

---

## 📷 Screenshots

(Add screenshots of:)

- Login / Registration screen  
- Google sign-in  
- Dashboard  
- Invoice creation screen  
- Appointment module  
- Inventory panel  

---

## 📄 License

This project is for demo and portfolio purposes.
