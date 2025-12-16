# Redis Task Tracker

A full-stack task management application built with Laravel 12 (PHP 8.4) and Redis as the primary database. Features JWT authentication, real-time task management, and audit logging using Redis Streams.

## Tech Stack

### Backend
- **PHP 8.4** - Laravel 12 framework
- **Redis** with RedisJSON module - Primary database
- **JWT** - Token-based authentication
- **Redis Streams** - Event logging and audit trails

### Frontend
- **Vite** - Development server and build tool
- **Port 5173** - Frontend development server

### DevOps
- **Docker** - Containerized development environment
- **Redis Insight** - Redis database management UI

## Features

- ✅ User registration and authentication with JWT
- ✅ CRUD operations for tasks
- ✅ Task categorization and due dates
- ✅ User-scoped task management (users only see their own tasks)
- ✅ Token blacklisting for secure logout
- ✅ Event logging with Redis Streams
- ✅ 30-day TTL for task data

## Architecture

### Data Storage
All data is stored in Redis using the RedisJSON module:
- **Users**: `user:{id}` - User profiles with hashed passwords
- **Tasks**: `task:{id}` - Task documents with user ownership
- **Counters**: `{type}:counter` - Auto-incrementing ID generators
- **JWT Blacklist**: `jwt:blacklist:{token}` - Revoked tokens (24h TTL)
- **Event Streams**: `stream:{type}-events` - Audit logs

### Authentication Flow
1. User registers/logs in → Receives JWT token (24h expiry)
2. Token validated by `JwtAuthMiddleware` on protected routes
3. `AuthContext` injected into services for user-scoped operations
4. Logout adds token to Redis blacklist

## Docker Deployment

### Prerequisites
- Docker
- Docker Compose

### Services

The application runs in 5 Docker containers:

| Service                | Container Name         | Port | Description                                        |
|------------------------|------------------------|------|----------------------------------------------------|
| **composer-app**       | redis-db_php8.4-runner | 3000 | Laravel 12 backend (PHP 8.4) + composer controller |
| **frontend**           | redis-db_frontend      | 5173 | Vite development server                            |
| **redis**              | redis-db_redis         | 6379 | Redis database with RedisJSON                      |
| **redis-insight**      | redis-db_redis-insight | 8001 | Redis management UI                                |
| **redis-insight-init** | -                      | -    | Auto-configures Redis Insight                      |

### Quick Start

```bash
# Start all services
docker compose up -d

# View logs
docker compose logs -f

# Stop all services
docker compose down

# Stop and remove volumes (deletes all data)
docker compose down -v
```

### Access Points

- **Backend API**: http://localhost:3000/api
- **Frontend**: http://localhost:3000
- **Frontend development server**: http://localhost:5173
- **Redis Insight**: http://localhost:8001

### Volume Mounts

Both backend and frontend containers mount the project root:
```yaml
volumes:
  - ../../:/app  # Project root mounted to /app
```

This enables:
- ✅ Hot reload during development
- ✅ Code changes reflect immediately
- ✅ No need to rebuild containers for code updates

### Persistent Data

Redis data is persisted in a Docker volume:
```yaml
volumes:
  redis-data:  # Survives container restarts
```

## API Endpoints

### Authentication
```
POST   /api/register      - Register new user
POST   /api/login         - Login and get JWT token
POST   /api/logout        - Logout (blacklist token)
GET    /api/me            - Get current user profile
```

### Tasks (Protected - Requires JWT)
```
GET    /api/tasks               - List all user's tasks
POST   /api/tasks               - Create new task
POST   /api/tasks/{id}/toggle   - Toggle task completion
DELETE /api/tasks/{id}          - Delete task
```

### Request Headers
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

## Redis Configuration

Redis is configured with:
- Alpine Linux base image
- Volume persistence
- Auto-restart policy
- RedisJSON module support

## Project Structure

```
.
├── app/
│   ├── Auth/
│   │   └── AuthContext.php                 # Auth context value object
│   ├── Constants/
│   │   ├── Category.php                    # Task categories enum
│   │   ├── KeyType.php                     # Redis key types enum
│   │   └── SubkeyType.php                  # Redis subkey types
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── TaskController.php          # Task CRUD endpoints
│   │   │   └── UserController.php          # Auth endpoints
│   │   ├── Middleware/
│   │   │   └── JwtAuthMiddleware.php       # JWT validation
│   │   ├── Services/
│   │   │   ├── TaskManagementService.php   # Task business logic
│   │   │   └── UserManagementService.php   # User management
│   │   └── RedisConnection.php             # Redis wrapper
│   └── Providers/
│       └── AppServiceProvider.php          # Service container setup
├── bin/docker/
│   ├── composer-app/
│   │   └── Dockerfile                      # PHP 8.4 backend
│   ├── frontend/
│   │   └── Dockerfile                      # Vite frontend
│   └── redis-insight-init/
│       └── entrypoint.sh                   # Redis Insight auto-config
└── docker compose.yml                      # Docker orchestration
```

## Development Notes

### Redis JSON Commands Used
- `JSON.GET` - Retrieve JSON documents
- `JSON.SET` - Store JSON documents
- `INCR` - Atomic counter for ID generation
- `KEYS` - Pattern matching for queries
- `EXPIRE` - TTL management
- `XADD` - Add events to streams
- `XRANGE` - Query stream entries

### Security Features
- ✅ Password hashing with Laravel's Hash facade
- ✅ JWT token expiration (24 hours)
- ✅ Token blacklisting on logout
- ✅ User-scoped data access
- ✅ Authorization checks on all operations

## Troubleshooting

### Container logs
```bash
# Backend
docker compose logs composer-app

# Frontend
docker compose logs frontend

# Redis
docker compose logs redis
```

### Rebuild containers
```bash
# Rebuild specific service
docker compose build composer-app

# Rebuild all services
docker compose build --no-cache
```
