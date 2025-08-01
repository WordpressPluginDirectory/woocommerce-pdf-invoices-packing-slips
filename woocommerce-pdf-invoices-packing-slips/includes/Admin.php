<?php

namespace WPO\IPS;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use WPO\IPS\Documents\OrderDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Admin' ) ) :

class Admin {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_listing_actions' ) );

		if ( $this->invoice_columns_enabled() ) { // prevents the expensive hooks below to be attached. Improves Order List page loading speed
			add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_invoice_columns' ), 200 ); // WC 7.1+ (we lowered the priority to 200 to make sure it works with Admin Columns plugin: https://www.admincolumns.com/)
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'invoice_columns_data' ), 10, 2 ); // WC 7.1+
			add_filter( 'manage_woocommerce_page_wc-orders_sortable_columns', array( $this, 'invoice_columns_sortable' ) ); // WC 7.1+
			add_filter( 'woocommerce_shop_order_list_table_sortable_columns', array( $this, 'add_invoice_column_to_sortable_columns' ) );
			add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'adjust_order_list_query_args' ) );

			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_columns' ), 200 ); // (we lowered the priority to 200 to make sure it works with Admin Columns plugin: https://www.admincolumns.com/)
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'invoice_columns_data' ), 10, 2 );
			add_filter( 'manage_edit-shop_order_sortable_columns', array( $this, 'invoice_columns_sortable' ) );
			add_action( 'pre_get_posts', array( $this, 'sort_orders_by_numeric_invoice_number' ) );
		}

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );

		add_filter( 'request', array( $this, 'request_query_sort_by_column' ) );

		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'bulk_actions' ), 20 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'bulk_actions' ), 20 ); // WC 7.1+

		if ( $this->invoice_number_search_enabled() ) { // prevents slowing down the orders list search
			add_filter( 'woocommerce_order_table_search_query_meta_keys', array( $this, 'search_fields' ) ); // HPOS specific filter
			add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'search_fields' ) );
		}

		add_action( 'woocommerce_process_shop_order_meta', array( $this,'save_invoice_number_date' ), 35, 2 );

		// manually send emails
		// WooCommerce core processes order actions at priority 50
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'send_emails' ), 60, 2 );

		add_action( 'admin_notices', array( $this, 'review_plugin_notice' ) );
		add_action( 'admin_notices', array( $this, 'install_wizard_notice' ) );

		add_action( 'init', array( $this, 'setup_wizard') );
		// add_action( 'wpo_wcpdf_after_pdf', array( $this,'update_pdf_counter' ), 10, 2 );

		add_action( 'admin_bar_menu', array( $this, 'debug_enabled_warning' ), 999 );

		// AJAX actions for deleting, regenerating and saving document data
		add_action( 'wp_ajax_wpo_wcpdf_delete_document', array( $this, 'ajax_crud_document' ) );
		add_action( 'wp_ajax_wpo_wcpdf_regenerate_document', array( $this, 'ajax_crud_document' ) );
		add_action( 'wp_ajax_wpo_wcpdf_save_document', array( $this, 'ajax_crud_document' ) );
		add_action( 'wp_ajax_wpo_wcpdf_preview_formatted_number', array( $this, 'ajax_preview_formatted_number' ) );

		// document actions
		add_action( 'wpo_wcpdf_document_actions', array( $this, 'add_regenerate_document_button' ) );

		// add "invoice number" column to WooCommerce Analytic - Orders
		add_filter( 'woocommerce_rest_prepare_report_orders', array( $this, 'add_invoice_number_to_order_report' ) );
		add_filter( 'woocommerce_report_orders_export_columns', array( $this, 'add_invoice_number_header_to_order_export' ) );
		add_filter( 'woocommerce_report_orders_prepare_export_item', array( $this, 'add_invoice_number_value_to_order_export' ), 10, 2 );
	}

	// display review admin notice after 100 pdf downloads
	public function review_plugin_notice() {
		if ( $this->is_order_page() === false && !( isset( $_GET['page'] ) && $_GET['page'] == 'wpo_wcpdf_options_page' ) ) {
			return;
		}

		if ( get_option( 'wpo_wcpdf_review_notice_dismissed' ) !== false ) {
			return;
		} else {
			if ( isset( $_REQUEST['wpo_wcpdf_dismiss_review'] ) && isset( $_REQUEST['_wpdismissnonce'] ) ) {
				// validate nonce
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpdismissnonce'] ) ), 'dismiss_review_nonce' ) ) {
					wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_dismiss_review' );
					return;
				} else {
					update_option( 'wpo_wcpdf_review_notice_dismissed', true );
					return;
				}
			}

			// get invoice count to determine whether notice should be shown
			$invoice_count = $this->get_invoice_count();
			if ( $invoice_count > 100 ) {
				// keep track of how many days this notice is show so we can remove it after 7 days
				$notice_shown_on = get_option( 'wpo_wcpdf_review_notice_shown', array() );
				$today           = gmdate( 'Y-m-d' );
				if ( ! in_array( $today, $notice_shown_on ) ) {
					$notice_shown_on[] = $today;
					update_option( 'wpo_wcpdf_review_notice_shown', $notice_shown_on );
				}
				// count number of days review is shown, dismiss forever if shown more than 7
				if ( count( $notice_shown_on ) > 7 ) {
					update_option( 'wpo_wcpdf_review_notice_dismissed', true );
					return;
				}

				$rounded_count = (int) substr( (string) $invoice_count, 0, 1 ) * pow( 10, strlen( (string) $invoice_count ) - 1);
				?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-review-notice">
					<h3>
						<?php
							printf(
								/* translators: rounded count */
								esc_html__( 'Wow, you have created more than %d invoices with our plugin!', 'woocommerce-pdf-invoices-packing-slips' ),
								esc_html( $rounded_count )
							);
						?>
					</h3>
					<p><?php esc_html_e( 'It would mean a lot to us if you would quickly give our plugin a 5-star rating. Help us spread the word and boost our motivation!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<ul>
						<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips/reviews/?rate=5#new-post" class="button"><?php esc_html_e( 'Yes you deserve it!', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></a></li>
						<li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_review', true ), 'dismiss_review_nonce', '_wpdismissnonce' ) ); ?>" class="wpo-wcpdf-dismiss"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?> / <?php esc_html_e( 'Already did!', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
						<li><a href="mailto:support@wpovernight.com?Subject=Here%20is%20how%20I%20think%20you%20can%20do%20better"><?php esc_html_e( 'Actually, I have a complaint...', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
					</ul>
				</div>
				<script type="text/javascript">
				jQuery( function( $ ) {
					$( '.wpo-wcpdf-review-notice' ).on( 'click', '.notice-dismiss', function( event ) {
						event.preventDefault();
						window.location.href = $( '.wpo-wcpdf-dismiss' ).attr('href');
					});
				});
				</script>
				<!-- Hide extensions ad if this is shown -->
				<style>.wcpdf-extensions-ad { display: none; }</style>
				<?php
			}
		}
	}

	public function install_wizard_notice() {
		// automatically remove notice after 1 week, set transient the first time
		if ( $this->is_order_page() === false && !( isset( $_GET['page'] ) && $_GET['page'] == 'wpo_wcpdf_options_page' ) ) {
			return;
		}

		if ( get_option( 'wpo_wcpdf_install_notice_dismissed' ) !== false ) {
			return;
		} else {
			if ( isset( $_REQUEST['wpo_wcpdf_dismiss_install'] ) && isset( $_REQUEST['_wpdismissnonce'] ) ) {
				// validate nonce
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpdismissnonce'] ) ), 'dismiss_install_nonce' ) ) {
					wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_dismiss_install' );
					return;
				} else {
					update_option( 'wpo_wcpdf_install_notice_dismissed', true );
					return;
				}
			}

			if ( get_transient( 'wpo_wcpdf_new_install' ) !== false ) {
				?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-install-notice">
					<p><strong><?php esc_html_e( 'New to PDF Invoices & Packing Slips for WooCommerce?', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong> &#8211; <?php esc_html_e( 'Jumpstart the plugin by following our wizard!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo-wcpdf-setup' ) ); ?>" class="button-primary"><?php esc_html_e( 'Run the Setup Wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a> <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_install', true ), 'dismiss_install_nonce', '_wpdismissnonce' ) ); ?>" class="wpo-wcpdf-dismiss-wizard"><?php esc_html_e( 'I am the wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
				<script type="text/javascript">
				jQuery( function( $ ) {
					$( '.wpo-wcpdf-install-notice' ).on( 'click', '.notice-dismiss', function( event ) {
						event.preventDefault();
						window.location.href = $( '.wpo-wcpdf-dismiss-wizard' ).attr('href');
					});
				});
				</script>
				<?php
			}
		}

	}

	public function setup_wizard() {
		// Setup/welcome
		if ( ! empty( $_GET['page'] ) && 'wpo-wcpdf-setup' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			delete_transient( 'wpo_wcpdf_new_install' );
			SetupWizard::instance();
		}
	}

	public function get_invoice_count() {
		global $wpdb;

		$invoice_count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT count(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_wcpdf_invoice_number'
			)
		);

		return (int) $invoice_count;
	}

	public function update_pdf_counter( $document_type, $document ) {
		if ( in_array( $document_type, array('invoice','packing-slip') ) ) {
			$pdf_count = (int) get_option( 'wpo_wcpdf_count_'.$document_type, 0 );
			update_option( 'wpo_wcpdf_count_'.$document_type, $pdf_count + 1 );
		}
	}

	/**
	 * Add PDF actions to the orders listing
	 */
	public function add_listing_actions( $order ) {
		// do not show buttons for trashed orders
		if ( 'trash' === $order->get_status() ) {
			return;
		}

		$this->disable_storing_document_settings();

		$listing_actions = array();
		$documents       = WPO_WCPDF()->documents->get_documents( 'enabled', 'any' );

		foreach ( $documents as $document ) {
			$document_title = $document->get_title();
			$document_type  = $document->get_type();
			$icon           = ! empty( $document->icon ) ? $document->icon : WPO_WCPDF()->plugin_url() . '/assets/images/generic_document.svg';

			if ( $document = wcpdf_get_document( $document_type, $order ) ) {
				foreach ( $document->output_formats as $output_format ) {
					switch ( $output_format ) {
						default:
						case 'pdf':
							if ( $document->is_enabled( $output_format ) ) {
								$document_url     = WPO_WCPDF()->endpoint->get_document_link( $order, $document->get_type() );
								$document_title   = is_callable( array( $document, 'get_title' ) ) ? $document->get_title() : $document_title;
								$document_exists  = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
								$document_printed = $document_exists && is_callable( array( $document, 'printed' ) ) ? $document->printed() : false;
								$class            = array( $document->get_type(), $output_format );

								if ( $document_exists ) {
									$class[] = 'exists';
								}
								if ( $document_printed ) {
									$class[] = 'printed';
								}

								$listing_actions[$document->get_type()] = array(
									'url'           => $document_url,
									'img'           => $icon,
									'alt'           => "PDF " . $document_title,
									'exists'        => $document_exists,
									'printed'       => $document_printed,
									'class'         => apply_filters( 'wpo_wcpdf_action_button_class', implode( ' ', $class ), $document ),
									'output_format' => $output_format,
								);
							}
							break;
						case 'ubl':
							if ( $document->is_enabled( $output_format ) && wcpdf_is_ubl_available() ) {
								$document_url    = WPO_WCPDF()->endpoint->get_document_link( $order, $document->get_type(), array( 'output' => $output_format ) );
								$document_title  = is_callable( array( $document, 'get_title' ) ) ? $document->get_title() : $document_title;
								$document_exists = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
								$class           = array( $document->get_type(), $output_format );

								if ( $document_exists ) {
									$class[] = 'exists';
								}

								$listing_actions[ $document->get_type()."_{$output_format}" ] = array(
									'url'           => $document_url,
									'img'           => $icon,
									'alt'           => "UBL " . $document_title,
									'exists'        => $document_exists,
									'printed'       => false,
									'ubl'           => true,
									'class'         => apply_filters( 'wpo_wcpdf_ubl_action_button_class', implode( ' ', $class ), $document ),
									'output_format' => $output_format,
								);
							}
							break;
					}
				}

			}
		}

		$listing_actions = apply_filters( 'wpo_wcpdf_listing_actions', $listing_actions, $order );

		foreach ( $listing_actions as $action => $data ) {
			if ( ! isset( $data['class'] ) ) {
				$data['class'] = $data['exists'] ? "exists {$action}" : $action;
			}

			$exists  = $data['exists']  ? '<svg class="icon-exists" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path></svg>' : '';
			$printed = $data['printed'] ? '<svg class="icon-printed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 4H16V6H8V4ZM18 6H22V18H18V22H6V18H2V6H6V2H18V6ZM20 16H18V14H6V16H4V8H20V16ZM8 16H16V20H8V16ZM8 10H6V12H8V10Z"></path></svg>' : '';

			// ubl replaces exists
			$exists  = isset( $data['output_format'] ) && 'ubl' === $data['output_format'] ? '<svg class="icon-ubl" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.59323 18.3608L9.95263 16.9123L9.95212 16.8932L4.85783 12.112L9.64826 7.00791L8.18994 5.63922L2.03082 12.2016L8.59323 18.3608ZM15.4068 18.3608L14.0474 16.9123L14.0479 16.8932L19.1422 12.112L14.3517 7.00791L15.8101 5.63922L21.9692 12.2016L15.4068 18.3608Z"/></svg>' : $exists;

			$allowed_svg_tags = array(
				'svg' => array(
					'class'   => true,
					'xmlns'   => true,
					'viewbox' => true, // Lowercase 'viewbox' because wp_kses() converts attribute names to lowercase
				),
				'path' => array(
					'fill-rule' => true,
					'clip-rule' => true,
					'd'         => true,
				),
			);

			if ( isset( $data['output_format'] ) && ( 'ubl' !== $data['output_format'] || $data['exists'] ) ) {
				printf(
					'<a href="%1$s" class="button tips wpo_wcpdf %2$s" target="_blank" alt="%3$s" data-tip="%3$s" style="background-image:url(%4$s);">%5$s%6$s</a>',
					esc_url( $data['url'] ),
					esc_attr( $data['class'] ),
					esc_attr( $data['alt'] ),
					esc_attr( $data['img'] ),
					! empty( $exists ) ? wp_kses( $exists, $allowed_svg_tags ) : '',
					! empty( $printed ) ? wp_kses( $printed, $allowed_svg_tags ) : ''
				);
			}
		}
	}

	/**
	 * Create additional Shop Order column for Invoice Number/Date
	 * @param array $columns shop order columns
	 */
	public function add_invoice_columns( $columns ) {
		$current_screen = get_current_screen();

		if ( WPO_WCPDF()->order_util->custom_orders_table_usage_is_enabled() && 'woocommerce_page_wc-orders' !== $current_screen->id ) {
			return $columns;
		}

		// get invoice settings
		$invoice          = wcpdf_get_invoice( null );
		$invoice_settings = $invoice->get_settings();
		$invoice_columns  = array(
			'invoice_number_column' => __( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' ),
			'invoice_date_column'   => __( 'Invoice Date', 'woocommerce-pdf-invoices-packing-slips' ),
		);

		$offset = 2; // after order number column
		foreach ( $invoice_columns as $slug => $name ) {
			if ( ! isset( $invoice_settings[$slug] ) ) {
				continue;
			}

			$columns = array_slice( $columns, 0, $offset, true ) +
				array( $slug => $name ) +
				array_slice( $columns, 2, count( $columns ) - 1, true ) ;

			$offset++;
		}

		return $columns;
	}

	/**
	 * Display Invoice Number/Date in Shop Order column (if available)
	 * @param  string $column                 column slug
	 * @param  string $post_or_order_object   object
	 */
	public function invoice_columns_data( $column, $post_or_order_object ) {
		if ( ! in_array( $column, array( 'invoice_number_column', 'invoice_date_column' ) ) ) {
			return;
		}

		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		if ( ! is_object( $order ) && is_numeric( $order ) ) {
			$order = wc_get_order( absint( $order ) );
		}

		$this->disable_storing_document_settings();

		$invoice = wcpdf_get_invoice( $order );

		switch ( $column ) {
			case 'invoice_number_column':
				$invoice_number = ! empty( $invoice ) && ! empty( $invoice->get_number() ) ? $invoice->get_number() : '';
				echo esc_html( $invoice_number );
				do_action( 'wcpdf_invoice_number_column_end', $order );
				break;
			case 'invoice_date_column':
				$invoice_date = ! empty( $invoice ) && ! empty( $invoice->get_date() ) ? $invoice->get_date()->date_i18n( wcpdf_date_format( $invoice, 'invoice_date_column' ) ) : '';
				echo esc_html( $invoice_date );
				do_action( 'wcpdf_invoice_date_column_end', $order );
				break;
			default:
				return;
		}
	}

	/**
	 * Check if at least 1 of the invoice columns is enabled.
	 */
	public function invoice_columns_enabled() {
		$is_enabled       = false;
		$invoice_settings = get_option( 'wpo_wcpdf_documents_settings_invoice', array() );
		$invoice_columns  = [
			'invoice_number_column',
			'invoice_date_column',
		];

		foreach ( $invoice_columns as $column ) {
			if ( isset( $invoice_settings[$column] ) ) {
				$is_enabled = true;
				break;
			}
		}

		return $is_enabled;
	}

	/**
	 * Check if the invoice number search is enabled.
	 */
	public function invoice_number_search_enabled() {
		$is_enabled       = false;
		$invoice_settings = get_option( 'wpo_wcpdf_documents_settings_invoice', array() );

		if ( isset( $invoice_settings['invoice_number_search'] ) ) {
			$is_enabled = true;
		}

		return $is_enabled;
	}


	/**
	 * Makes invoice columns sortable
	 */
	public function invoice_columns_sortable( $columns ) {
		$columns['invoice_number_column'] = 'invoice_number_column';
		$columns['invoice_date_column']   = 'invoice_date_column';
		return $columns;
	}

	/**
	 * WC3.X+ sorting
	 */
	public function request_query_sort_by_column( $query_vars ) {
		global $typenow;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) && ! empty( $query_vars['orderby'] ) ) {
			switch ( $query_vars['orderby'] ) {
				case 'invoice_number_column':
					$query_vars = array_merge( $query_vars, array(
						'meta_key' => '_wcpdf_invoice_number',
						'orderby'  => apply_filters( 'wpo_wcpdf_invoice_number_column_orderby', 'meta_value' ),
					) );
					break;
				case 'invoice_date_column':
					$query_vars = array_merge( $query_vars, array(
						'meta_key' => '_wcpdf_invoice_date',
						'orderby'  => apply_filters( 'wpo_wcpdf_invoice_date_column_orderby', 'meta_value' ),
					) );
					break;
				default:
					return $query_vars;
			}
		}

		return $query_vars;
	}

	/**
	 * Add the meta boxes on the single order page
	 *
	 * @param string $wc_screen_id  Can be also $post_type
	 * @param object $wc_order      Can be also $post
	 * @return void
	 */
	public function add_meta_boxes( $wc_screen_id, $wc_order ) {
		if ( WPO_WCPDF()->order_util->custom_orders_table_usage_is_enabled() ) {
			$screen_id = wc_get_page_screen_id( 'shop-order' );
		} else {
			$screen_id = 'shop_order';
		}

		if ( $wc_screen_id != $screen_id ) {
			return;
		}

		// resend order emails
		add_meta_box(
			'wpo_wcpdf_send_emails',
			__( 'Send order email', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'send_order_email_meta_box' ),
			$screen_id,
			'side',
			'high'
		);

		// create PDF buttons
		add_meta_box(
			'wpo_wcpdf-box',
			__( 'Create PDF', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'pdf_actions_meta_box' ),
			$screen_id,
			'side',
			'default'
		);


		$ubl_documents = WPO_WCPDF()->documents->get_documents( 'enabled', 'ubl' );
		if ( count( $ubl_documents ) > 0 ) {
			// create UBL buttons
			add_meta_box(
				'wpo_wcpdf-ubl-box',
				__( 'Create UBL', 'woocommerce-pdf-invoices-packing-slips' ),
				array( $this, 'ubl_actions_meta_box' ),
				$screen_id,
				'side',
				'default'
			);
		}

		// Invoice number & date
		add_meta_box(
			'wpo_wcpdf-data-input-box',
			__( 'PDF document data', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'data_input_box_content' ),
			$screen_id,
			'normal',
			'default'
		);
	}

	/**
	 * Resend order emails
	 */
	public function send_order_email_meta_box( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		?>
		<ul class="wpo_wcpdf_send_emails order_actions submitbox">
			<li class="wide" id="actions" style="padding-left:0; padding-right:0; border:0;">
				<select name="wpo_wcpdf_send_emails">
					<option value=""><?php esc_html_e( 'Choose an email to send&hellip;', 'woocommerce-pdf-invoices-packing-slips' ); ?></option>
					<?php
					$mailer           = WC()->mailer();
					$order_emails     = array( 'new_order', 'cancelled_order', 'customer_processing_order', 'customer_completed_order', 'customer_invoice' );
					$available_emails = apply_filters_deprecated( 'woocommerce_resend_order_emails_available', array( $order_emails ), '3.5.7', 'wpo_wcpdf_resend_order_emails_available' );
					$available_emails = apply_filters( 'wpo_wcpdf_resend_order_emails_available', $available_emails, $order->get_id() );
					$mails            = $mailer->get_emails();

					if ( ! empty( $mails ) && ! empty( $available_emails ) ) { ?>
						<?php
						foreach ( $mails as $mail ) {
							if ( in_array( $mail->id, $available_emails ) && 'no' !== $mail->enabled ) {
								echo '<option value="send_email_' . esc_attr( $mail->id ) . '">' . esc_html( $mail->title ) . '</option>';
							}
						} ?>
						<?php
					}
					?>
				</select>
			</li>
			<li class="wide" style="border:0; padding-left:0; padding-right:0; padding-bottom:0; float:left;">
				<input type="submit" class="button save_order button-primary" name="save" value="<?php esc_attr_e( 'Save order & send email', 'woocommerce-pdf-invoices-packing-slips' ); ?>" />
				<?php
				$url = esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_action', 'resend_email' ), 'generate_wpo_wcpdf' ) );
				?>
			</li>
		</ul>
		<?php
	}

	/**
	 * Create the PDF meta box content on the single order page
	 */
	public function pdf_actions_meta_box( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$this->disable_storing_document_settings();

		$meta_box_actions = array();
		$documents        = WPO_WCPDF()->documents->get_documents();

		foreach ( $documents as $document ) {
			$document_title = $document->get_title();
			$document       = wcpdf_get_document( $document->get_type(), $order );

			if ( $document ) {
				$document_url          = WPO_WCPDF()->endpoint->get_document_link( $order, $document->get_type() );
				$document_title        = is_callable( array( $document, 'get_title' ) ) ? $document->get_title() : $document_title;
				$document_exists       = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
				$document_printed      = $document_exists && is_callable( array( $document, 'printed' ) ) ? $document->printed() : false;
				$document_printed_data = $document_exists && $document_printed && is_callable( array( $document, 'get_printed_data' ) ) ? $document->get_printed_data() : [];
				$document_settings     = get_option( 'wpo_wcpdf_documents_settings_'.$document->get_type() ); // $document-settings might be not updated with the last settings
				$unmark_printed_url    = ! empty( $document_printed_data ) && isset( $document_settings['unmark_printed'] ) ? WPO_WCPDF()->endpoint->get_document_printed_link( 'unmark', $order, $document->get_type() ) : false;
				$manually_mark_printed = WPO_WCPDF()->main->document_can_be_manually_marked_printed( $document );
				$mark_printed_url      = $manually_mark_printed ? WPO_WCPDF()->endpoint->get_document_printed_link( 'mark', $order, $document->get_type() ) : false;
				$class                 = [ $document->get_type() ];

				if ( $document_exists ) {
					$class[] = 'exists';
				}
				if ( $document_printed ) {
					$class[] = 'printed';
				}

				$meta_box_actions[$document->get_type()] = array(
					'url'                   => esc_url( $document_url ),
					'alt'                   => "PDF " . $document_title,
					'title'                 => "PDF " . $document_title,
					'exists'                => $document_exists,
					'printed'               => $document_printed,
					'printed_data'          => $document_printed_data,
					'unmark_printed_url'    => $unmark_printed_url,
					'manually_mark_printed' => $manually_mark_printed,
					'mark_printed_url'      => $mark_printed_url,
					'class'                 => apply_filters( 'wpo_wcpdf_action_button_class', implode( ' ', $class ), $document ),
				);
			}
		}

		$meta_box_actions = apply_filters( 'wpo_wcpdf_meta_box_actions', $meta_box_actions, $order->get_id() );

		?>
		<ul class="wpo_wcpdf-actions">
			<?php
			foreach ( $meta_box_actions as $document_type => $data ) {

				$url                   = isset( $data['url'] ) ? $data['url'] : '';
				$class                 = isset( $data['class'] ) ? $data['class'] : '';
				$alt                   = isset( $data['alt'] ) ? $data['alt'] : '';
				$title                 = isset( $data['title'] ) ? $data['title'] : '';
				$exists                = isset( $data['exists'] ) && $data['exists'] ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path></svg>' : '';
				$manually_mark_printed = isset( $data['manually_mark_printed'] ) && $data['manually_mark_printed'] && ! empty( $data['mark_printed_url'] ) ? '<p class="printed-data">&#x21b3; <a href="' . $data['mark_printed_url'] . '">' . __( 'Mark printed', 'woocommerce-pdf-invoices-packing-slips' ) . '</a></p>' : '';
				$printed               = isset( $data['printed'] ) && $data['printed'] ? '<svg class="icon-printed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 4H16V6H8V4ZM18 6H22V18H18V22H6V18H2V6H6V2H18V6ZM20 16H18V14H6V16H4V8H20V16ZM8 16H16V20H8V16ZM8 10H6V12H8V10Z"></path></svg>' : '';
				$unmark_printed        = isset( $data['unmark_printed_url'] ) && $data['unmark_printed_url'] ? '<a class="unmark_printed" href="' . $data['unmark_printed_url'].'">' . __( 'Unmark', 'woocommerce-pdf-invoices-packing-slips' ).'</a>' : '';
				$printed_data          = isset( $data['printed'] ) && $data['printed'] && ! empty( $data['printed_data']['date'] ) ? '<p class="printed-data">&#x21b3; ' . $printed . '' . date_i18n( 'Y/m/d H:i:s', (int) $data['printed_data']['date'] ) . '' . $unmark_printed . '</p>' : '';

				$allowed_tags = array(
					'svg' => array(
						'class'   => true,
						'xmlns'   => true,
						'viewbox' => true, // Lowercase 'viewbox' because wp_kses() converts attribute names to lowercase
					),
					'path' => array(
						'fill-rule' => true,
						'clip-rule' => true,
						'd'         => true,
					),
					'p' => array(
						'class' => true,
					),
					'a' => array(
						'href' => true,
						'class' => true,
					),
				);

				printf(
					'<li><a href="%1$s" class="button %2$s" target="_blank" alt="%3$s">%4$s%5$s</a>%6$s%7$s</li>',
					esc_url( $url ),
					esc_attr( $class ),
					esc_attr( $alt ),
					esc_html( $title ),
					! empty( $exists ) ? wp_kses( $exists, $allowed_tags ) : '',
					wp_kses( $manually_mark_printed, $allowed_tags ),
					! empty( $printed_data ) ? wp_kses( $printed_data, $allowed_tags ) : ''
				);
			}
			?>
		</ul>
		<?php
	}

	/**
	 * Create the UBL meta box content on the single order page
	 */
	public function ubl_actions_meta_box( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$this->disable_storing_document_settings();

		$meta_box_actions = array();
		$documents        = WPO_WCPDF()->documents->get_documents( 'enabled', 'ubl' );

		foreach ( $documents as $document ) {
			if ( in_array( 'ubl', $document->output_formats ) ) {
				$document_title = $document->get_title();
				$document       = wcpdf_get_document( $document->get_type(), $order );

				if ( $document ) {
					$document_url    = WPO_WCPDF()->endpoint->get_document_link( $order, $document->get_type(), array( 'output' => 'ubl' ) );
					$document_title  = is_callable( array( $document, 'get_title' ) ) ? $document->get_title() : $document_title;
					$document_exists = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
					$class           = array( $document->get_type(), 'ubl' );

					if ( $document_exists ) {
						$class[] = 'exists';
					}

					$meta_box_actions[ $document->get_type() ] = array(
						'url'    => $document_url,
						'alt'    => "UBL " . $document_title,
						'title'  => "UBL " . $document_title,
						'exists' => $document_exists,
						'class'  => apply_filters( 'wpo_wcpdf_ubl_action_button_class', implode( ' ', $class ), $document ),
					);
				}
			}
		}

		$meta_box_actions = apply_filters( 'wpo_wcpdf_ubl_meta_box_actions', $meta_box_actions, $order->get_id() );
		if ( empty( $meta_box_actions ) || ! wcpdf_is_ubl_available() ) {
			return;
		}
		?>
		<ul class="wpo_wcpdf-ubl-actions">
			<?php
				$ubl_documents = 0;

				foreach ( $meta_box_actions as $document_type => $data ) {
					$url    = isset( $data['url'] ) ? $data['url'] : '';
					$class  = isset( $data['class'] ) ? $data['class'] : '';
					$alt    = isset( $data['alt'] ) ? $data['alt'] : '';
					$title  = isset( $data['title'] ) ? $data['title'] : '';
					$exists = isset( $data['exists'] ) && $data['exists'] ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path></svg>' : '';

					$allowed_svg_tags = array(
						'svg' => array(
							'class'   => true,
							'xmlns'   => true,
							'viewbox' => true, // Lowercase 'viewbox' because wp_kses() converts attribute names to lowercase
						),
						'path' => array(
							'fill-rule' => true,
							'clip-rule' => true,
							'd'         => true,
						),
					);

					if ( ! empty( $exists ) ) {
						printf(
							'<li><a href="%1$s" class="button %2$s" target="_blank" alt="%3$s">%4$s%5$s</a></li>',
							esc_url( $url ),
							esc_attr( $class ),
							esc_attr( $alt ),
							esc_html( $title ),
							! empty( $exists ) ? wp_kses( $exists, $allowed_svg_tags ) : ''
						);

						$ubl_documents++;
					}
				}

				if ( 0 === $ubl_documents ) {
					esc_html_e( 'UBL documents require the correspondent PDF to be generated first.', 'woocommerce-pdf-invoices-packing-slips' );
				}
			?>
		</ul>
		<?php
	}

	public function data_input_box_content( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$this->disable_storing_document_settings();

		$invoice = wcpdf_get_document( 'invoice', $order );

		do_action( 'wpo_wcpdf_meta_box_start', $order, $this );

		if ( $invoice ) {
			// data
			$data = array(
				'number' => array(
					'label' => __( 'Invoice number:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'date' => array(
					'label' => __( 'Invoice date:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'display_date' =>  array(
					'label' => __( 'Invoice display date:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'creation_trigger' =>  array(
					'label' => __( 'Invoice created via:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'notes' => array(
					'label' => __( 'Notes (displayed in the invoice):', 'woocommerce-pdf-invoices-packing-slips' ),
				),

			);
			// output
			$this->output_number_date_edit_fields( $invoice, $data );

		}

		do_action( 'wpo_wcpdf_meta_box_end', $order, $this );
	}

	/**
	 * Returns the current values for the document data.
	 *
	 * @param OrderDocument $document The document instance.
	 * @param array $data The data to be processed.
	 *
	 * @return array The current values for the document data.
	 */
	public function get_current_values_for_document_data( OrderDocument $document, array $data ): array {
		$current     = array();
		$name_prefix = "_wcpdf_{$document->slug}_";

		// Document number + date data
		if ( $document->exists() ) {
			$document_number_instance = $document->get_number();

			if ( ! empty( $document_number_instance ) ) {
				$current['number'] = array(
					'prefix' => array(
						'value' => $document_number_instance->get_prefix() ?: null,
						'name'  => "{$name_prefix}number_prefix",
					),
					'plain' => array(
						'value' => $document_number_instance->get_plain() ?: null,
						'name'  => "{$name_prefix}number_plain",
					),
					'suffix' => array(
						'value' => $document_number_instance->get_suffix() ?: null,
						'name'  => "{$name_prefix}number_suffix",
					),
					'padding' => array(
						'value' => $document_number_instance->get_padding() ?: null,
						'name'  => "{$name_prefix}number_padding",
					),
					'formatted' => array(
						'value' => $document_number_instance->get_formatted() ?: null,
						'name'  => "{$name_prefix}number_formatted",
					),
				);
			}

			$document_date_instance = $document->get_date();

			if ( ! empty( $document_date_instance ) ) {
				$current['date'] = array(
					'formatted' => $document_date_instance->date_i18n( wc_date_format().' @ '.wc_time_format() ) ?: null,
					'date'      => $document_date_instance->date_i18n( 'Y-m-d' ) ?: date_i18n( 'Y-m-d' ),
					'hour'      => $document_date_instance->date_i18n( 'H' ) ?: date_i18n( 'H' ),
					'minute'    => $document_date_instance->date_i18n( 'i' ) ?: date_i18n( 'i' ),
					'name'      => "{$name_prefix}date",
				);
			}
		}

		// Default number data
		if ( ! isset( $current['number'] ) ) {
			$number_settings = $document->get_number_settings();

			$current['number'] = array(
				'prefix' => array(
					'value' => $number_settings['prefix'] ?? null,
					'name'  => "{$name_prefix}number_prefix",
				),
				'plain' => array(
					'value' => 0,
					'name'  => "{$name_prefix}number_plain",
				),
				'suffix' => array(
					'value' => $number_settings['suffix'] ?? null,
					'name'  => "{$name_prefix}number_suffix",
				),
				'padding' => array(
					'value' => ! empty( $number_settings['padding'] ) ? absint( $number_settings['padding'] ) : 1,
					'name'  => "{$name_prefix}number_padding",
				),
			);

			$current['number']['formatted'] = array(
				'value' => wpo_wcpdf_format_document_number(
					absint( $current['number']['plain']['value'] ),
					$current['number']['prefix']['value'],
					$current['number']['suffix']['value'],
					absint( $current['number']['padding']['value'] ),
					$document,
					$document->order
				),
				'name'  => "{$name_prefix}number_formatted",
			);
		}

		// Default date data
		if ( ! isset( $current['date'] ) ) {
			$current['date'] = array(
				'formatted' => date_i18n( wc_date_format() . ' @ ' . wc_time_format() ),
				'date'      => date_i18n( 'Y-m-d' ),
				'hour'      => date_i18n( 'H' ),
				'minute'    => date_i18n( 'i' ),
				'name'      => "{$name_prefix}date",
			);
		}

		// Other complementary data
		if ( ! empty( $data['notes'] ) ) {
			$current['notes'] = array(
				'value' => $document->get_document_notes(),
				'name'  => "{$name_prefix}notes",
			);
		}

		if ( ! empty( $data['display_date'] ) ) {
			$current['display_date'] = array(
				'value' => $document->document_display_date(),
				'name'  => "{$name_prefix}display_date",
			);
		}

		if ( ! empty( $data['creation_trigger'] ) ) {
			$document_triggers = WPO_WCPDF()->main->get_document_triggers();
			$creation_trigger  = $document->get_creation_trigger();
			$current['creation_trigger'] = array(
				'value' => isset( $document_triggers[ $creation_trigger ] ) ? $document_triggers[ $creation_trigger] : '',
				'name'  => "{$name_prefix}creation_trigger",
			);
		}

		foreach ( $data as $key => $value ) {
			if ( isset( $current[ $key ] ) ) {
				$data[ $key ] = array_merge( $current[ $key ], $value );
			}
		}

		return apply_filters( 'wpo_wcpdf_current_values_for_document', $data, $document );
	}

	public function output_number_date_edit_fields( $document, $data ): void {
		if ( empty( $document ) || empty( $document->order ) || ! is_callable( array( $document->order, 'get_id' ) ) ) {
			return;
		}

		$data = apply_filters( 'wpo_wcpdf_document_data_meta_box_fields', $data, $document );

		if ( empty( $data ) ) {
			return;
		}

		$data = $this->get_current_values_for_document_data( $document, $data );

		$document_data_editing_enabled = \WPO_WCPDF()->settings->user_can_manage_settings() &&
			( ! empty( \WPO_WCPDF()->settings->debug_settings['enable_document_data_editing'] ) || ! in_array( $document->get_type(), array( 'invoice', 'credit-note' ) ) );
		?>
		<div class="wcpdf-data-fields" data-document="<?php echo esc_attr( $document->get_type() ); ?>" data-order_id="<?php echo esc_attr( $document->order->get_id() ); ?>">
			<section class="wcpdf-data-fields-section number-date">
				<!-- Title -->
				<h4>
					<?php echo wp_kses_post( $document->get_title() ); ?>
					<?php if ( $document->exists() && ( isset( $data['number'] ) || isset( $data['date'] ) ) && $this->user_can_manage_document( $document->get_type() ) ) : ?>
						<span class="wpo-wcpdf-edit-date-number dashicons dashicons-edit"></span>
						<span class="wpo-wcpdf-delete-document dashicons dashicons-trash" data-action="delete" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpo_wcpdf_delete_document' ) ); ?>"></span>
						<?php do_action( 'wpo_wcpdf_document_actions', $document ); ?>
					<?php endif; ?>
				</h4>

				<!-- Read only -->
				<div class="read-only">
					<?php if ( $document->exists() ) : ?>
						<?php if ( isset( $data['number'] ) ) : ?>
							<div class="<?php echo esc_attr( $document->get_type() ); ?>-number">
								<p class="form-field <?php echo esc_attr( $data['number']['formatted']['name'] ); ?>_field">
									<p>
										<span><strong><?php echo wp_kses_post( $data['number']['label'] ); ?></strong></span>
										<span><?php echo esc_attr( $data['number']['formatted']['value'] ); ?></span>
									</p>
								</p>
							</div>
						<?php endif; ?>
						<?php if ( isset( $data['date'] ) ) : ?>
							<div class="<?php echo esc_attr( $document->get_type() ); ?>-date">
								<p class="form-field form-field-wide">
									<p>
										<span><strong><?php echo wp_kses_post( $data['date']['label'] ); ?></strong></span>
										<span><?php echo esc_attr( $data['date']['formatted'] ); ?></span>
									</p>
								</p>
							</div>
						<?php endif; ?>
						<div class="pdf-more-details" style="display:none;">
							<?php if ( isset( $data['display_date'] ) ) : ?>
								<div class="<?php echo esc_attr( $document->get_type() ); ?>-display-date">
									<p class="form-field form-field-wide">
										<p>
											<span><strong><?php echo wp_kses_post( $data['display_date']['label'] ); ?></strong></span>
											<span><?php echo esc_attr( $data['display_date']['value'] ); ?></span>
										</p>
									</p>
								</div>
							<?php endif; ?>
							<?php if ( isset( $data['creation_trigger'] ) && ! empty( $data['creation_trigger']['value'] ) ) : ?>
								<div class="<?php echo esc_attr( $document->get_type() ); ?>-creation-status">
									<p class="form-field form-field-wide">
										<p>
											<span><strong><?php echo wp_kses_post( $data['creation_trigger']['label'] ); ?></strong></span>
											<span><?php echo esc_attr( $data['creation_trigger']['value'] ); ?></span>
										</p>
									</p>
								</div>
							<?php endif; ?>
						</div>
						<?php if ( isset( $data['display_date'] ) || isset( $data['creation_trigger'] ) ) : ?>
							<div>
								<a href="#" class="view-more"><?php esc_html_e( 'View more details', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
								<a href="#" class="hide-details" style="display:none;"><?php esc_html_e( 'Hide details', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
							</div>
						<?php endif; ?>
						<?php do_action( 'wpo_wcpdf_meta_box_after_document_data', $document, $document->order ); ?>
					<?php else : ?>
						<?php if ( $this->user_can_manage_document( $document->get_type() ) ) : ?>
							<?php if ( $document_data_editing_enabled ) : ?>
								<span class="wpo-wcpdf-set-date-number button">
									<?php
										printf(
											/* translators: document title */
											esc_html__( 'Set %s number & date', 'woocommerce-pdf-invoices-packing-slips' ),
											esc_html( $document->get_title() )
										);
									?>
								</span>
							<?php else : ?>
								<?php $this->document_data_editing_disabled_notice( $document ); ?>
							<?php endif; ?>
						<?php else : ?>
							<p><?php echo esc_html__( 'You do not have sufficient permissions to edit this document.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<!-- Editable -->
				<div class="editable editable-number-date">
					<?php if ( $document_data_editing_enabled ) : ?>
						<?php if ( ! empty( $data['number'] ) ) : ?>
							<div class="data-fields-grid">
								<div class="data-fields-row">
									<div class="field-group">
										<label for="<?php echo esc_attr( $data['number']['prefix']['name'] ); ?>">
											<?php esc_html_e( 'Number prefix', 'woocommerce-pdf-invoices-packing-slips' ); ?>
											<?php
												$tip_text = sprintf(
													'%s %s',
													__( 'If set, this value will be used as number prefix.' , 'woocommerce-pdf-invoices-packing-slips' ),
													sprintf(
														/* translators: 1. document slug, 2-3 placeholders */
														__( 'You can use the %1$s year and/or month with the %2$s or %3$s placeholders respectively.', 'woocommerce-pdf-invoices-packing-slips' ),
														esc_html( $document->get_title() ),
														'<strong>[' . esc_html( $document->slug ) . '_year]</strong>',
														'<strong>[' . esc_html( $document->slug ) . '_month]</strong>'
													)
												);
												echo wc_help_tip( wp_kses_post( $tip_text ), true );
											?>
										</label>
										<input type="text" class="short" name="<?php echo esc_attr( $data['number']['prefix']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['prefix']['name'] ); ?>" value="<?php echo esc_html( $data['number']['prefix']['value'] ); ?>" disabled="disabled">
									</div>
									<div class="field-group">
										<label for="<?php echo esc_attr( $data['number']['suffix']['name'] ); ?>">
											<?php esc_html_e( 'Number suffix', 'woocommerce-pdf-invoices-packing-slips' ); ?>
											<?php
												$tip_text = sprintf(
													'%s %s',
													__( 'If set, this value will be used as number suffix.' , 'woocommerce-pdf-invoices-packing-slips' ),
													sprintf(
														/* translators: 1. document slug, 2-3 placeholders */
														__( 'You can use the %1$s year and/or month with the %2$s or %3$s placeholders respectively.', 'woocommerce-pdf-invoices-packing-slips' ),
														esc_html( $document->get_title() ),
														'<strong>[' . esc_html( $document->slug ) . '_year]</strong>',
														'<strong>[' . esc_html( $document->slug ) . '_month]</strong>'
													)
												);
												echo wc_help_tip( wp_kses_post( $tip_text ), true );
											?>
										</label>
										<input type="text" class="short" name="<?php echo esc_attr( $data['number']['suffix']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['suffix']['name'] ); ?>" value="<?php echo esc_html( $data['number']['suffix']['value'] ); ?>" disabled="disabled">
									</div>
									<div class="field-group">
										<label for="<?php echo esc_attr( $data['number']['padding']['name'] ); ?>">
											<?php esc_html_e( 'Number padding', 'woocommerce-pdf-invoices-packing-slips' ); ?>
											<?php
												$tip_text = sprintf(
													/* translators: %1$s: code, %2$s: document title, %3$s: number, %4$s: padded number */
													__( 'Enter the number of digits you want to use as padding. For instance, enter %1$s to display the %2$s number %3$s as %4$s, filling it with zeros until the number set as padding is reached.' , 'woocommerce-pdf-invoices-packing-slips' ),
													'<code>6</code>',
													esc_html( $document->get_title() ),
													'<code>123</code>',
													'<code>000123</code>'
												);
												echo wc_help_tip( wp_kses_post( $tip_text ), true );
											?>
										</label>
										<input type="number" min="1" step="1" class="short" name="<?php echo esc_attr( $data['number']['padding']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['padding']['name'] ); ?>" value="<?php echo absint( $data['number']['padding']['value'] ); ?>" disabled="disabled">
									</div>
									<div class="row-note">
										<?php
											echo wp_kses_post(
												sprintf(
													/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
													__( 'For more information about setting up the number format and see the available placeholders for the prefix and suffix, check this article: %1$sNumber format explained%2$s', 'woocommerce-pdf-invoices-packing-slips' ),
													'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/number-format-explained/" target="_blank">',
													'</a>'
												)
											);
										?>
									</div>
								</div>
								<div class="data-fields-row">
									<div class="field-group">
										<label for="<?php echo esc_attr( $data['number']['plain']['name'] ); ?>">
											<?php
												printf(
													/* translators: %s document title */
													esc_html__( '%s number', 'woocommerce-pdf-invoices-packing-slips' ),
													esc_html( $document->get_title() )
												);
											?>
										</label>
										<input type="number" min="1" step="1" class="short" name="<?php echo esc_attr( $data['number']['plain']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['plain']['name'] ); ?>" value="<?php echo absint( $data['number']['plain']['value'] ); ?>" disabled="disabled">
									</div>
									<div class="field-group">
										<label><?php esc_html_e( 'Formatted number', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
										<input type="text" class="formatted-number" data-current="<?php echo esc_html( $data['number']['formatted']['value'] ); ?>" value="<?php echo esc_html( $data['number']['formatted']['value'] ); ?>" readonly>
									</div>
									<div class="field-group placeholder"></div> <!-- Empty cell -->
									<div class="row-note">
										<?php echo wp_kses_post( sprintf(
											/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
											__( 'Manually changing the document\'s plain number also requires updating the next document number in the %1$sdocument settings%2$s.' ),
											'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=documents&section=' . $document->get_type() ) ) . '#next_' . $document->slug . '_number" target="_blank">',
											'</a>'
										) ); ?>
										<?php esc_html_e( 'Please note that changing the document number may create gaps in the numbering sequence.', 'woocommerce-pdf-invoices-packing-slips' ); ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( isset( $data['date'] ) ) : ?>
							<div class="data-fields-grid">
								<div class="data-fields-row">
									<div class="field-group">
										<label for="<?php echo esc_attr( $data['date']['name'] ); ?>[date]">
											<?php
												printf(
													/* translators: %s document title */
													esc_html__( '%s date', 'woocommerce-pdf-invoices-packing-slips' ),
													esc_html( $document->get_title() )
												);
											?>
										</label>
										<input type="text" class="date-picker-field" name="<?php echo esc_attr( $data['date']['name'] ); ?>[date]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[date]" maxlength="10" value="<?php echo esc_attr( $data['date']['date'] ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" disabled="disabled">
									</div>
									<div class="field-group">
										<label for="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]"><?php esc_html_e( 'Hour', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
										<input type="number" class="hour" placeholder="<?php esc_attr_e( 'h', 'woocommerce-pdf-invoices-packing-slips' ); ?>" name="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]" min="0" max="23" size="2" value="<?php echo esc_attr( $data['date']['hour'] ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" disabled="disabled">
									</div>
									<div class="field-group">
										<label for="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]"><?php esc_html_e( 'Minute', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
										<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce-pdf-invoices-packing-slips' ); ?>" name="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]" min="0" max="59" size="2" value="<?php echo esc_attr( $data['date']['minute'] ); ?>" pattern="[0-5]{1}[0-9]{1}"  disabled="disabled">
									</div>
								</div>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<?php $this->document_data_editing_disabled_notice( $document ); ?>
					<?php endif; ?>
				</div>

				<!-- Document Notes -->
				<?php if ( array_key_exists( 'notes', $data ) ) : ?>
					<?php do_action( 'wpo_wcpdf_meta_box_before_document_notes', $document, $document->order ); ?>
					<!-- Read only -->
					<div class="read-only">
						<span><strong><?php echo wp_kses_post( $data['notes']['label'] ); ?></strong></span>
						<?php if ( $this->user_can_manage_document( $document->get_type() ) ) : ?>
							<span class="wpo-wcpdf-edit-document-notes dashicons dashicons-edit" data-edit="notes"></span>
						<?php endif; ?>
						<p><?php echo ( $data['notes']['value'] == wp_strip_all_tags( $data['notes']['value'] ) ) ? wp_kses_post( nl2br( $data['notes']['value'] ) ) : wp_kses_post( $data['notes']['value'] ); ?></p>
					</div>
					<!-- Editable -->
					<div class="editable-notes">
						<div class="data-fields-grid">
							<div class="data-fields-row">
								<div class="field-group">
									<label for="<?php echo esc_attr( $data['notes']['name'] ); ?>"><?php esc_html_e( 'Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
									<textarea name="<?php echo esc_attr( $data['notes']['name'] ); ?>" class="<?php echo esc_attr( $data['notes']['name'] ); ?>" cols="60" rows="5" disabled="disabled"><?php echo wp_kses_post( $data['notes']['value'] ); ?></textarea>
								</div>
								<div class="field-group placeholder"></div> <!-- Empty cell -->
								<div class="field-group placeholder"></div> <!-- Empty cell -->
								<div class="row-note"><?php esc_html_e( 'Displayed in the document!', 'woocommerce-pdf-invoices-packing-slips' ); ?></div>
							</div>
						</div>
					</div>
					<?php do_action( 'wpo_wcpdf_meta_box_after_document_notes', $document, $document->order ); ?>
				<?php endif; ?>
			</section>

			<!-- Save/Cancel buttons -->
			<section class="wcpdf-data-fields-section wpo-wcpdf-document-buttons">
				<div>
					<a class="button button-primary wpo-wcpdf-save-document" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpo_wcpdf_save_document' ) ); ?>" data-action="save"><?php esc_html_e( 'Save changes', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
					<a class="button wpo-wcpdf-cancel"><?php esc_html_e( 'Cancel', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
				</div>
			</section>
			<!-- / Save/Cancel buttons -->
		</div>
		<?php
	}

	public function add_regenerate_document_button( $document ) {
		$document_settings = $document->get_settings( true );
		if ( $document->use_historical_settings() == true || isset( $document_settings['archive_pdf'] ) ) {
			printf( '<span class="wpo-wcpdf-regenerate-document dashicons dashicons-update-alt" data-nonce="%s" data-action="regenerate"></span>', esc_attr( wp_create_nonce( 'wpo_wcpdf_regenerate_document' ) ) );
		}
	}

	/**
	 * Add actions to menu, WP3.5+
	 */
	public function bulk_actions( $actions ) {
		foreach ( wcpdf_get_bulk_actions() as $action => $title ) {
			$actions[$action] = $title;
		}
		return $actions;
	}

	/**
	 * Save invoice number date
	 */
	public function save_invoice_number_date( $order_id, $order ) {
		// Skip any auto-draft or draft request
		if (
			( isset( $_POST['original_post_status'] ) && in_array( $_POST['original_post_status'], array( 'auto-draft', 'draft' ), true ) ) ||
			( isset( $_POST['post_status'] ) && in_array( $_POST['post_status'], array( 'auto-draft', 'draft' ), true ) )
		) {
			return;
		}

		if ( ! ( $order instanceof \WC_Order ) && ! empty( $order_id ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! ( $order instanceof \WC_Order ) || 'auto-draft' === $order->get_status() ) {
			return;
		}

		$order_type = $order->get_type();

		if ( 'shop_order' === $order_type ) {
			if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return;
			}

			// Check if user is allowed to change invoice data
			if ( ! $this->user_can_manage_document( 'invoice' ) ) {
				return;
			}

			$form_data = array();
			$invoice   = wcpdf_get_invoice( $order );

			if ( $invoice ) {
				// IMPORTANT: $is_new must be set before calling initiate_number().
				// The exists() method uses the number to determine existence, so
				// if we call initiate_number() first, it may affect the result of exists().
				$is_new        = ( false === $invoice->exists() );
				$form_data     = stripslashes_deep( $_POST );
				$document_data = $this->process_order_document_form_data( (array) $form_data, $invoice );

				if ( empty( $document_data ) ) {
					return;
				}

				$invoice->set_data( $document_data, $order );

				// check if we have number, and if not generate one
				if  ( $invoice->get_date() && ! $invoice->get_number() && is_callable( array( $invoice, 'initiate_number' ) ) ) {
					$invoice->initiate_number();
				}

				$invoice->save();

				if ( $is_new ) {
					WPO_WCPDF()->main->log_document_creation_to_order_notes( $invoice, 'document_data' );
					WPO_WCPDF()->main->mark_document_printed( $invoice, 'document_data' );
				}
			}

			// allow other documents to hook here and save their form data
			do_action( 'wpo_wcpdf_on_save_invoice_order_data', $form_data, $order, $this );
		}
	}

	/**
	 * Document objects are created in order to check for existence and retrieve data,
	 * but we don't want to store the settings for uninitialized documents.
	 * Only use in frontend/backed (page requests), otherwise settings will never be stored!
	 */
	public function disable_storing_document_settings() {
		add_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	public function restore_storing_document_settings() {
		remove_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	public function return_false() {
		return false;
	}

	/**
	 * Send emails manually
	 */
	public function send_emails( $post_or_order_object_id, $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		if ( ! empty( $_POST['wpo_wcpdf_send_emails'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_POST['wpo_wcpdf_send_emails'] ) );
			if ( ! empty( $action ) && strstr( $action, 'send_email_' ) ) {
				$email_to_send = str_replace( 'send_email_', '', $action );
				// Switch back to the site locale.
				wc_switch_to_site_locale();
				do_action( 'woocommerce_before_resend_order_emails', $order, $email_to_send );
				// Ensure gateways are loaded in case they need to insert data into the emails.
				WC()->payment_gateways();
				WC()->shipping();
				// Load mailer.
				$mailer = WC()->mailer();
				$mails  = $mailer->get_emails();
				if ( ! empty( $mails ) ) {
					foreach ( $mails as $mail ) {
						if ( $mail->id == $email_to_send ) {
							add_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
							$mail->trigger( $order->get_id(), $order );
							remove_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
							/* translators: %s: email title */
							$order->add_order_note( sprintf( esc_html__( '%s email notification manually sent.', 'woocommerce-pdf-invoices-packing-slips' ), $mail->title ), false, true );
						}
					}
				}
				do_action( 'woocommerce_after_resend_order_email', $order, $email_to_send );
				// Restore user locale.
				wc_restore_locale();
				// Change the post saved message.
				add_filter( 'redirect_post_location', function( $location ) {
					// messages in includes/admin/class-wc-admin-post-types.php
					// 11 => 'Order updated and sent.'
					return esc_url_raw( add_query_arg( 'message', 11, $location ) );
				} );
			}
		}
	}

	/**
	 * Add invoice number to order search scope
	 */
	public function search_fields ( $custom_fields ) {
		$custom_fields[] = '_wcpdf_invoice_number';
		$custom_fields[] = '_wcpdf_formatted_invoice_number';
		return $custom_fields;
	}

	/**
	 * Check if this is a shop_order page (edit or list)
	 */
	public function is_order_page() {
		$screen = get_current_screen();
		if ( ! is_null( $screen ) && in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function user_can_manage_document( $document_type ) {
		return apply_filters( 'wpo_wcpdf_current_user_is_allowed', ( current_user_can( 'manage_woocommerce_orders' ) || current_user_can( 'edit_shop_orders' ) ), $document_type );
	}

	/**
	 * Save, regenerate or delete a document from AJAX request
	 */
	public function ajax_crud_document() {
		if ( check_ajax_referer( 'wpo_wcpdf_regenerate_document', 'security', false ) === false && check_ajax_referer( 'wpo_wcpdf_save_document', 'security', false ) === false && check_ajax_referer( 'wpo_wcpdf_delete_document', 'security', false ) === false ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Nonce expired!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		$request = stripslashes_deep( $_POST );

		if ( ! isset( $request['action'] ) ||  ! in_array( $request['action'], array( 'wpo_wcpdf_regenerate_document', 'wpo_wcpdf_save_document', 'wpo_wcpdf_delete_document' ) ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Bad action!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		if ( empty( $request['order_id'] ) || empty( $request['document_type'] ) || empty( $request['action_type'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Incomplete request!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		if ( ! $this->user_can_manage_document( $request['document_type'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'No permissions!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		$order_id      = absint( $request['order_id'] );
		$order         = wc_get_order( $order_id );
		$document_type = sanitize_text_field( $request['document_type'] );
		$action_type   = sanitize_text_field( $request['action_type'] );
		$notice        = isset( $request['wpcdf_document_data_notice'] ) ? sanitize_text_field( $request['wpcdf_document_data_notice'] ) : 'saved';

		// parse form data
		parse_str( $request['form_data'], $form_data );

		if ( is_array( $form_data ) ) {
			foreach ( $form_data as $key => &$value ) {
				if ( is_array( $value ) && ! empty( $value[ $order_id ] ) ) {
					$value = $value[ $order_id ];
				}
			}
		}

		$form_data = stripslashes_deep( $form_data );

		// notice messages
		$notice_messages = array(
			'saved'       => array(
				'success' => __( 'Document data saved!', 'woocommerce-pdf-invoices-packing-slips' ),
				'error'   => __( 'An error occurred while saving the document data!', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'regenerated' => array(
				'success' => __( 'Document regenerated!', 'woocommerce-pdf-invoices-packing-slips' ),
				'error'   => __( 'An error occurred while regenerating the document!', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'deleted' => array(
				'success' => __( 'Document deleted!', 'woocommerce-pdf-invoices-packing-slips' ),
				'error'   => __( 'An error occurred while deleting the document!', 'woocommerce-pdf-invoices-packing-slips' ),
			),
		);

		try {
			$document = wcpdf_get_document( $document_type, wc_get_order( $order_id ) );

			if ( ! empty( $document ) ) {

				// perform legacy date fields replacements check
				if ( isset( $form_data["_wcpdf_{$document->slug}_date"] ) && ! is_array( $form_data["_wcpdf_{$document->slug}_date"] ) ) {
					$form_data = $this->legacy_date_fields_replacements( $form_data, $document->slug );
				}

				// save document data
				$document_data = $this->process_order_document_form_data( (array) $form_data, $document );

				// on regenerate
				if ( 'regenerate' === $action_type && $document->exists() ) {
					$document->regenerate( $order, $document_data );
					WPO_WCPDF()->main->log_document_creation_trigger_to_order_meta( $document, 'document_data', true, $request );
					$response = array(
						'message' => $notice_messages[$notice]['success'],
					);

				// on delete
				} elseif ( 'delete' === $action_type && $document->exists() ) {
					$document->delete();

					$response = array(
						'message' => $notice_messages[$notice]['success'],
					);

				// on save
				} elseif ( 'save' === $action_type ) {
					// IMPORTANT: $is_new must be set before calling initiate_number().
					// The exists() method uses the number to determine existence, so
					// if we call initiate_number() first, it may affect the result of exists().
					$is_new = ( false === $document->exists() );

					$document->set_data( $document_data, $order );

					// check if we have number, and if not generate one
					if ( $document->get_date() && ! $document->get_number() && is_callable( array( $document, 'initiate_number' ) ) ) {
						$document->initiate_number();
					}

					$document->save();

					if ( $is_new ) {
						WPO_WCPDF()->main->log_document_creation_to_order_notes( $document, 'document_data' );
						WPO_WCPDF()->main->log_document_creation_trigger_to_order_meta( $document, 'document_data', false, $request );
						WPO_WCPDF()->main->mark_document_printed( $document, 'document_data' );
					}
					$response      = array(
						'message' => $notice_messages[$notice]['success'],
					);

				// document not exist
				} else {
					$message_complement = __( 'Document does not exist.', 'woocommerce-pdf-invoices-packing-slips' );
					wp_send_json_error( array(
						'message' => wp_kses_post( $notice_messages[$notice]['error'] . ' ' . $message_complement ),
					) );
				}

				// clean/escape response message
				if ( ! empty( $response['message'] ) ) {
					$response['message'] = wp_kses_post( $response['message'] );
				}

				wp_send_json_success( $response );

			} else {
				$message_complement = __( 'Document is empty.', 'woocommerce-pdf-invoices-packing-slips' );
				wp_send_json_error( array(
					'message' => wp_kses_post( $notice_messages[$notice]['error'] . ' ' . $message_complement ),
				) );
			}
		} catch ( \Throwable $e ) {
			wp_send_json_error( array(
				'message' => wp_kses_post( $notice_messages[$notice]['error'] . ' ' . $e->getMessage() ),
			) );
		}
	}

	public function legacy_date_fields_replacements( $form_data, $document_slug ) {
		$legacy_date   = sanitize_text_field( $form_data["_wcpdf_{$document_slug}_date"] );
		$legacy_hour   = sanitize_text_field( $form_data["_wcpdf_{$document_slug}_date_hour"] );
		$legacy_minute = sanitize_text_field( $form_data["_wcpdf_{$document_slug}_date_minute"] );
		unset( $form_data["_wcpdf_{$document_slug}_date_hour"] );
		unset( $form_data["_wcpdf_{$document_slug}_date_minute"] );

		$form_data["_wcpdf_{$document_slug}_date"] = array(
			'date'   => $legacy_date,
			'hour'   => $legacy_hour,
			'minute' => $legacy_minute,
		);

		return $form_data;
	}

	public function debug_enabled_warning( $wp_admin_bar ) {
		if ( isset(WPO_WCPDF()->settings->debug_settings['enable_debug']) && current_user_can( 'administrator' ) ) {
			$status_settings_url = 'admin.php?page=wpo_wcpdf_options_page&tab=debug';
			$title = __( 'DEBUG output enabled', 'woocommerce-pdf-invoices-packing-slips' );
			$args = array(
				'id'    => 'admin_bar_wpo_debug_mode',
				'title' => sprintf( '<a href="%s" style="background-color: red; color: white;">%s</a>', esc_attr( $status_settings_url ), esc_html( $title ) ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 * AJAX handler to preview a formatted number
	 *
	 * @return void
	 */
	public function ajax_preview_formatted_number(): void {
		if ( ! check_ajax_referer( 'generate_wpo_wcpdf', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'woocommerce-pdf-invoices-packing-slips' ) ) );
		}

		$request       = stripslashes_deep( $_POST );
		$prefix        = isset( $request['prefix'] )   ? sanitize_text_field( $request['prefix'] )   : '';
		$suffix        = isset( $request['suffix'] )   ? sanitize_text_field( $request['suffix'] )   : '';
		$padding       = isset( $request['padding'] )  ? absint( $request['padding'] )               : 0;
		$plain         = isset( $request['plain'] )    ? absint( $request['plain'] )                 : 0;
		$document_type = isset( $request['document'] ) ? sanitize_text_field( $request['document'] ) : '';
		$order_id      = isset( $request['order_id'] ) ? absint( $request['order_id'] )              : 0;

		if ( empty( $order_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order ID.', 'woocommerce-pdf-invoices-packing-slips' ) ) );
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order.', 'woocommerce-pdf-invoices-packing-slips' ) ) );
		}

		if ( empty( $document_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document type.', 'woocommerce-pdf-invoices-packing-slips' ) ) );
		}

		$document = wcpdf_get_document( $document_type, $order );

		if ( ! $document ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document.', 'woocommerce-pdf-invoices-packing-slips' ) ) );
		}

		$formatted = wpo_wcpdf_format_document_number( $plain, $prefix, $suffix, $padding, $document, $order );

		wp_send_json_success( array( 'formatted' => $formatted ) );
	}

	/**
	 * Process the order document form data and return an array with the data to be saved.
	 *
	 * @param array $form_data The form data submitted via AJAX.
	 * @param string|OrderDocument $document The document object. It accepted a document type string before 4.6.0.
	 * @return array Processed data ready to be saved.
	 */
	public function process_order_document_form_data( array $form_data, $document ): array {
		if ( ! $document instanceof OrderDocument ) {
			// Before this parameter accepted a document type string, but now we require a document object.
			// If a string is passed it's because an old version of the Professional or Proposal extension is active.
			$extension_needs_update = array();

			// Check if the Professional extension is active
			if ( function_exists( 'WPO_WCPDF_Pro' ) ) {
				$extension_needs_update[] = 'Professional';
			}

			// Check if the Proposal extension is active
			if ( function_exists( 'wc_order_proposal' ) ) {
				$extension_needs_update[] = 'Proposal';
			}

			$message = __METHOD__ . ': The parameter passed is a string (legacy behavior). This method now requires a document object.';

			if ( ! empty( $extension_needs_update ) ) {
				$message .= ' Please update the following extension' . ( count( $extension_needs_update ) > 1 ? 's' : '' ) . ': ' . implode( ' and ', $extension_needs_update ) . '.';
			} else {
				$message .= ' An outdated or third-party plugin or code snippet may be using the old method.';
			}

			wcpdf_log_error( $message, 'critical' );

			return array();
		}

		$data = array();

		if (
			check_ajax_referer( 'wpo_wcpdf_regenerate_document', 'security', false ) === false &&
			check_ajax_referer( 'wpo_wcpdf_save_document', 'security', false ) === false &&
			check_ajax_referer( 'wpo_wcpdf_delete_document', 'security', false ) === false &&
			( empty( $_POST['woocommerce_meta_nonce'] ) ||
			  ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			return $data;
		}

		$key_prefix                    = "_wcpdf_{$document->slug}_";
		$document_data_editing_enabled = \WPO_WCPDF()->settings->user_can_manage_settings() &&
			( ! empty( \WPO_WCPDF()->settings->debug_settings['enable_document_data_editing'] ) || ! in_array( $document->get_type(), array( 'invoice', 'credit-note' ) ) );

		if ( $document_data_editing_enabled ) {
			// Number
			if ( isset( $form_data["{$key_prefix}number_prefix"] ) ) {
				$data['number']['prefix'] = sanitize_text_field( $form_data["{$key_prefix}number_prefix"] );
			}

			if ( isset( $form_data["{$key_prefix}number_plain"] ) ) {
				$data['number']['number'] = absint( $form_data["{$key_prefix}number_plain"] );
			}

			if ( isset( $form_data["{$key_prefix}number_suffix"] ) ) {
				$data['number']['suffix'] = sanitize_text_field( $form_data["{$key_prefix}number_suffix"] );
			}

			if ( isset( $form_data["{$key_prefix}number_padding"] ) ) {
				$data['number']['padding'] = absint( $form_data["{$key_prefix}number_padding"] );
			}

			if ( isset( $form_data["{$key_prefix}number_formatted"] ) ) {
				$data['number']['formatted_number'] = sanitize_text_field( $form_data["{$key_prefix}number_formatted"] );
			}

			if ( ! empty( $data['number'] ) ) {
				$data['number']['document_type'] = $document->get_type();
				$data['number']['order_id']      = $document->order->get_id();
			}

			// Date
			$date_entered = ! empty( $form_data["{$key_prefix}date"] ) && ! empty( $form_data["{$key_prefix}date"]['date'] );

			if ( $date_entered ) {
				$date         = $form_data["{$key_prefix}date"]['date'];
				$hour         = ! empty( $form_data["{$key_prefix}date"]['hour'] ) ? $form_data["{$key_prefix}date"]['hour'] : '00';
				$minute       = ! empty( $form_data["{$key_prefix}date"]['minute'] ) ? $form_data["{$key_prefix}date"]['minute'] : '00';

				// clean & sanitize input
				$date         = gmdate( 'Y-m-d', strtotime( $date ) );
				$hour         = sprintf( '%02d', intval( $hour ) );
				$minute       = sprintf( '%02d', intval( $minute ) );
				$data['date'] = "{$date} {$hour}:{$minute}:00";

			} elseif ( ! $date_entered && ! empty( $_POST["{$key_prefix}number"] ) ) {
				$data['date'] = current_time( 'timestamp', true );
			}
		}

		// Notes
		if ( isset( $form_data["{$key_prefix}notes"] ) ) {
			// allowed HTML
			$allowed_html = array(
				'a'		=> array(
					'href' 	=> array(),
					'title' => array(),
					'id' 	=> array(),
					'class'	=> array(),
					'style'	=> array(),
				),
				'br'	=> array(),
				'em'	=> array(),
				'strong'=> array(),
				'div'	=> array(
					'id'	=> array(),
					'class' => array(),
					'style'	=> array(),
				),
				'span'	=> array(
					'id' 	=> array(),
					'class'	=> array(),
					'style'	=> array(),
				),
				'p'		=> array(
					'id' 	=> array(),
					'class' => array(),
					'style' => array(),
				),
				'b'		=> array(),
			);

			$data['notes'] = wp_kses( $form_data["{$key_prefix}notes"], $allowed_html );
		}

		return $data;
	}

	public function add_invoice_number_to_order_report( $response ) {
		$order = wc_get_order( $response->data['order_id'] );
		if ( ! empty( $order ) ) {
			$response->data['invoice_number'] = $order->get_meta( '_wcpdf_invoice_number' );
		}

		return $response;
	}

	public function add_invoice_number_header_to_order_export( $export_columns ) {
		$export_columns['invoice_number'] = __( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' );

		return $export_columns;
	}

	public function add_invoice_number_value_to_order_export( $export_item, $item ) {
		if ( ! empty( $item['invoice_number'] ) ) {
			$export_item['invoice_number'] = $item['invoice_number'];
		}

		return $export_item;
	}

	public function add_invoice_column_to_sortable_columns( array $columns ): array {
		$columns['invoice_date_column']   = 'invoice_date_column';
		$columns['invoice_number_column'] = 'invoice_number_column';

		return $columns;
	}

	public function adjust_order_list_query_args( array $order_query_args ): array {
		if ( 'invoice_number_column' === $order_query_args['orderby'] ) {
			$is_numeric = $this->is_invoice_number_numeric();

			$order_query_args['meta_query'] = array(
				'invoice_number_column' => array(
					'key'     => '_wcpdf_invoice_number',
					'compare' => '!=',
					'value'   => '0',
					'type'    => $is_numeric ? 'NUMERIC' : 'CHAR',
				),
			);
		}

		if ( 'invoice_date_column' === $order_query_args['orderby'] ) {
			$order_query_args['meta_query'] = array(
				'invoice_date_column' => array(
					'key'     => '_wcpdf_invoice_date',
					'compare' => '!=',
					'value'   => '',
				),
			);
		}

		return $order_query_args;
	}

	public function sort_orders_by_numeric_invoice_number( $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || 'shop_order' !== $query->get( 'post_type' ) || '_wcpdf_invoice_number' !== $query->get( 'meta_key' ) ) {
			return;
		}

		$query->set( 'orderby', $this->is_invoice_number_numeric() ? 'meta_value_num' : 'meta_value' );
	}

	/**
	 * Determines if the invoice number is numeric.
	 * It evaluates the presence of non-numeric characters in the prefix and suffix of the invoice number.
	 *
	 * @return bool
	 */
	private function is_invoice_number_numeric() {
		$invoice_settings = WPO_WCPDF()->settings->get_document_settings( 'invoice' );
		$is_numeric       = ( empty( $invoice_settings['number_format']['prefix'] ) || ctype_digit( $invoice_settings['number_format']['prefix'] ) ) &&
							( empty( $invoice_settings['number_format']['suffix'] ) || ctype_digit( $invoice_settings['number_format']['suffix'] ) );

		return apply_filters( 'wpo_wcpdf_invoice_number_is_numeric', $is_numeric );
	}

	/**
	 * Document data editing disabled notice
	 *
	 * @param OrderDocument $document
	 * @return void
	 */
	private function document_data_editing_disabled_notice( OrderDocument $document ): void {
		?>
		<div class="notice notice-warning inline" style="margin:0;">
			<p>
				<?php
					echo wp_kses_post(
						sprintf(
							'%s %s',
							sprintf(
								/* translators: %s document title */
								esc_html__( 'Editing of %s number and date is currently disabled.', 'woocommerce-pdf-invoices-packing-slips' ),
								esc_html( $document->get_title() )
							),
							sprintf(
								/* translators: %1$s: open anchor tag, %2$s: close anchor tag, %3$s: setting name */
								esc_html__( 'If you need to enable this feature, you can do so in the %1$sAdvanced Settings%2$s section under %3$s.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug#enable_document_data_editing' ) ) . '" target="_blank">',
								'</a>',
								'<strong>' . esc_html__( 'Enable document data editing', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>'
							)
						)
					);
				?>
			</p>
		</div>
		<?php
	}

}

endif; // class_exists
