#!/usr/bin/env powershell

<#
.SYNOPSIS
    Performance Optimization Script for Trans Bandung Microservices
.DESCRIPTION
    Optimizes container resources, database performance, and caching configurations
.PARAMETER Target
    Optimization target (containers, database, cache, network, all)
.PARAMETER Profile
    Performance profile (development, production, high-performance)
.PARAMETER Monitor
    Monitor performance after optimization
#>

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("containers", "database", "cache", "network", "all")]
    [string]$Target = "all",
    
    [Parameter(Mandatory=$false)]
    [ValidateSet("development", "production", "high-performance")]
    [string]$Profile = "production",
    
    [Parameter(Mandatory=$false)]
    [switch]$Monitor,
    
    [Parameter(Mandatory=$false)]
    [switch]$Force,
    
    [Parameter(Mandatory=$false)]
    [switch]$Verbose
)

# Configuration
$optimizationDir = "optimization"
$logFile = "storage/logs/optimization-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').log"
$composeFile = "docker-compose.services.yml"

# Performance profiles
$profiles = @{
    "development" = @{
        "cpu_limit" = "0.5"
        "memory_limit" = "512m"
        "mysql_buffer_pool" = "128M"
        "redis_maxmemory" = "64m"
        "nginx_worker_processes" = "1"
        "php_memory_limit" = "256M"
        "opcache_memory" = "64"
    }
    "production" = @{
        "cpu_limit" = "1.0"
        "memory_limit" = "1g"
        "mysql_buffer_pool" = "512M"
        "redis_maxmemory" = "256m"
        "nginx_worker_processes" = "auto"
        "php_memory_limit" = "512M"
        "opcache_memory" = "128"
    }
    "high-performance" = @{
        "cpu_limit" = "2.0"
        "memory_limit" = "2g"
        "mysql_buffer_pool" = "1G"
        "redis_maxmemory" = "512m"
        "nginx_worker_processes" = "auto"
        "php_memory_limit" = "1G"
        "opcache_memory" = "256"
    }
}

