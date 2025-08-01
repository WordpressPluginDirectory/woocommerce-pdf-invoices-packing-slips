<?php
namespace WPO\IPS\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\OrderDocumentMethods' ) ) :

/**
 * Abstract Order Methods
 *
 * Collection of methods to be used on orders within a Document
 * Created as abstract rather than traits to support PHP versions older than 5.4
 */

abstract class OrderDocumentMethods extends OrderDocument {

	public function is_refund( $order ) {
		return 'shop_order_refund' === $order->get_type();
	}

	public function get_refund_parent_id( $order ) {
		return $order->get_parent_id();
	}


	public function get_refund_parent( $order ) {
		// only try if this is actually a refund
		if ( ! $this->is_refund( $order ) ) {
			return $order;
		}

		$parent_order_id = $this->get_refund_parent_id( $order );
		$order = wc_get_order( $parent_order_id );
		return $order;
	}

	/**
	 * Check if billing address and shipping address are equal
	 */
	public function ships_to_different_address() {
		// always prefer parent address for refunds
		if ( $this->is_refund( $this->order ) ) {
			$order = $this->get_refund_parent( $this->order );
		} else {
			$order = $this->order;
		}

		// only check if there is a shipping address at all
		if ( $formatted_shipping_address = $order->get_formatted_shipping_address() ) {
			$address_comparison_fields = apply_filters( 'wpo_wcpdf_address_comparison_fields', array(
				'first_name',
				'last_name',
				'company',
				'address_1',
				'address_2',
				'city',
				'state',
				'postcode',
				'country'
			), $this );

			foreach ( $address_comparison_fields as $address_field ) {
				$billing_field  = call_user_func( array( $order, "get_billing_{$address_field}" ) );
				$shipping_field = call_user_func( array( $order, "get_shipping_{$address_field}" ) );
				if ( $shipping_field != $billing_field ) {
					// this address field is different -> ships to different address!
					return true;
				}
			}
		}

		//if we got here, it means the addresses are equal -> doesn't ship to different address!
		return apply_filters( 'wpo_wcpdf_ships_to_different_address', false, $order, $this );
	}

	/**
	 * Get the billing address
	 *
	 * @return string
	 */
	public function get_billing_address(): string {
		$original_order = $this->order;
		$address        = '';

		if ( $this->is_refund( $original_order ) ) {
			$this->order = $this->get_refund_parent( $original_order ) ?: $original_order;
		}

		if ( is_callable( array( $this->order, 'get_formatted_billing_address' ) ) ) {
			$address = $this->order->get_formatted_billing_address();
		}

		if ( empty( $address ) ) {
			$address = __( 'N/A', 'woocommerce-pdf-invoices-packing-slips' );
		}

		$address = apply_filters( 'wpo_wcpdf_billing_address', wpo_wcpdf_sanitize_html_content( $address, 'address' ), $this );

		if ( is_null( $address ) ) {
			$address = '';
		}

		// Restore the original order if modified.
		$this->order = $original_order;

		return $address;
	}
	public function billing_address() {
		echo wp_kses_post( $this->get_billing_address() );
	}

	/**
	 * Check whether the billing address should be shown
	 */
	public function show_billing_address() {
		if ( 'packing-slip' !== $this->get_type() ) {
			return true;
		} else {
			return ! empty( $this->settings['display_billing_address'] ) && ( $this->ships_to_different_address() || 'always' === $this->settings['display_billing_address'] );
		}
	}

	/**
	 * Return/Show billing email
	 */
	public function get_billing_email() {
		// normal order
		if ( ! $this->is_refund( $this->order ) && is_callable( array( $this->order, 'get_billing_email' ) ) ) {
			$billing_email = $this->order->get_billing_email();
		// refund order
		} else {
			// try parent
			$parent_order  = $this->get_refund_parent( $this->order );
			$billing_email = $parent_order->get_billing_email();
		}

		return apply_filters( 'wpo_wcpdf_billing_email', sanitize_email( $billing_email ), $this );
	}
	public function billing_email() {
		echo esc_html( $this->get_billing_email() );
	}

	/**
	 * Return/Show phone by type
	 */
	public function get_phone( $phone_type = 'billing' ) {
		$phone = '';
		if ( ! empty( $order = $this->is_refund( $this->order ) ? $this->get_refund_parent( $this->order ) : $this->order ) ) {
			$getter = "get_{$phone_type}_phone";
			$phone  = is_callable( array( $order, $getter ) ) ? call_user_func( array( $order, $getter ) ) : $phone;
		}

		return wpo_wcpdf_sanitize_phone_number( $phone );
	}

	public function get_billing_phone() {
		$phone = $this->get_phone( 'billing' );

		return apply_filters( "wpo_wcpdf_billing_phone", $phone, $this );
	}

	public function get_shipping_phone( $fallback_to_billing = false ) {
		$phone = $this->get_phone( 'shipping' );

		if( $fallback_to_billing && empty( $phone ) ) {
			$phone = $this->get_billing_phone();
		}

		return apply_filters( "wpo_wcpdf_shipping_phone", $phone, $this );
	}

	public function billing_phone() {
		echo esc_html( $this->get_billing_phone() );
	}

	public function shipping_phone( $fallback_to_billing = false ) {
		echo esc_html( $this->get_shipping_phone( $fallback_to_billing ) );
	}

	/**
	 * Return/Show shipping address
	 *
	 * @return string
	 */
	public function get_shipping_address(): string {
		$original_order = $this->order;
		$address        = '';

		if ( $this->is_refund( $original_order ) ) {
			$this->order = $this->get_refund_parent( $original_order ) ?: $original_order;
		}

		if ( is_callable( array( $this->order, 'get_formatted_shipping_address' ) ) ) {
			$address = $this->order->get_formatted_shipping_address();
		}

		if ( empty( $address ) ) {
			if (
				apply_filters( 'wpo_wcpdf_shipping_address_fallback', ( 'packing-slip' === $this->get_type() ), $this ) &&
				is_callable( array( $this->order, 'get_formatted_billing_address' ) )
			) {
				$address = $this->order->get_formatted_billing_address();
			} else {
				$address = __( 'N/A', 'woocommerce-pdf-invoices-packing-slips' );
			}
		}

		$address = apply_filters( 'wpo_wcpdf_shipping_address', wpo_wcpdf_sanitize_html_content( $address, 'address' ), $this );

		if ( is_null( $address ) ) {
			$address = '';
		}

		// Restore the original order if modified.
		$this->order = $original_order;

		return $address;
	}
	public function shipping_address() {
		echo wp_kses_post( $this->get_shipping_address() );
	}

