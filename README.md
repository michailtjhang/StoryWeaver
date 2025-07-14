# StoryWeaver

StoryWeaver is a collaborative platform for writing, sharing, and managing stories. Built on Laravel and Filament, it provides an easy-to-use interface for authors, editors, and readers. With StoryWeaver, users can write stories, review and approve drafts, and keep track of their creative journey in a structured way.

## Requirements

<a href="https://laravel.com/docs/11.x/releases"><img src="https://img.shields.io/badge/laravel-v11-blue" alt="Laravel Version"></a> <a href="https://www.php.net/releases/8.2/en.php"><img src="https://img.shields.io/badge/PHP-v8.2.4-blue" alt="PHP Version"></a> <a href="https://getcomposer.org/download/2.6.5/composer.phar"><img src="https://img.shields.io/badge/Composer-v2.6.5-brown" alt="Composer Version"></a> <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/MySQL-v8.0-orange" alt="MySQL"></a> <a href="https://filamentphp.com/"><img src="https://img.shields.io/badge/Filament-v3-green" alt="Filament"></a>

## Installation

* Download the ZIP file: <a href="https://github.com/michailtjhang/StoryWeaver/archive/refs/heads/main.zip">Click here</a>
* Or clone the repository via terminal:

  ```bash
  git clone https://github.com/michailtjhang/StoryWeaver.git
  ```

## Setup

1. Open your terminal and navigate to the project directory.
2. Copy the `.env.example` file to `.env`:

   ```bash
   cp .env.example .env
   ```
3. Install the PHP dependencies:

   ```bash
   composer install
   ```
4. Generate the application key:

   ```bash
   php artisan key:generate
   ```

### Configure the Database

1. Create a new MySQL database.
2. Update your `.env` file with the database credentials.
3. Run database migrations:

   ```bash
   php artisan migrate
   ```
4. (Optional) Seed the database:

   ```bash
   php artisan db:seed
   ```

### Run the Application

1. Start the Laravel server:

   ```bash
   php artisan serve
   ```
2. For asset compilation (if needed):

   ```bash
   npm install
   npm run dev
   ```

## Features

### Core Features

* **Story Writing**: Authors can create, edit, and manage stories through a modern and simple editor.
* **Draft Review**: Editors can review submitted drafts, provide feedback, and approve stories for publication.
* **Category & Tag Management**: Organize stories with categories and tags for better discovery.
* **User Roles**: Supports multiple roles (Author, Editor, Admin) with different permissions.
* **Revision History**: Track changes and revisions for each story draft.
* **Comment System**: Users can comment on stories (optional, based on configuration).
* **Search & Filter**: Easily find stories by title, author, category, or status.
* **Dashboard Analytics**: Overview of story stats, user activity, and system health (for Admin).

### Admin & CMS Features

* **Filament Admin Panel**: Clean, responsive backend for managing users, stories, categories, and more.
* **User Management**: Add, remove, and update user roles and access.
* **Approval Workflow**: Stories move through statuses (Draft, Waiting for Review, Published, etc.) to streamline editorial flow.

### Additional Features

* **Responsive UI**: Works well on both desktop and mobile.
* **Notification System**: Alerts users of status changes and comments (via email or in-app notifications, depending on setup).
* **Export & Backup**: Export stories for backup or external use.

### Non-Functional Requirements

* **Security**: Built on Laravel 11 with best practices for authentication, authorization, and data protection.
* **Performance**: Uses Laravel’s query builder and cache to keep the platform fast, even with many stories and users.
* **Accessibility**: UI designed for clarity and accessibility.

## Accounts & Access

* Default roles: Author, Editor, Admin. You can set these up via the admin panel after installation.

## Contribution

Contributions are welcome! Please open an issue or submit a pull request if you have improvements, new features, or bugfixes.

## Author

**Project by Michail Tjhang**
[https://github.com/michailtjhang](https://github.com/michailtjhang)
