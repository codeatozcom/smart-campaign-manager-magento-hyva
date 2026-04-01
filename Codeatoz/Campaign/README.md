# Codeatoz Smart Campaign Builder — Free Edition

[![Magento 2](https://img.shields.io/badge/Magento-2.4.6%2B-orange.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2F8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-OSL--3.0-green.svg)](https://opensource.org/licenses/OSL-3.0)
[![Theme](https://img.shields.io/badge/Theme-Hyvä%20%7C%20Luma-purple.svg)](https://hyva.io)

A visual marketing campaign automation module for Magento 2. Create scheduled promotional campaigns that automatically activate and expire — no manual work required.

Works with **Hyvä**, **Luma**, and any standard Magento 2 theme.

---

## Screenshots

> Admin → Codeatoz → Smart Campaigns

**Campaign Grid with Stats Dashboard**
- Live status badges (Active, Scheduled, Draft, Expired)
- Conflict warnings when multiple campaigns overlap
- Upcoming campaign notices

**Campaign Edit Form**
- General settings, promo bar configuration, product badge settings
- Live preview without saving
- Auto status based on dates

**Frontend**
- Full-width promo bar with countdown timer and Shop Now button
- Product badge on PDP, category pages, and search results

---

## Features

### Admin
- **Stats Dashboard** — Active / Scheduled / Draft / Expired counts at a glance
- **Campaign Types** — General, Flash Sale, Weekend Deal, Clearance, Seasonal, Product Launch
- **Smart Status** — Auto-transitions: Draft → Scheduled → Active → Expired based on dates
- **Conflict Detection** — Warning when multiple campaigns are active simultaneously
- **Upcoming Notice** — See what campaigns are about to start
- **Duplicate** — Clone any campaign as a Draft
- **Live Preview** — Preview the promo bar before saving
- **Quick Actions** — Schedule, Deactivate, Edit, Duplicate, Delete per row

### Frontend
- **Promo Bar** — Full-width announcement bar at the top of every page
  - Custom background and text color
  - Live countdown timer to campaign end date
  - Optional close button with 24-hour localStorage persistence
  - Optional "Shop Now" button with custom label linking to any URL
- **Product Badge** — Text badge overlaid on product images
  - PDP (Product Detail Page)
  - CLP (Category Listing Page)
  - Search results page

### Automation
- **Cron Job** — Runs every 5 minutes, auto-activates and auto-expires campaigns
- **Redis Compatible** — Active campaign cached, not queried on every page load
- **Instant Save Correction** — Status auto-corrected on save without waiting for cron

---

## Requirements

| Requirement | Version |
|---|---|
| Magento | 2.4.6+ |
| PHP | 8.1 or 8.2 |
| Theme | Hyvä, Luma, or any standard Magento 2 theme |
| Cache | Redis recommended (also works with default cache) |

---

## Installation

### Via Composer (recommended)

```bash
composer require codeatoz/module-campaign
bin/magento module:enable Codeatoz_Campaign
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

### Manual

1. Download the ZIP from GitHub releases
2. Extract to `app/code/Codeatoz/Campaign/`
3. Run:

```bash
bin/magento module:enable Codeatoz_Campaign
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

---

## Usage

### Creating your first campaign

1. Go to **Codeatoz → Smart Campaigns** in the Magento admin sidebar
2. Click **Create New Campaign**
3. Fill in:
   - **Campaign Name** — internal reference name
   - **Campaign Type** — Flash Sale, Weekend Deal, etc.
   - **Start Date / End Date** — status is auto-set based on these
   - **Priority** — higher number wins when campaigns overlap
4. Configure the **Promo Bar** — enable it, set your message, colors, countdown, and optional CTA button
5. Configure the **Product Badge** — enable it and set badge text (e.g. SALE, HOT, NEW)
6. Click **Save Campaign**

The campaign will activate automatically within 5 minutes when cron runs.

**To activate immediately:**
```bash
bin/magento cron:run --group default
```

### Understanding campaign statuses

| Status | Meaning |
|---|---|
| **Draft** | Not scheduled — admin is still setting it up |
| **Scheduled** | Ready to go — will activate when start date arrives |
| **Active** | Live on the storefront right now |
| **Expired** | End date has passed — no longer shown |

Status is automatically determined on save:
- If you choose **Draft** — it stays Draft regardless of dates
- If end date is in the past → **Expired**
- If start date is in the future → **Scheduled**
- If current time is between start and end → **Active**

---

## Free Edition Limits

| Feature | Free | Pro |
|---|---|---|
| Total campaigns | 3 | Unlimited |
| Simultaneous active | 1 | Multiple |
| Targeting | Global | Category, Product, Customer Group |
| Store views | Single | Multi-store |
| Promo bar messages | 1 | Rotation |
| Badge style | Text, fixed color | Custom colors + image |
| PDP promotional block | ❌ | ✅ |
| Category banner | ❌ | ✅ |
| Exit intent trigger | ❌ | ✅ |
| UTM activation | ❌ | ✅ |
| REST API | ❌ | ✅ |
| Import / Export | ❌ | ✅ |
| Campaign templates | ❌ | ✅ |

---

## Upgrade to Pro

Pro edition removes all limits and adds advanced targeting, conversion features, and developer tools.

Visit **[codeatoz.com](https://codeatoz.com)** for Pro pricing and details.

---

## Cron Configuration

The module registers a cron job in the `default` group:

```
*/5 * * * *   codeatoz_campaign_activation
```

This runs every 5 minutes and:
1. Transitions Scheduled → Active when start date is reached
2. Transitions Active/Scheduled → Expired when end date passes
3. Updates the Redis cache with the current winning campaign per store

Ensure Magento cron is running on your server:

```bash
# Check cron is configured
bin/magento cron:install

# Run manually
bin/magento cron:run --group default
```

---

## Uninstallation

```bash
bin/magento module:disable Codeatoz_Campaign
bin/magento setup:upgrade
php bin/magento setup:db-schema:upgrade

# Remove module files
rm -rf app/code/Codeatoz/Campaign

# Drop the database table (optional)
# mysql -u root -p your_db -e "DROP TABLE codeatoz_campaign;"
```

---

## Contributing

Contributions are welcome. Please open an issue first to discuss what you would like to change.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -m 'Add my feature'`)
4. Push to the branch (`git push origin feature/my-feature`)
5. Open a Pull Request

---

## Support

- **Issues** — [GitHub Issues](https://github.com/codeatoz/magento2-campaign/issues)
- **Pro support** — [codeatoz.com](https://codeatoz.com)

---

## License

[OSL-3.0](https://opensource.org/licenses/OSL-3.0) — Open Software License 3.0

Copyright © 2026 Codeatoz. All rights reserved.