	/**
	 * Check whether the shipping address should be shown
	 */
	public function show_shipping_address() {
		if ( 'packing-slip' !== $this->get_type() ) {
			return ! empty( $this->settings['display_shipping_address'] ) && ( $this->ships_to_different_address() || 'always' === $this->settings['display_shipping_address'] );
		} else {
			return true;
		}
	}

	/**
	 * Return/Show a custom field
	 */
	public function get_custom_field( $field_name ) {
		if ( !$this->is_order_prop( $field_name ) ) {
			$custom_field = $this->order->get_meta( $field_name );
		}
		// if not found, try prefixed with underscore (not when ACF is active!)
		if ( empty( $custom_field ) && substr( $field_name, 0, 1 ) !== '_' && !$this->is_order_prop( "_{$field_name}" ) && !class_exists('ACF') ) {
			$custom_field = $this->order->get_meta( "_{$field_name}" );
		}

		// WC3.0 fallback to properties
		$property = ! empty( $field_name ) ? str_replace( '-', '_', sanitize_title( ltrim( $field_name, '_' ) ) ) : '';
		if ( empty( $custom_field ) && is_callable( array( $this->order, "get_{$property}" ) ) ) {
			$custom_field = $this->order->{"get_{$property}"}( 'view' );
		}

		// fallback to parent for refunds
		if ( empty( $custom_field ) && $this->is_refund( $this->order ) ) {
			$parent_order = $this->get_refund_parent( $this->order );
			if ( !$this->is_order_prop( $field_name ) ) {
				$custom_field = $parent_order->get_meta( $field_name );
			}

			// WC3.0 fallback to properties
			if ( empty( $custom_field ) && is_callable( array( $parent_order, "get_{$property}" ) ) ) {
				$custom_field = $parent_order->{"get_{$property}"}( 'view' );
			}
		}

		return apply_filters( 'wpo_wcpdf_billing_custom_field', $custom_field, $this );
	}
	public function custom_field( $field_name, $field_label = '', $display_empty = false ) {
		$custom_field = $this->get_custom_field( $field_name );

		if ( ! empty( $field_label ) ) {
			// add a trailing space to the label
			$field_label .= ' ';
		}

		if ( ! empty( $custom_field ) || $display_empty ) {
			$allow_tags = array(
				'p'    => array(
					'class' => array(),
					'style' => array(),
					'id'    => array(),
				),
				'span' => array(
					'class' => array(),
					'style' => array(),
					'id'    => array(),
				),
				'ul'   => array(
					'class' => array(),
					'style' => array(),
					'id'    => array(),
				),
				'ol'   => array(
					'class' => array(),
					'style' => array(),
					'id'    => array(),
				),
				'li'   => array(
					'class' => array(),
					'style' => array(),
					'id'    => array(),
				),
			);

			if ( is_array( $custom_field ) ) {
				$custom_field = array_map( function( $field ) use ( $allow_tags ) {
					return wpo_wcpdf_sanitize_html_content( $field, 'custom_field', $allow_tags );
				}, $custom_field );
				echo wp_kses( $field_label . implode( '<br>', $custom_field ), $allow_tags );
			} else {
				$custom_field = wpo_wcpdf_sanitize_html_content( $custom_field, 'custom_field', $allow_tags );
				echo wp_kses( $field_label . nl2br( $custom_field ), $allow_tags );
			}
		}
	}

	public function is_order_prop( $key ) {
		// Taken from WC class
		$order_props = array(
			// Abstract order props
			'parent_id',
			'status',
			'currency',
			'version',
			'prices_include_tax',
			'date_created',
			'date_modified',
			'discount_total',
			'discount_tax',
			'shipping_total',
			'shipping_tax',
			'cart_tax',
			'total',
			'total_tax',
			// Order props
			'customer_id',
			'order_key',
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_country',
			'billing_email',
			'billing_phone',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
			'shipping_country',
			'payment_method',
			'payment_method_title',
			'transaction_id',
			'customer_ip_address',
			'customer_user_agent',
			'created_via',
			'customer_note',
			'date_completed',
			'date_paid',
			'cart_hash',
		);

		if ( version_compare( WOOCOMMERCE_VERSION, '5.6', '>=' ) ) {
			$order_props[] = 'shipping_phone';
		}

		return in_array($key, $order_props);
	}

	/**
	 * Return/show product attribute
	 */
	public function get_product_attribute( $attribute_name, $product ) {
		// first, check the text attributes
		$attributes = $product->get_attributes();
		$attribute_key = @wc_attribute_taxonomy_name( $attribute_name );
		if (array_key_exists( sanitize_title( $attribute_name ), $attributes) ) {
			$attribute = $product->get_attribute ( $attribute_name );
		} elseif (array_key_exists( sanitize_title( $attribute_key ), $attributes) ) {
			$attribute = $product->get_attribute ( $attribute_key );
		}

		if (empty($attribute)) {
			// not a text attribute, try attribute taxonomy
			$attribute_key = @wc_attribute_taxonomy_name( $attribute_name );
			$product_id    = $product->get_id();
			$product_terms = @wc_get_product_terms( $product_id, $attribute_key, array( 'fields' => 'names' ) );
			// check if not empty, then display
			if ( !empty($product_terms) ) {
				$attribute = array_shift( $product_terms );
			}
		}

		// WC3.0+ fallback parent product for variations
		if ( empty( $attribute ) && $product->is_type( 'variation' ) ) {
			$product   = wc_get_product( $product->get_parent_id() );
			$attribute = $this->get_product_attribute( $attribute_name, $product );
		}

		return isset($attribute) ? $attribute : false;
	}
	public function product_attribute( $attribute_name, $product ) {
		echo esc_html( $this->get_product_attribute( $attribute_name, $product ) );
	}

