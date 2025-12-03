# Orion: Starter Kit for Laravel MoonShine 🚀

**Orion** is a modular starter project that speeds up the development of admin panels in Laravel using [MoonShine](https://moonshine-laravel.com/) as the admin framework.

![Screenshot](./_docs/image.png)

## 📦 Main Technologies

| Package                     | Version | Description                  |
| --------------------------- | ------- | ---------------------------- |
| Laravel                     | v12     | Core PHP framework           |
| MoonShine                   | v4      | Admin panel                  |
| moonshine-roles-permissions | v4      | Roles and permissions system |
| internachi/modular          | v2      | Modular architecture         |

## ✨ Key Features

### 🛠 Base Configuration

-   Fully pre-configured MoonShine
-   Ready-to-use modular architecture
-   WithProperties - Trait for applying chainable properties to resources  
-   WithSoftDeletes - Trait for implementing soft deletes to resources
-   WithTrashedQuery - Trait for implementing query with trashed resources

### 🔐 Security

-   Integrated RBAC (Roles and Permissions) system
-   Command for automatic permission generation

Permissions are automatically generated using the [`LaunchPermissions`](app-modules/moon-launch/src/Console/Commands/LaunchPermissions.php) command. This command scans the **registered MoonShine resources** and creates the necessary permissions automatically.

### 🎨 Interface

-   Support for both English and Spanish


## 🚀 Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/maycolmunoz/orion.git
    cd orion
    ```

2. Set up the environment:

    ```bash
    cp .env.example .env
    composer install
    ```

3. Run the installer:
    ```bash
    php artisan launch:install
    ```

The installer will automatically:

-   Generate the application key
-   Run database migrations
-   Create permissions and the superadmin role
-   Create the initial user

---

📘 **Additional Documentation**:

-   [moonshine](https://moonshine-laravel.com/docs)
-   [moonshine-roles-permissions](https://github.com/SWEET1S/moonshine-roles-permissions/)
-   [modular](https://github.com/InterNACHI/modular)
