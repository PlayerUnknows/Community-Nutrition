# Community Nutrition Information System

## Installation

After cloning the project, follow these steps:

1. Run `composer install` to install all vendor libraries.

2. Set up the assets directory structure:
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

3. Download Font Awesome 5.15.4:
   - Go to https://github.com/FortAwesome/Font-Awesome/releases/tag/5.15.4
   - Download the zip file
   - Extract and copy the contents to `assets/fontawesome-free-5.15.4-web/`

4. Download required CSS and JS files:
   - Bootstrap 5.3.3 CSS and JS
   - SweetAlert2 CSS and JS

5. Place your custom images in the `assets/images/` directory. You can customize the images while maintaining the same directory structure.

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

## Note
- The `assets/images/` directory can contain your custom images
- Make sure to maintain the file structure for proper functionality
- You can modify the styles by editing the CSS files in `assets/css/`