	/**
	 * Get order notes
	 * could use $order->get_customer_order_notes(), but that filters out private notes already
	 *
	 * @param string $filter 'customer' or 'private'
	 * @param bool $include_system_notes include system notes
	 *
	 * @return array $notes
	 */
	public function get_order_notes( string $filter = 'customer', bool $include_system_notes = true ): array {
		if ( $this->is_refund( $this->order ) ) {
			$order_id = $this->get_refund_parent_id( $this->order );
		} else {
			$order_id = $this->order_id;
		}

		if ( empty( $order_id ) ) {
			return array(); // prevent order notes from all orders showing when document is not loaded properly
		}

		$type  = ( 'private' === $filter ) ? 'internal' : $filter;
		$notes = wc_get_order_notes( array(
			'order_id' => $order_id,
			'type'     => $type, // use 'internal' for admin and system notes, empty for all
		) );

		if ( ! $include_system_notes ) {
			foreach ( $notes as $key => $note ) {
				if ( $note->added_by == 'system' ) {
					unset( $notes[ $key ] );
				}
			}
		}

		return $notes;

	}

	/**
	 * Show order notes
	 *
	 * @param string $filter 'customer' or 'private'
	 * @param bool $include_system_notes include system notes
	 *
	 * @return void
	 */
	public function order_notes( string $filter = 'customer', bool $include_system_notes = true ): void {
		$notes = $this->get_order_notes( $filter, $include_system_notes );

		if ( ! empty( $notes ) ) {
			foreach ( $notes as $note ) {
				$css_class   = array( 'note', 'note_content' );
				$css_class[] = $note->customer_note ? 'customer-note' : '';
				$css_class[] = 'system' === $note->added_by ? 'system-note' : '';
				$css_class   = apply_filters( 'woocommerce_order_note_class', array_filter( $css_class ), $note );
				$content     = isset( $note->content ) ? $note->content : $note->comment_content;
				$content     = apply_filters( 'wpo_wcpdf_order_note', $content, $note );
				?>
				<div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
					<?php echo wpo_wcpdf_sanitize_html_content( $content, 'notes' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Return/Show the current date
	 */
	public function get_current_date() {
		return apply_filters( 'wpo_wcpdf_date', date_i18n( wcpdf_date_format( $this, 'current_date' ) ) );
	}
	public function current_date() {
		echo esc_html( $this->get_current_date() );
	}

	/**
	 * Return/Show payment method
	 */
	public function get_payment_method() {
		if ( $this->is_refund( $this->order ) ) {
			$parent_order         = $this->get_refund_parent( $this->order );
			$payment_method_title = $parent_order->get_payment_method_title();
		} else {
			$payment_method_title = $this->order->get_payment_method_title();
		}

		$payment_method = wpo_wcpdf_dynamic_translate( $payment_method_title, 'woocommerce' );

		return apply_filters( 'wpo_wcpdf_payment_method', $payment_method, $this );
	}
	public function payment_method() {
		echo esc_html( $this->get_payment_method() );
	}

	/**
	 * Return/Show payment date
	 */
	public function get_payment_date() {
		if ( $this->is_refund( $this->order ) ) {
			$parent_order = $this->get_refund_parent( $this->order );
			$payment_date = $parent_order->get_date_paid();
		} else {
			$payment_date = $this->order->get_date_paid();
		}

		$payment_date = empty( $payment_date ) ? null : apply_filters( 'wpo_wcpdf_date', date_i18n( wcpdf_date_format( $this, 'order_date_paid' ), $payment_date->getTimestamp() ) );

		return apply_filters( 'wpo_wcpdf_payment_date', $payment_date, $this );
	}
	public function payment_date() {
		echo esc_html( $this->get_payment_date() );
	}

	/**
	 * Return/Show shipping method
	 */
	public function get_shipping_method() {
		$shipping_method = wpo_wcpdf_dynamic_translate( $this->order->get_shipping_method(), 'woocommerce' );
		return apply_filters( 'wpo_wcpdf_shipping_method', $shipping_method, $this );
	}
	public function shipping_method() {
		echo esc_html( $this->get_shipping_method() );
	}

	/**
	 * Return/Show order number
	 */
	public function get_order_number() {
		// try parent first
		if ( $this->is_refund( $this->order ) ) {
			$parent_order = $this->get_refund_parent( $this->order );
			$order_number = $parent_order->get_order_number();
		} else {
			$order_number = $this->order->get_order_number();
		}

		// Trim the hash to have a clean number but still
		// support any filters that were applied before.
		$order_number = ltrim($order_number, '#');
		return apply_filters( 'wpo_wcpdf_order_number', $order_number, $this );
	}
	public function order_number() {
		echo esc_html( $this->get_order_number() );
	}

	/**
	 * Return/Show the order date
	 */
	public function get_order_date() {
		if ( $this->is_refund( $this->order ) ) {
			$parent_order = $this->get_refund_parent( $this->order );
			$order_date = $parent_order->get_date_created();
		} else {
			$order_date = $this->order->get_date_created();
		}

		$date = $order_date->date_i18n( wcpdf_date_format( $this, 'order_date' ) );
		$mysql_date = $order_date->date( "Y-m-d H:i:s" );
		return apply_filters( 'wpo_wcpdf_order_date', $date, $mysql_date, $this );
	}
	public function order_date() {
		echo esc_html( $this->get_order_date() );
	}

	/**
	 * Return the order items
	 *
	 * @return array $data_list
	 */
	public function get_order_items(): array {
		$items     = $this->order->get_items();
		$data_list = array();

		if ( sizeof( $items ) > 0 ) {
			foreach ( $items as $item_id => $item ) {
				// Array with data for the pdf template
				$data = array();

				// Set the item_id
				$data['item_id'] = $item_id;

				// Set the item row class
				$data['row_class'] = apply_filters( 'wpo_wcpdf_item_row_class', 'item-' . $item_id, $this->get_type(), $this->order, $item_id );

				// Set the id
				$data['product_id']   = $item['product_id'];
				$data['variation_id'] = $item['variation_id'];

				// Compatibility: WooCommerce Composite Products uses a workaround for
				// setting the order before the item name filter, so we run this first
				if ( class_exists( 'WC_Composite_Products' ) ) {
					$order_item_class = apply_filters( 'woocommerce_order_item_class', '', $item, $this->order );
				}

				// Set item name
				$data['name'] = apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false );
				$data['name'] = apply_filters( 'wpo_wcpdf_order_item_name', $data['name'], $item, $this->order );

				// Set item quantity
				$data['quantity'] = $item['qty'];

				// Set the line total (=after discount)
				$data['line_total']           = $this->format_price( $item['line_total'] );
				$data['single_line_total']    = $this->format_price( $item['line_total'] / max( 1, abs( $item['qty'] ) ) );
				$data['line_tax']             = $this->format_price( $item['line_tax'] );
				$data['single_line_tax']      = $this->format_price( $item['line_tax'] / max( 1, abs( $item['qty'] ) ) );

				$data['tax_rates']            = $this->get_tax_rate( $item, $this->order, false );
				$data['calculated_tax_rates'] = $this->get_tax_rate( $item, $this->order, true );

				// Set the line subtotal (=before discount)
				$data['line_subtotal']     = $this->format_price( $item['line_subtotal'] );
				$data['line_subtotal_tax'] = $this->format_price( $item['line_subtotal_tax'] );
				$data['ex_price']          = $this->get_formatted_item_price( $item, 'total', 'excl' );
				$data['price']             = $this->get_formatted_item_price( $item, 'total' );
				$data['order_price']       = $this->order->get_formatted_line_subtotal( $item ); // formatted according to WC settings

				// Calculate the single price with the same rules as the formatted line subtotal (!)
				// = before discount
				$data['ex_single_price'] = $this->get_formatted_item_price( $item, 'single', 'excl' );
				$data['single_price']    = $this->get_formatted_item_price( $item, 'single' );

				// Pass complete item array
				$data['item'] = $item;

				// Get the product to add more info
				if ( is_callable( array( $item, 'get_product' ) ) ) { // WC4.4+
					$product = $item->get_product();
				} elseif ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '4.4', '<' ) ) {
					$product = $this->order->get_product_from_item( $item );
				} else {
					$product = null;
				}

				// Checking for existence, thanks to MDesigner0
				if ( ! empty( $product ) ) {
					// Thumbnail (full img tag)
					$data['thumbnail'] = $this->get_thumbnail( $product );

					// Set item SKU
					$data['sku'] = is_callable( array( $product, 'get_sku' ) ) ? $product->get_sku() : '';

					// Set item weight
					$data['weight'] = is_callable( array( $product, 'get_weight' ) ) ? $product->get_weight() : '';

					// Set item dimensions
					if ( function_exists( 'wc_format_dimensions' ) && is_callable( array( $product, 'get_dimensions' ) ) ) {
						$data['dimensions'] = wc_format_dimensions( $product->get_dimensions( false ) );
					} else {
						$data['dimensions'] = '';
					}

					// Pass complete product object
					$data['product'] = $product;

				} else {
					$data['product'] = null;
				}
				
				// Set item meta
				$data['meta'] = wpo_ips_display_item_meta( $item, apply_filters( 'wpo_wcpdf_display_item_meta_args', array( 'echo' => false ), $this ) );

				$data_list[ $item_id ] = apply_filters( 'wpo_wcpdf_order_item_data', $data, $this->order, $this->get_type() );
			}
		}

		return apply_filters( 'wpo_wcpdf_order_items_data', $data_list, $this->order, $this->get_type() );
	}

	/**
	 * Get the tax rates/percentages for an item
	 * @param  object $item order item
	 * @param  object $order WC_Order
	 * @param  bool $force_calculation force calculation of rates rather than retrieving from db
	 * @return string $tax_rates imploded list of tax rates
	 */
	public function get_tax_rate( $item, $order, $force_calculation = false ) {
		$tax_data_container = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
		$tax_data_key       = ( $item['type'] == 'line_item' ) ? 'subtotal' : 'total';
		$line_total_key     = ( $item['type'] == 'line_item' ) ? 'line_total' : 'total';
		$line_tax_key       = ( $item['type'] == 'shipping' ) ? 'total_tax' : 'line_tax';

		$tax_class          = isset($item['tax_class']) ? $item['tax_class'] : '';
		$line_tax           = $item[$line_tax_key];
		$line_total         = $item[$line_total_key];
		$line_tax_data      = $item[$tax_data_container];

		// first try the easy wc2.2+ way, using line_tax_data
		if ( !empty( $line_tax_data ) && isset($line_tax_data[$tax_data_key]) ) {
			$tax_rates = array();

			$line_taxes = $line_tax_data[$tax_data_key];
			foreach ( $line_taxes as $tax_id => $tax ) {
				if ( isset($tax) && $tax !== '' ) {
					$tax_rate = $this->get_tax_rate_by_id( $tax_id, $order );
					if ( $tax_rate !== false && $force_calculation === false ) {
						$tax_rates[] = $tax_rate . ' %';
					} else {
						$tax_rates[] = $this->calculate_tax_rate( $line_total, $line_tax );
					}
				}
			}

			// apply decimal setting
			if ( function_exists( 'wc_get_price_decimal_separator' ) ) {
				foreach ( $tax_rates as &$tax_rate ) {
					$tax_rate = ! empty( $tax_rate ) ? str_replace( '.', wc_get_price_decimal_separator(), strval( $tax_rate ) ) : $tax_rate;
				}
			}

			$tax_rates = implode(', ', $tax_rates );
			return $tax_rates;
		}

		if ( $line_tax == 0 ) {
			return '-'; // no need to determine tax rate...
		}

		if ( ! apply_filters( 'wpo_wcpdf_calculate_tax_rate', false ) ) {
			$tax = new \WC_Tax();
			$taxes = $tax->get_rates( $tax_class );

			$tax_rates = array();

			foreach ($taxes as $tax) {
				$tax_rates[$tax['label']] = round( $tax['rate'], 2 ).' %';
			}

			if (empty($tax_rates)) {
				// one last try: manually calculate
				$tax_rates[] = $this->calculate_tax_rate( $line_total, $line_tax );
			}

			$tax_rates = implode(' ,', $tax_rates );
		}

		return $tax_rates;
	}

	public function calculate_tax_rate( $price_ex_tax, $tax ) {
		$precision = apply_filters( 'wpo_wcpdf_calculate_tax_rate_precision', 1 );
		if ( $price_ex_tax != 0) {
			$tax_rate = round( ($tax / $price_ex_tax)*100, $precision ).' %';
		} else {
			$tax_rate = '-';
		}
		return $tax_rate;
	}

	/**
	 * Returns the percentage rate (float) for a given tax rate ID.
	 * @param  int         $rate_id  woocommerce tax rate id
	 * @return float|bool  $rate     percentage rate
	 */
	public function get_tax_rate_by_id( $rate_id, $order = null ) {
		global $wpdb;

		// WC 3.7+ stores rate in tax items!
		if ( $order_rates = $this->get_tax_rates_from_order( $order ) ) {
			if ( isset( $order_rates[ $rate_id ] ) ) {
				return (float) $order_rates[ $rate_id ];
			}
		}

		$rate = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT tax_rate FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %d;",
				$rate_id
			)
		);

