<?php
namespace WPO\WC\PDF_Invoices\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_Upgrade' ) ) :

class Settings_Upgrade {

	public           $extensions;
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->extensions = array( 'pro', 'templates' );

		add_action( 'wpo_wcpdf_before_settings_page', array( $this, 'extensions_license_cache_notice' ), 10, 2 );
		add_action( 'wpo_wcpdf_after_settings_page', array( $this, 'extension_overview' ), 10, 2 );
		add_action( 'wpo_wcpdf_schedule_extensions_license_cache_clearing', array( $this, 'clear_extensions_license_cache' ) );
	}

	public function extensions_license_cache_notice( $tab, $active_section ) {
		if ( 'upgrade' === $tab && WPO_WCPDF()->settings->upgrade->get_extensions_license_data() ) {
			$message = sprintf(
				/* translators: 1. open anchor tag, 2. close anchor tag */
				__( 'Kindly be aware that the extensions\' license data is currently stored in cache, impeding the instant update of the information displayed below. To access the latest details, we recommend clearing the cache %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=tools' ) ) . '">',
				'</a>'
			);
			printf( '<div class="notice inline notice-warning"><p>%s</p></div>', $message );
		}
	}

	public function extension_overview( $tab, $section ) {
		if ( 'upgrade' === $tab ) {
			$features = array(
				array(
					'label'       => __( 'Proforma Invoice, Credit Note & Receipt', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Update your workflow and handle refunds. Both Proforma & Credit Note documents can either follow the main invoice numbering or have their own separate number sequence.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Attach to email', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Also attach the Packing Slip, Proforma Invoice and Credit Note to any of the outgoing emails.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Cloud storage upload', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Automatically upload your documents via FTP/SFTP or to Dropbox.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Bulk export', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Easily export documents for a specific date range.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Multilingual support', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Handle document translations with WPML, Polylang, Weglot, TranslatePress or GTranslate.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Attach static files', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Add up to three static files to your emails.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Custom document titles and filenames', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Customize document titles and filenames right in the plugin settings.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Custom address format', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Customize the address format of the billing and shipping addresses.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Order notification email', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						__( 'Send a notification email to user specified addresses.', 'woocommerce-pdf-invoices-packing-slips' ),
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/configuring-the-order-notification-email/',
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' )
					),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'PDF Customizer', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						__( 'Fully customize the product table and totals table on your documents.', 'woocommerce-pdf-invoices-packing-slips' ),
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/using-the-customizer/',
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' )
					),
					'extensions'  => array( 'templates', 'bundle' ),
				),
				array(
					'label'       => __( 'Add custom data to your documents', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						__( 'Display all sorts of data and apply conditional logic using Custom Blocks.', 'woocommerce-pdf-invoices-packing-slips' ),
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/using-custom-blocks/',
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' )
					),
					'extensions'  => array( 'templates', 'bundle' ),
				),
				array(
					'label'       => __( 'Additional PDF templates', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Make use of our Business or Modern template designs.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'templates', 'bundle' ),
				),
				array(
					'label'       => __( 'Add styling', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Easily change the look and feel of your documents by adding some custom CSS.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'templates', 'bundle' ),
				),
			);

			$extension_license_infos = $this->get_extension_license_infos();

			include( WPO_WCPDF()->plugin_path() . '/includes/views/upgrade-table.php' );
		}
	}

	/**
	 * Check if a PDF extension is enabled
	 *
	 * @param  string  $extension  can be 'pro' or 'templates'
	 * @return boolean
	 */
	public function extension_is_enabled( $extension ) {
		$is_enabled = false;

		if ( ! empty( $extension ) || ! in_array( $extension, $this->extensions ) ) {
			$extension_main_function = "WPO_WCPDF_".ucfirst( $extension );
			if ( function_exists( $extension_main_function ) ) {
				$is_enabled = true;
			}
		}

		return $is_enabled;
	}

	/**
	 * Get PDF extensions license info
	 *
	 * @param  bool  $ignore_cache
	 * @return array
	 */
	public function get_extension_license_infos( $ignore_cache = false ) {
		$extensions          = $this->extensions;
		$license_info        = ! $ignore_cache ? $this->get_extensions_license_data( 'cached' ) : array();
		$bundle_upgrade_link = '';
		$license_status      = 'inactive';

		if ( ! empty( $license_info ) ) {
			return $license_info;
		}

		foreach ( $extensions as $extension ) {
			$license_info[ $extension ] = array();
			$args                       = array();
			$request                    = null;
			$license_key                = '';
			$sidekick                   = false;
			$updater                    = null;

			if ( $this->extension_is_enabled( $extension ) ) {
				$extension_main_function = "WPO_WCPDF_".ucfirst( $extension );
				$updater                 = $extension_main_function()->updater;

				if ( $extension == 'templates' && version_compare( $extension_main_function()->version, '2.20.0', '<=' ) ) { // 'updater' property had 'private' visibility
					continue;
				}

				if ( is_null( $updater ) ) {
					continue;
				}

				// built-in updater
				if ( is_callable( [ $updater, 'get_license_key' ] ) ) {
					$license_key = $updater->get_license_key();
				// sidekick (legacy)
				} elseif ( property_exists( $updater, 'license_key' ) ) {
					$license_slug     = "wpo_wcpdf_{$extension}_license";
					$wpo_license_keys = get_option( 'wpocore_settings', array() );
					$license_key      = isset( $wpo_license_keys[$license_slug] ) ? $wpo_license_keys[$license_slug] : $license_key;
					$sidekick         = true;
				}

				if ( ! empty( $license_key ) ) {
					$args['edd_action']  = 'check_license';
					$args['license_key'] = trim( $license_key );

					// legacy
					if ( $sidekick ) {
						if ( ! class_exists( 'WPO_Update_Helper' ) ) {
							include_once( $extension_main_function()->plugin_path() . '/updater/update-helper.php' );
						}

						$item_name = 'PDF Invoices & Packing Slips for WooCommerce - ';
						$file      = $extension_main_function()->plugin_path();
						$version   = $extension_main_function()->version;
						$author    = 'WP Overnight';

						switch ( $extension ) {
							case 'pro':
								$item_name = "{$item_name}Professional";
								break;
							case 'templates':
								$item_name = "{$item_name}Premium Templates";
								break;
						}

						$updater = new \WPO_Update_Helper( $item_name, $file, $license_slug, $version, $author );
					}

				} else {
					continue;
				}

				if ( $updater && is_callable( array( $updater, 'remote_license_actions' ) ) && ! empty( $args ) ) {
					$request = $updater->remote_license_actions( $args );

					if ( is_object( $request ) && isset( $request->license ) ) {
						$license_info[$extension]['status'] = $license_status = $request->license;

						if ( empty( $bundle_upgrade_link ) && ! empty( $request->bundle_upgrade ) && is_string( $request->bundle_upgrade ) ) {
							$bundle_upgrade_link = $request->bundle_upgrade; // https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips/pull/503#issue-1678203436
						}
					}
				}
			}
		}

		$extensions[] = 'bundle';
		foreach ( $extensions as $extension ) {
			if ( ! empty( $bundle_upgrade_link ) && $license_status == 'valid' ) {
				$license_info[$extension]['url'] = $bundle_upgrade_link;
			} else {
				switch ( $extension ) {
					case 'pro':
						$license_info[$extension]['url'] = 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/';
						break;
					case 'templates':
						$license_info[$extension]['url'] = 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/';
						break;
					case 'bundle':
						$license_info[$extension]['url'] = 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/';
						break;
				}
			}
		}

		update_option( 'wpo_wcpdf_extensions_license_cache', $license_info );

		if ( as_next_scheduled_action( 'wpo_wcpdf_schedule_extensions_license_cache_clearing' ) ) {
			as_unschedule_action( 'wpo_wcpdf_schedule_extensions_license_cache_clearing' );
		}

		as_schedule_single_action( strtotime( "+1 week" ), 'wpo_wcpdf_schedule_extensions_license_cache_clearing' );

		return $license_info;
	}

	/**
	 * Clear extensions license cache
	 *
	 * @return void
	 */
	public function clear_extensions_license_cache() {
		delete_option( 'wpo_wcpdf_extensions_license_cache' );
	}

	/**
	 * Get extensions license data
	 *
	 * @param string $type can be 'cached' or 'live'
	 * @return array
	 */
	public function get_extensions_license_data( string $type = 'cached' ): array {
		$option_key = 'wpo_wcpdf_extensions_license_cache';

		// default to fetching cached data
		$data = get_option( $option_key, array() );

		// if type is 'live' or cached data is empty, fetch live data
		if ( 'live' === $type || empty( $data ) ) {
			$data = $this->get_extension_license_infos( true );

			if ( 'cached' === $type ) {
				update_option( $option_key, $data );
			}
		}

		return $data;
	}

	/**
	 * Check if are any extensions installed
	 *
	 * @return bool
	 */
	public function are_any_extensions_installed() {
		$installed = false;

		foreach ( $this->extensions as $extension ) {
			if ( $this->extension_is_enabled( $extension ) ) {
				$installed = true;
				break;
			}
		}

		return $installed;
	}

	/**
	 * Check if bundle (Pro + Templates) is active
	 *
	 * @return bool
	 */
	public function bundle_is_active() {
		$extension_license_infos = $this->get_extension_license_infos();
		$bundle                  = false;

		if ( ! empty( $extension_license_infos ) ) {
			$bundle = true;

			foreach ( $this->extensions as $extension ) {
				if (
					( isset( $extension_license_infos[ $extension ]['status'] ) && 'valid' !== $extension_license_infos[ $extension ]['status'] ) ||
					! isset( $extension_license_infos[ $extension ]['status'] )
				) {
					$bundle = false;
					break;
				}
			}
		}

		return $bundle;
	}

}

endif; // class_exists
