# 🐳 Trans Bandung Microservices Containerization

## 📋 Overview

This repository contains the containerized implementation of Trans Bandung microservices architecture, featuring independent, scalable services with proper isolation and inter-service communication.

## 🏗️ Architecture

### Service Structure
```
┌─────────────────────────────────────────────────────────────┐
│                    NGINX LOAD BALANCER                      │
│                        (Port 80)                           │
└─────────────────────────┬───────────────────────────────────┘
                         │
┌─────────────────────────┴───────────────────────────────────┐
│                    API GATEWAY                              │
│                     (Port 8000)                            │
└─┬─────────┬─────────────┬─────────────┬───────────────────┬─┘
  │         │             │             │                   │
┌─▼─────┐ ┌─▼───────────┐ ┌─▼───────────┐ ┌─▼─────────────┐ ┌─▼───────┐
│ User   │ │ Ticketing   │ │ Payment     │ │ Inbox         │ │ Redis   │
│Service │ │ Service     │ │ Service     │ │ Service       │ │ Cache   │
│:8001   │ │ :8002       │ │ :8003       │ │ :8004         │ │ :6379   │
└─┬─────┘ └─┬───────────┘ └─┬───────────┘ └─┬─────────────┘ └─────────┘
  │         │               │               │
┌─▼─────┐ ┌─▼─────────────┐ ┌─▼─────────────┐ ┌─▼─────────────┐
│User DB │ │Ticketing DB   │ │Payment DB     │ │Inbox DB       │
│:3306   │ │:3306          │ │:3306          │ │:3306          │
└───────┘ └───────────────┘ └───────────────┘ └───────────────┘
```

### Services

| Service | Port | Purpose | Database |
|---------|------|---------|----------|
| **API Gateway** | 8000 | Main entry point, web interface | User DB (shared) |
| **User Service** | 8001 | Authentication, user management | transbandung_users |
| **Ticketing Service** | 8002 | Routes, schedules, bookings | transbandung_ticketing |
| **Payment Service** | 8003 | Payment processing, verification | transbandung_payments |
| **Inbox Service** | 8004 | Messaging, notifications | transbandung_inbox |
| **Load Balancer** | 80 | Traffic distribution | - |

## 🚀 Quick Start

### Prerequisites
- Docker Desktop (Windows)
- Docker Compose
- PowerShell (Windows)
- Minimum 8GB RAM
- 20GB free disk space

### 1. Clone and Setup
```powershell
# Navigate to project directory
cd d:\0.Laragon\laragon\www\Tubes_EAI\transbandunglast

# Make scripts executable (if needed)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### 2. Deploy All Services
```powershell
# Full deployment with build
.\scripts\deploy-services.ps1 -Build

# Quick deployment (images already built)
.\scripts\deploy-services.ps1

# Clean deployment (remove existing data)
.\scripts\deploy-services.ps1 -Clean -Build
```

### 3. Verify Deployment
```powershell
# Test all services
.\scripts\test-services.ps1

# Test specific service
.\scripts\test-services.ps1 -Service user-service -Verbose
```

### 4. Access Services
- **Main Application**: http://localhost
- **API Gateway**: http://localhost:8000
- **User Service**: http://localhost:8001
- **Ticketing Service**: http://localhost:8002
- **Payment Service**: http://localhost:8003
- **Inbox Service**: http://localhost:8004
- **phpMyAdmin**: http://localhost:8080

## 📊 Service Management

### Scaling Services
```powershell
# Scale user service to 3 instances
.\scripts\scale-service.ps1 -Service user-service -Replicas 3

# Scale down to 1 instance
.\scripts\scale-service.ps1 -Service user-service -Replicas 1

# Force scaling beyond recommended limits
.\scripts\scale-service.ps1 -Service api-gateway -Replicas 5 -Force
```

### Viewing Logs
```powershell
# View all service logs
.\scripts\logs-service.ps1

# Follow specific service logs
.\scripts\logs-service.ps1 -Service user-service -Follow

# View logs with timestamps
.\scripts\logs-service.ps1 -Service payment-service -Timestamps -Lines 200

# View recent logs
.\scripts\logs-service.ps1 -Service ticketing-service -Since "1h"
```

### Manual Docker Commands
```powershell
# View running containers
docker-compose -f docker-compose.services.yml ps

# Restart specific service
docker-compose -f docker-compose.services.yml restart user-service

# View service logs
docker-compose -f docker-compose.services.yml logs -f user-service

# Scale services manually
docker-compose -f docker-compose.services.yml up -d --scale user-service=3

# Stop all services
docker-compose -f docker-compose.services.yml down

