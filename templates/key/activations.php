<?php
/**
 * Activations key template.
 *
 * Shows list of keys customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/key/activations.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 1.0.0
 *
 * @package KeyManager/Templates
 *
 * @var Key $key The key object.
 */

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$activations = $key->activations()->set( 'status', 'active' )->get_results();

?>
<section class="woocommerce-order-details woocommerce-order-details__activations">
	<h2 class="woocommerce-order-details__title wckm-section-title"><?php esc_html_e( 'Activations', 'wc-key-manager' ); ?></h2>
	<table class="shop_table shop_table_responsive my_account_orders woocommerce-orders-table woocommerce-MyAccount-orders woocommerce-orders-table--activations">
		<thead>
		<tr>
			<?php foreach ( $columns as $column_id => $column_name ) : ?>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody>
		<?php if ( $activations ) : ?>
			<?php foreach ( $activations as $activation ) : ?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--activations">
					<?php foreach ( $columns as $column_id => $column_name ) : ?>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php
							if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) {
								do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $activation );
							} else {
								switch ( $column_id ) {
									case 'instance':
										echo esc_html( $activation->instance );
										break;
									case 'ip_address':
										echo esc_html( $activation->ip_address );
										break;
									case 'date':
										echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $activation->created_at ) ) );
										break;
									case 'actions':
										$deactivate_url = add_query_arg(
											array(
												'action'   => 'wckm_deactivate_key',
												'uuid'     => $key->uuid,
												'instance' => $activation->instance,
											)
										);
										if ( 'active' === $activation->status ) {
											printf( '<a href="%s" class="button wckm-deactivate-key">%s</a>', esc_url( wp_nonce_url( $deactivate_url, 'wckm_deactivate_key' ) ), esc_html__( 'Deactivate', 'wc-key-manager' ) );
										}
										break;

								}
							}
							?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="<?php echo count( $columns ); ?>"><?php esc_html_e( 'No activations found.', 'wc-key-manager' ); ?></td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<?php if ( 'yes' === get_option( 'wckm_my_account_allow_activation', 'yes' ) && ! $key->is_expired() && ! $key->is_at_limit() ) : ?>
		<h2 class="woocommerce-order-details__title wckm-section-title"><?php esc_html_e( 'Activate Key', 'wc-key-manager' ); ?></h2>
		<form id="wckm-activation-form" method="get" enctype="multipart/form-data">
			<?php
			/**
			 * Fires before the activation form fields.
			 *
			 * @param Key      $key The key object.
			 * @param WC_Order $order The order object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_key_manager_before_activation_form_fields', $key, $order );
			?>
			<p class="form-row">
				<label for="instance"><?php esc_html_e( 'Instance', 'wc-key-manager' ); ?>&nbsp;<span class="optional">(<?php esc_html_e( 'optional', 'wc-key-manager' ); ?>)</span></label>
				<input type="text" id="instance" name="instance" class="input-text" placeholder="<?php esc_html_e( 'Enter a reference for this activation', 'wc-key-manager' ); ?>">
			</p>
			<?php
			/**
			 * Fires after the activation form fields.
			 *
			 * @param Key      $key The key object.
			 * @param WC_Order $order The order object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_key_manager_after_activation_form_fields', $key, $order );
			?>
			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Submit', 'wc-key-manager' ); ?></button>
			</p>
			<?php printf( '<input type="hidden" name="uuid" value="%s">', esc_attr( $key->uuid ) ); ?>
			<input type="hidden" name="action" value="wckm_activate_key">
			<?php wp_nonce_field( 'wckm_activate_key' ); ?>
		</form>
	<?php endif; ?>

</section>
