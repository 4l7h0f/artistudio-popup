# Artistudio Popup

**Artistudio Popup** is a modern WordPress plugin built with Vue.js that enables dynamic, modal popup displays without page reloads. Designed for developers and users alike, it leverages the WordPress REST API, Vuex, and Vue Router for a clean, responsive, and reactive user experience.

## 🧰 Tech Stack

- Vue.js + Vuex + Vue Router (Frontend)
- WordPress Plugin Boilerplate (Backend)
- REST API Integration
- PHP 8.2+
- Node.js 20+

---

## 🚀 Getting Started (Development Setup)

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/artistudio-popup.git
cd artistudio-popup
```
### 2. Install PHP Dependencies

```bash
composer install
```
### 3. Install JavaScript Dependencies

```bash
npm install
```
### 4. Build Assets
For development with live reload:
```bash
npm run dev
```
For production:
```bash
npm run build
```
### 5. Set Up in WordPress

- Copy or symlink the plugin directory into your wp-content/plugins/ folder.
- Activate the plugin from the WordPress admin.
- Use the [artistudio_popup] shortcode to render popups.

## 💡 Plugin Usage

- Create popups under Popups > Add New
- Use [artistudio_popup] shortcode in posts/pages
- Popups are dynamically fetched via REST API

## 📦 Requirements

- PHP 8.2 or higher
- Node.js v20 or higher
- WordPress 6.5.4 or higher
- Composer 2+
- NPM 8+

## 📄 License
GPLv2 or later
https://www.gnu.org/licenses/gpl-2.0.html
