#!/usr/bin/env powershell

# Enhanced Microservices Logs Viewer and Analyzer
# Advanced log viewing, filtering, and analysis capabilities

param(
    [string]$Service = "all",
    [int]$Lines = 100,
    [switch]$Follow = $false,
    [switch]$Timestamps = $true,
    [string]$Since = "",
    [string]$Level = "",
    [string]$Search = "",
    [switch]$Export = $false,
    [switch]$Stats = $false,
    [switch]$Errors = $false
)

Write-Host "📋 Trans Bandung Enhanced Logs Analyzer" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green

$composeFile = "docker-compose.services.yml"

# Available services with log patterns
$services = @{
    "user-service" = @{
        Name = "user-service"
        LogPatterns = @("ERROR", "WARN", "INFO", "DEBUG")
        HealthKeywords = @("healthy", "database", "redis")
    }
    "ticketing-service" = @{
        Name = "ticketing-service"
        LogPatterns = @("ERROR", "WARN", "INFO", "DEBUG", "booking", "schedule")
        HealthKeywords = @("healthy", "database", "redis")
    }
    "payment-service" = @{
        Name = "payment-service"
        LogPatterns = @("ERROR", "WARN", "INFO", "DEBUG", "payment", "transaction")
        HealthKeywords = @("healthy", "database", "redis", "secure")
    }
    "inbox-service" = @{
        Name = "inbox-service"
        LogPatterns = @("ERROR", "WARN", "INFO", "DEBUG", "message", "notification")
        HealthKeywords = @("healthy", "database", "redis")
    }
    "reviews-service" = @{
        Name = "reviews-service"
        LogPatterns = @("ERROR", "WARN", "INFO", "DEBUG", "review", "rating")
        HealthKeywords = @("healthy", "database", "redis")
    }
    "api-gateway" = @{
        Name = "api-gateway"
        LogPatterns = @("ERROR", "WARN", "INFO", "DEBUG", "request", "response", "proxy")
        HealthKeywords = @("healthy", "service", "connection")
    }
    "nginx-lb" = @{
        Name = "nginx-lb"
        LogPatterns = @("error", "warn", "info", "access")
        HealthKeywords = @("upstream", "backend", "health")
    }
}

function Write-LogOutput {
    param($Message, $Level = "INFO", $Service = "")
    $timestamp = Get-Date -Format "HH:mm:ss"
    $prefix = if ($Service) { "[$Service] " } else { "" }
    
    $color = switch ($Level) {
        "ERROR" { "Red" }
        "WARN" { "Yellow" }
        "SUCCESS" { "Green" }
        "INFO" { "Cyan" }
        default { "White" }
    }
    
    Write-Host "[$timestamp] $prefix$Message" -ForegroundColor $color
}

function Get-EnhancedLogs {
    param(
        [string]$ServiceName,
        [int]$TailLines,
        [bool]$FollowLogs,
        [bool]$ShowTimestamps,
        [string]$SinceTime,
        [string]$LogLevel,
        [string]$SearchTerm
    )
    
    $logArgs = @("-f", $composeFile, "logs")
    
    if ($TailLines -gt 0) {
        $logArgs += "--tail=$TailLines"
    }
    
    if ($FollowLogs) {
        $logArgs += "-f"
    }
    
    if ($ShowTimestamps) {
        $logArgs += "-t"
    }
    
    if ($SinceTime) {
        $logArgs += "--since=$SinceTime"
    }
    
    if ($ServiceName -ne "all") {
        $logArgs += $ServiceName
    }
    
    Write-LogOutput "Fetching logs for: $ServiceName" "INFO"
    Write-LogOutput "Arguments: $($logArgs -join ' ')" "INFO"
    
    if ($SearchTerm -or $LogLevel -or $Errors) {
        # Filtered output
        $logOutput = & docker-compose @logArgs 2>&1
        
        foreach ($line in $logOutput) {
            $shouldShow = $true
            
            # Filter by log level
            if ($LogLevel -and $line -notmatch $LogLevel) {
                $shouldShow = $false
            }
            
            # Filter by search term
            if ($SearchTerm -and $line -notmatch $SearchTerm) {
                $shouldShow = $false
            }
            
            # Show only errors
            if ($Errors -and $line -notmatch "ERROR|CRITICAL|FATAL|Exception|Failed") {
                $shouldShow = $false
            }
            
            if ($shouldShow) {
                # Color code based on content
                $color = "White"
                if ($line -match "ERROR|CRITICAL|FATAL|Exception") {
                    $color = "Red"
                } elseif ($line -match "WARN|WARNING") {
                    $color = "Yellow"
                } elseif ($line -match "INFO") {
                    $color = "Cyan"
                } elseif ($line -match "DEBUG") {
                    $color = "Gray"
                } elseif ($line -match "SUCCESS|healthy|connected") {
                    $color = "Green"
                }
                
                Write-Host $line -ForegroundColor $color
            }
        }
    } else {
        # Direct output
        & docker-compose @logArgs
    }
}
    }
    
    if ($ShowTimestamps) {
        $logArgs += "-t"
    }
    
    if ($SinceTime) {
        $logArgs += "--since=$SinceTime"
    }
    
    $logArgs += $ServiceName
    
    Write-Host "📋 Showing logs for: $ServiceName" -ForegroundColor Yellow
    if ($FollowLogs) {
        Write-Host "   Following logs (Press Ctrl+C to stop)..." -ForegroundColor Gray
    }
    Write-Host "   Lines: $TailLines | Timestamps: $ShowTimestamps" -ForegroundColor Gray
    Write-Host "----------------------------------------" -ForegroundColor Gray
    
    & docker-compose @logArgs
}

