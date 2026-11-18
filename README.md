# Resume & Job Application Tracker

A web-based Resume and Job Application Tracker developed using PHP, MySQL, HTML, CSS, and JavaScript. The application helps users organize resumes, track job applications, manage interviews and offers, and stay updated with reminders and notifications.

## Features

- User registration and login
- User profile management
- Resume management and uploads
- Skills management
- Job browsing and applications
- Application status tracking and history
- Interview scheduling and tracking
- Offer management
- Reminders and notifications
- Admin dashboard
- Activity logs

## Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Development Environment:** XAMPP
- **Version Control:** Git and GitHub

## Requirements

Install the following before running the project:

- [XAMPP](https://www.apachefriends.org/)
- [Git](https://git-scm.com/)
- [MySQL Workbench](https://dev.mysql.com/downloads/workbench/)

## Installation

### 1. Clone the repository

Open a terminal and run:

```bash
git clone https://github.com/aryanadelker/resume-job-tracker.git
```

### 2. Move the project into XAMPP

Place the cloned `resume-job-tracker` folder inside:

```text
C:\xampp\htdocs\
```

### 3. Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL (if using XAMPP's MySQL server)

Make sure the project uses the same MySQL server configured in its database connection settings.

### 4. Set up the database

1. Open MySQL Workbench.
2. Connect to your MySQL server.
3. Create a database named `jobtrackerdb`.
4. Import the project's SQL database export once it is added to the repository.

### 5. Configure the database connection

The database configuration file is:

```text
config/db.php
```

This file is excluded from Git because it may contain local database credentials. Create your own local copy and configure the database host, username, password, and database name to match your MySQL setup.

### 6. Run the application

Open your browser and visit:

```text
http://localhost/resume-job-tracker/
```

## Project Structure

```text
resume-job-tracker/
│
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
│
├── config/
│   └── db.php
│
├── database/
├── includes/
├── uploads/
│
├── admin_dashboard.php
├── activity_logs.php
├── application_history.php
├── apply_job.php
├── dashboard.php
├── index.php
├── interviews.php
├── jobs.php
├── login.php
├── register.php
├── resume.php
└── README.md
```

## Team Collaboration

This project is maintained collaboratively using GitHub.

Before starting work, pull the latest changes:

```bash
git pull origin main
```

After making changes:

```bash
git add .
git commit -m "Describe your changes"
git push origin main
```

Coordinate with teammates before editing the same files to reduce merge conflicts.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.