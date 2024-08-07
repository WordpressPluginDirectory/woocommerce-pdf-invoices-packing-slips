<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$memory_limit   = function_exists( 'wc_let_to_num' ) ? wc_let_to_num( WP_MEMORY_LIMIT ) : woocommerce_let_to_num( WP_MEMORY_LIMIT );
$php_mem_limit  = function_exists( 'memory_get_usage' ) ? @ini_get( 'memory_limit' ) : '-';
$gmagick        = extension_loaded( 'gmagick' );
$imagick        = extension_loaded( 'imagick' );

$server_configs = array(
	'PHP version' => array(
		'required' => __( '7.2+ (7.4 or higher recommended)', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'    => PHP_VERSION,
		'result'   => version_compare( PHP_VERSION, '7.2', '>' ),
	),
	'DOMDocument extension' => array(
		'required' => true,
		'value'    => phpversion( 'DOM' ),
		'result'   => class_exists( 'DOMDocument' ),
	),
	'MBString extension' => array(
		'required' => true,
		'value'    => phpversion( 'mbstring' ),
		'result'   => function_exists( 'mb_send_mail' ),
		'fallback' => __( 'Recommended, will use fallback functions', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'GD' => array(
		'required' => true,
		'value'    => phpversion( 'gd' ),
		'result'   => function_exists( 'imagecreate' ),
		'fallback' => __( 'Required if you have images in your documents', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'WebP Support' => array(
		'required' => __( 'Required when using .webp images', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'    => null,
		'result'   => function_exists( 'imagecreatefromwebp' ),
		'fallback' => __( 'Required if you have .webp images in your documents', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	// "PCRE" => array(
	// 	"required" => true,
	// 	"value"    => phpversion("pcre"),
	// 	"result"   => function_exists("preg_match") && @preg_match("/./u", "a"),
	// 	"failure"  => "PCRE is required with Unicode support (the \"u\" modifier)",
	// ),
	'Zlib' => array(
		'required' => __( 'To compress PDF documents', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'    => phpversion( 'zlib' ),
		'result'   => function_exists( 'gzcompress' ),
		'fallback' => __( 'Recommended to compress PDF documents', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'opcache' => array(
		'required' => __( 'For better performances', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'    => null,
		'result'   => false,
		'fallback' => __( 'Recommended for better performances', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'GMagick or IMagick' => array(
		'required' => __( 'Better with transparent PNG images', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'    => $imagick ? 'IMagick ' . phpversion( 'imagick' ) : ( $gmagick ? 'GMagick ' . phpversion( 'gmagick' ) : null ),
		'result'   => $gmagick || $imagick,
		'fallback' => __( 'Recommended for better performances', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'glob()' => array(
		'required' => __( 'Required to detect custom templates and to clear the temp folder periodically', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'    => null,
		'result'   => function_exists( 'glob' ),
		'fallback' => __( 'Check PHP disable_functions', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'WP Memory Limit' => array(
		/* translators: <a> tags */
		'required' => sprintf( __( 'Recommended: 128MB (more for plugin-heavy setups<br/>See: %1$sIncreasing the WordPress Memory Limit%2$s', 'woocommerce-pdf-invoices-packing-slips' ), '<a href="https://docs.woocommerce.com/document/increasing-the-wordpress-memory-limit/" target="_blank">', '</a>' ),
		'value'    => sprintf( 'WordPress: %s, PHP: %s', WP_MEMORY_LIMIT, $php_mem_limit ),
		'result'   => $memory_limit > 67108864,
	),
	'allow_url_fopen' => array (
		'required' => __( 'Allow remote stylesheets and images', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'	   => null,
		'result'   => ini_get( 'allow_url_fopen' ),
		'fallback' => __( 'allow_url_fopen disabled', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'fileinfo' => array (
		'required' => __( 'Necessary to verify the MIME type of local images.', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'	   => null,
		'result'   => extension_loaded( 'fileinfo' ),
		'fallback' => __( 'fileinfo disabled', 'woocommerce-pdf-invoices-packing-slips' ),
	),
	'base64_decode'	=> array (
		'required' => __( 'To compress and decompress font and image data', 'woocommerce-pdf-invoices-packing-slips' ),
		'value'	   => null,
		'result'   => function_exists( 'base64_decode' ),
		'fallback' => __( 'base64_decode disabled', 'woocommerce-pdf-invoices-packing-slips' ),
	),
);

if ( $imagick ) {
	$gmagick_imagick_position = array_search( 'GMagick or IMagick', array_keys( $server_configs ) ) + 1;
	$image_magick_config      = array(
		'ImageMagick' => array(
			'required' => __( 'Required for IMagick', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => ( $imagick && class_exists( '\\Imagick' ) ) ? esc_attr( \Imagick::getVersion()['versionString'] ) : null,
			'result'   => $imagick,
			'fallback' => __( 'ImageMagick library, integrated via the IMagick PHP extension for advanced image processing capabilities', 'woocommerce-pdf-invoices-packing-slips' ),
		),
	);

	$server_configs = array_slice( $server_configs, 0, $gmagick_imagick_position, true ) + $image_magick_config + array_slice( $server_configs, $gmagick_imagick_position, null, true );
}

$server_configs = apply_filters( 'wpo_wcpdf_server_configs', $server_configs );

if ( ( $xc = extension_loaded( 'xcache' ) ) || ( $apc = extension_loaded( 'apc' ) ) || ( $zop = extension_loaded( 'Zend OPcache' ) ) || ( $op = extension_loaded( 'opcache' ) ) ) {
	$server_configs['opcache']['result'] = true;
	$server_configs['opcache']['value'] = (
		$xc ? 'XCache '.phpversion( 'xcache' ) : (
			$apc ? 'APC '.phpversion( 'apc' ) : (
				$zop ? 'Zend OPCache '.phpversion( 'Zend OPcache' ) : 'PHP OPCache '.phpversion( 'opcache' )
			)
		)
	);
}

if ( ! $server_configs['PHP version']['result'] ) {
	/* translators: <a> tags */
	$server_configs['PHP version']['required'] .= '<br/>' . sprintf( __( 'Download %1$sthis addon%2$s to enable backwards compatibility.', 'woocommerce-pdf-invoices-packing-slips' ), '<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/backwards-compatibility-with-php-5-6/" target="_blank">', '</a>' );
}
?>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<thead>
		<tr>
			<td colspan="3"><strong><?php esc_html_e( 'System Configuration', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th align="left">&nbsp;</th>
			<th align="left"><?php esc_html_e( 'Required', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Present', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>

		<?php
			$server_configs = apply_filters( 'wpo_wcpdf_advanced_status_server_configs', $server_configs );
			foreach ( $server_configs as $label => $server_config ) :
				if ( $server_config['result'] ) {
					$background = '#68de7c'; // green
					$color      = 'black';
				} elseif ( isset( $server_config['fallback'] ) ) {
					$background = '#f2d675'; // yellow
					$color      = 'black';
				} else {
					$background = '#ffabaf'; // red
					$color      = 'black';
				}
				?>
				<tr>
					<td class="title"><?php echo esc_html( $label ); ?></td>
					<td><?php echo wp_kses_post( $server_config['required'] === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : $server_config['required'] ); ?></td>
					<td style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>">
						<?php
						if ( ! empty( $server_config['value'] ) ) {
							echo wp_kses_post( $server_config['value'] );
						}
						if ( $server_config['result'] && ! $server_config['value'] ) {
							echo esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' );
						}
						if ( ! $server_config['result'] ) {
							if ( isset( $server_config['fallback'] ) ) {
								printf( '<div>%s. %s</div>', esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ), esc_html( $server_config['fallback'] ) );
							} elseif ( isset( $server_config['failure'] ) ) {
								printf( '<div>%s</div>', wp_kses_post( $server_config['failure'] ) );
							} else {
								printf( '<div>%s</div>', esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) );
							}
						}
						?>
					</td>
				</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php do_action( 'wpo_wcpdf_after_system_status_table' ); ?>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<thead>
		<tr>
			<td colspan="3"><strong><?php esc_html_e( 'Documents status', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th align="left">&nbsp;</th>
			<th align="left"><?php esc_html_e( 'Enabled', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Yearly reset', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
		<?php
			foreach ( WPO_WCPDF()->documents->get_documents( 'all' ) as $document ) :
				$is_reset_enabled = isset( $document->settings['reset_number_yearly'] ) ? true : false;
				$is_enabled       = $document->is_enabled() ? true : false;
		?>
		<tr>
			<td class="title"><?php echo esc_html( $document->get_title() ); ?></td>
			<td style="<?= $is_enabled ? 'background-color:#68de7c; color:black;' : 'background-color:#ffabaf; color:black;' ?>"><?php echo wp_kses_post( $is_enabled === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></td>
			<td style="<?= $is_reset_enabled ? 'background-color:#68de7c; color:black;' : 'background-color:#ffabaf; color:black;' ?>"><?php echo wp_kses_post( $is_reset_enabled === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></td>
		</tr>
	</tbody>
	<?php endforeach; ?>
	<?php
		if ( WPO_WCPDF()->settings->maybe_schedule_yearly_reset_numbers() ) :
			if ( function_exists( 'as_get_scheduled_actions' ) ) {
				$scheduled_actions = as_get_scheduled_actions( array(
					'hook'   => 'wpo_wcpdf_schedule_yearly_reset_numbers',
					'status' => \ActionScheduler_Store::STATUS_PENDING,
				) );

				$yearly_reset = array(
					'required' => __( 'Required to reset documents numeration', 'woocommerce-pdf-invoices-packing-slips' ),
					'fallback' => __( 'Yearly reset action not found', 'woocommerce-pdf-invoices-packing-slips' ),
				);

				if ( ! empty( $scheduled_actions ) ) {
					$total_actions = count( $scheduled_actions );
					if ( $total_actions === 1 ) {
						$action      = reset( $scheduled_actions );
						$action_date = is_callable( array( $action->get_schedule(), 'get_date' ) ) ? $action->get_schedule()->get_date() : $action->get_schedule()->get_next( as_get_datetime_object() );
						/* translators: action date */
						$yearly_reset['value']  = sprintf(
							__( 'Scheduled to: %s' ), date( wcpdf_date_format( null, 'yearly_reset_schedule' ),
							$action_date->getTimeStamp() )
						);
						$yearly_reset['result'] = true;
					} else {
						/* translators: total actions */
						$yearly_reset['value']  = sprintf(
							/* translators: total scheduled actions */
							__( 'Only 1 scheduled action should exist, but %s were found', 'woocommerce-pdf-invoices-packing-slips' ),
							$total_actions
						);
						$yearly_reset['result'] = false;
					}
				} else {
					$yearly_reset['value']  = sprintf(
						/* translators: 1. open anchor tag, 2. close anchor tag */
						__( 'Scheduled action not found. Please reschedule it %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="' . esc_url( add_query_arg( 'section', 'tools' ) ) . '" style="color:black; text-decoration:underline;">',
						'</a>'
					);
					$yearly_reset['result'] = false;
				}
			}

			$label = __( 'Yearly reset', 'woocommerce-pdf-invoices-packing-slips' );

			if ( $yearly_reset['result'] ) {
				$background = '#68de7c'; // green
				$color      = 'black';
			} else {
				$background = '#ffabaf'; // red
				$color      = 'black';
			}
	?>
		<tfoot>
			<tr>
				<td class="title"><strong><?php echo esc_html( $label ); ?></strong></td>
				<td colspan="2" style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>">
					<?php
						echo wp_kses_post( $yearly_reset['value'] );
						if ( $yearly_reset['result'] && ! $yearly_reset['value'] ) {
							echo esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' );
						}
					?>
				</td>
			</tr>
		</tfoot>
	<?php endif; ?>
</table>

<?php
	$status = array(
		'ok'     => __( 'Writable', 'woocommerce-pdf-invoices-packing-slips' ),
		'failed' => __( 'Not writable', 'woocommerce-pdf-invoices-packing-slips' ),
	);

	$permissions = apply_filters( 'wpo_wcpdf_plugin_directories', array(
		'WCPDF_TEMP_DIR' => array (
			'description'    => __( 'Central temporary plugin folder', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => WPO_WCPDF()->main->get_tmp_path(),
			'status'         => is_writable( WPO_WCPDF()->main->get_tmp_path() ) ? 'ok' : 'failed',
			'status_message' => is_writable( WPO_WCPDF()->main->get_tmp_path() ) ? $status['ok'] : $status['failed'],
		),
		'WCPDF_ATTACHMENT_DIR' => array (
			'description'    => __( 'Temporary attachments folder', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => trailingslashit( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ),
			'status'         => is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? 'ok' : 'failed',
			'status_message' => is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? $status['ok'] : $status['failed'],
		),
		'DOMPDF_TEMP_DIR' => array (
			'description'    => __( 'Temporary DOMPDF folder', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => trailingslashit(WPO_WCPDF()->main->get_tmp_path( 'dompdf' )),
			'status'         => is_writable(WPO_WCPDF()->main->get_tmp_path( 'dompdf' )) ? 'ok' : 'failed',
			'status_message' => is_writable(WPO_WCPDF()->main->get_tmp_path( 'dompdf' )) ? $status['ok'] : $status['failed'],
		),
		'DOMPDF_FONT_DIR' => array (
			'description'    => __( 'DOMPDF fonts folder (needs to be writable for custom/remote fonts)', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => trailingslashit(WPO_WCPDF()->main->get_tmp_path( 'fonts' )),
			'status'         => is_writable(WPO_WCPDF()->main->get_tmp_path( 'fonts' )) ? 'ok' : 'failed',
			'status_message' => is_writable(WPO_WCPDF()->main->get_tmp_path( 'fonts' )) ? $status['ok'] : $status['failed'],
		),
	), $status );

	$upload_dir  = wp_upload_dir();
	$upload_base = trailingslashit( $upload_dir['basedir'] );
?>
<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<thead>
		<tr>
			<td colspan="3"><strong><?php esc_html_e( 'Write Permissions', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th align="left">&nbsp;</th>
			<th align="left"><?php esc_html_e( 'Path', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Status', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
		<?php
			foreach ( $permissions as $permission ) {
				if ( $permission['status'] == 'ok' ) {
					$background = '#68de7c'; // green
					$color      = 'black';
				} else {
					$background = '#ffabaf'; // red
					$color      = 'black';
				}
		?>
		<tr>
			<td><?php echo wp_kses_post( $permission['description'] ); ?></td>
			<td><?php echo ! empty( $permission['value'] ) ? str_replace( array('/','\\' ), array('/<wbr>','\\<wbr>' ), wp_kses_post( $permission['value'] ) ) : ''; ?></td>
			<td style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>"><?php echo wp_kses_post( $permission['status_message'] ); ?></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3">
				<?php
					/* translators: 1,2. directory paths, 3. UPLOADS, 4. wpo_wcpdf_tmp_path, 5. attachments, 6. dompdf, 7. fonts */
					printf( esc_attr__( 'The central temp folder is %1$s. By default, this folder is created in the WordPress uploads folder (%2$s), which can be defined by setting %3$s in wp-config.php. Alternatively, you can control the specific folder for PDF invoices by using the %4$s filter. Make sure this folder is writable and that the subfolders %5$s, %6$s and %7$s are present (these will be created by the plugin if the central temp folder is writable).', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>'.WPO_WCPDF()->main->get_tmp_path().'</code>',
						'<code>'.$upload_base.'</code>',
						'<code>UPLOADS</code>',
						'<code>wpo_wcpdf_tmp_path</code>',
						'<code>attachments</code>',
						'<code>dompdf</code>',
						'<code>fonts</code>'
					);
				?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<?php
					/* translators: directory path */
					printf( esc_attr__('If the temporary folders were not automatically created by the plugin, verify that all the font files (from %s) are copied to the fonts folder. Normally, this is fully automated, but if your server has strict security settings, this automated copying may have been prohibited. In that case, you also need to make sure these folders get synchronized on plugin updates!', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>'.WPO_WCPDF()->plugin_path() . "/vendor/dompdf/dompdf/lib/fonts/".'</code>'
					);
				?>
			</td>
		</tr>
	</tfoot>
</table>