# Stop with data cleanup
docker-compose -f docker-compose.services.yml down --volumes
```

## 🔧 Configuration

### Environment Files
- `envs/.env.user-service` - User service configuration
- `envs/.env.ticketing-service` - Ticketing service configuration
- `envs/.env.payment-service` - Payment service configuration
- `envs/.env.inbox-service` - Inbox service configuration
- `envs/.env.api-gateway` - API Gateway configuration

### Key Configuration Options

#### Service-Specific Settings
```bash
# Service identification
SERVICE_NAME=user-service
SERVICE_PORT=8001
SERVICE_ENVIRONMENT=containerized

# Enable microservice features
MICROSERVICE_MODE=true
ENABLE_INTERNAL_API=true
ENABLE_CROSS_SERVICE_AUTH=true
```

#### Database Configuration
```bash
# Each service has its own database
DB_HOST=user-db
DB_DATABASE=transbandung_users
DB_USERNAME=microservice
DB_PASSWORD=microservice123
```

#### Inter-Service URLs
```bash
USER_SERVICE_URL=http://user-service
TICKETING_SERVICE_URL=http://ticketing-service
PAYMENT_SERVICE_URL=http://payment-service
INBOX_SERVICE_URL=http://inbox-service
API_GATEWAY_URL=http://api-gateway
```

## 🔗 Inter-Service Communication

### HTTP Client Classes
Services communicate via HTTP using dedicated client classes:

- `App\Services\Http\UserServiceClient`
- `App\Services\Http\TicketingServiceClient` 
- `App\Services\Http\PaymentServiceClient`
- `App\Services\Http\InboxServiceClient`

### Example Usage
```php
// In any service
$userClient = new UserServiceClient();
$user = $userClient->getUserById(1);

$ticketingClient = new TicketingServiceClient();
$booking = $ticketingClient->createBooking($bookingData);
```

### Internal API Endpoints
Each service exposes internal APIs for cross-service communication:

```
# User Service
GET /api/v1/internal/users/{id}
POST /api/v1/internal/users/multiple

# Ticketing Service  
GET /api/v1/internal/ticketing/routes/{id}
POST /api/v1/internal/ticketing/bookings

# Payment Service
POST /api/v1/internal/payments
GET /api/v1/internal/payments/{id}

# Inbox Service
POST /api/v1/internal/inbox/messages
GET /api/v1/internal/inbox/user/{id}/messages
```

## 🏥 Health Monitoring

### Health Check Endpoints
All services provide health check endpoints:

```bash
# Check individual services
curl http://localhost:8001/health  # User Service
curl http://localhost:8002/health  # Ticketing Service
curl http://localhost:8003/health  # Payment Service
curl http://localhost:8004/health  # Inbox Service
curl http://localhost:8000/health  # API Gateway
curl http://localhost/health       # Load Balancer
```

### Health Check Response
```json
{
  "service": "user-service",
  "status": "healthy",
  "timestamp": "2025-06-12T10:30:00Z",
  "database": "connected",
  "redis": "connected"
}
```

### Automated Health Checks
Docker Compose includes automatic health checks:
- **Interval**: 30 seconds
- **Timeout**: 10 seconds  
- **Retries**: 3
- **Start Period**: 40 seconds

## 🔒 Security

### Network Isolation
- Services communicate through dedicated Docker network
- External access only through Load Balancer and API Gateway
- Database access restricted to specific services

### Service Authentication
- Internal API calls include service identification headers
- Request tracing with unique request IDs
- Circuit breaker pattern for fault tolerance

### Headers
```
X-Service-Name: user-service
X-Service-Source: api-gateway
X-Request-ID: req_123456789
```

## 📈 Performance & Scaling

### Scalability Matrix
| Service | Min Replicas | Max Replicas | Recommended |
|---------|--------------|--------------|-------------|
| User Service | 1 | 5 | 2-3 |
| Ticketing Service | 1 | 5 | 2-3 |
| Payment Service | 1 | 5 | 2 |
| Inbox Service | 1 | 3 | 1-2 |
| API Gateway | 1 | 3 | 1-2 |

### Load Balancing
- **Algorithm**: Least connections
- **Health Checks**: Automatic failover
- **Sticky Sessions**: Not required (stateless services)

### Performance Monitoring
```powershell
# Monitor resource usage
docker stats

# Check container health
docker-compose -f docker-compose.services.yml ps

# View performance metrics
docker-compose -f docker-compose.services.yml top
```

## 🗃️ Database Management

### Database Structure
```
├── transbandung_users (User Service)
├── transbandung_ticketing (Ticketing Service)  
├── transbandung_payments (Payment Service)
├── transbandung_inbox (Inbox Service)
└── transbandung_reviews (Future use)
```

### Accessing Databases
```bash
# Via phpMyAdmin
http://localhost:8080

