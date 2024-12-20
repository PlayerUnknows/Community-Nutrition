# Community Nutrition Information System

## Installation

After cloning the project, follow these steps:
1. Install Node.js and npm
    Download and install Node.js from https://nodejs.org         (LTS version recommended).
    Verify the installation: 
        node -v
        npm -v
2. Run `composer install` to install all vendor libraries.

3. Set up the assets directory structure:
```
assets/
├── css/
│   ├── bootstrap.min.css
│   └── sweetalert2.css
├── dist/
│   ├── sweetalert.js
│   └── bootstrap.bundle.min.js
├── images/
│   └── [your custom images]
└── fontawesome-free-5.15.4-web/
    ├── css/
    │   └── all.css
    └── webfonts/
        └── [font files]
```

4. Download Font Awesome 5.15.4:
   - Go to https://github.com/FortAwesome/Font-Awesome/releases/tag/5.15.4
   - Download the zip file
   - Extract and copy the contents to `assets/fontawesome-free-5.15.4-web/`

5. Download required CSS and JS files:
   - Bootstrap 5.3.3 CSS and JS
   - SweetAlert2 CSS and JS

6. Place your custom images in the `assets/images/` directory. You can customize the images while maintaining the same directory structure.

7. Run npm install to install any required Node.js packages.



## Directory Structure

```
src/
├── controllers/
├── models/
├── view/
└── script/

assets/
[structure as shown above]
```

## WebSocket Real-Time Audit Trail

### Setup Instructions
1. Ensure PHP and Composer are installed
2. Run `composer install` to install dependencies
3. Start WebSocket server:
   - On Windows: Run `start_websocket.bat`
   - On Linux/Mac: `php src/websocket/audit_trail_server.php`

### Requirements
- PHP 7.4+
- Composer
- Node.js and npm
- Ratchet WebSocket Library

### Configuration
- WebSocket runs on `localhost:8080`
- Modify `src/script/audit_trail.js` to change WebSocket URL if needed

### WebSocket Troubleshooting

#### Common Issues
1. **WebSocket Server Not Running**
   - Ensure WebSocket server is started using `start_websocket.bat`
   - Check PHP process is running and not blocked by firewall

2. **Connection Errors**
   - Verify localhost is accessible
   - Check port 8080 is not in use by another application
   - Ensure PHP has socket extension enabled

3. **Diagnostic Steps**
   - Run `php src/backend/websocket_test.php` to check environment
   - Check server logs in `error_log` or console output

#### Firewall Configuration
- Allow PHP and WebSocket server through Windows Firewall
- Check antivirus software for potential blocking

#### Recommended PHP Settings
```ini
; In php.ini
extension=php_sockets.dll
```

#### Manual Verification
1. Start WebSocket server
2. Open browser console
3. Check for any connection or parsing errors
4. Verify real-time updates are received

## Note
- The `assets/images/` directory can contain your custom images
- Make sure to maintain the file structure for proper functionality
- You can modify the styles by editing the CSS files in `assets/css/`
