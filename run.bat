@echo off
title PlayGame Web Server
cls
echo Starting PlayGame Platform on port 8000...
echo.
echo Local IP Addresses:
ipconfig | findstr /i "ipv4"
echo.
echo Open in browser: http://localhost:8000
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause

