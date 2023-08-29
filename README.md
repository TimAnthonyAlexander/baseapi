# 🚀 BaseAPI: The Framework-less PHP API Solution 🚀

Welcome to **BaseAPI**, a sleek, flexible, and powerful framework-less PHP API project.
Whether you're setting up a small backend service or scaling up to a larger system, BaseAPI is designed to make the process smooth and efficient.

---

## 🌟 Features

### 🖥️ Seamless Server Support
BaseAPI provides seamless integration with **NGINX** and **PHP-Server** ensuring your API has a robust and optimized environment to run on.

### 📦 Controllers & Routing Capabilities
Effortlessly manage your application's logic and endpoints with our intuitive controllers and routing system.

### 📑 OpenAPI Documentation & TypeScript Models
- **Auto-generated OpenAPI documentation** based on all your controllers.
- **Auto-generate TypeScript models** from all controllers and response models, ensuring your frontend and backend remain in sync.

### 🗃️ Entity Management & Migrations
- Define and manage your data structures with our **Entities** system.
- Keep track and execute **migrations** with ease using our **Migration Tracker**.
- Use the **Migration Generator** to automatically generate migrations based on entity model properties. 

### ✉ Mailing System
- Send **instant mails** or queue them for later.
- **QueueWorker** ensures your queued mails are processed efficiently.

### 🔒 UserModel & File Uploads
- A pre-defined **UserModel** to kickstart your user management.
- Handle **file uploads** with ease using our FileModel system.

### 🌍 Translations & Multi-language Support
Bring your API to an international audience with our easy-to-use translation system.

### 🛠️ And Many More!
- **QueryBuilder** for efficient database queries.
- **Dependency Injection** for better code organization.
- **EnvService**: Differentiate between Dev, Integration, Staging, and Production systems.
- **ParameterTools**: Ensure the incoming data is clean and sanitized.
- **Profiler**: Analyze and optimize your method runtimes.
- **MockAPI**: Test your controllers with ease.
- **DBCache**: Store and retrieve serialized data from the database.
- **UriService**: A helper service for URL construction.

---

## 🚀 Getting Started

1. **Clone the Repository**

    ```bash
    git clone https://github.com/TimAnthonyAlexander/baseapi.git
    ```

2. **Set Up Your Environment**

    EnvService detects the environment. Write your current environment in the ignored config/system.json file.

3. **Kickstart Your First Entity**

    Extend on EntityModel, define your properties, and use the Migration Generator to generate your first migration.

---

## 💪 Contributing

Your contributions are always welcome! Please fork, create a pull request against main.

---

## 📜 License

This project is licensed under the MIT License.
