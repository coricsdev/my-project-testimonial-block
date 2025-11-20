#  My Project Testimonial Block  
A Custom ACF + React Gutenberg Slider Block for WordPress

This plugin provides a modern **testimonial slider block** for the WordPress Gutenberg editor.  
It uses **ACF (Advanced Custom Fields)** for data storage and **React** for rendering the editor preview and frontend output — ensuring true WYSIWYG consistency.

---

##  Features

- 🧩 Gutenberg block built with ACF  
- 🎛️ Supports **multiple testimonials** via ACF Repeater  
- ⚛️ React-powered editor preview and frontend rendering  
- 🎠 Automatic slider (2-second autoplay)  
- 🔘 Dot navigation for manual slide control  
- 📱 Fully responsive layout  
- 🛡️ Sanitized HTML using `wp_kses_post()`  
- 🎨 Clean and modern testimonial card UI  

---

##  Folder Structure
```bash
my-project-testimonial-block/
│── my-project-testimonial-block.php
│── src/
│ └── index.js
│── build/
│ └── index.js
│── assets/
│ ├── css/
│ │ └── testimonial-card.css
│ └── img/
│ └── default-avatar.png
│── package.json
│── package-lock.json
│── README.md
│── .gitignore
```

---

## Installation

### 1. Clone or download the plugin

```bash
cd wp-content/plugins/
git clone https://github.com/coricsdev/my-project-testimonial-block.git
```
### 2. Install dependencies
```bash
npm install
```

### 3. Build assets
```bash
npm run build
```

### 4. Activate the plugin
Go to your WordPress admin:

Plugins → Installed Plugins → Activate "My Project – Testimonial Block"

### 5. Ensure ACF Pro (or ACF with block support) plugin is active
Go to your WordPress admin and install ACF Pro or any ACF with block support plugin

---

## Using the Block

**Add Testimonials (Via ACF)**

The plugin automatically registers an ACF Field Group for:

- Name
- Role
- Testimonial Text
- Image

You can add multiple testimonials using the Repeater.

**Insert the Block**

Inside Gutenberg:
- Click + Add Block
- Search for **Testimonial**
- Add/edit testimonial rows in the ACF fields area

---

## Slider Behavior

- Automatically slides every 2 seconds
- Users can click dots to switch slides manually
- Uses React for smooth UI transitions
- Works seamlessly on mobile and desktop

---

## React Rendering

This block uses a shared React component for:
- The Gutenberg editor preview
- The frontend output

Data flow:
- ACF values → PHP render_callback()
- PHP outputs a wrapper with a JSON data-* attribute
- React mounts and renders the slider using the JSON data

This ensures editor = frontend visually (true WYSIWYG).

---

## Styling

Main stylesheet:
```bash
assets/css/testimonial-card.css
```

Includes:

- Card layout styling
- Avatar circle cropping
- Typography improvements
- Slider dot styling
- Responsive adjustments
- Shadow + spacing for modern aesthetic


---

## Development
Run in watch mode:
```bash
npm start
```
Build for production:
```bash
npm run build
```

---

## Requirements:
- WordPress 6+
- ACF Pro 6+ (or ACF Free + ACF Blocks enabled)
- PHP 8.0+
- Node.js 16+ (for build tools)

##  AI Usage Note

- AI was used to assist with adding code comments and generating this README.md file.  
- All functionality, logic, and final implementation were reviewed and validated manually.
