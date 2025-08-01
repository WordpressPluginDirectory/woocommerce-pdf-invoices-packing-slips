=== PDF Invoices & Packing Slips for WooCommerce ===
Contributors: pomegranate, alexmigf, yordansoares, kluver, dpeyou, dwpriv, mohamadntr
Donate link: https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/
Tags: woocommerce, pdf, ubl, invoices, packing slips
Requires at least: 4.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 4.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create, print & automatically email PDF or UBL Invoices & PDF Packing Slips for WooCommerce orders.

== Description ==

This WooCommerce extension automatically adds a PDF or UBL invoice to the order confirmation emails sent out to your customers. Includes a basic template (additional templates are available from [WP Overnight](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/)) as well as the possibility to modify/create your own templates. In addition, you can choose to download or print invoices and packing slips from the WooCommerce order admin.

= Main features =
- Automatically attach invoice PDF or UBL to WooCommerce emails of your choice
- Download the PDF or UBL Invoice / PDF Packing Slip from the order admin page
- Generate PDF invoices / packing slips in bulk
- **Fully customizable** HTML/CSS invoice templates
- Download invoices from the My Account page
- Sequential invoice numbers - with custom formatting
- **Available in:** Czech, Dutch, English, Finnish, French, German, Hungarian, Italian, Japanese (see FAQ for adding custom fonts!), Norwegian, Portuguese, Polish, Romanian, Russian, Slovak, Slovenian, Spanish, Swedish & Ukrainian

= Free extensions =
The following free extensions are available to add additional features to the plugin:

- **[PDF Invoices & Packing Slips for WooCommerce - mPDF](https://github.com/wpovernight/woocommerce-pdf-ips-mpdf)**: Adds support for RTL layout and Arabic script.
- **[PDF Invoices & Packing Slips for WooCommerce - mPDF CJK](https://github.com/wpovernight/woocommerce-pdf-ips-mpdf-cjk)**: Provides support for Chinese, Japanese, and Korean (CJK) fonts for the mPDF extension.
- **[PDF Invoices & Packing Slips for WooCommerce - Unicode Language Pack](https://github.com/wpovernight/woocommerce-pdf-ips-unicode)**: Adds support for Chinese, Japanese, and Korean scripts.
- **[PDF Invoices & Packing Slips for WooCommerce - Taxes Summary](https://github.com/wpovernight/wcpdf-taxes-summary)**: Adds a taxes summary table after the order details.

= Premium extensions =
In addition to this, we offer several premium extensions:

- Create/email PDF Proforma Invoices, Credit Notes (for Refunds), email Packing Slips, automatic upload to Dropbox & more with [PDF Invoices & Packing Slips for WooCommerce Professional](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/)
- Automatically send new orders or packing slips to your printer as soon as the customer orders! [WooCommerce Automatic Order Printing](https://www.simbahosting.co.uk/s3/product/woocommerce-printnode-automatic-order-printing/?affiliates=2) (from our partners at Simba Hosting)
- Get the complete feature set, including **Professional**, **Premium Templates**, and additional features with the [WooCommerce PDF Invoices & Packing Slips Plus Bundle](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/)

= Fully customizable =
In addition to a number of default settings (including a custom header/logo) and several layout fields that you can use out of the box, the plugin contains HTML/CSS-based templates that allow for customization & full control over the PDF output. Copy the templates to your theme folder and you don't have to worry that your customizations will be overwritten when you update the plugin.

- Insert customer header image/logo
- Modify shop data / footer / disclaimer etc. on the invoices & packing slips
- Select paper size (Letter or A4)
- Translation ready

== Installation ==

= Minimum Requirements =

* WooCommerce 3.3 or later
* WordPress 4.4 or later

= Automatic installation =
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of PDF Invoices & Packing Slips for WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "PDF Invoices & Packing Slips for WooCommerce" and click Search Plugins. You can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation. After installation has finished, click the 'activate plugin' link.

= Manual installation via the WordPress interface =
1. Download the plugin zip file to your computer
2. Go to the WordPress admin panel menu Plugins > Add New
3. Choose upload
4. Upload the plugin zip file, the plugin will now be installed
5. After installation has finished, click the 'activate plugin' link

= Manual installation via FTP =
1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==

= Where can I find the documentation? =

[PDF Invoices & Packing Slips for WooCommerce documentation](https://docs.wpovernight.com/topic/woocommerce-pdf-invoices-packing-slips/)

= It's not working! =

Check out our step by step diagnostic instructions here: https://wordpress.org/support/topic/read-this-first-9/

= Where can I find more templates? =

Go to [wpovernight.com](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/) to checkout more templates! These include templates with more tax details and product thumbnails. Need a custom templates? Contact us at support@wpovernight.com for more information.

= Can I create/send a proforma invoice or a credit note? =
This is a feature of our Professional extension, which can be found at [wpovernight.com](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/)

= Can I contribute to the code? =
You're more than welcome! This plugin is hosted on github, where you can post issues or make pull requests.
https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips

= How can I display the HTML/CSS source for debugging/developing templates? =
There's a setting on the Advanced tab of the settings page that allows you to toggle HTML output. Don't forget to turn if off after you're done testing!


== Screenshots ==

1. Simple invoice PDF
2. Simple packing slip PDF
3. Quickly print individual invoices or packing slips from the order list
4. Print invoices or packing slips in bulk
5. Attach invoices to any WooCommerce email
6. Set shop name, address, header logo, etc.

== Changelog ==

= 4.6.2 (2025-07-31) =
- Fix: Restore full item meta display in PDF
- Fix: Undefined array key "padding"
- Translations: Updated translation template (POT)

= 4.6.1 (2025-07-29) =
- Tweak: Updated CSS class name used for the shop email in templates
- Fix: Resolved issue with date format for "Mark as printed"
- Fix: Improved settings menu tab styling on smaller screens
- Fix: Prevented PHP warnings when `allow_url_fopen` is disabled
- Fix: Corrected argument mismatch in `add_document_link_to_email()`
- Translations: Updated translation template (POT)

= 4.6.0 (2025-07-15) =
- New: Added filter to modify the return value of `get_shop_address()`
- New: Enhanced document data editing on the order page
- New: Refined styling for settings submenus
- New: Improved Country/State selection in the Setup Wizard Shop Address step
- New: Added "Email Address" to Shop Address fields
- New: Document data editing for Invoices and Credit Notes is now disabled by default. Enable via Advanced settings
- New: Added sync action button to Shop Address fields
- New: Linked documentation to historical settings descriptions
- New: Displayed shop phone number below address in the Simple template
- Tweak: Deprecated legacy `shop_address` text hook
- Tweak: Updated UBL tax filters to append values instead of overriding defaults
- Fix: Resolved issue where Order Proposal plugin email attachments failed without the Professional extension
- Fix: Allowed HTML in the "Shop Additional Info" field
- Fix: Replaced free-text state input with a select field to ensure valid WooCommerce state codes
- Fix: Added missing documentation link to UBL Tax settings
- Translations: Updated POT file for translations
- Tested: Confirmed compatibility with WooCommerce 10.0

= 4.5.2 (2025-05-27) =
- Tweak: Suppress log entries for missing font files that are expected and not required
- Fix: Support for `{state_code}` in shop address formatting
- Fix: Restore `wpo_wcpdf_shop_address` hook for compatibility
- Fix: Missing support for placeholders like `{city_upper}` in shop address formatting

= 4.5.1 (2025-05-26) =
- Fix: Shop address not shown in documents when historical settings are enabled
- Fix: Validation issues flagged by WP Plugin Check

= 4.5.0 (2025-05-26) =
- New: Separated shop address fields introduced
- New: Categories added to General settings
- New: E-Invoicing tax categories, schemes, and exemption reasons aligned with EN16931 standard
- New: Setup wizard option to display PDF action buttons in orders list
- Tweak: Added permission check to pre-release notice display
- Tweak: Default to PHP file functions in Filesystem class due to `WP_Filesystem` instability
- Fix: "Reload Attachment Translations" setting always shown as checked
- Fix: Fatal error in Semaphore class when `wcpdf_log_error()` is undefined
- Fix: Restrict document number input to valid positive integers
- Fix: Fonts not reinstalled properly after upgrade when temporary folder is cleared
- Fix: Delay in updating PDF Document Data
- Translations: Updated translation template (POT)
- Tested: Tested up to WooCommerce 9.9

= 4.4.1 (2025-04-28) =
- Tweak: Improve Invoice My Account link logic for custom status checks and code clarity
- Fix: Type error when accessing debug settings in FileSystem constructor
- Translations: Updated translation template (POT)

= 4.4.0 (2025-04-22) =
- New: Add index on `order_id` column to document number database tables
- New: Add `wpo_wcpdf_settings_default_user_capability` filter and improve user capability fallback logic
- New: Helper function to handle UBL file writing with error handling
- New: Hybrid filesystem support
- Tweak: Minor code improvements in Setup Wizard view
- Fix: Deprecation warning for `null` value passed to `substr()` when extracting thumbnail URL
- Fix: Missing log function fallback when dependencies are not loaded
- Fix: "Gapped" invoices shown when other documents used the invoice numbering
- Fix: i18n issue in Invoice settings
- Fix: Fatal error when `null` is passed to `in_array()` in `Documents.php`
- Fix: Missing global call to AS functions in Semaphore class
- Fix: PDF preview issues
- Translations: Updated translation template (POT)

= 4.3.0 (2025-04-07) =
- New: Adds setting to toggle PDF translation reload for email attachments
- New: Adds Dompdf log file reference and enable additional debug output when debug mode is active
- New: Add filter to allow customization of document link
- New: Display latest stable and unstable GitHub releases on status page and show admin notice for new unstable versions
- New: Refactor SQL queries for safe table name handling
- Fix: Restore `translate()` fallback in `wpo_wcpdf_dynamic_translate()` function
- Fix: Numbers tab datepicker style when `SCRIPT_DEBUG` is defined
- Fix: Missing translation function for string on Advanced Numbers page
- Translations: Updated translation template (POT)
- Tested: Tested up to WooCommerce 9.8 & WordPress 6.8

= 4.2.0 (2025-03-24) =
- New: Improved fonts check and reinstatement in temporary fonts directory
- New: Function to reload WC webhooks
- New: Improved autoloading for prefixed dependencies
- New: Added plugin-specific filter hook for order item name
- New: Removed the 'Guest' document access type, as it is covered by 'Full'
- New: Updated DOMPDF to v3.1.0
- New: Upgrade link on Packing Slip document status
- New: Added order coupons data to UBL document
- Tweak: Ensured correct PDF Content-Type for improved browser handling
- Tweak: Refactored PDF Simple template order details header for cleaner syntax
- Fix: Improved date field options in Danger Zone tools for clarity
- Fix: SQL interpolation issues flagged by WP Plugin Check
- Fix: Removed duplicate `AccountingSupplierParty` and `AccountingCustomerParty` in UBL
- Fix: Resolved issues rendering the number tab results
- Translations: Updated translation template (POT)

= 4.1.1 (2025-02-17) =
- Tweak: Added a documentation link to the System Configuration label
- Tweak: Improved WP Filesystem implementation
- Fix: Cleans up special characters in `get_html()` for Bulk Document
- Fix: Fixed line breaks not being respected in Document notes

= 4.1.0 (2025-02-12) =
- New: Display Installed Plugin Versions table under Advanced Status
- New: Setting to include document link in emails for guest customers
- Tweak: Enhanced the readme file to list available free extensions
- Tweak: Use `wp_kses_post` instead of `wpo_wcpdf_sanitize_html_content` for footer and extras fields
- Fix: Product bundle border bottom in PDF template styles
- Fix: `OrderDocument::get_language_attributes()` return value must be of type `string`, `null` returned
- Fix: Regression in PDF totals style
- Fix: Check if `is_enabled()` is callable for the document in `wcpdf_get_document_file()`
- Translations: Updated translation template (POT)
- Tested: Tested up to WooCommerce 9.7

= 4.0.0 (2025-02-04) =
- New: Updated setup Wizard
- New: Comply with WP Plugin Check standards
- New: Function `wpo_wcpdf_gettext()`
- New: Adds the default PDF column labels to the strings file
- New: Add Lithuanian language pack (document strings only)
- Fix: Document access security issue
- Fix: Remove translatability from VAT and COC fields
- Fix: Call to undefined function `standardSerializer()` on UBL
- Translations: Updated translation template (POT)

= 3.9.6 (2025-01-21) =
- Fix: Error calling undefined method `WC_Order_Refund::get_customer_id()`

= 3.9.5 (2025-01-20) =
- New: Email input callback function for settings
- New: Adds support for additional UBL root elements
- New: Add support for multiple handlers under a single root in `UblDocument::get_data()`
- New: Validation for order tax exemption to apply UBL tax reason
- New: The "Regenerate Invoice" feature now updates UBL Tax item data
- New: Enhance UBL Taxes default selector, 'None' Option
- New: Add `cbc:BaseQuantity` support to UBL structure
- New: UBL Tax settings now align with the latest settings option
- New: Add utility function for dynamic string translation
- New: Add `cbc:BuyerReference` element to UBL for buyer reference
- New: Enable Support for `cac:PaymentMeans` in UBL
- New: Filter hooks for UBL tax schemes, categories, and reasons
- New: Add `cac:Price` support to UBL
- New: Support for UBL Tax Category Reason
- Tweak: Replace UBL code to get the customer VAT number with new function
- Tweak: Update UBL format selector description
- Tweak: Allow `0` days for the due date
- Tweak: Apply `wpo_wcpdf_settings_fields_documents_{$this->type}_pdf` before checking the emptiness of the `$settings_fields`
- Fix: Logo height setting can break document layout on Simple template
- Fix: Setting categories not applied to disabled documents
- Fix: Postcode and city defaults in UBL tax output
- Fix: Incorrect treatment of UBL format setting as historical
- Translations: Updated translation template (POT).
- Tested: Tested up to WooCommerce 9.6.

= 3.9.4 (2024-12-23) =
* New: add a note to inform users about the availability of additional documents
* New: display a notice when the yearly reset action is not scheduled
* Tweak: transition UBL support out of beta
* Translations: update translation template (POT)

* New: enable searching for multiple invoice numbers in the Orders list for HPOS setups
* New: `calculate_due_date()` document function
* Fix: correct the type of the abstract document `order_id` property
* Fix: UBL shop country code
* Translations: update translation template (POT)

= 3.9.2 (2024-12-17) =
- New: adds description to UBL format selector
- Fix: issue with PHP extension load checks
- Translations: Updated translation template (POT).

= 3.9.1 (2024-12-16) =
- New: Adds support for multiple UBL formats.
- New: Adds a shop phone number field for e-Invoice support.
- New: Adds user info to order notes when generating documents.
- New: Added an admin notice to inform when server requirements are not met.
- New: Raised the minimum PHP version requirement to 7.4.
- New: Removes space between items table and totals.
- New: Added sections to settings for better organization.
- Tweak: Improve the description of the "Remove released semaphore locks" tool.
- Fix: Upgrade links not displaying correctly.
- Fix: Temp folder warning style issue.
- Fix: Remove unused legacy notice code: `check_auto_increment_increment()`.
- Fix: AJAX preview loading when disabled on settings pages.
- Fix: UBL issue with empty tax on line items.
- Fix: jQuery `tipTip` function not available.
- Fix: Template item meta styling.
- Fix: Semaphore class name on two classes that were still using the previous name.
- Translations: Updated translation template (POT).
- Tested: Tested up to WooCommerce 9.5.

= 3.9.0 (2024-10-21) =
* New: Updated `sabre/xml` library to version 4.
* New: Added notice about dropping support for PHP versions below 7.4.
* New: Implemented PSR-4 Autoloading Standard for improved code structure and maintainability.
* New: Deprecated `wpo_wcpdf_due_date_display` hook.
* Fix: WC item meta style issue when using `dd`.
* Fix: Relocated `wpo_wcpdf_use_path` hook before `$src` empty check.
* Translations: Updated translation template (POT).

= 3.8.8 (2024-10-08) =
* Fix: adds missing deprecated filters to document title methods

= 3.8.7 (2024-10-07) =
* New: Add full compatibility with mPDF to the Simple Template.
* New: Add refund reason and invoice number titles to the document methods abstract class.
* New: Improve template titles registration.
* New: Improve invoice due date setting.
* New: Add "You might also like these plugins" section to the Upgrade tab.
* New: Function to determine if checkout is using block: `wpo_wcpdf_checkout_is_block()`.
* New: Clean legacy Semaphore locks tool.
* New: Automatic cleanup of unlocked Semaphore locks and other improvements.
* New: Tool to remove expired Semaphore locks.
* New: Filter to modify PDF document data fields: `wpo_wcpdf_document_data_meta_box_fields`.
* Tweak: Lower hook priority (200) for invoice columns in WooCommerce orders list.
* Fix: Replace deprecated jQuery `change()` function usage.
* Fix: Correct path to generic document icon.
* Fix: Check user capabilities in the `enable_debug()` function.
* Fix: Load plugin translations later on the `init` hook.
* Fix: Unblock UI when a tool button is clicked.
* Translations: Update translation template (POT).
* Tested up to WooCommerce 9.4.

= 3.8.6 (2024-07-18) =
* Fix: function `add_yith_product_bundles_classes()` errors
* Fix: `get_header_logo_id()` return type fatal error

= 3.8.5 (2024-07-16) =
* New: PDFJS v4.3.136 upgrade
* New: advanced embed images setting
* New: includes ImageMagick library in the Advanced Status
* New: adds Invoice number to search type selector in HPOS setups
* New: adds row classes for "YITH WooCommerce Product Bundles"
* New: register a log for document deletion in the Order Notes
* Tweak: improves header logo loading
* Fix: make Simple template total labels translatable
* Fix: wraps document addresses around `<p>` tags
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 9.1 & WordPress 6.6

= 3.8.4 (2024-06-19) =
* New: filter hook `wpo_wcpdf_multilingual_languages`
* New: `wpo_wcpdf_get_multilingual_languages()` function has been introduced, derived from the deprecated `get_languages()` function
* Tweak: Added new strings to the `strings.php` file to improve translations
* Tweak: enhanced notes functions for improved translations when using the Professional extension.
* Fix: `normalize_settings_args()` function bug on multilingual setups
* Fix: settings language tab issue when adding new language in multilingual setups
* Translations: Updated translation template (POT)

= 3.8.3 (2024-06-06) =
* New: adds language attribute to PDF HTML
* New: Dompdf `v3.0.0` upgrade
* New: filter hook `wpo_wcpdf_after_system_status_table`
* New: filter for server configs `wpo_wcpdf_advanced_status_server_configs`
* New: PDFJS `v3.11.174` upgrade
* Tweak: adds `p` as allowed tag of the `wpo_wcpdf_sanitize_html_content()` function
* Fix: number formatting `[order_number]` placeholder not using parent order number on Credit Notes
* Fix: remove unwanted tags from `wpo_wcpdf_sanitize_html_content()`
* Fix: removes unused "Log invoice number generation" setting
* Fix: reverts the `customer_invoice` email title to the original Woo title
* Fix: the function `get_extensions_license_data()` returning empty if no cached data found
* Fix: bug created by the output of the function `wpo_wcpdf_sanitize_html_content()` being `null`
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 9.0

= 3.8.2 (2024-04-29) =
* Fix: "Customer Invoice" email title under "Send order email" metabox
* Fix: filter called with a refund object in `get_billing_address()` and `get_shipping_address()`
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 8.9

= 3.8.1 (2024-04-23) =
* New: updates DOMPDF to `v2.0.7`
* New: adds `woocommerce` as required plugin
* New: improvements to shop logo to be translatable in future Professional extension version
* Fix: document date parse function being called on non HPOS stores
* Fix: replaces deprecated jQuery functions
* Fix: bug with type hint in `wpo_wcpdf_parse_document_date_for_wp_query()` function
* Fix: bug calling `is_allowed()` on `bool` under `wcpdf_get_document()` function
* Fix: not removing documents meta on Woo subscription renewal
* Fix: XSS vulnerability on Woo Checkout user input address data
* Fix: Semaphore attachments issue on concurrent refunds
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 8.8

= 3.8.0 (2024-03-18) =
* New: updates composer libraries, specially `php-svg-lib` because of a security issue
* New: hooks to allow adding new Settings Debug tabs and their contents
* New: implements date type selector on Danger tools
* New: filter `wpo_wcpdf_process_danger_zone_tools_date_type`
* New: bumps WooCommerce min version to 3.3
* New: function `wcpdf_error_handling()`
* New: function `wcpdf_get_document_output_format_extension()`
* New: function `wcpdf_get_document_file()` to get the document file
* Tweak: adds new filter to include invoice number in HPOS search queries
* Tweak: replace `date()` with `date_i18n()` on `get_filename()`
* Tweak: improves number initiation with new function: `init_number()` to `initiate_number()`
* Tweak: hides the extensions banner temporarily
* Fix: bug that caused a sorting issue in the orders list
* Fix: JS error `$(...).pointer` is not a function
* Fix: danger tools forms not working
* Fix: bulk document missing slug property
* Fix: changed default nonce name for wizard dismissal
* Fix: advanced tools issues like "Generate random temporary directory" not working
* Fix: number tool pagination
* Fix: renumbering documents not working
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 8.7 & WordPress 6.5

= 3.7.7 (2024-01-29) =
* New: extensions license cache system
* New: hides the Upgrade tab on bundle
* New: get document filter hook `wcpdf_get_document`
* Tweak: improves Simple template files markup
* Tweak: moves the notes hooks position in template files
* Fix: sorting orders by invoice number and date issue on the orders listing page
* Fix: the level parameter sent to the `log()` method in semaphore class
* Fix: SQL injection bug in Advanced Numbers search
* Translations: make the due date output translatable
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 8.5

= 3.7.6 (2024-01-09) =
* New: enforces PHP 7.2 as minimum version
* New: DOMPDF upgrade to `v2.0.4`
* Tweak: declare new "Payment date" string for translation
* Fix: potential SQL injection bug in Number Tools
* Fix: bug in `get_payment_date()`
* Translations: Updated translation template (POT)

= 3.7.5 (2023-12-13) =
* Tweak: includes `$args` in filter hook `wpo_wcpdf_filename` parameters
* Tweak: re-adds the legacy hook `woocommerce_invoice_number` for backwards compatibility
* Fix: bug in "Full" document access using bulk actions
* Fix: fatal error in UBL document

= 3.7.4 (2023-12-06) =
* New: adds new setting to enable semaphore logs
* Tweak: adds descriptions to the COC and VAT settings
* Tweak: replaces Packing Slip shipping function in the template file
* Tweak: adds semaphore to email attachment function
* Fix: bug on `get_payment_date()`
* Fix: bug on getting VAT & CoC number saved values
* Tested up to WooCommerce 8.4

= 3.7.3 (2023-11-27) =
* New: temporary promo ad
* Tweak: improves the templates number/date functions
* Tweak: update the filter `wpo_wcpdf_due_date_display` arguments
* Tweak: change the way the setting is retrieved for the due date
* Fix: Packing Slip attachment when Pro is disabled
* Fix: modifying order creation date in the `get_due_date` method
* Fix: PHP 8.1 `preg_replace` warning
* Fix: fatal error in the `custom_field()` function when custom field is an `array`
* Fix: multiple document generation with "Full" access type
* Fix: empty argument bug for `display_due_date` method

= 3.7.2 (2023-11-08) =
* New: adds Due Date option to the Invoice settings
* New: filter for plugin directories in Advanced Status tab: `wpo_wcpdf_plugin_directories`
* New: advanced tab with Number tools extension code integrated
* Tweak: updates Upgrade tab extension features
* Fix: bug on historical settings not being deleted when using most current settings
* Fix: bug on document variable name on `output_ubl()` method
* Tested up to WooCommerce 8.3

= 3.7.1 (2023-10-20) =
* Fix: bug on UBL preview that forces the invoice creation

= 3.7.0 (2023-10-18) =
* New: adds `payment_date()` function to be used in templates
* New: adds UBL as additional output format for documents
* New: bump min required PHP version to 7.2
* New: bump min required WordPress version to 4.4 (which was already implicitly by the minimum required WooCommerce version)
* Tweak: switch `wpo_wcpdf_before_html` and `wpo_wcpdf_before_html` hooks positions
* Tweak: hide next Invoice number if displaying order number
* Tweak: removed legacy `normalize_path` functions and replaced with `wp_normalize_path`
* Fix: bug with HTML output using pretty links. Improved endpoint.
* Fix: potential fatal error if document is `false` in order list action buttons
* Fix: error when calling order ID on `null` under `output_number_date_edit_fields` method
* Fix: bug when displaying the Invoice number/date columns, causing significant drop of performance
* Fix: my account PDF link conflict with Jetpack analytics module
* Fix: PHP error on calling `get_title()` document method on `bool`
* Fix: `strpos()` and `str_replace()` `null` parameter deprecated warnings
* Tested up to WooCommerce 8.2 & WordPress 6.4

= 3.6.3 (2023-09-04) =
* New: adds Invoice Number column to the orders export of WooCommerce Analytics
* New: adds a document access denied redirect page setting
* New: hooks before and after debug tools: `wpo_wcpdf_before_debug_tools`, `wpo_wcpdf_after_debug_tools`
* Tweak: add a note to make clear that updating the number format only affects new orders
* Tweak: removes legacy mode & textdomain from debug settings
* Fix: undefined WC PageController method `is_admin_page` bug and replace it with `is_admin_or_embed_page`

= 3.6.2 (2023-08-23) =
* New `wcpdf_document_link` shortcode
* New: parameters to download PDF shortcode
* New: adds notice for the upcoming PHP 7.2 requirement
* New: filter hook to strip HTML tags from Shipping Notes `wpo_wcpdf_shipping_notes_strip_all_tags`
* Fix: bug of passing the wrong variable for the filtered order emails
* Fix: remove old temporary directory when generating new one from status tool
* Fix: displays always the Generate random temporary directory tool
* Fix: issue with Shipping Notes line breaks

= 3.6.1 (2023-08-16) =
* Fix: string encoding issues in PDF

= 3.6.0 (2023-08-15) =
* New: setting for improved document link access type
* New: implemented autoloader for plugin classes
* New: adds a new Status tool to reset plugin settings
* New: invoice number column added to the WooCommerce order analytics
* Fix: replaces `utf8_decode()` which is deprecated in PHP 8.2
* Fix: allows the translation for the Shipping Notes strings
* Fix: bug when trying to delete temporary files when using mPDF extension
* Fix: bug on `$updater` returning `null` in Upgrade tab function
* Fix: deprecates `woocommerce_resend_order_emails_available` filter
* Fix: several string typos
* Fix: revert breaking long characters in order data labels
* Fix: PHP8.2 creation of dynamic property deprecated warnings
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 8.0 & WordPress 6.3

= 3.5.6 (2023-06-21) =
* New: adds a generic shortcode `[wcpdf_download_pdf]` for PDF download links
* New: bump preview PDFJS library to v3.7.107
* Tweak: optimize PDFJS library size
* Tweak: remove WPO hiring banner from the Status tab
* Tweak: composer dependencies update
* Fix: font deletion bug after plugin upgrade
* Fix: bug on previewing Credit Note if the order has multiple refunds
* Fix: PHP error on `log_document_creation_trigger_to_order_meta()` function when trying to get order ID and type
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 7.8

= 3.5.5 (2023-06-01) =
* New: toggle display date and created via on document data
* New: adds support for legacy Sidekick activated licenses in Upgrade tab
* Tweak: log invoice number generation on setting

= 3.5.4 (2023-05-19) =
* Tweak: improves code for the upgrade tab get license info function
* Fix: bug on templates using legacy versions of the Premium Templates extension
* Fix: string translation issues & updated POT
* Fix: bug in slug property not set when defining the document number lock name

= 3.5.3 (2023-05-15) =
* New: display creation trigger in document data and order meta
* New: detects the extension license status in the Upgrade tab
* New: invoice number search document setting
* New: adds new filter to stick Document data metabox: `wpo_wcpdf_sticky_document_data_metabox`
* Fix: adds semaphore to Invoice number init to prevent concurrent number issues
* Fix: invoice column hooks only on setting condition
* Tested up to WooCommerce 7.7

= 3.5.2 (2023-04-12) =
* New: added upgrade tab

= 3.5.1 (2023-04-07) =
* Tweak: adds user permission check on AJAX document printed
* Fix: bug on trying to save bulk document setting on `document_can_be_manually_marked_printed()`
* Fix: disallow document creation for anonymized orders

= 3.5.0 (2023-04-05) =
* New: save invoice display date option
* New: mark/unmark Invoice as printed
* New: adds notice when RTL is detected
* New: `wpo_wcpdf_preview_after_reload_settings` action
* New: `wpo_wcpdf_export_settings` filter
* Fix: fatal error on WC deactivation
* Fix: invoice number/date screen options bug with HPOS enabled
* Tested up to WooCommerce 7.6 & WordPress 6.2

= 3.4.0 (2023-02-20) =
* New: filter `wpo_wcpdf_settings_user_role_capabilities` to change role capabilities to access plugin settings
* New: improved debug tools UI
* New: import/export settings tools
* New: dompdf upgrade to v2.0.3
* Fix: save document settings in order meta only on document init
* Tested up to WooCommerce 7.4

= 3.3.2 (2023-02-03) =
* New: dompdf upgrade to v2.0.2
* New: filter to control the value returned by `is_woocommerce_activated()`
* Fix: JS undefined error when trying to retrieve preview gutter texts

= 3.3.1 (2023-01-20) =
* Fix: applies `overflow-wrap:anywhere;` to the Simple template `body` in CSS styles
* Fix: displays a message if the typed next number is superior to MySQL INT max
* Fix: allow preview gutters text to be translated
* Fix: settings forms background color issue
* Fix: PHP notice for undefined index `exists`
* Fix the year in the date release of v3.3.0 in readme.txt

= 3.3.0 (2023-01-16) =
* New: WooCommerce HPOS compatibility (beta)
* New: reschedule the yearly reset of the numbering system on a button from the Status page
* New: document status table in the Status page
* New: adds document object argument to PDF maker class constructor
* New: filter to allow user to disable the documents private data removal: `wpo_wcpdf_remove_order_personal_data`
* Tweak: optimizes `$wpdb` use on `Sequential_Number_store` class
* Tweak: improves yearly reset number with Action Scheduler and Updraft Plus Semaphore
* Fix: replaces the use of the deprecated `wcs_` filter hooks from WooCommerce Subscriptions plugin
* Fix: bail if document data is empty when saving order
* Fix: add nonces to several admin unsecure requests
* Tested up to WooCommerce 7.3

= 3.2.6 (2022-12-15) =
* Fix: adds nonce check in hide link for attachments hint in admin
* Tested up to WooCommerce 7.2

= 3.2.5 (2022-11-22) =
* Fix: allow for WooCommerce Booking without order

= 3.2.4 (2022-11-07) =
* Tweak: update translation template and files
* Fix: break long URLs in different lines if it reaches the available space in Simple template
* Fix: restore deleted strings and load them using standalone strings.php file
* Fix: warning on deprecated argument from product get_dimensions() method
* Tested up to WooCommerce 7.1

= 3.2.3 (2022-10-28) =
* Fix: check if the order is unsaved or doesn't exist before rendering the PDF document
* Fix: bug on getting the order ID from third party objects when attaching PDF to email
* Fix: reverts documents functions/templates escaping that caused issues on version 3.2.2
* Fix: billing/shipping phone getter functions for refund orders
* Tested up to WordPress 6.1

= 3.2.2 (2022-10-25) =
* New: filter to allow customers to access your PDF with a unique key
* Fix: check if the shop logo exists
* Fix: check if HTML is escaped properly before echoing
* Fix: maybe reinstall fonts (if are missing) before PDF output
* Fix: bug on automatic cleanup cron job
* Fix: removes WC legacy versions compatibility classes. Bumps WC minimum version to 3.0
* Fix: allow manually resending new order email
* Fix: run action hooks within invoice columns under order list
* Marked tested up to WooCommerce 7.0

= 3.2.1 (2022-10-06) =
* Renames the plugin to comply with trademark rules

= 3.2.0 (2022-09-26) =
* New: setting to display the Invoice date column in the WooCommerce orders list
* New: updated Dompdf to version 2.0.1, which fixes a security vulnerability.
* New: filter `wpo_wcpdf_document_link_additional_vars` to add additional query variables to the document link
* Tweak: improved document settings data init/save
* Tweak: improved wizard display settings
* Tweak: improved styles and descriptions for the document 'Number format' settings
* Tweak: new query variable for the shortcode document link

= 3.1.1 (2022-09-13) =
* Fix: fatal error caused by list_files() function missing

= 3.1.0 (2022-09-06) =
* New: custom document links feature available from the Status tab debug settings page. Changes the document links to a prettier URL scheme
* New: action hooks before and after the shop logo: `wpo_wcpdf_before_shop_logo` and `wpo_wcpdf_after_shop_logo`
* Fix: replaces WP_Filesystem with PHP functions to delete temporary files
* Marked tested up to WooCommerce 6.8

= 3.0.1 (2022-08-02) =
* New: admin pointer for document settings dropdown
* Security: escape the tab and section parameters before outputting it back, leading to a Reflected Cross-Site Scripting. This bug was reintroduced on version 2.14.0
* Tweak: prevent requirements select to reload settings preview
* Fix: allow remove requirement trigger secondary save button on settings pages
* Marked tested up to WooCommerce 6.7

= 3.0.0 =
* Libraries: Updated dompdf to 2.0.0, addressing security vulnerabilities and introducing some changes that could possibly break high level customized setups ([more information](https://github.com/dompdf/dompdf/releases/tag/v2.0.0))
* Fix: respect custom (filtered) woocommerce template paths
* Fix: Check if the invoice is allowed before the shortcode output
* UI: Link to Professional extension for packing slip attachments

= 2.16.0 =
* Security: Fix authenticated reflected XSS on the settings page
* Fix: Redirection URLs in wizard and when sending emails manually
* Libraries: updated dompdf to 1.2.2

= 2.15.0 =
* New: Filter hooks to override ability to edit document data
* Simple template: Only show shipping & payment method lines when set
* Security: escape urls as late as possible
* Fix: WP6.0+ converting interface elements to emojis
* Tweak: Show sticky save button for all setting changes

= 2.14.5 =
* Fix: Preview invoice number & date display settings
* Tested up to WooCommerce 6.5 & WordPress 6.0

= 2.14.4 =
* Fix: Content-Length header causing invalid response on some servers

= 2.14.3 =
* Fix: preview not updating (unless test mode was enabled)
* Tweak: add styles for custom settings sections
* Tweak: Set Content-Length header for inline display

= 2.14.2 =
* Fix: unescape text strings entered in the setup wizard
* Dev: New filter wpo_wcpdf_email_order_object
* UI: Update setup wizard layout
* Libraries: updated dompdf to 1.2.1 (addressing potential security vulnerability)
* Marked tested up to WooCommerce 6.4

= 2.14.1 =
* New: Relaunch the Setup Wizard manually from the Status tab
* Fix: Dynamic `wpo_wcpdf_tmp_path_{$type}` filter hook name parsing
* Fix: removing inline preview for media upload
* UI: Show sticky save button on settings change
* UI: Fill in the document icon sheet with white

= 2.14.0 =
* New: Live preview of PDF document on the settings page
* Fix: checks if number store table exists before applying DB migration
* Tweak: Remove i18n for some log strings
* Marked tested up to WooCommerce 6.3

= 2.13.1 =
* Fix: load missing non-historical settings for existing invoices
* Tweak: print file & line number for exceptions in error logs & output
* Marked tested up to WooCommerce 6.2

= 2.13.0 =
* New: include dompdf temporary folder in cleanup procedure
* New: Add CSS row classes for WPC Product Bundles
* New: filter to override `wc_display_item_meta` arguments
* Fix: Prevent errors when server doesn't support `.webp` image rendering
* Fix: change invalid default date 0000-00-00 00:00:00 on number store tables
* Tweak: Don't store non-historical document settings in order meta
* Templates: New action hook before the document label (`wpo_wcpdf_before_document_label`)
* Libraries: updated dompdf to 1.2.0
* Marked tested up to WP5.9

= 2.12.1 =
* Fix: Show a feedback notice after saving settings
* Fix: images with min-width/min-height styles rendered tables incorrectly (dompdf patch)
* Tweak: Disable composer platform check

= 2.12.0 =
* New: Support for webp images
* Fix: Plugin version for font synchronizer upgrade procedure
* Fix: force reloading installed template list during template path migration
* Fix: PHP8.1 incompatible return type notice
* Fix: WooCommerce 6.1 deprecations notices
* Dependencies: Updated dompdf to 1.1.1
* Marked tested up to WooCommerce 6.1

= 2.11.4 =
* Fix: bundled fonts being deleted during upgrades
* Fix: dompdf 1.1.0+ font cache data compatibility

= 2.11.3 =
* Fix: Extended currency symbol support in bulk documents
* Fix: Prevent copying packing slip and other document data for renewal orders (WooCommerce Subscriptions)
* Marked tested up to WooCommerce 6.0

= 2.11.2 =
* New: filter and fallback for the default settings tab
* Tweak: Improved font synchronization during plugin updates
* Fix: Allow non-historical text settings
* Fix: Fail more gracefully during install/upgrade/downgrade
* Fix: notice on missing setting on fresh install
* Fix: don't initialize settings when document can't be loaded
* Fix: Prevent unnecessary database queries when settings API is initiated
* Fix: Use ISO currency code for RTL currencies when the default PDF library (dompdf) is used

= 2.11.1 =
* Fix: Errors were incorrectly logged after installation when no invoices had been created yet
* Fix: Button styles in order backend

= 2.11.0 =
* New: Use year-based number stores for future and past years to handle yearly resets more reliably
* Fix: PHP iconv fallback for custom PHP builds without this function

= 2.10.6 =
* Fix: PHP7.1 compatibility

= 2.10.5 =
* Security: Apply escaping to translated strings

= 2.10.4 =
* Fix: Billing phone not displaying in Packing Slip when billing address was enabled
* Fix: Support for special characters on sites without the PHP MB-string module
* Fix: Don't alter order object when logging document creation for refunds to order notes

= 2.10.3 =
* Fix: Secondary address always showed, regardless of setting

= 2.10.2 =
* New: Print shipping phone number when available (and enabled in the settings)
* New: Show inline warning on the settings page when the logo is bigger than 600dpi
* Fix: Prevent fatal error when trying to log notes to refund orders
* Fix: MailPoet email compatibility notice
* Translations: Updated translation template (POT)
* Translations: Removed bundled translations for language packs available on wordpress.org
* Marked tested up to WooCommerce 5.9

= 2.10.1 =
* Fix: prevent fatal errors if template functions cannot be loaded

= 2.10.0 =
* New: Use minified JS & CSS files to reduce load time on live sites (enabling `SCRIPT_DEBUG` will load full versions)
* New: Selected template setting is now stored as a reference ID rather than a fixed path ([#209](https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips/pull/209))
* Fix: Fallback to first available translation for settings when migrating from multilingual to single language setup
* Fix: Undefined variable notice when using [wcpdf_download_invoice] on non-order pages
* Fix: Updated documentation links
* Marked tested up to WooCommerce 5.7

= 2.9.3 =
* Fix: JavaScript errors on My Account page with empty link tags
* Fix: Long URLs in notes area breaking layout

= 2.9.2 =
* Tweak: Added new 'item-' prefix to item row class ID
* New: filter to set sort order for bulk documents (`wpo_wcpdf_bulk_document_sort_order`)
* Marked tested up to WooCommerce 5.6

= 2.9.1 =
* New: Log manual invoice creation (with logging enabled)
* New: Filters to override body class and content (`wpo_wcpdf_body_class` & `wpo_wcpdf_html_content`)
* New: Document methods to get (and override) the number and date titles
* Fix: Open PDF on My Account page in a new browser tab/window (following settings)
* Translations: Update template (POT) and current translation projects
* Marked tested up to WooCommerce 5.5 and WordPress 5.8

= 2.9.0 =
* New: Setting to log document generation time & context to order notes
* New: template hooks 'wpo_wcpdf_before_footer' and 'wpo_wcpdf_after_footer'
* New: Save and Cancel buttons for the Document Data metabox
* Fix: Fallback to bundled fonts when temporary folder is not writable
* Fix: empty first page under specific conditions
* Fix: HTML line breaks and special characters in invoice notes
* Fix: Allow setting document date prior to generating it
* Fix: variable not set for filename
* Fix: ensure unique filename in case order number or document number not accessible
* Fix: Fallback if no template is selected
* Translations: Added hints for translators, use numbered placeholders
* Translations: Added Arabic (thanks to [Nabil Moqbel](https://profiles.wordpress.org/nabilmoqbel/))

= 2.8.3 =
* New: Allow filtering action button classes
* Fix: Error when no order data passed to filename function
* Fix: During first install, only set defaults if not already (pre-) configured
* Fix: Use `WC()->template_path()` instead of `WC_TEMPLATE_PATH` for theme template overrides
* Fix: Checks existence of document data number and date for order metabox fields
* Fix: Prevent WooCommerce deprecation notices for non-product item types
* Fix: jQuery deprecation notices
* Tweak: Show instructions for emails metabox
* Marked tested up to WooCommerce 5.2 and WordPress 5.7

= 2.8.2 =
* Fix: Layout issues with totals for documents with more than 1 page
* Fix: Allow setting an Invoice number and date manually
* Fix: Prevent errors on PHP8.0 when order not loaded for a document

= 2.8.1 =
* Fix: Images and fonts loading from custom locations for uploads and temporary folders

= 2.8.0 =
* Fix: Support for PHP8.0, deprecating support for PHP7.0 or older (separate addon available for backwards compatibility)
* Fix: Setup wizard crash when 3rd party plugins/themes check screen object
* Dev: Use internal date formatting function, allowing easier PDF specific date format overrides
* Dev: Introduced new action hook `wpo_wcpdf_document_created_manually`
* Marked tested up to WooCommerce 5.0

= 2.7.4 =
* New: Show notice if incompatible MailPoet mailing service is used
* New: WooCommerce webhook topic for document saves
* Fix: Don't reverse order of bulk document if already sorted oldest to newest
* Marked tested up to WooCommerce 4.9

= 2.7.3 =
* New: Support for line breaks in invoice notes
* Fix: Only pass opened edit fields when regenerating document
* Fix: Words in table headers could be broken up
* Deprecated: legacy translations (pre-2.0) are no longer read automatically (can be enabled in the Status tab)

= 2.7.2 =
* Fix: Update invoice number and date when regenerating document from edit mode
* Fix: Prevent infinite loop when temporary folder is not writable
* Fix: Prevent layout issues when custom order data exceeds column width
* Fix: Error when PHP Ctype extension is not installed
* Tested up to WooCommerce 4.8 & WP 5.6

= 2.7.1 =
* New: Redesigned action buttons
* New: Randomized temporary folder name for easier protection
* New: Setting to enable/disable customer notes
* New: Completely disable free invoice when that setting is enabled (not just attachments)
* New: Template action hooks before and after shop name and address
* New: Filter to set starting number for yearly reset
* Fix: Errors on third party products without weight/sku/dimensions
* Fix: Uneven spacing between action icons
* Fix: Missing `$email_to_send` parameter for `woocommerce_before_resend_order_emails` hook
* Fix: Break long words in billing address
* Tested up to WooCommerce 4.7

= 2.7.0 =
* New: Add per-order notes to invoices (requires template update if you have a custom template)
* New: Show notice with instructions for protecting the invoice folder on NGINX setups
* Fix: Show correct "next number" on settings page for sites using MySQL 8+
* Tested up to WooCommerce 4.6

= 2.6.1 =
* Fix: Load custom documents once rather than on every document request
* Tweak: execute wpo_wcpdf_init_document action in invoice too
* Tested up to WooCommerce 4.5

= 2.6.0 =
* Feature: More advanced address visibility options
* Fix: Deprecation notice in WooCommerce 4.4

= 2.5.4 =
* Fix: check for existence of WooCommerce functions preventing incidental crashes in specific deployment setups
* Fix: documents could still be generated programmatically when document disabled and not specifically checking for `$document->is_allowed()`
* Dev: Filter to disable reloading attachment translations
* Tested up to WooCommerce 4.4 & WP 5.5

= 2.5.3 =
* Fix: WP5.5 compatible PHPMailer integration
* Tested up to WooCommerce 4.3

= 2.5.2 =
* Fix: ImageMagick version conflict
* Translations: Updated POT

= 2.5.1 =
* Fix: Correct integration with permalink settings for `[wcpdf_download_invoice]` shortcode
* Fix: Plugin assets versioning

= 2.5.0 =
* Feature: Manually regenerate individual documents with latest settings (shop address/footer/etc)
* Feature: Shortcode to download invoice: `[wcpdf_download_invoice]`
* Feature: Logo height setting
* Fix: textdomain fallback would fail on specific site domains including .mo
* Fix: Unnecessary extra page on edge case table heights
* Fix: Settings disappearing when overriding document titles to empty string
* Fix: check if header logo file still exists before loading
* Fix: If document is already created, disregard 'disable for:' setting
* Fix: Reading document settings & number when stored incorrectly (by external plugins)
* Tested up to WooCommerce 4.2

= 2.4.10 =
* Tested up to WooCommerce 4.1

= 2.4.9 =
* Fix: Backwards compatibility with WooCommerce 2.6
* Fix: Description of the setting to disable invoice for free orders
* Changed: shorter my account button text ("Invoice" instead of "Download Invoice (PDF)")

= 2.4.8 =
* Dev: Added `wpo_wcpdf_pdf_data` filter for direct loading of PDF data
* Dev: Added `is_bulk` property to bulk documents

= 2.4.7 =
* Fix: missing order number in filename when invoice number not enabled
* Dev: Added action hook for document save method (`wpo_wcpdf_save_document`)
* Dev: Added action hook for printing custom data in PDF invoice data panel (`wpo_wcpdf_meta_box_after_document_data`)
* Tested up to WooCommerce 4.0 & WP 5.4

= 2.4.6 =
* Fix: Locale determination for admins on their own my account page
* Fix: Action buttons icon alignment in WP5.3+
* Fix: Add bulk actions via native WP methods
* Tweak: minimize calls to WooCommerce mailer class when loading settings

= 2.4.5 =
* Fix: Prevent errors for subscription tax fallback on refunds

= 2.4.4 =
* Fix: German Market thumbnail settings conflict
* Fix: Correctly sanitize wizard text input
* Fix: Link to documentation for increasing memory
* Fix: Fallback for subscription renewal tax rates

= 2.4.3 =
* Fix: Prevent errors unsetting a non-existing setting
* Fix: Potential crash on improperly initiated documents
* Fix: Reversed tax rate calculation arguments
* Fix: Support tax rate functions for non-line items
* Fix: comma position on multiple tax rates
* Fix: Setup wizard styles
* Translations: Added lv locale for Latvian (keeping lv_LV as fallback)
* Translations: Updated bundled Czech translations
* Tested up to WooCommerce 3.9

= 2.4.2 =
* Fix: 'No' option in new date & number visibility setting
* Fix: Resetting headers caused unintended caching of PDF files on some hosts

= 2.4.1 =
* Fix: Creating invoices for draft orders would crash plugin
* Tweak: Include time in default invoice date

= 2.4.0 =
* Feature: Option to use order number & date for invoice number & date
* Fix: prevent errors during update when WC not active
* Fix: don't auto create invoice number when manually entered & directly changing order status
* Fix: invoice tax amount for refunded orders (in combination with WooCommerce tax setting "as a single total")
* Tweak: Default to today's date when editing empty invoice date


= 2.3.5 =
* Feature: Accept single order ID for wcpdf_get_document function
* Feature: Filter to change number store for invoice
* Tweak: Always prefer WC() function over global for WC3.0+
* Fix: Incorrectly stored attachment settings couldn't be reset
* Fix: Prevent error notices during setup wizard
* Tested up to WooCommerce 3.8

= 2.3.4 =
* Fix: Prevent duplicate invoice numbers for multiple attachment setups
* Fix: Apply email order filter for each email separately

= 2.3.3 =
* Tweak: Move filter to override order object to document level (rather than per email)

= 2.3.2 =
* Fix: Load enhanced selection styles on settings page
* Fix: WC Bookings email attachment
* Tweak: Use WooCommerce 3.7 tax rate data when available.

= 2.3.1 =
* Fix: Errors for filtered formatted invoice numbers

= 2.3.0 =
* Feature: Setting to disable invoices globally for specific order statuses
* Feature: Control action buttons visibility from settings wizard.
* Feature: Allow loading of existing PDF file instead of generating on the fly via filter (`wpo_wcpdf_load_pdf_file_path`)
* Fix: Check if temp folder exists before creating
* Fix: Newlines in address from settings wizard
* Fix: Double images issue with WooCommerce German Market
* Fix: Only store document settings when creating one
* Tested with WooCommerce 3.7

= 2.2.14 =
* Fix: Set default PHPMailer validator to 'php' (fixing 'setFrom' errors on PHP 7.3)
* Fix: Attachment path for file lock check
* Tweak: Don't wait for file lock if locking disabled
* Tweak: JIT loading of core documents for early requests (before init 15)

= 2.2.13 =
* Feature: Better order notes formatting & optional filter for system notes
* Feature: add email object to attachment hook and allow order object filtering
* Fix: WooCommerce Chained Products row classes
* Fix: Issues with locked attachment files preventing the email from being sent correctly

= 2.2.12 =
* Tested up to WC3.6
* Fix: Prevent infinite loop on temporary folder creation for partially migrated sites or write permission issues
* Tweak: Removed height & width attributes from logo image (+filter `wpo_wcpdf_header_logo_img_element`)
* Dev: Enable guest access to PDF with order key in URL

= 2.2.11 =
* Fix: Fatal error on orders with multiple refunds

= 2.2.10 =
* Fix: Possible conflict with latest Subscriptions
* Fix: Load correct translations when admin user profile language is set to different locale
* Fix: Use file lock to prevent parallel processes creating the same attachment file
* Fix: Prevent notices for incorrectly loaded email classes
* Feature: Allow different invoice number column sorting methods by filter
* Feature: Filter for global prevention of creating specific document (`wpo_wcpdf_document_is_allowed`)

= 2.2.9 =
* Feature: Added customer note email to attachment options
* Fix: Prevent empty invoice dates from being saved as 1970 (fallback to current date/time)

= 2.2.8 =
* Tested up to WP5.1
* Tweak: Re-use attachment file if not older than 60 seconds (tentative fix for parallel read & write issues)
* Dev: Added URL overrides to switch between output mode (`&output=html`) and debug (`&debug=true`)

= 2.2.7 =
* Fix: Hardened permissions & security checks on several admin actions (audit by pluginvulnerabilities.com)
* Feature: Show checkmarks for existing documents on order details page buttons too
* Tweak: Product Bundles compatibility, hide items by default, following bundle settings (Simple Template)
* Tweak: Fallback to billing address on packing slip for orders without shipping address

= 2.2.6 =
* Fix: ship to different address check for empty shipping addresses
* Fix: Fix notice when using invoice number by plugin
* Fix: Underline position
* Fix: PHP 7.3 compatibility
* Tweak: Updated dompdf to 0.8.3
* Tweak: move admin menu item to the end of WooCommerce menu
* Tweak: pass document object to paper format & orientation filters

= 2.2.5 =
* Feature: Check marks to indicate whether a document exists
* Feature: Test mode to automatically apply updated settings to existing documents
* Feature: Admin bar indicator for debug mode setting
* Fix: always use latest email settings
* Fix: WooCommerce Composite Products item name compatibility
* Fix: Use woocommerce_thumbnail for WC3.3+
* Tweak: apply woocommerce_order_item_name filter (fixes compatibility with WooCommerce Product Addons 3.0)
* Tweak: Use WooCommerce date format instead of WP date format

= 2.2.4 =
* Fix: excluding some display options from historical settings
* Fix: fix notices when requesting properties as custom fields (in a custom template)

= 2.2.3 =
* Fix: issues reading shop settings

= 2.2.2 =
* Feature: Added option to always use most current settings for the invoice
* Fix: Double check for empty document numbers on initialization
* New filter: `wpo_wcpdf_output_format` to set output per document type

= 2.2.1 =
* Fix: potential number formatting issues with `wpo_wcpdf_raw_document_number` filter
* Fix: prevent direct loading of template files

= 2.2.0 =
* Feature: Document settings are now saved per order - changing settings after a PDF has been created will no longer affect the output
* Feature: Button to delete invoice or packing slip
* Feature: Better error handling and logging via WC Logger (WooCommerce > Status > Logs)
* Fix: Broader payment gateway compatibility (lower priority for documents initialization)
* Fix: undefined variable in construct when loading document programmatically (props to Christopher)
* Fix: compatibility with renamed WooCommerce plugins (settings page detection)
* Tweak: Reload translations before creating attachment
* Translations: Updated translations POT

= 2.1.10 =
* Feature: Include invoice number and date in WooCommerce data remover and exporter
* Fix: Row class for Chained Products compatibility
* Fix: Improved compatibility with Advanced Custom Fields
* Fix: Setting for disabling for free invoices should be applied even when other plugins are applying rules

= 2.1.9 =
* Feature: Automatic cleanup of temporary attachments folder (settings in Status tab)
* Fix: prevent infinite loop on sites without uploads folder
* Fix: tag replacements for externally hosted images (CDN)

= 2.1.8 =
* Fix: Fatal error on PHP 5.X

= 2.1.7 =
* Feature: add [order_number] placeholder for number format
* Feature: $order and $order_id variables now available directly template (without needing the document object)
* Feature: add actions before & after addresses
* Fix: Sorting orders by invoice number
* Fix: Aelia Currency Switcher - use decimal & Thousand separator settings
* Fix: fix jquery migrate warnings for media upload script
* Tweak: add calculated tax rate to item data

= 2.1.6 =
* Fix: Extended currency symbol setting for WooCommerce Currency Switcher by realmag777
* Fix: Apply WooCommerce decimal settings to tax rates with decimals
* Tweak: Pass document object to `wpo_wcpdf_email_attachment` filter

= 2.1.5 =
* Feature: Filter for number store table (wpo_wcpdf_number_store_table_name)
* Fix: prevent accessing order properties as custom field/order meta
* Fix: prevent wrong application of wpo_wcpdf_filename filter
* Fix: Improved tax rate calculation fallback

= 2.1.4 =
* Fix: WooCommerce 3.3 action buttons
* Feature: Added row classes for WooCommerce Composite Products

= 2.1.3 =
* Fix: Fatal PHP error on My Account page.

= 2.1.2 =
* Feature: New action wpo_wcpdf_init_document
* Fix: Use title getters for my-account and backend buttons
* Fix: Legacy Premium Templates reference
* Tweak: Skip documents overview in settings, default to invoice

= 2.1.1 =
* Fix: WooCommerce Order Status & Actions Manager emails compatibility
* Feature: sort orders by invoice number column
* Tweak: pass document object to title filters
* Tweak: use title getter in template files (instead of title string)

= 2.1.0 =
* Feature: WooCommerce Order Status & Actions Manager emails compatibility
* Fix: Better url fallback for images stored in cloud
* Update: dompdf library updated to 0.8.2 - DOMDocument parser set to default again

= 2.0.15 =
* Fix: Prevent saving invoice number/date from order details page when not edited

= 2.0.14 =
* Feature: Manually resend specific order emails in WooCommerce 3.2+
* Tweak: Show full size logo preview in settings
* Tweak: Custom field fallback to underscore prefixed meta key
* Dev: added `wpo_wcpdf_before_sequential_number_increment` action

= 2.0.13 =
* Fix: Minor XSS issue on settings screens by escaping and sanitizing 'tab' & 'section' GET variables. Discovered by Detectify.
* Fix: Pakistani Rupee Symbol
* Feature: Automatically enable extended currency symbol support for currencies not supported by Open Sans
* Dev: added `wpo_wcpdf_document_number_settings` filter

= 2.0.12 =
* Option: Use different HTML parser (debug settings)

= 2.0.11 =
* Fix: Improved fonts update routine (now preserves custom fonts)
* Fix: Enable HTML5 parser by default (fixes issues with libxml)
* Tweak: Show both PHP & WP Memory limit in Status tab

= 2.0.10 =
* Fix: Set invoice number backend button
* Fix: Thumbail paths
* Tweak: Make dompdf options filterable

= 2.0.9 =
* Feature: use `[invoice_date="ymd"]` in invoice number prefix or suffix to include a specific date format in the invoice number
* Fix: Postmeta table prefix for invoice counter
* Fix: 0% tax rates

= 2.0.8 =
* Feature: Add support for Bedrock / alternative folder structures
* Dev: Filter for merged documents
* Fix: Better attributes fallback for product variations

= 2.0.7 =
* Feature: Added button to delete legacy settings
* Feature: Option to enable font subsetting
* Fix: Invoice number sequence for databases with alternative auto_increment_increment settings
* Fix: Fallback function for MB String (mb_stripos)

= 2.0.6 =
* Feature: Improved third party invoice number filters (`wpo_wcpdf_external_invoice_number_enabled` & `wpo_wcpdf_external_invoice_number`)
* Fix: Underline position for Open Sans font
* Fix: Invoice number auto_increment for servers that restarted frequently
* Fix: Dompdf log file location (preventing open base_dir notices breaking PDF header)
* Fix: 1.6.6 Settings migration duplicates merging
* Tweak: Clear fonts folder when manually reinstalling fonts

= 2.0.5 =
* Feature: Remove temporary files (Status tab)
* Fix: Page number replacement
* Tweak: Fallback functions for MB String extension
* Tweak: Improved wpo_wcpdf_check_privs usability for my account privileges
* Legacy support: added wc_price alias for format_price method in document

= 2.0.4 =
* Fix: Apply filters for custom invoice number formatting in document too
* Fix: Parent fallback for missing dates from refunds

= 2.0.3 =
* Fix: Better support for legacy invoice number filter (`wpo_wcpdf_invoice_number` -  replaced by `wpo_wcpdf_formatted_document_number`)
* Fix: Document number formatting fallback to order date if no document date available
* Fix: Updated classmap: PSR loading didn't work on some installations
* Fix: Prevent order notes from all orders showing when document is not loaded properly in filter
* Tweak: Disable deprecation notices during email sending
* Tweak: ignore outdated language packs

= 2.0.2 =
* Fix: order notes using correct order_id
* Fix: WC3.0 deprecation notice for currency
* Fix: Avoid crashing on PHP5.2 and older
* Fix: Only use PHP MB String when present
* Fix: Remote images
* Fix: Download option

= 2.0.1 =
* Fix: PHP 5.4 issue

= 2.0.0 =
* New: Better structured & more advanced settings for documents
* New: Option to enable & disable Packing Slips or Invoices
* New: Invoice number sequence stored separately for improved speed & performance
* New: Completely rewritten codebase for more flexibility & better reliability
* New: Updated PDF library to DOMPDF 0.8
* New: PDF Library made pluggable (by using the `wpo_wcpdf_pdf_maker` filter)
* New: lots of new functions & filters to allow developers to hook into the plugin
* Changed: **$wpo_wcpdf variable is now deprecated** (legacy mode available & automatically enabled on update)
* Fix: Improved PHP 7 & 7.1 support
* Fix: Positive prices for refunds
* Fix: Use parent for attributes retrieved for product variations
* Fix: Set content type to PDF for download

= 1.6.6 =
* Feature: Facilitate downgrading from 2.0 (re-installing fonts & resetting version)
* Fix: Update currencies font (added Georgian Lari)
* Translations: Added Indonesian

== Upgrade Notice ==

= 2.1.10 =
2.X is a BIG update! Make a full site backup before upgrading if you were using version 1.X!
