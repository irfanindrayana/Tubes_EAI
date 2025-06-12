#!/usr/bin/env powershell

<#
.SYNOPSIS
    Security Hardening Script for Trans Bandung Microservices
.DESCRIPTION
    Applies security configurations, SSL/TLS setup, and security policies
.PARAMETER Action
    Action to perform (harden, ssl, scan, audit, restore)
.PARAMETER Domain
    Domain for SSL certificate
.PARAMETER Force
    Force operations without confirmation
#>

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("harden", "ssl", "scan", "audit", "restore", "all")]
    [string]$Action = "harden",
    
    [Parameter(Mandatory=$false)]
    [string]$Domain = "localhost",
    
    [Parameter(Mandatory=$false)]
    [switch]$Force,
    
    [Parameter(Mandatory=$false)]
    [switch]$Verbose
)

# Configuration
$securityConfigDir = "security"
$certDir = "$securityConfigDir/certs"
$logFile = "storage/logs/security-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').log"

function Write-Log {
    param($Message, $Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    
    Write-Host $logMessage
    if (Test-Path $logFile) {
        Add-Content -Path $logFile -Value $logMessage
    }
}

function Initialize-SecurityStructure {
    Write-Log "Initializing security directory structure..." "INFO"
    
    $directories = @(
        $securityConfigDir,
        $certDir,
        "$securityConfigDir/policies",
        "$securityConfigDir/firewall",
        "storage/logs"
    )
    
    foreach ($dir in $directories) {
        if (-not (Test-Path $dir)) {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
            Write-Log "Created directory: $dir" "INFO"
        }
    }
}

function Create-SSLCertificates {
    param($Domain)
    
    Write-Log "Generating SSL certificates for domain: $Domain" "INFO"
    
    # Check if OpenSSL is available
    try {
        $null = openssl version
    }
    catch {
        Write-Log "OpenSSL not found. Installing..." "WARNING"
        # Try to install OpenSSL via chocolatey or provide instructions
        Write-Log "Please install OpenSSL manually or via chocolatey: choco install openssl" "ERROR"
        return $false
    }
    
    $keyFile = "$certDir/$Domain.key"
    $certFile = "$certDir/$Domain.crt"
    $csrFile = "$certDir/$Domain.csr"
    
    # Generate private key
    Write-Log "Generating private key..." "INFO"
    openssl genrsa -out $keyFile 2048
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Failed to generate private key" "ERROR"
        return $false
    }
    
    # Create certificate signing request
    Write-Log "Creating certificate signing request..." "INFO"
    $subject = "/C=ID/ST=West Java/L=Bandung/O=Trans Bandung/OU=IT Department/CN=$Domain"
    openssl req -new -key $keyFile -out $csrFile -subj $subject
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Failed to create CSR" "ERROR"
        return $false
    }
    
    # Generate self-signed certificate
    Write-Log "Generating self-signed certificate..." "INFO"
    openssl x509 -req -days 365 -in $csrFile -signkey $keyFile -out $certFile
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Failed to generate certificate" "ERROR"
        return $false
    }
    
    # Set secure permissions
    if ($IsWindows) {
        icacls $keyFile /inheritance:r /grant:r "$($env:USERNAME):(R)" | Out-Null
    } else {
        chmod 600 $keyFile
    }
    
    Write-Log "SSL certificates generated successfully" "SUCCESS"
    return $true
}

function Create-NginxSSLConfig {
    $sslConfig = @"
# SSL Configuration for Trans Bandung Microservices
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA384;
ssl_prefer_server_ciphers off;

# SSL Session Cache
ssl_session_cache shared:SSL:1m;
ssl_session_timeout 5m;

# OCSP Stapling
ssl_stapling on;
ssl_stapling_verify on;

# Security Headers
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

# Hide Nginx version
server_tokens off;
"@
    
    $sslConfigFile = "$securityConfigDir/nginx-ssl.conf"
    Set-Content -Path $sslConfigFile -Value $sslConfig
    Write-Log "Created Nginx SSL configuration: $sslConfigFile" "INFO"
}

