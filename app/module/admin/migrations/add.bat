@echo off
Setlocal EnableDelayedExpansion
set RUN_DIR=%~dp0
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
set "MIGRATION=%YYYY%-%MM%-%DD%T%HH%-%Min%-%Sec%"
set "DATETIME=%DD%.%MM%.%YYYY% %HH%:%Min%:%Sec%"

for /f "tokens=2 delims==" %%i in ('git config user.name') do set USERNAME=%%i
for /f %%i in ('git config user.email') do set EMAIL=%%i
set NEWFILE=%MIGRATION%.sql
set "AUTHOR=%USERNAME% <%EMAIL%>"

COPY "%RUN_DIR%_template.sql" "%RUN_DIR%%NEWFILE%"

powershell -Command "(gc -Raw '%RUN_DIR%%NEWFILE%') -replace '_DATETIME_', '%DATETIME%' | Out-File -Encoding UTF8 '%RUN_DIR%%NEWFILE%'"
powershell -Command "(gc -Raw '%RUN_DIR%%NEWFILE%') -replace '_MIGRATION_', '%MIGRATION%' | Out-File -Encoding UTF8 '%RUN_DIR%%NEWFILE%'"
powershell -Command "(gc -Raw '%RUN_DIR%%NEWFILE%') -replace '_AUTHOR_', '%AUTHOR%' | Out-File -Encoding UTF8 '%RUN_DIR%%NEWFILE%'"
powershell -Command "(gc -Raw '%RUN_DIR%%NEWFILE%') -replace '_NEWFILE_', '%NEWFILE%' | Out-File -Encoding UTF8 '%RUN_DIR%%NEWFILE%'"

powershell -Command "($MyFile = Get-Content '%RUN_DIR%%NEWFILE%') ; ($Utf8NoBomEncoding = New-Object System.Text.UTF8Encoding $False) ; ([System.IO.File]::WriteAllLines('%RUN_DIR%%NEWFILE%', $MyFile, $Utf8NoBomEncoding))"
