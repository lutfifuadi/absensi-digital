# Create ZIP for VPS deployment
$zipName = "source-absensi.zip"
$excludeList = @("node_modules", "vendor", ".git", ".env", "storage/framework/cache/*", "storage/framework/sessions/*", "storage/framework/views/*", "storage/logs/*", "source-absensi.zip", "scratch/*")

# Filter files and compress
Get-ChildItem -Path . -Recurse | Where-Object {
    $itemPath = $_.FullName.Replace((Get-Location).Path + "\", "")
    $shouldExclude = $false
    foreach ($exclude in $excludeList) {
        if ($itemPath -like "*$exclude*") {
            $shouldExclude = $true
            break
        }
    }
    !$shouldExclude
} | Compress-Archive -DestinationPath $zipName -Force

Write-Host "ZIP created successfully: $zipName"