function Create-SecurityPolicies {
    Write-Log "Creating security policies..." "INFO"
    
    # Docker security policy
    $dockerSecurityPolicy = @"
# Docker Security Policy for Trans Bandung Microservices

# 1. Container Runtime Security
- Run containers as non-root user
- Use read-only root filesystems where possible
- Limit container capabilities
- Use security profiles (AppArmor/SELinux)

# 2. Network Security
- Use custom bridge networks
- Implement network segmentation
- Restrict inter-container communication
- Use TLS for all service communication

# 3. Image Security
- Use official base images only
- Scan images for vulnerabilities
- Use multi-stage builds
- Keep base images updated

# 4. Secrets Management
- Never embed secrets in images
- Use Docker secrets or external secret managers
- Rotate secrets regularly
- Use least privilege access

# 5. Monitoring and Logging
- Enable container logging
- Monitor resource usage
- Set up alerts for suspicious activity
- Regular security audits
"@
    
    Set-Content -Path "$securityConfigDir/policies/docker-security.md" -Value $dockerSecurityPolicy
    
    # Database security policy
    $dbSecurityPolicy = @"
# Database Security Policy

# 1. Access Control
- Use dedicated database users per service
- Implement least privilege access
- Use strong passwords
- Enable audit logging

# 2. Network Security
- Restrict database network access
- Use TLS for database connections
- Implement database firewalls
- Regular connection monitoring

# 3. Data Protection
- Encrypt data at rest
- Encrypt data in transit
- Regular backups with encryption
- Secure backup storage

# 4. Monitoring
- Monitor failed login attempts
- Log all database operations
- Set up alerts for suspicious queries
- Regular security assessments
"@
    
    Set-Content -Path "$securityConfigDir/policies/database-security.md" -Value $dbSecurityPolicy
    
    Write-Log "Security policies created" "SUCCESS"
}

function Create-FirewallRules {
    Write-Log "Creating firewall configuration..." "INFO"
    
    $firewallConfig = @"
# Firewall Rules for Trans Bandung Microservices

# Allow essential services only
# HTTP/HTTPS
80/tcp
443/tcp

# Service ports (internal network only)
8000/tcp  # API Gateway
8001/tcp  # User Service
8002/tcp  # Ticketing Service
8003/tcp  # Payment Service
8004/tcp  # Inbox Service
8005/tcp  # Reviews Service
8080/tcp  # phpMyAdmin (development only)

# Database ports (internal network only)
3306/tcp  # MySQL

# Redis (internal network only)
6379/tcp  # Redis

# SSH (if needed for management)
22/tcp

# Deny all other traffic by default
"@
    
    Set-Content -Path "$securityConfigDir/firewall/rules.conf" -Value $firewallConfig
    Write-Log "Firewall configuration created" "INFO"
}

function Scan-ContainerSecurity {
    Write-Log "Scanning container security..." "INFO"
    
    # Check if containers are running as root
    $containers = docker ps --format "table {{.Names}}\t{{.Image}}"
    
    foreach ($line in $containers -split "`n" | Select-Object -Skip 1) {
        if ($line.Trim()) {
            $containerName = ($line -split "\t")[0]
            if ($containerName -and $containerName -ne "NAMES") {
                Write-Log "Checking container: $containerName" "INFO"
                
                # Check user
                $user = docker exec $containerName whoami 2>$null
                if ($user -eq "root") {
                    Write-Log "WARNING: Container $containerName running as root" "WARNING"
                } else {
                    Write-Log "Container $containerName running as user: $user" "INFO"
                }
                
                # Check for security updates
                try {
                    $updates = docker exec $containerName apk list --upgradable 2>$null
                    if ($updates) {
                        Write-Log "Security updates available for $containerName" "WARNING"
                    }
                }
                catch {
                    # Non-Alpine containers
                }
            }
        }
    }
}