function Show-LogMenu {
    Write-Host "`n📋 Available Services:" -ForegroundColor Green
    for ($i = 0; $i -lt $services.Count; $i++) {
        $status = Get-ServiceStatus -ServiceName $services[$i]
        $statusColor = if ($status -eq "running") { "Green" } else { "Red" }
        Write-Host "  $($i + 1). $($services[$i])" -ForegroundColor $statusColor -NoNewline
        Write-Host " ($status)" -ForegroundColor Gray
    }
    Write-Host "  0. All services" -ForegroundColor Cyan
    Write-Host ""
}

function Get-ServiceStatus {
    param([string]$ServiceName)
    
    try {
        $status = docker-compose -f $composeFile ps $ServiceName --format "{{.State}}" 2>$null
        return if ($status) { $status.Trim() } else { "not found" }
    }
    catch {
        return "unknown"
    }
}

function Show-LogSummary {
    Write-Host "📊 Service Status Summary:" -ForegroundColor Green
    Write-Host "-------------------------" -ForegroundColor Green
    
    foreach ($svc in $services) {
        $status = Get-ServiceStatus -ServiceName $svc
        $statusColor = switch ($status) {
            "running" { "Green" }
            "exited" { "Red" }
            "restarting" { "Yellow" }
            default { "Gray" }
        }
        Write-Host "  $svc : " -NoNewline
        Write-Host $status -ForegroundColor $statusColor
    }
    Write-Host ""
}

try {
    if ($Service -eq "all") {
        if ($Follow) {
            Write-Host "📋 Following logs from all services..." -ForegroundColor Yellow
            Write-Host "   Press Ctrl+C to stop" -ForegroundColor Gray
            Show-ServiceLogs -ServiceName "" -TailLines $Lines -FollowLogs $true -ShowTimestamps $Timestamps -SinceTime $Since
        } else {
            Show-LogSummary
            
            $choice = Read-Host "Enter service number (0 for all, Enter for menu)"
            
            if ($choice -eq "" -or $choice -eq "menu") {
                Show-LogMenu
                return
            }
            
            if ($choice -eq "0") {
                Show-ServiceLogs -ServiceName "" -TailLines $Lines -FollowLogs $false -ShowTimestamps $Timestamps -SinceTime $Since
            } elseif ([int]$choice -ge 1 -and [int]$choice -le $services.Count) {
                $selectedService = $services[[int]$choice - 1]
                Show-ServiceLogs -ServiceName $selectedService -TailLines $Lines -FollowLogs $false -ShowTimestamps $Timestamps -SinceTime $Since
            } else {
                Write-Host "❌ Invalid choice" -ForegroundColor Red
            }
        }
    } elseif ($services -contains $Service) {
        Show-ServiceLogs -ServiceName $Service -TailLines $Lines -FollowLogs $Follow -ShowTimestamps $Timestamps -SinceTime $Since
    } else {
        Write-Host "❌ Unknown service: $Service" -ForegroundColor Red
        Write-Host "Available services:" -ForegroundColor Gray
        foreach ($svc in $services) {
            Write-Host "  - $svc" -ForegroundColor Gray
        }
    }

    # Show helpful commands
    if (!$Follow) {
        Write-Host "`n💡 Helpful Commands:" -ForegroundColor Green
        Write-Host "  Follow logs: .\logs-service.ps1 -Service $Service -Follow" -ForegroundColor Gray
        Write-Host "  With timestamps: .\logs-service.ps1 -Service $Service -Timestamps" -ForegroundColor Gray
        Write-Host "  Since time: .\logs-service.ps1 -Service $Service -Since '1h'" -ForegroundColor Gray
        Write-Host "  More lines: .\logs-service.ps1 -Service $Service -Lines 500" -ForegroundColor Gray
    }

}
catch {
    Write-Host "`n❌ Error viewing logs: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Make sure the services are running with: .\deploy-services.ps1" -ForegroundColor Gray
    exit 1
}
