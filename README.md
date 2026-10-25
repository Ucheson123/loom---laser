# Custom WooCommerce Product Configurator

A custom-built WooCommerce storefront and product configurator demonstrating advanced dynamic pricing and custom session data management. Built for a premium leather goods brand (Loom & Laser Co.), this project bypasses heavy off-the-shelf plugins in favor of lightweight, custom PHP hooks and vanilla JavaScript.

## Core Features
* **Live Dynamic Pricing:** Vanilla JavaScript listens to WooCommerce variation events to update the UI pricing instantly as users type custom engraving text or select options, without page reloads.
* **Custom Cart Data Flow:** Custom PHP filters intercept the `Add to Cart` action, sanitize custom input fields, and inject them directly into the WooCommerce cart session.
* **Admin Order Synchronization:** Checkout hooks ensure temporary session data is permanently written to the WordPress database and cleanly displayed in the WooCommerce Admin Order view.
* **Premium UI/UX:** Aggressive CSS overrides strip out default block-theme styling to deliver a modern, responsive, high-converting e-commerce interface.
* **Stripe Integration:** Configured with Stripe Sandbox for end-to-end payment flow testing.

## Tech Stack
* **CMS:** WordPress
* **E-commerce:** WooCommerce
* **Backend:** PHP (Custom WooCommerce Action/Filter Hooks)
* **Frontend:** Vanilla JavaScript, Custom CSS
* **Version Control:** Git / GitHub Actions (CI/CD FTP Deployment)

## Repository Structure
To adhere to WordPress version control best practices, this repository ignores WordPress core files and tracks only custom development:
* `/wp-content/plugins/loom-laser-configurator/` - Contains the custom PHP logic for session data, pricing, and admin display.
* `/wp-content/themes/loom-laser-theme/` - Contains the global typography overrides, layout fixes, and premium configurator styling.