=== Calculate price by weight/dimension for WooCommerce ===
Contributors: gkastorinis, nevma
Donate link: https://wecommerce.gr/
Tags: woocommerce, product, size, sizes, dimensions, weight, price, calculator
Requires at least: 4.7
Tested up to: 6.3
Stable tag: 1.2.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customer select the exact dimensions/weight for selected products & automatically calculate their cost.

== Description ==

This is a WooCommerce plugin that lets customers select their desired dimensions or weight for any product and then calculates the correct price based on the selected values.
Free options:
- Length (m, cm, mm, in, yd)
- Weight (kg, g, lbs, oz)
- All options work for simple products
Pro options:
- Area (Length & Width)
- Area (Length & Fixed Width)
- Circular Area (Diameter)
- Simple & Variable Products

== Installation ==
1. Upload \"wecom-product-dimensions.zip\" to the \"/wp-content/plugins/\" directory.
2. Activate the plugin through the \"Plugins\" menu in WordPress.
3. Edit any simple product, select the appropriate product type (length|weight) and add the desired limits & step.

== Screenshots ==

1. Select product measurement type in edit product page.
2. Add limits & step in edit product page.

== Frequently Asked Questions ==

= How do I configure this plugin? =

This plugin needs no configuration after installing. Just install the plugin, activate it & edit your desired products.

= How do I change the price calculation? =

This plugin uses the active product price to calculate the price based on customer selected values.
E.g. product active price: 10€ & selected length 4.5m, total price 45€

== Changelog ==

= 1.0.0 =
* Initial release.

= 1.0.1 =
* Bug fix with wp_kses_post.

= 1.1.0 =
* Bug fix in mini cart prices.

== Upgrade Notice ==

= 1.0.0 =
Initial release

= 1.0.1 =
Product page missing input fix

= 1.1.0 =
Wrond mini cart prices fix.

= 1.2.0 =
Add WPSC standards

= 1.2.1 =
Add nevma team

= 1.2.2 =
Update images to nevma branding