function Audit-SecurityConfiguration {
    Write-Log "Performing security audit..." "INFO"
    
    $auditResults = @()
    
    # Check SSL certificates
    if (Test-Path "$certDir/$Domain.crt") {
        $auditResults += "✅ SSL certificate exists"
        
        # Check certificate expiry
        try {
            $certInfo = openssl x509 -in "$certDir/$Domain.crt" -text -noout
            if ($certInfo -match "Not After\s*:\s*(.+)") {
                $expiryDate = [DateTime]::Parse($matches[1])
                $daysToExpiry = ($expiryDate - (Get-Date)).Days
                
                if ($daysToExpiry -lt 30) {
                    $auditResults += "⚠️  SSL certificate expires in $daysToExpiry days"
                } else {
                    $auditResults += "✅ SSL certificate valid for $daysToExpiry days"
                }
            }
        }
        catch {
            $auditResults += "❌ Could not check SSL certificate expiry"
        }
    } else {
        $auditResults += "❌ SSL certificate not found"
    }
    
    # Check Docker Compose security settings
    $composeFile = "docker-compose.services.yml"
    if (Test-Path $composeFile) {
        $composeContent = Get-Content $composeFile -Raw
        
        if ($composeContent -match "user:") {
            $auditResults += "✅ Non-root users configured in Docker Compose"
        } else {
            $auditResults += "⚠️  Consider adding non-root users to Docker Compose"
        }
        
        if ($composeContent -match "read_only:") {
            $auditResults += "✅ Read-only filesystems configured"
        } else {
            $auditResults += "⚠️  Consider using read-only filesystems"
        }
    }
    
    # Check environment file security
    $envFiles = Get-ChildItem "envs/*.env" -ErrorAction SilentlyContinue
    foreach ($envFile in $envFiles) {
        if ($IsWindows) {
            $permissions = (Get-Acl $envFile.FullName).Access
            $publicRead = $permissions | Where-Object { $_.IdentityReference -like "*Everyone*" -or $_.IdentityReference -like "*Users*" }
            if ($publicRead) {
                $auditResults += "⚠️  Environment file $($envFile.Name) has public read access"
            } else {
                $auditResults += "✅ Environment file $($envFile.Name) has restricted access"
            }
        }
    }
    
    # Generate audit report
    $auditReport = @"
# Security Audit Report
Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

## Audit Results
$($auditResults -join "`n")

## Recommendations
1. Regularly update base images and dependencies
2. Implement network segmentation with Docker networks
3. Use secrets management for sensitive data
4. Enable container security scanning in CI/CD
5. Implement log monitoring and alerting
6. Regular security assessments and penetration testing
7. Use HTTPS for all external communications
8. Implement rate limiting and DDoS protection
9. Regular backup testing and disaster recovery planning
10. Security training for development team

## Next Steps
- Review and address any warnings or errors above
- Schedule regular security audits
- Implement automated security scanning
- Update security policies as needed
"@
    
    $auditFile = "$securityConfigDir/audit-report-$(Get-Date -Format 'yyyy-MM-dd').md"
    Set-Content -Path $auditFile -Value $auditReport
    
    Write-Log "Security audit completed. Report saved to: $auditFile" "SUCCESS"
    Write-Host ""
    Write-Host "Audit Results:" -ForegroundColor Cyan
    foreach ($result in $auditResults) {
        Write-Host "  $result"
    }
}

