# ✅ Laravel Task Manager API

A RESTful API built with Laravel 10.x for managing tasks with full CRUD , file upload support, and secure authentication using Laravel Sanctum.

---

## 📦 Features

- 🛠️ Laravel 10.x with modular architecture (Service Layer + Form Requests + Resources)
- 🔐 Token-based API authentication with Laravel Sanctum
- 📂 File upload and storage using `ManageFileTrait`
- 🧾 Task assignment between users
- 📑 Validation with custom `apiResponse` structure
- 🧪 Unit Tests for service logic
- 🌐 Pagination & eager loading of relations

---

## ⚙️ Requirements

- PHP >= 8.1
- Composer
- Laravel 10.x
- MySQL / PostgreSQL
- Laravel Sanctum

---

## 🚀 Installation & Setup

```bash
git clone https://github.com/your-username/task-api.git
cd task-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve

🔐 Authentication Routes
Method | Endpoint | Description
POST | /register | Register new user
POST | /login | Login user
GET | /logout | Logout user
GET | /profile | View user profile
💡 All routes except /register & /login require a Bearer Token in the Authorization header.


📋 Task API Routes
Method | Endpoint | Description
GET | /tasks | Get all tasks (paginated)
GET | /tasks/{id} | Get single task by ID
POST | /tasks | Create a new task
PUT | /tasks/{id} | Fully update a task
PATCH | /tasks/{id} | Partially update a task
DELETE | /tasks/{id} | Soft delete a task
GET | /tasks/user/created | Tasks created by authenticated user
GET | /tasks/user/assigned | Tasks assigned to authenticated user

🧾 Example API Response
GET | /tasks/{id} | Get single task by ID
{
    "status": 200,
    "data": {
        "id": 50,
        "name": "Rerum veritatis ut dolores et consequatur.",
        "description": "Sit aut autem placeat officiis nemo velit in. Debitis non placeat enim quos amet eius laudantium. Sunt nesciunt id provident quas qui.",
        "status": "in_progress",
        "deadline": "2025-05-19",
        "assign_to": {
            "id": 2,
            "name": "junior",
            "email": "junior@gmail.com",
            "phone": "01554287290",
            "image": null
        },
        "image": null,
        "created_at": "2025-04-22",
        "created_by": {
            "id": 1,
            "name": "teamlead",
            "email": "teamlead@gmail.com",
            "phone": "0115648976",
            "image": null
        },
        "updated_at": "2025-04-22"
    },
    "message": "Task Returned Successfully"
}



✅ Custom Response Format (apiResponse())
{
  "status": 200,
  "data": {},
  "message": "message for help the user for understand the returned"
}

🧪 Testing
php artisan test --filter TaskServiceTest
