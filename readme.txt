=== Artistudio Popup ===
Contributors: M. Arief Rachman
Tags: popup, modal, vue.js, wordpress popup
Requires at least: WordPress 5.6
Tested up to: WordPress 6.5
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern WordPress popup plugin using Vue.js, Vuex and Vue Router with dynamic content loading via REST API.

== Description ==

Artistudio Popup is a lightweight but powerful popup plugin that lets you create and manage beautiful popups without page reloads. Built with modern JavaScript technologies for seamless user experience.

**Key Features:**
- Vue.js powered frontend with Vuex state management
- Vue Router for smooth navigation between popups
- Custom Post Type for managing popups
- Dynamic page selection dropdown
- REST API endpoint for data fetching
- Responsive design with clean CSS
- Admin interface for easy management

== Installation ==

1. Upload the `artistudio-popup` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create your first popup under 'Popups' in the WordPress admin
4. Use the [artistudio_popup] shortcode to display popups

== Usage ==

**Creating Popups:**
1. Go to WordPress Admin → Popups → Add New
2. Enter a title and description
3. Select the target page from the dropdown
4. Publish your popup

**Displaying Popups:**
- Use the [artistudio_popup] shortcode in any post/page
- The popup list will automatically appear where the shortcode is placed

**Frontend Features:**
- Click any popup to view details without page reload
- Clean, responsive design
- Dynamic data loading via REST API

== Frequently Asked Questions ==

= Why isn't my popup showing up? =
1. Make sure you've added the [artistudio_popup] shortcode
2. Verify you've created at least one published popup
3. Check browser console for JavaScript errors

= How do I change the popup style? =
Edit the CSS in assets/css/style.css or override styles in your theme.

= Can I use this with page builders? =
Yes! The shortcode works with most page builders like Elementor and WP Bakery.

== Screenshots ==
1. Popup list in admin dashboard
2. Popup editor with page selection
3. Frontend popup list view
4. Popup detail view

== Changelog ==

= 1.0 =
* Initial release with Vue.js integration
* Custom Post Type implementation
* REST API endpoint
* Admin interface for popup management

== Upgrade Notice ==

N/A - Initial release

== Roadmap ==
- Add popup display conditions
- Include animation options
- Add support for custom fields
- Implement A/B testing