function Write-Log {
    param($Message, $Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    
    Write-Host $logMessage
    if (Test-Path $logFile) {
        Add-Content -Path $logFile -Value $logMessage
    }
}

function Initialize-OptimizationStructure {
    Write-Log "Initializing optimization directory structure..." "INFO"
    
    $directories = @(
        $optimizationDir,
        "$optimizationDir/configs",
        "$optimizationDir/monitoring",
        "storage/logs"
    )
    
    foreach ($dir in $directories) {
        if (-not (Test-Path $dir)) {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
            Write-Log "Created directory: $dir" "INFO"
        }
    }
}

function Get-SystemResources {
    Write-Log "Analyzing system resources..." "INFO"
    
    try {
        # Get system information
        if ($IsWindows) {
            $cpu = Get-WmiObject -Class Win32_Processor | Measure-Object -Property NumberOfCores -Sum
            $memory = Get-WmiObject -Class Win32_ComputerSystem
            $totalCores = $cpu.Sum
            $totalMemoryGB = [math]::Round($memory.TotalPhysicalMemory / 1GB, 2)
        } else {
            $totalCores = nproc
            $totalMemoryGB = [math]::Round((Get-Content /proc/meminfo | Select-String "MemTotal" | ForEach-Object { ($_ -split "\s+")[1] }) / 1024 / 1024, 2)
        }
        
        $systemInfo = @{
            "cores" = $totalCores
            "memory_gb" = $totalMemoryGB
            "recommended_profile" = if ($totalMemoryGB -ge 8) { "high-performance" } elseif ($totalMemoryGB -ge 4) { "production" } else { "development" }
        }
        
        Write-Log "System: $totalCores cores, $totalMemoryGB GB RAM" "INFO"
        Write-Log "Recommended profile: $($systemInfo.recommended_profile)" "INFO"
        
        return $systemInfo
        
    } catch {
        Write-Log "Failed to analyze system resources: $($_.Exception.Message)" "ERROR"
        return @{ "cores" = 2; "memory_gb" = 4; "recommended_profile" = "production" }
    }
}

function Optimize-ContainerResources {
    param($Profile)
    
    Write-Log "Optimizing container resources for $Profile profile..." "INFO"
    
    $config = $profiles[$Profile]
    
    # Create optimized Docker Compose override
    $optimizedCompose = @"
version: '3.8'

# Performance-optimized configuration for $Profile profile
# This file should be used with docker-compose.services.yml

services:
  # User Service Optimization
  user-service:
    deploy:
      resources:
        limits:
          cpus: '$($config.cpu_limit)'
          memory: $($config.memory_limit)
        reservations:
          cpus: '0.25'
          memory: 256m
    environment:
      - PHP_MEMORY_LIMIT=$($config.php_memory_limit)
      - OPCACHE_MEMORY_CONSUMPTION=$($config.opcache_memory)

  # Ticketing Service Optimization
  ticketing-service:
    deploy:
      resources:
        limits:
          cpus: '$($config.cpu_limit)'
          memory: $($config.memory_limit)
        reservations:
          cpus: '0.25'
          memory: 256m
    environment:
      - PHP_MEMORY_LIMIT=$($config.php_memory_limit)
      - OPCACHE_MEMORY_CONSUMPTION=$($config.opcache_memory)

  # Payment Service Optimization
  payment-service:
    deploy:
      resources:
        limits:
          cpus: '$($config.cpu_limit)'
          memory: $($config.memory_limit)
        reservations:
          cpus: '0.5'
          memory: 512m
    environment:
      - PHP_MEMORY_LIMIT=$($config.php_memory_limit)
      - OPCACHE_MEMORY_CONSUMPTION=$($config.opcache_memory)

  # Inbox Service Optimization
  inbox-service:
    deploy:
      resources:
        limits:
          cpus: '$($config.cpu_limit)'
          memory: $($config.memory_limit)
        reservations:
          cpus: '0.25'
          memory: 256m
    environment:
      - PHP_MEMORY_LIMIT=$($config.php_memory_limit)
      - OPCACHE_MEMORY_CONSUMPTION=$($config.opcache_memory)

  # Reviews Service Optimization
  reviews-service:
    deploy:
      resources:
        limits:
          cpus: '$($config.cpu_limit)'
          memory: $($config.memory_limit)
        reservations:
          cpus: '0.25'
          memory: 256m
    environment:
      - PHP_MEMORY_LIMIT=$($config.php_memory_limit)
      - OPCACHE_MEMORY_CONSUMPTION=$($config.opcache_memory)

  # API Gateway Optimization
  api-gateway:
    deploy:
      resources:
        limits:
          cpus: '1.5'
          memory: 1g
        reservations:
          cpus: '0.5'
          memory: 512m
    environment:
      - PHP_MEMORY_LIMIT=$($config.php_memory_limit)
      - OPCACHE_MEMORY_CONSUMPTION=$($config.opcache_memory)

  # Redis Optimization
  redis:
    command: >
      redis-server 
      --maxmemory $($config.redis_maxmemory)
      --maxmemory-policy allkeys-lru
      --save 900 1
      --save 300 10
      --save 60 10000
      --appendonly yes
      --appendfsync everysec
    deploy:
      resources:
        limits:
          cpus: '0.5'
          memory: $($config.redis_maxmemory + 64)m
        reservations:
          cpus: '0.1'
          memory: 64m
"@
    
    $optimizedFile = "$optimizationDir/docker-compose.$Profile.yml"
    Set-Content -Path $optimizedFile -Value $optimizedCompose
    Write-Log "Created optimized Docker Compose file: $optimizedFile" "SUCCESS"
}

function Optimize-DatabaseConfiguration {
    param($Profile)
    
    Write-Log "Optimizing database configuration for $Profile profile..." "INFO"
    
    $config = $profiles[$Profile]
    
    # MySQL optimization configuration
    $mysqlConfig = @"
[mysqld]
# Performance optimization for $Profile profile

# Memory settings
innodb_buffer_pool_size = $($config.mysql_buffer_pool)
innodb_log_file_size = 64M
innodb_log_buffer_size = 16M
innodb_flush_log_at_trx_commit = 2

# Connection settings
max_connections = 200
max_connect_errors = 10000
connect_timeout = 60
wait_timeout = 600

# Query cache
query_cache_type = 1
query_cache_size = 32M
query_cache_limit = 2M

# InnoDB settings
innodb_file_per_table = 1
innodb_flush_method = O_DIRECT
innodb_lock_wait_timeout = 50

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Binary logging
log_bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
max_binlog_size = 100M

# Security
local_infile = 0

[mysql]
no_auto_rehash

[mysqldump]
quick
max_allowed_packet = 16M
"@
    
    Set-Content -Path "$optimizationDir/configs/mysql-$Profile.cnf" -Value $mysqlConfig
    Write-Log "Created MySQL configuration: mysql-$Profile.cnf" "SUCCESS"
}

function Optimize-CacheConfiguration {
    param($Profile)
    
    Write-Log "Optimizing cache configuration for $Profile profile..." "INFO"
    
    $config = $profiles[$Profile]
    
    # Redis optimization configuration
    $redisConfig = @"
# Redis optimization for $Profile profile

# Memory management
maxmemory $($config.redis_maxmemory)
maxmemory-policy allkeys-lru
maxmemory-samples 5

# Persistence
save 900 1
save 300 10
save 60 10000

appendonly yes
appendfsync everysec
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb

# Network
tcp-keepalive 60
timeout 300

# Security
requirepass $((New-Guid).Guid.Replace('-','').Substring(0,16))

# Performance
hash-max-ziplist-entries 512
hash-max-ziplist-value 64
list-max-ziplist-size -2
list-compress-depth 0
set-max-intset-entries 512
zset-max-ziplist-entries 128
zset-max-ziplist-value 64

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
"@
    
    Set-Content -Path "$optimizationDir/configs/redis-$Profile.conf" -Value $redisConfig
    Write-Log "Created Redis configuration: redis-$Profile.conf" "SUCCESS"
    
    # OPcache configuration
    $opcacheConfig = @"
; OPcache optimization for $Profile profile

; Enable OPcache
opcache.enable=1
opcache.enable_cli=1

; Memory settings
opcache.memory_consumption=$($config.opcache_memory)
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000

; Performance settings
opcache.revalidate_freq=2
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1

; File cache
opcache.file_cache=/tmp/opcache
opcache.file_cache_only=0
opcache.file_cache_consistency_checks=1
"@
    
    Set-Content -Path "$optimizationDir/configs/opcache-$Profile.ini" -Value $opcacheConfig
    Write-Log "Created OPcache configuration: opcache-$Profile.ini" "SUCCESS"
}

function Optimize-NetworkConfiguration {
    param($Profile)
    
    Write-Log "Optimizing network configuration for $Profile profile..." "INFO"
    
    $config = $profiles[$Profile]
    
    # Nginx optimization
    $nginxConfig = @"
# Nginx optimization for $Profile profile

user nginx;
worker_processes $($config.nginx_worker_processes);
worker_rlimit_nofile 65535;

events {
    worker_connections 8192;
    use epoll;
    multi_accept on;
    worker_aio_requests 32;
}

http {
    # Basic settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    keepalive_requests 1000;
    types_hash_max_size 2048;
    server_tokens off;
    
    # Buffer settings
    client_body_buffer_size 10K;
    client_header_buffer_size 1k;
    client_max_body_size 8m;
    large_client_header_buffers 2 1k;
    
    # Timeout settings
    client_body_timeout 12;
    client_header_timeout 12;
    send_timeout 10;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 10240;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;
    
    # File caching
    open_file_cache max=200000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=global:10m rate=100r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=200r/m;
    limit_conn_zone $binary_remote_addr zone=addr:10m;
    
    # Connection limiting
    limit_conn addr 20;
    
    # Logging
    access_log /var/log/nginx/access.log combined buffer=16k flush=2m;
    error_log /var/log/nginx/error.log warn;
}
"@
    
    Set-Content -Path "$optimizationDir/configs/nginx-$Profile.conf" -Value $nginxConfig
    Write-Log "Created Nginx configuration: nginx-$Profile.conf" "SUCCESS"
}

function Monitor-Performance {
    Write-Log "Starting performance monitoring..." "INFO"
    
    # Create monitoring script
    $monitoringScript = @"
#!/bin/bash
# Performance monitoring script

echo "=== Performance Monitoring Report ===" > $optimizationDir/monitoring/performance-report.txt
echo "Generated: `$(date)" >> $optimizationDir/monitoring/performance-report.txt
echo "" >> $optimizationDir/monitoring/performance-report.txt

# Container resource usage
echo "Container Resource Usage:" >> $optimizationDir/monitoring/performance-report.txt
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}\t{{.NetIO}}\t{{.BlockIO}}" >> $optimizationDir/monitoring/performance-report.txt
echo "" >> $optimizationDir/monitoring/performance-report.txt

# Service response times
echo "Service Response Times:" >> $optimizationDir/monitoring/performance-report.txt
services=("8001" "8002" "8003" "8004" "8005" "8000")
for port in `${services[@]}; do
    response_time=`$(curl -o /dev/null -s -w '%{time_total}' http://localhost:`$port/health 2>/dev/null || echo "N/A")
    echo "Port `$port: `${response_time}s" >> $optimizationDir/monitoring/performance-report.txt
done
echo "" >> $optimizationDir/monitoring/performance-report.txt

# Database performance
echo "Database Performance:" >> $optimizationDir/monitoring/performance-report.txt
docker exec transbandung-user-db mysql -u root -proot123 -e "SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_read_requests';" 2>/dev/null >> $optimizationDir/monitoring/performance-report.txt
docker exec transbandung-user-db mysql -u root -proot123 -e "SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_reads';" 2>/dev/null >> $optimizationDir/monitoring/performance-report.txt
echo "" >> $optimizationDir/monitoring/performance-report.txt

# Redis performance
echo "Redis Performance:" >> $optimizationDir/monitoring/performance-report.txt
docker exec transbandung-redis redis-cli info stats | grep -E "(keyspace_hits|keyspace_misses|used_memory_human)" >> $optimizationDir/monitoring/performance-report.txt

echo "Performance report saved to: $optimizationDir/monitoring/performance-report.txt"
"@
    
    Set-Content -Path "$optimizationDir/monitoring/monitor.sh" -Value $monitoringScript
    Write-Log "Created monitoring script: monitor.sh" "SUCCESS"
    
    # Make executable on Unix systems
    if (-not $IsWindows) {
        chmod +x "$optimizationDir/monitoring/monitor.sh"
    }
}

function Apply-Optimizations {
    param($Profile)
    
    Write-Log "Applying optimizations for $Profile profile..." "INFO"
    
    $optimizedComposeFile = "$optimizationDir/docker-compose.$Profile.yml"
    
    if (Test-Path $optimizedComposeFile) {
        Write-Log "Restarting services with optimized configuration..." "INFO"
        
        # Stop services
        docker-compose -f $composeFile down
        
        # Start with optimized configuration
        docker-compose -f $composeFile -f $optimizedComposeFile up -d
        
        if ($LASTEXITCODE -eq 0) {
            Write-Log "Services restarted with optimizations" "SUCCESS"
        } else {
            Write-Log "Failed to restart services with optimizations" "ERROR"
            return $false
        }
    } else {
        Write-Log "Optimized compose file not found: $optimizedComposeFile" "ERROR"
        return $false
    }
    
    return $true
}

function Create-PerformanceDashboard {
    $dashboardScript = @"
#!/usr/bin/env powershell

# Performance Dashboard for Trans Bandung Microservices

Write-Host "🚀 Trans Bandung Performance Dashboard" -ForegroundColor Cyan
Write-Host "=" * 50

# System resources
Write-Host ""
Write-Host "📊 System Resources:" -ForegroundColor Yellow
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}"

# Service health
Write-Host ""
Write-Host "🏥 Service Health:" -ForegroundColor Yellow
`$services = @{
    "User Service" = 8001
    "Ticketing Service" = 8002
    "Payment Service" = 8003
    "Inbox Service" = 8004
    "Reviews Service" = 8005
    "API Gateway" = 8000
}

foreach (`$service in `$services.Keys) {
    `$port = `$services[`$service]
    try {
        `$response = Invoke-RestMethod -Uri "http://localhost:`$port/health" -TimeoutSec 3
        if (`$response.status -eq "healthy") {
            Write-Host "  ✅ `$service (:`$port)" -ForegroundColor Green
        } else {
            Write-Host "  ⚠️  `$service (:`$port) - `$(`$response.status)" -ForegroundColor Yellow
        }
    }
    catch {
        Write-Host "  ❌ `$service (:`$port) - Unreachable" -ForegroundColor Red
    }
}

# Database status
Write-Host ""
Write-Host "🗄️  Database Status:" -ForegroundColor Yellow
`$databases = @("user-db", "ticketing-db", "payment-db", "inbox-db", "reviews-db")
foreach (`$db in `$databases) {
    try {
        `$result = docker exec "transbandung-`$db" mysqladmin ping -h localhost 2>`$null
        if (`$LASTEXITCODE -eq 0) {
            Write-Host "  ✅ `$db" -ForegroundColor Green
        } else {
            Write-Host "  ❌ `$db" -ForegroundColor Red
        }
    }
    catch {
        Write-Host "  ❌ `$db - Error" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "🔄 Auto-refresh every 30 seconds. Press Ctrl+C to exit."
"@
    
    Set-Content -Path "$optimizationDir/monitoring/dashboard.ps1" -Value $dashboardScript
    Write-Log "Created performance dashboard script" "SUCCESS"
}

# Main execution
Write-Host ""
Write-Host "⚡ Trans Bandung Microservices Performance Optimization" -ForegroundColor Cyan
Write-Host "=" * 60

# Initialize directory structure
Initialize-OptimizationStructure

# Create log file
if (-not (Test-Path (Split-Path $logFile -Parent))) {
    New-Item -ItemType Directory -Path (Split-Path $logFile -Parent) -Force | Out-Null
}
New-Item -ItemType File -Path $logFile -Force | Out-Null

Write-Log "Performance optimization script started" "INFO"
Write-Log "Target: $Target, Profile: $Profile" "INFO"

# Analyze system resources
$systemInfo = Get-SystemResources

# Recommend profile based on system resources
if (-not $Force -and $Profile -ne $systemInfo.recommended_profile) {
    Write-Host "💡 Recommended profile for your system: $($systemInfo.recommended_profile)" -ForegroundColor Yellow
    $confirm = Read-Host "Continue with $Profile profile? (y/N)"
    if ($confirm -ne "y" -and $confirm -ne "Y") {
        Write-Host "Operation cancelled." -ForegroundColor Yellow
        exit 0
    }
}

# Perform optimizations based on target
switch ($Target) {
    "containers" {
        Write-Host "🐳 Optimizing container resources..." -ForegroundColor Yellow
        Optimize-ContainerResources $Profile
    }
    
    "database" {
        Write-Host "🗄️  Optimizing database configuration..." -ForegroundColor Yellow
        Optimize-DatabaseConfiguration $Profile
    }
    
    "cache" {
        Write-Host "⚡ Optimizing cache configuration..." -ForegroundColor Yellow
        Optimize-CacheConfiguration $Profile
    }
    
    "network" {
        Write-Host "🌐 Optimizing network configuration..." -ForegroundColor Yellow
        Optimize-NetworkConfiguration $Profile
    }
    
    "all" {
        Write-Host "🔄 Performing all optimizations..." -ForegroundColor Yellow
        Optimize-ContainerResources $Profile
        Optimize-DatabaseConfiguration $Profile
        Optimize-CacheConfiguration $Profile
        Optimize-NetworkConfiguration $Profile
        Create-PerformanceDashboard
    }
}

# Set up monitoring
if ($Monitor) {
    Write-Host "📊 Setting up performance monitoring..." -ForegroundColor Yellow
    Monitor-Performance
}

# Apply optimizations if requested
if ($Force -or $Target -eq "all") {
    $apply = Read-Host "Apply optimizations now? This will restart services. (y/N)"
    if ($apply -eq "y" -or $apply -eq "Y") {
        Apply-Optimizations $Profile
    }
}

Write-Host ""
Write-Host "🏁 Performance Optimization Summary" -ForegroundColor Cyan
Write-Host "=" * 40
Write-Host "Profile: $Profile"
Write-Host "Target: $Target"
Write-Host "Optimization Directory: $optimizationDir"
Write-Host "Log File: $logFile"
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Review generated configuration files"
Write-Host "2. Apply configurations to your services"
Write-Host "3. Monitor performance using the dashboard"
Write-Host "4. Adjust configurations based on monitoring results"
Write-Host "5. Set up automated performance monitoring"

if (Test-Path "$optimizationDir/monitoring/dashboard.ps1") {
    Write-Host ""
    Write-Host "📊 Start performance dashboard with:" -ForegroundColor Cyan
    Write-Host "   .\optimization\monitoring\dashboard.ps1"
}

Write-Log "Performance optimization script completed" "INFO"