# Direct MySQL connection
docker exec -it transbandung-user-db mysql -u microservice -pmicroservice123 transbandung_users

# Redis CLI
docker exec -it transbandung-redis redis-cli
```

### Database Migrations
```powershell
# Run migrations on specific service
docker exec transbandung-user-service php artisan migrate --database=user_management

# Seed data
docker exec transbandung-ticketing-service php artisan db:seed
```

## 🔧 Development Workflow

### Local Development
```powershell
# Start development environment
.\scripts\deploy-services.ps1

# Follow logs during development
.\scripts\logs-service.ps1 -Service api-gateway -Follow

# Test changes
.\scripts\test-services.ps1 -Verbose
```

### Service Updates
```powershell
# Update specific service
docker-compose -f docker-compose.services.yml build user-service
docker-compose -f docker-compose.services.yml up -d user-service

# Zero-downtime update with scaling
.\scripts\scale-service.ps1 -Service user-service -Replicas 2
# Update one instance at a time
```

### Debugging
```powershell
# Execute commands in service container
docker exec -it transbandung-user-service bash

# View detailed logs
docker-compose -f docker-compose.services.yml logs --details user-service

# Check service configuration
docker exec transbandung-user-service env | grep SERVICE
```

## 🧪 Testing

### Automated Testing
```powershell
# Run all tests
.\scripts\test-services.ps1

# Test specific functionality
.\scripts\test-services.ps1 -Service user-service

# Verbose testing with detailed output
.\scripts\test-services.ps1 -Verbose
```

### Manual Testing
```bash
# Test user service
curl -X GET http://localhost:8001/api/v1/internal/users/1

# Test ticketing service
curl -X GET http://localhost:8002/api/v1/internal/ticketing/routes/1

# Test payment service  
curl -X GET http://localhost:8003/payments/methods

# Test inbox service
curl -X GET http://localhost:8004/api/v1/internal/inbox/user/1/messages
```

### Load Testing
```powershell
# Scale services for load testing
.\scripts\scale-service.ps1 -Service user-service -Replicas 3
.\scripts\scale-service.ps1 -Service ticketing-service -Replicas 3

# Monitor during load testing
.\scripts\logs-service.ps1 -Service nginx-lb -Follow
```

## 🚨 Troubleshooting

### Common Issues

#### Services Not Starting
```powershell
# Check container logs
.\scripts\logs-service.ps1 -Service user-service

# Verify database connections
docker exec transbandung-user-service php artisan tinker
```

#### Database Connection Issues
```powershell
# Check database health
docker exec transbandung-user-db mysqladmin ping -h localhost

# Verify database permissions
docker exec transbandung-user-db mysql -u root -proot123 -e "SHOW GRANTS FOR 'microservice'@'%'"
```

#### Inter-Service Communication Failures
```powershell
# Test service connectivity
docker exec transbandung-api-gateway curl http://user-service/health

# Check service discovery
docker exec transbandung-api-gateway nslookup user-service
```

#### Memory/Resource Issues
```powershell
# Check resource usage
docker stats

# Reduce replicas if needed
.\scripts\scale-service.ps1 -Service user-service -Replicas 1
```

### Debug Commands
```powershell
# Container information
docker inspect transbandung-user-service

# Network information
docker network inspect transbandung-microservices_transbandung-microservices

# Volume information
docker volume ls | Select-String "transbandung"
```

## 📚 Additional Resources

### Logs Location
- Container logs: `.\scripts\logs-service.ps1`
- Application logs: `storage/logs/[service-name]/`
- Nginx logs: Inside nginx-lb container at `/var/log/nginx/`

### Configuration Files
- Docker Compose: `docker-compose.services.yml`
- Nginx Config: `nginx/nginx.conf`, `nginx/default.conf`
- Service Configs: `docker/nginx/[service].conf`
- Supervisor Configs: `docker/supervisor/[service].conf`

### Port Mapping
```
80    -> Load Balancer (Nginx)
8000  -> API Gateway
8001  -> User Service
8002  -> Ticketing Service  
8003  -> Payment Service
8004  -> Inbox Service
8080  -> phpMyAdmin
3306  -> Database ports (internal)
6379  -> Redis (internal)
```

---

## 🎯 Success Criteria Checklist

- ✅ All 5 services running in separate containers
- ✅ Each service accessible via designated ports
- ✅ Inter-service communication working
- ✅ Database isolation maintained per service
- ✅ API Gateway routing requests correctly
- ✅ Services can be scaled independently
- ✅ Health checks functional and responsive
- ✅ Container restart on failure
- ✅ Centralized logging implemented
- ✅ Easy local development setup
- ✅ Individual service debugging capabilities
- ✅ Simple service update process

**🎉 Microservices containerization implementation complete!**
