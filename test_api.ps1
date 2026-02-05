$ErrorActionPreference = "Stop"
$baseUrl = "http://localhost:8080"

function Test-Endpoint {
    param($Method, $Uri, $Body, $Token, $Description)
    
    Write-Host "Testing: $Description..." -NoNewline
    
    $headers = @{"Content-Type" = "application/json"}
    if ($Token) { $headers["Authorization"] = "Bearer $Token" }
    
    try {
        if ($Body) {
            $response = Invoke-RestMethod -Method $Method -Uri "$baseUrl$Uri" -Body ($Body | ConvertTo-Json) -Headers $headers -ErrorAction Stop
        } else {
            $response = Invoke-RestMethod -Method $Method -Uri "$baseUrl$Uri" -Headers $headers -ErrorAction Stop
        }
        Write-Host " [OK]" -ForegroundColor Green
        return $response
    } catch {
        Write-Host " [FAILED]" -ForegroundColor Red
        Write-Host $_.Exception.Message
        if ($_.Exception.Response) {
            $reader = New-Object System.IO.StreamReader $_.Exception.Response.GetResponseStream()
            Write-Host $reader.ReadToEnd()
        }
        return $null
    }
}

# 1. Register
$email = "test-$(Get-Random)@example.com"
$pass = "password123"
Test-Endpoint -Method POST -Uri "/auth/register" -Description "Register New User" -Body @{email=$email; password=$pass} | Out-Null

# 2. Login
$tokens = Test-Endpoint -Method POST -Uri "/auth/login" -Description "Login" -Body @{email=$email; password=$pass}
$accessToken = $tokens.accessToken
$refreshToken = $tokens.refreshToken

if (!$accessToken) { Write-Error "Login failed, cannot proceed." }

# 3. Get Me
Test-Endpoint -Method GET -Uri "/me" -Description "Get Profile (/me)" -Token $accessToken | Select-Object id, email | Format-Table

# 4. Refresh Token
$newTokens = Test-Endpoint -Method POST -Uri "/auth/refresh" -Description "Refresh Token" -Body @{refreshToken=$refreshToken}
$newAccessToken = $newTokens.accessToken

# 5. Admin Flow (Login as Admin)
$adminTokens = Test-Endpoint -Method POST -Uri "/auth/login" -Description "Admin Login" -Body @{email="admin@example.com"; password="secret123"}
$adminToken = $adminTokens.accessToken

if ($adminToken) {
    # 6. List Users (Admin only)
    Test-Endpoint -Method GET -Uri "/admin/users" -Description "Admin: List Users" -Token $adminToken | Select-Object id, email | Format-Table
    
    # 7. Check Permissions (Should fail for normal user)
    Write-Host "Testing: Access Forbidden for Normal User..." -NoNewline
    try {
        Invoke-RestMethod -Method GET -Uri "$baseUrl/admin/users" -Headers @{"Authorization"="Bearer $accessToken"} -ErrorAction Stop
        Write-Host " [Unwanted Success]" -ForegroundColor Red
    } catch {
        if ($_.Exception.Response.StatusCode -eq 403) {
            Write-Host " [OK - 403 Forbidden]" -ForegroundColor Green
        } else {
            Write-Host " [FAILED - $($_.Exception.Message)]" -ForegroundColor Red
        }
    }
}