		if ( is_null( $rate ) ) {
			return false;
		} else {
			return (float) $rate;
		}
	}

	public function get_tax_rates_from_order( $order ) {
		if ( !empty( $order ) && is_callable( array( $order, 'get_version' ) ) && version_compare( $order->get_version(), '3.7', '>=' ) && version_compare( WC_VERSION, '3.7', '>=' ) ) {
			$tax_rates = array();
			$tax_items = $order->get_items( array('tax') );

			if ( empty( $tax_items ) ) {
				return $tax_rates;
			}

			foreach( $tax_items as $tax_item_key => $tax_item ) {
				if ( is_callable( array( $order, 'get_created_via' ) ) && $order->get_created_via() == 'subscription' ) {
					// subscription renewals didn't properly record the rate_percent property between WC3.7 and WCS3.0.1
					// so we use a fallback if the rate_percent = 0 and the amount != 0
					$rate_percent = $tax_item->get_rate_percent();
					$tax_amount = $tax_item->get_tax_total() + $tax_item->get_shipping_tax_total();
					if ( $tax_amount > 0 && $rate_percent > 0 ) {
						$tax_rates[ $tax_item->get_rate_id() ] = $rate_percent;
					} else {
						continue; // not setting the rate will let the plugin fall back to the rate from the settings
					}
				} else {
					$tax_rates[ $tax_item->get_rate_id() ] = $tax_item->get_rate_percent();
				}

			}
			return $tax_rates;
		} else {
			return false;
		}
	}

	/**
	 * Returns a an array with rate_id => tax rate data (array) of all tax rates in woocommerce
	 * @return array  $tax_rate_ids  keyed by id
	 */
	public function get_tax_rate_ids() {
		global $wpdb;
		$rates = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			"SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates"
		);

		$tax_rate_ids = array();
		foreach ($rates as $rate) {
			$rate_id = $rate->tax_rate_id;
			unset($rate->tax_rate_id);
			$tax_rate_ids[$rate_id] = (array) $rate;
		}

		return $tax_rate_ids;
	}

	/**
	 * Returns the main product image ID
	 * Adapted from the WC_Product class
	 * (does not support thumbnail sizes)
	 *
	 * @access public
	 * @return string
	 */
	public function get_thumbnail_id ( $product ) {
		$product_id = $product->get_id();

		if ( has_post_thumbnail( $product_id ) ) {
			$thumbnail_id = get_post_thumbnail_id ( $product_id );
		} elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
			$thumbnail_id = get_post_thumbnail_id ( $parent_id );
		} else {
			$thumbnail_id = false;
		}

		return $thumbnail_id;
	}

	/**
	 * Returns the thumbnail image tag
	 *
	 * uses the internal WooCommerce/WP functions and extracts the image url or path
	 * rather than the thumbnail ID, to simplify the code and make it possible to
	 * filter for different thumbnail sizes
	 *
	 * @access public
	 * @return string
	 */
	public function get_thumbnail( $product ) {
		// Get default WooCommerce img tag (url/http)
		$thumbnail_size        = 'woocommerce_thumbnail';
		$size                  = apply_filters( 'wpo_wcpdf_thumbnail_size', $thumbnail_size );
		$thumbnail_img_tag_url = $product->get_image( $size, array( 'title' => '' ) );

		// Extract the url from img
		preg_match( '/<img(.*)src(.*)=(.*)"(.*)"/U', $thumbnail_img_tag_url, $thumbnail_url_matches );
		$thumbnail_url = ! empty( $thumbnail_url_matches ) ? array_pop( $thumbnail_url_matches ) : '';

		// remove http/https from image tag url to avoid mixed origin conflicts
		$contextless_thumbnail_url = ! empty( $thumbnail_url ) ? ltrim( str_replace( array( 'http://', 'https://' ), '', $thumbnail_url ), '/' ) : $thumbnail_url;

		// convert url to path
		if ( defined( 'WP_CONTENT_DIR' ) && ! empty( WP_CONTENT_DIR ) && false !== strpos( WP_CONTENT_DIR, ABSPATH ) ) {
			$forwardslash_basepath = ! empty( ABSPATH ) ? str_replace( '\\', '/', ABSPATH ) : '';
			$site_url              = trailingslashit( get_site_url() );
		} else {
			// bedrock e.a
			$forwardslash_basepath = defined( 'WP_CONTENT_DIR' ) && ! empty( WP_CONTENT_DIR ) ? str_replace( '\\', '/', WP_CONTENT_DIR ) : '';
			$site_url              = defined( 'WP_CONTENT_URL' ) && ! empty( WP_CONTENT_URL ) ? trailingslashit( WP_CONTENT_URL ) : '';
		}

		$contextless_site_url  = ! empty( $site_url ) ? str_replace( array( 'http://', 'https://' ), '', $site_url ) : $site_url;
		$thumbnail_path        = ! empty( $contextless_thumbnail_url ) ? str_replace( $contextless_site_url, trailingslashit( $forwardslash_basepath ), $contextless_thumbnail_url ) : $contextless_site_url;

		// fallback if thumbnail file doesn't exist
		if ( apply_filters( 'wpo_wcpdf_use_path', true ) && ! WPO_WCPDF()->file_system->exists( $thumbnail_path ) ) {
			$thumbnail_id = $this->get_thumbnail_id( $product );
			if ( $thumbnail_id ) {
				$thumbnail_path = get_attached_file( $thumbnail_id );
			}
		}

		// Thumbnail (full img tag)
		if ( apply_filters( 'wpo_wcpdf_use_path', true ) && WPO_WCPDF()->file_system->exists( $thumbnail_path ) ) {
			// load img with server path by default
			$thumbnail = sprintf( '<img width="90" height="90" src="%s" class="attachment-shop_thumbnail wp-post-image">', $thumbnail_path );

		} elseif ( apply_filters( 'wpo_wcpdf_use_path', true ) && ! WPO_WCPDF()->file_system->exists( $thumbnail_path ) ) {
			// should use paths but file not found, replace // with http(s):// for dompdf compatibility
			if ( is_string( $thumbnail_url ) && substr( $thumbnail_url, 0, 2 ) === "//" ) {
				$prefix                = is_ssl() ? 'https://' : 'http://';
				$https_thumbnail_url   = $prefix . ltrim( $thumbnail_url, '/' );
				$thumbnail_img_tag_url = ! empty( $thumbnail_img_tag_url ) ? str_replace( $thumbnail_url, $https_thumbnail_url, $thumbnail_img_tag_url ) : $thumbnail_img_tag_url;
			}
			$thumbnail = $thumbnail_img_tag_url;
		} else {
			// load img with http url when filtered
			$thumbnail = $thumbnail_img_tag_url;
		}

		/**
		 * PHP GD library can be installed but 'webp' support could be disabled,
		 * which turns the function 'imagecreatefromwebp()' inexistent,
		 * leading to display an error in DOMPDF.
		 *
		 * Check 'System configuration' in the Advanced tab for 'webp' support.
		 */
		if ( 'webp' === wp_check_filetype( $thumbnail_path )['ext'] && ! function_exists( 'imagecreatefromwebp' ) ) {
			$thumbnail = '';
		}

		// die($thumbnail);
		return $thumbnail;
	}

	/**
	 * Return the order totals listing
	 */
	public function get_woocommerce_totals() {
		// get totals and remove the semicolon
		$totals = apply_filters( 'wpo_wcpdf_raw_order_totals', $this->order->get_order_item_totals(), $this->order );

		// remove the colon for every label
		foreach ( $totals as $key => $total ) {
			$label = $total['label'];
			$colon = strrpos( $label, ':' );

			if ( ! empty( $colon ) ) {
				$label = substr_replace( $label, '', $colon, 1 );
			}

			if ( ! empty( $label ) ) {
				$totals[ $key ]['label'] = wpo_wcpdf_dynamic_translate( $label, 'woocommerce-pdf-invoices-packing-slips' );
			}
		}

		// Fix order_total for refunded orders
		// not if this is the actual refund!
		if ( ! $this->is_refund( $this->order ) && apply_filters( 'wpo_wcpdf_remove_refund_totals', true, $this ) ) {
			$total_refunded = is_callable( array( $this->order, 'get_total_refunded' ) ) ? $this->order->get_total_refunded() : 0;
			if ( isset($totals['order_total']) && $total_refunded ) {
				$tax_display = get_option( 'woocommerce_tax_display_cart' );
				$totals['order_total']['value'] = wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_currency() ) );
				$tax_string = '';

				// Tax for inclusive prices
				if ( wc_tax_enabled() && 'incl' == $tax_display ) {
					$tax_string_array = array();
					if ( 'itemized' == get_option( 'woocommerce_tax_total_display' ) ) {
						foreach ( $this->order->get_tax_totals() as $code => $tax ) {
							$tax_amount         = $tax->formatted_amount;
							$tax_string_array[] = sprintf( '%s %s', $tax_amount, $tax->label );
						}
					} else {
						$tax_string_array[] = sprintf( '%s %s', wc_price( $this->order->get_total_tax(), array( 'currency' => $this->order->get_currency() ) ), WC()->countries->tax_or_vat() );
					}
					if ( ! empty( $tax_string_array ) ) {
						$tax_string = ' ' . sprintf(
							/* translators: %s: tax information */
							__( '(includes %s)', 'woocommerce-pdf-invoices-packing-slips' ),
							implode( ', ', $tax_string_array )
						);
					}
				}

				$totals['order_total']['value'] .= $tax_string;
			}

			// remove refund lines (shouldn't be in invoice)
			foreach ( $totals as $key => $total ) {
				if ( ! empty( $key ) && false !== strpos( $key, 'refund_' ) ) {
					unset( $totals[$key] );
				}
			}

		}

		return apply_filters( 'wpo_wcpdf_woocommerce_totals', $totals, $this->order, $this->get_type() );
	}

	/**
	 * Return/show the order subtotal
	 */
	public function get_order_subtotal( $tax = 'excl', $discount = 'incl' ) { // set $tax to 'incl' to include tax, same for $discount
		//$compound = ($discount == 'incl')?true:false;
		$subtotal = $this->order->get_subtotal_to_display( false, $tax );

		$subtotal = ! empty( $subtotal ) && ( $pos = strpos( $subtotal, ' <small' ) ) ? substr( $subtotal, 0, $pos ) : $subtotal; //removing the 'excluding tax' text

		$subtotal = array (
			'label'	=> __('Subtotal', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'	=> $subtotal,
		);

		return apply_filters( 'wpo_wcpdf_order_subtotal', $subtotal, $tax, $discount, $this );
	}
	public function order_subtotal( $tax = 'excl', $discount = 'incl' ) {
		$subtotal = $this->get_order_subtotal( $tax, $discount );
		echo esc_html( $subtotal['value'] );
	}

	/**
	 * Return/show the order shipping costs
	 */
	public function get_order_shipping( $tax = 'excl' ) { // set $tax to 'incl' to include tax
		$shipping_cost = $this->order->get_shipping_total();
		$shipping_tax  = $this->order->get_shipping_tax();

		if ($tax == 'excl' ) {
			$formatted_shipping_cost = $this->format_price( $shipping_cost );
		} else {
			$formatted_shipping_cost = $this->format_price( $shipping_cost + $shipping_tax );
		}

		$shipping = array (
			'label'	=> __('Shipping', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'	=> $formatted_shipping_cost,
			'tax'	=> $this->format_price( $shipping_tax ),
		);
		return apply_filters( 'wpo_wcpdf_order_shipping', $shipping, $tax, $this );
	}
	public function order_shipping( $tax = 'excl' ) {
		$shipping = $this->get_order_shipping( $tax );
		echo esc_html( $shipping['value'] );
	}

	/**
	 * Return/show the total discount
	 */
	public function get_order_discount( $type = 'total', $tax = 'incl' ) {
		if ( 'incl' === $tax ) {
			switch ( $type ) {
				case 'total':
					// Total Discount
					$discount_value = $this->order->get_total_discount( false ); // $ex_tax = false
					break;
				default:
					// Total Discount - Cart & Order Discounts combined
					$discount_value = $this->order->get_total_discount();
					break;
			}
		} else { // calculate discount excluding tax
			$discount_value = $this->order->get_total_discount( true ); // $ex_tax = true
		}

		$discount = array (
			'label'		=> __( 'Discount', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'		=> $this->format_price( $discount_value ),
			'raw_value'	=> $discount_value,
		);

		if ( round( $discount_value, 3 ) != 0 ) {
			return apply_filters( 'wpo_wcpdf_order_discount', $discount, $type, $tax, $this );
		}
	}
	public function order_discount( $type = 'total', $tax = 'incl' ) {
		$discount = $this->get_order_discount( $type, $tax );
		echo esc_html( $discount['value'] );
	}

	/**
	 * Return the order fees
	 */
	public function get_order_fees( $tax = 'excl' ) {
		if ( $_fees = $this->order->get_fees() ) {
			foreach( $_fees as $id => $fee ) {
				if ($tax == 'excl' ) {
					$fee_price = $this->format_price( $fee['line_total'] );
				} else {
					$fee_price = $this->format_price( $fee['line_total'] + $fee['line_tax'] );
				}

				$fees[ $id ] = array(
					'label' 		=> $fee['name'],
					'value'			=> $fee_price,
					'line_total'	=> $this->format_price( $fee['line_total'] ),
					'line_tax'		=> $this->format_price( $fee['line_tax'] )
				);
			}
			return $fees;
		}
	}

	/**
	 * Return the order taxes
	 */
	public function get_order_taxes() {
		$tax_rate_ids = $this->get_tax_rate_ids();
		if ( $order_taxes = $this->order->get_taxes() ) {
			foreach ( $order_taxes as $key => $tax ) {
				$taxes[$key] = array(
					'label'               => $tax->get_label(),
					'value'               => $this->format_price( $tax->get_tax_total() + $tax->get_shipping_tax_total() ),
					'rate_id'             => $tax->get_rate_id(),
					'tax_amount'          => $tax->get_tax_total(),
					'shipping_tax_amount' => $tax->get_shipping_tax_total(),
					'rate'                => isset( $tax_rate_ids[ $tax->get_rate_id() ] ) ? ( (float) $tax_rate_ids[$tax->get_rate_id()]['tax_rate'] ) . ' %': '',
				);

			}

			return apply_filters( 'wpo_wcpdf_order_taxes', $taxes, $this );
		}
	}

	/**
	 * Return/show the order grand total
	 */
	public function get_order_grand_total( $tax = 'incl' ) {
		$total_unformatted = $this->order->get_total();

		if ($tax == 'excl' ) {
			$total = $this->format_price( $total_unformatted - $this->order->get_total_tax() );
			$label = __( 'Total ex. VAT', 'woocommerce-pdf-invoices-packing-slips' );
		} else {
			$total = $this->format_price( ( $total_unformatted ) );
			$label = __( 'Total', 'woocommerce-pdf-invoices-packing-slips' );
		}

		$grand_total = array(
			'label' => $label,
			'value'	=> $total,
		);

		return apply_filters( 'wpo_wcpdf_order_grand_total', $grand_total, $tax, $this );
	}
	public function order_grand_total( $tax = 'incl' ) {
		$grand_total = $this->get_order_grand_total( $tax );
		echo esc_html( $grand_total['value'] );
	}


	/**
	 * Get shipping notes
	 *
	 * @return string
	 */
	public function get_shipping_notes(): string {
		if ( $this->is_refund( $this->order ) ) {
			$shipping_notes = $this->order->get_reason();
		} else {
			$shipping_notes = wpautop( wptexturize( $this->order->get_customer_note() ) );
		}

		// check document specific setting
		if ( isset( $this->settings['display_customer_notes'] ) && $this->settings['display_customer_notes'] == 0 ) {
			$shipping_notes = '';
		}

		if ( apply_filters( 'wpo_wcpdf_shipping_notes_strip_all_tags', false ) ) {
			$shipping_notes = wp_strip_all_tags( $shipping_notes );
		}

		return apply_filters( 'wpo_wcpdf_shipping_notes', $shipping_notes, $this );
	}

	/**
	 * Display shipping notes
	 *
	 * @return void
	 */
	public function shipping_notes(): void {
		$shipping_notes = $this->get_shipping_notes();

		if ( ! empty( $shipping_notes ) ) {
			echo wpo_wcpdf_sanitize_html_content( $shipping_notes, 'notes' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * wrapper for wc_price, ensuring currency is always passed
	 */
	public function format_price( $price, $args = array() ) {
		$args['currency'] = $this->order->get_currency();
		$formatted_price  = wc_price( $price, $args );

		return $formatted_price;
	}
	public function wc_price( $price, $args = array() ) {
		return $this->format_price( $price, $args );
	}

	/**
	 * Gets price - formatted for display.
	 *
	 * @access public
	 * @param mixed $item
	 * @return string
	 */
	public function get_formatted_item_price ( $item, $type, $tax_display = '' ) {
		if ( ! isset( $item['line_subtotal'] ) || ! isset( $item['line_subtotal_tax'] ) ) {
			return '';
		}

		$divide_by = ($type == 'single' && $item['qty'] != 0 )?abs($item['qty']):1; //divide by 1 if $type is not 'single' (thus 'total')
		if ( $tax_display == 'excl' ) {
			$item_price = $this->format_price( ($this->order->get_line_subtotal( $item )) / $divide_by );
		} else {
			$item_price = $this->format_price( ($this->order->get_line_subtotal( $item, true )) / $divide_by );
		}

		return $item_price;
	}

	/**
	 * Legacy function (v3.7.2 or inferior)
	 * Use $this->get_number() instead.
	 */
	public function get_invoice_number() {
		wcpdf_log_error( 'The method get_invoice_number() is deprecated since version 3.7.3, please use the method get_number() instead.' );

		if ( is_callable( array( $this, 'get_number' ) ) ) {
			return $this->get_number( 'invoice', null, 'view', true );
		} else {
			return '';
		}
	}

	/**
	 * Legacy function (v3.7.2 or inferior)
	 * Use $this->number( 'invoice' ) instead.
	 */
	public function invoice_number() {
		wcpdf_log_error( 'The method invoice_number() is deprecated since version 3.7.3, please use the method number() instead.' );

		if ( is_callable( array( $this, 'number' ) ) ) {
			$this->number( 'invoice' );
		} else {
			echo '';
		}
	}

	/**
	 * Legacy function (v3.7.2 or inferior)
	 * Use $this->get_date() instead.
	 */
	public function get_invoice_date() {
		wcpdf_log_error( 'The method get_invoice_date() is deprecated since version 3.7.3, please use the method get_date() instead.' );

		if ( is_callable( array( $this, 'get_date' ) ) ) {
			return $this->get_date( 'invoice', null, 'view', true );
		} else {
			return '';
		}
	}

	/**
	 * Legacy function (v3.7.2 or inferior)
	 * Use $this->date( 'invoice' ) instead.
	 */
	public function invoice_date() {
		wcpdf_log_error( 'The method invoice_date() is deprecated since version 3.7.3, please use the method date() instead.' );

		if ( is_callable( array( $this, 'date' ) ) ) {
			$this->date( 'invoice' );
		} else {
			echo '';
		}
	}

	/**
	 * Get document notes
	 *
	 * @return string
	 */
	public function get_document_notes(): string {
		$document_notes = $this->get_notes( $this->get_type() );

		return apply_filters( 'wpo_wcpdf_document_notes', $document_notes ?? '', $this );
	}

	/**
	 * Display document notes
	 *
	 * @return void
	 */
	public function document_notes(): void {
		$document_notes = $this->get_document_notes();

		if ( ! empty( $document_notes ) ) {
			echo wpo_wcpdf_sanitize_html_content( wpautop( $document_notes ), 'notes' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function document_display_date(): string {
		$document_display_date = $this->get_display_date( $this->get_type() );

		// If display date data is not available in order meta (for older orders), get the display date information from document settings order meta.
		if ( empty( $document_display_date ) ) {
			$document_settings     = $this->settings;
			$document_display_date = $document_settings['display_date'] ?? 'document_date';
		}

		// Convert the old `invoice_date` slug to the new `document_date` slug.
		if ( 'invoice_date' === $document_display_date ) {
			$document_display_date = 'document_date';
		}

		return $this->get_display_date_label( $document_display_date );
	}

	public function get_display_date_label( string $date_string ): string {
		$date_labels = array(
			'document_date' => sprintf(
				/* translators: Document title */
				__( '%s Date', 'woocommerce-pdf-invoices-packing-slips' ),
				$this->title
			),
			'order_date'    => __( 'Order Date', 'woocommerce-pdf-invoices-packing-slips' ),
		);

		return $date_labels[ $date_string ] ?? '';
	}

	/**
	 * Get the invoice number title,
	 * this allows other documents to use
	 * the invoice number title. Example: Receipt document
	 *
	 * @return string
	 */
	public function get_invoice_number_title() {
		$title = __( 'Invoice Number:', 'woocommerce-pdf-invoices-packing-slips' );
		return apply_filters_deprecated( "wpo_wcpdf_invoice_number_title", array( $title, $this ), '3.8.7', 'wpo_wcpdf_document_number_title' );
	}

	/**
	 * Print the invoice number title,
	 * this allows other documents to use
	 * the invoice number title. Example: Receipt document
	 *
	 * @return void
	 */
	public function invoice_number_title() {
		echo esc_html( $this->get_invoice_number_title() );
	}

	/**
	 * Get the title for the refund reason,
	 * used by the Credit Note document.
	 * (Later we can move this to the Pro extension.)
	 *
	 * @return string
	 */
	public function get_refund_reason_title(): string {
		return apply_filters( 'wpo_wcpdf_refund_reason_title', __( 'Reason for refund:', 'woocommerce-pdf-invoices-packing-slips' ), $this );
	}

	/**
	 * Display the title for the refund reason,
	 * used by the Credit Note document.
	 * (Later we can move this to the Pro extension.)
	 *
	 * @return void
	 */
	public function refund_reason_title(): void {
		echo esc_html( $this->get_refund_reason_title() );
	}

}

endif; // class_exists
