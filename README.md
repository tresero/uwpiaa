# Idloom Events Attendee Display

A WordPress plugin for displaying attendee information from the Idloom Events API with sorting, filtering, and pagination capabilities.

## Features

- Displays attendees in a sortable table
- Real-time search filtering (minimum 3 characters)
- Pagination (20 attendees per page)
- Sortable columns for attendee details
- Displays primary cast, other casts, and country information
- API integration with Idloom Events
- Admin configuration panel
- Caching system (5min primary, 1hr backup)
- Support for large events (200+ attendees)

## Installation

1. Download the plugin files
2. Upload to your `/wp-content/plugins/` directory
3. Activate the plugin through WordPress admin menu
4. Navigate to "Idloom Settings" and configure

## Configuration

1. Navigate to "Idloom Settings" in WordPress admin menu
2. Enter your API Key
3. Enter your Event ID
4. Save settings
5. (Optional) Monitor cache status in Cache Status submenu

## Usage

Use the shortcode `[display_attendees]` in any post or page to display the attendee list.

## File Structure

- idloom-attendee-display.php
- includes/
  - class-api-handler.php
  - class-admin.php
  - class-display.php
- templates/
  - admin-page.php
  - attendee-list.php
  - cache-status.php
- assets/
  - css/style.css
  - js/script.js

## API Requirements

- Requires valid Idloom Events API credentials
- API must return attendee data with:
  - firstname
  - lastname
  - free_field12 (Primary Cast)
  - free_field40 (Other Casts)
  - cpy_country
  - free_field56 (Who's Registered List visibility)

## Display Fields

- First Name
- Last Name
- Primary Cast
- Other Casts (comma-separated)
- Country

## Filtering

Only displays attendees with:
- registration_status = 'Complete'
- payment_status = 'Paid'
- free_field56 = true (Who's Registered List permission)

## Dependencies

- WordPress 5.0+
- PHP 7.2+
- jQuery (included with WordPress)
- Dashicons (included with WordPress)

## Development

### API Handler
Handles API communication, caching, and data filtering. Includes pagination support for large datasets.

### Admin Panel
Manages plugin settings and cache monitoring through WordPress admin interface.

### Display Handler
Controls frontend display, sorting, searching, and shortcode functionality.

## Debug Mode
Enable WP_DEBUG in wp-config.php for detailed logging to wp-content/uploads/idloom-debug.log.

## Styling

Custom CSS classes for styling:
- .attendee-list
- .attendee-search
- .attendee-table
- .sortable
- .pagination

## License

GPL v3 or later