<?php
/**
 * Email Keys.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-keys.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package KeyManager\Templates\Emails
 * @version 1.0.0
 */

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';
?><h2 class="woocommerce-order-downloads__title"><?php esc_html_e( 'Key(s)', 'wc-key-manager' ); ?></h2>

<?php if ( ! empty( $keys ) && in_array( $order->get_status(), wckm_get_order_paid_statuses(), true ) ) : ?>
	<?php
	/**
	 * Action before rendering the keys.
	 *
	 * @param Key[]    $keys The list of keys.
	 * @param WC_Order $order The order object.
	 * @param bool     $plain_text Whether the email is plain text.
	 */
	do_action( 'wc_key_manager_email_before_keys', $keys, $order, $plain_text );
	?>

	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
		<thead>
		<tr>
			<?php foreach ( $columns as $column_id => $column_name ) : ?>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html( $column_name ); ?></th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $keys as $key ) : ?>
			<tr>
				<?php foreach ( $columns as $column_id => $column_name ) : ?>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
						<?php
						/**
						 * Action before rendering the key.
						 *
						 * @param Key  $key The key object.
						 * @param bool $plain_text Whether the email is plain text.
						 */
						do_action( 'wc_key_manager_email_before_keys_column_' . $column_id, $key, $plain_text );

						switch ( $column_id ) {
							case 'product':
								printf( '<a href="%s" style="text-decoration: none">%s</a>', esc_url( $key->get_product_url() ), esc_html( $key->get_product_name() ) );
								break;
							case 'key':
								if ( 'yes' === get_option( 'wckm_enable_my_account_keys_page', 'yes' ) ) {
									printf( '<a href="%s" style="text-decoration: none"><code>%s</code></a>', esc_url( $key->get_view_key_url() ), esc_html( $key->key ) );
								} else {
									printf( '<code>%s</code>', esc_html( $key->key ) );
								}
								break;
							case 'expires':
								echo wp_kses_post( $key->get_expires_html() );
								break;
						}

						/**
						 * Action after rendering the key.
						 *
						 * @param Key  $key The key object.
						 * @param bool $plain_text Whether the email is plain text.
						 */
						do_action( 'wc_key_manager_email_keys_column_' . $column_id, $key, $plain_text );
						?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php
	/**
	 * Action after rendering the keys.
	 *
	 * @param Key[]    $keys The list of keys.
	 * @param WC_Order $order The order object.
	 * @param bool     $plain_text Whether the email is plain text.
	 */
	do_action( 'wc_key_manager_email_after_keys', $keys, $order, $plain_text );
	?>

<?php else : ?>
	<?php
	/**
	 * Filter pending order email keys text.
	 *
	 * @param string   $text The text to display.
	 * @param WC_Order $order The order object.
	 */
	$pending_keys_text = apply_filters(
		'wc_key_manager_email_pending_keys_text',
		esc_html__( 'Your keys will be dispatched shortly. The delivery timeframe ranges from a few minutes to a maximum of 24 hours, depending on payment processing and our internal procedures. Your understanding and patience are highly valued.', 'wc-key-manager' ),
		$order
	);

	echo wp_kses_post( wpautop( wptexturize( $pending_keys_text ) ) );
endif;
