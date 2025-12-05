# Project Manager API (Laravel 12 / PHP 8.5)

## Overview

This application is a scalable, strictly typed backend API for a Project Management System. It allows an organization to manage employees, teams, and projects while facilitating a review system where employees can provide feedback on the projects they are working on.

The system implements a complex **Role-Based Access Control (RBAC)** architecture that handles both global roles (Executive, Manager) and contextual access (Internal Advisors, Team Assignments). It uses JWT (JSON Web Tokens) for secure, stateless authentication with token rotation.

## Technology Stack

* **Framework**: Laravel 12
* **Language**: PHP 8.5
* **Database**: PostgreSQL (uses native `SERIAL` and custom schemas)
* **Authentication**: Custom JWT implementation via `firebase/php-jwt` (bypassing Sanctum/Passport for specific payload control).

## Goals

1.  **Orchestration**: Manage the relationship between `Users`, `Teams`, and `Projects`.
2.  **Feedback Loop**: Enable a review system where `Associates` and `Managers` can rate projects (1-5 stars) and provide written content.
3.  **Security**: Enforce strict data visibility rules—only Executives can see who wrote a specific review, while others see anonymized data.
4.  **Contextual Access**: Allow users (Managers/Associates) to become "Internal Advisors" on projects outside their team, granting temporary access.

---

## Setup & Commands

### Prerequisites
* PHP 8.5+
* PostgreSQL
* Composer

### Running Tests
This project includes a comprehensive test suite. The base `TestCase` is configured to automatically seed the database with Roles, Permissions, and an Admin user before every test to ensure a valid RBAC state.

\`\`\`bash
php artisan test
\`\`\`

* **Usage**: Runs all Feature and Unit tests located in the `/tests` directory.
* **What it checks**: Authentication flows, Role enforcement, Policy logic (e.g., ensuring an Associate cannot access Executive routes), and Data integrity.

### Serving the Application
To run the API locally for development:

\`\`\`bash
php artisan serve
\`\`\`

* **Usage**: Starts the built-in Laravel development server at `http://127.0.0.1:8000`.
* **Note**: Ensure your `.env` file is configured with the correct database credentials before running.

---

## Roles & Permissions Architecture

The system uses a "Waterfall" policy logic combined with Global Roles.

### 1. Executive
* **Scope**: Global System Administrator.
* **Permissions**:
    * **Users**: Create, View, Update, Delete (Soft Delete).
    * **Teams**: Create new teams and manage roster (add/remove members).
    * **Projects**: View ALL projects, Create, Archive.
    * **Assignments**: Can assign Teams to Projects and assign Internal Advisors.
    * **Reviews**: Can see the **real names** of reviewers (bypassing anonymity). Can delete any review.

### 2. Manager
* **Scope**: Team Level.
* **Permissions**:
    * **Projects**: Can view projects assigned to their Team(s).
    * **Reviews**: Can view reviews for their team's projects. Can create reviews. Can update/delete their *own* reviews.
    * **Advisory**: Can be assigned as an Internal Advisor to other projects.

### 3. Associate
* **Scope**: Individual Contributor.
* **Permissions**:
    * **Projects**: Can view projects assigned to their Team(s).
    * **Reviews**: Can view reviews. Can create reviews. Can update/delete their *own* reviews.

### 4. Internal Advisor (Contextual Role)
* **Definition**: A user (Manager or Associate) granted temporary access to a specific project they are not normally assigned to.
* **Rules**:
    * Bypasses the standard Team check in `ProjectPolicy`.
    * Can view the specific project and leave reviews.

---

## API Endpoints

All endpoints are prefixed with `/api`. Responses are strictly typed JSON.

### Authentication (Public)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/login` | Exchange credentials for Access & Refresh Tokens. |
| `POST` | `/register` | Complete account setup (set password) for invited users. |
| `GET` | `/health` | API status check. |

### Protected Routes (Requires Bearer Token)

#### Dashboard & Profile
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/dashboard` | Returns stats based on user Role (Exec/Mgr/Assoc). |
| `GET` | `/profile` | Get current user details. |
| `POST` | `/refresh-token` | Rotate the Refresh Token. |
| `POST` | `/logout` | Revoke all tokens. |

#### Projects & Reviews
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/projects` | List projects (Filtered by Access Policy). |
| `GET` | `/projects/{id}` | View project details. |
| `GET` | `/projects/{id}/reviews` | List reviews (Names hidden unless Exec/Self). |
| `POST` | `/projects/{id}/reviews` | Create a review (Requires Team/Advisor access). |
| `GET` | `/my-reviews` | Personal audit log of user's reviews. |
| `PUT` | `/reviews/{id}` | Edit own review. |
| `DELETE` | `/reviews/{id}` | Delete own review (or Exec deletes any). |

#### Teams
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/teams` | List all teams. |
| `GET` | `/teams/{id}` | View team roster. |

### Executive Administration (Role Restricted)
*Requires `role:Executive` middleware.*

#### User Management
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/users` | List all employees. |
| `POST` | `/users` | Create a new employee. |
| `PUT` | `/users/{id}` | Update role or status. |
| `DELETE` | `/users/{id}` | Deactivate (Soft Delete) user. |

#### Team Management
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/teams` | Create a new Team. |
| `POST` | `/teams/{id}/members` | Add a user to the team roster. |
| `DELETE` | `/teams/{id}/members/{uid}` | Remove a user from the team. |

#### Assignments (Orchestration)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/assignments/project-teams` | Assign entire Teams to a Project. |
| `POST` | `/assignments/advisors` | Assign an Internal Advisor to a Project. |
| `DELETE` | `/assignments/advisors/{prj}/{uid}` | Remove an Advisor. |
