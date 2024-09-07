# Zillow Listing Scraper Chrome Extension

## Overview

The Zillow Listing Scraper is a Chrome extension that allows users to easily scrape listing data from Zillow property pages and optionally send this data to a WordPress site. This tool is designed for real estate professionals, investors, or researchers who need to quickly collect and analyze property information from Zillow.

## Features

- Scrape key information from Zillow listing pages
- Extract property images
- Display scraped data within the extension popup
- Option to send collected data to a WordPress site

## Installation

1. Clone this repository or download the source code.
2. Open Google Chrome and navigate to `chrome://extensions/`.
3. Enable "Developer mode" in the top right corner.
4. Click "Load unpacked" and select the directory containing the extension files.

## Usage

1. Navigate to a Zillow listing page.
2. Click on the Zillow Listing Scraper extension icon in your Chrome toolbar.
3. In the popup, click "Scrape Listing" to extract data from the current page.
4. Review the scraped data displayed in the popup.
5. If desired, click "Send to WordPress" to transmit the data to your configured WordPress site.

## Files and Structure

- `manifest.json`: Extension configuration file
- `background.js`: Background script for handling extension events
- `content.js`: Content script for interacting with Zillow pages
- `popup.html`: HTML structure for the extension popup
- `popup.js`: JavaScript for popup functionality
- `icon.png`: Extension icon (you'll need to add this)

## Configuration

To send data to your WordPress site, you'll need to update the WordPress endpoint URL in `popup.js`:

```javascript
fetch('https://your-wordpress-site.com/wp-json/zls/v1/submit-listing', {
    // ... (existing code)
});