function Apply-SecurityHardening {
    Write-Log "Applying security hardening..." "INFO"
    
    # Create hardened Docker Compose file
    $hardenedCompose = @"
version: '3.8'

# Security-hardened version of docker-compose.services.yml
# This file includes additional security configurations

x-common-security: &common-security
  user: "1000:1000"  # Non-root user
  read_only: true
  tmpfs:
    - /tmp
    - /var/tmp
  cap_drop:
    - ALL
  cap_add:
    - CHOWN
    - SETGID
    - SETUID
  security_opt:
    - no-new-privileges:true

networks:
  transbandung-microservices:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16

# Note: Apply these security settings to existing services in docker-compose.services.yml
# This is a template showing recommended security configurations
"@
    
    Set-Content -Path "$securityConfigDir/docker-compose.security.yml" -Value $hardenedCompose
    Write-Log "Created security-hardened Docker Compose template" "INFO"
    
    # Create secure environment template
    $secureEnvTemplate = @"
# Secure Environment Configuration Template

# Database
DB_HOST=service-db
DB_DATABASE=service_database
DB_USERNAME=service_user
# DB_PASSWORD should be set via Docker secrets or external secret manager

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=# Use strong password

# Service Configuration
SERVICE_NAME=service-name
SERVICE_PORT=8000
SERVICE_ENVIRONMENT=production

# Security Settings
APP_DEBUG=false
APP_ENV=production
LOG_LEVEL=warning

# Session Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# CORS
CORS_ALLOWED_ORIGINS=https://yourdomain.com

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60

# JWT Security
JWT_SECRET=# Generate strong secret
JWT_TTL=60

# Enable security features
ENABLE_CSRF_PROTECTION=true
ENABLE_XSS_PROTECTION=true
ENABLE_CONTENT_SECURITY_POLICY=true
"@
    
    Set-Content -Path "$securityConfigDir/.env.security.template" -Value $secureEnvTemplate
    Write-Log "Created secure environment template" "INFO"
}

# Main execution
Write-Host ""
Write-Host "🔒 Trans Bandung Microservices Security Hardening" -ForegroundColor Cyan
Write-Host "=" * 60

# Initialize directory structure
Initialize-SecurityStructure

# Create log file
if (-not (Test-Path (Split-Path $logFile -Parent))) {
    New-Item -ItemType Directory -Path (Split-Path $logFile -Parent) -Force | Out-Null
}
New-Item -ItemType File -Path $logFile -Force | Out-Null

Write-Log "Security hardening script started" "INFO"
Write-Log "Action: $Action, Domain: $Domain" "INFO"

switch ($Action) {
    "harden" {
        Write-Host "🛡️  Applying security hardening..." -ForegroundColor Yellow
        Apply-SecurityHardening
        Create-SecurityPolicies
        Create-FirewallRules
        Write-Host "✅ Security hardening completed" -ForegroundColor Green
    }
    
    "ssl" {
        Write-Host "🔐 Setting up SSL/TLS..." -ForegroundColor Yellow
        if (Create-SSLCertificates $Domain) {
            Create-NginxSSLConfig
            Write-Host "✅ SSL/TLS setup completed" -ForegroundColor Green
        } else {
            Write-Host "❌ SSL/TLS setup failed" -ForegroundColor Red
            exit 1
        }
    }
    
    "scan" {
        Write-Host "🔍 Scanning container security..." -ForegroundColor Yellow
        Scan-ContainerSecurity
        Write-Host "✅ Security scan completed" -ForegroundColor Green
    }
    
    "audit" {
        Write-Host "📋 Performing security audit..." -ForegroundColor Yellow
        Audit-SecurityConfiguration
        Write-Host "✅ Security audit completed" -ForegroundColor Green
    }
    
    "all" {
        Write-Host "🔄 Performing all security operations..." -ForegroundColor Yellow
        Apply-SecurityHardening
        Create-SecurityPolicies
        Create-FirewallRules
        Create-SSLCertificates $Domain
        Create-NginxSSLConfig
        Scan-ContainerSecurity
        Audit-SecurityConfiguration
        Write-Host "✅ All security operations completed" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "🏁 Security Configuration Summary" -ForegroundColor Cyan
Write-Host "=" * 40
Write-Host "Security Directory: $securityConfigDir"
Write-Host "Certificates: $certDir"
Write-Host "Log File: $logFile"
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Review generated security configurations"
Write-Host "2. Apply SSL certificates to Nginx configuration"
Write-Host "3. Update Docker Compose with security settings"
Write-Host "4. Implement firewall rules on your system"
Write-Host "5. Set up monitoring and alerting"

Write-Log "Security hardening script completed" "INFO"
