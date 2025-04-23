# 📊 PHP-MySQL Employee Timesheet System

A simple and responsive **Employee Timesheet Web App** built using **PHP** and **MySQL**. This application helps track daily work logs submitted by employees, with full control via the admin dashboard. Designed for digital departments, it includes an "Assigned By" feature restricted to HODs (**Santosh** and **Dharmendar**).

---

## 🚀 Features

### 👤 Employee Panel
- Secure login
- Submit daily timesheet entries
- Auto-calculate total hours
- View own work history

### 🛠️ Admin Panel
- Admin login
- View all timesheets
- Filter by employee, date range, or HOD
- Edit/Delete entries
- Export reports (CSV/Excel)
- Manage employees (Add/Edit/Delete)

---

## 🗃️ Database Structure

### Database: `timesheet_system`

#### Table: `employees`
| Field        | Type         | Notes              |
|--------------|--------------|--------------------|
| id           | INT (AI, PK) |                    |
| name         | VARCHAR(100) |                    |
| email        | VARCHAR(100) | Unique             |
| password     | VARCHAR(255) | Hashed password    |
| role         | ENUM         | 'admin' or 'user'  |

#### Table: `timesheets`
| Field        | Type         | Notes              |
|--------------|--------------|--------------------|
| id           | INT (AI, PK) |                    |
| employee_id  | INT          | Foreign key        |
| date         | DATE         | Work date          |
| task_name    | VARCHAR(255) | Task title         |
| description  | TEXT         | Work summary       |
| start_time   | TIME         |                    |
| end_time     | TIME         |                    |
| total_hours  | FLOAT        | Auto-calculated    |
| assigned_by  | VARCHAR(50)  | Santosh/Dharmendar|

---

## 🧑‍💻 Admin Login
admin@example.com
admin123
