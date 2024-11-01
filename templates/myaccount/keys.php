<?php
/**
 * My keys' template.
 *
 * Shows list of keys customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/keys.php.
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
 */

defined( 'ABSPATH' ) || exit;
/**
 * Action before rendering the keys.
 *
 * @param \KeyManager\Models\Key[] $keys The keys.
 */
do_action( 'wc_key_manager_before_my_account_keys', $keys ); ?>

<?php if ( ! empty( $keys ) ) : ?>
	<table class="shop_table shop_table_responsive my_account_orders woocommerce-orders-table woocommerce-MyAccount-orders woocommerce-orders-table--keys">
		<thead>
		<tr>
			<?php foreach ( $columns as $column_id => $column_name ) : ?>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $keys as $key ) {
			?>
			<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( strtolower( $key->status ) ); ?> order">
				<?php foreach ( $columns as $column_id => $column_name ) : ?>
					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
						<?php

						/**
						 * Action before rendering the key.
						 *
						 * @param \KeyManager\Models\Key $key The key object.
						 */
						do_action( 'wc_key_manager_my_account_keys_before_column_' . $column_id, $key );

						switch ( $column_id ) {
							case 'product':
								echo '<a href="' . esc_url( $key->get_product_url() ) . '">' . esc_html( $key->get_product_name() ) . '</a>';
								break;
							case 'key':
								echo wp_kses_post( $key->get_key_html() );
								break;
							case 'expires':
								echo wp_kses_post( $key->get_expires_html() );
								break;
							case 'actions':
								?>
								<a class="button view" href="<?php echo esc_url( $key->get_view_key_url() ); ?>"><?php esc_html_e( 'View', 'wc-key-manager' ); ?></a>
								<a class="button view" href="<?php echo esc_url( $key->get_order()->get_view_order_url() ); ?>"><?php esc_html_e( 'Order', 'wc-key-manager' ); ?></a>
								<?php
								break;
						}

						/**
						 * Action after rendering the key.
						 *
						 * @param \KeyManager\Models\Key $key The key object.
						 */
						do_action( 'wc_key_manager_my_account_keys_column_' . $column_id, $key );

						?>
					</td>
				<?php endforeach; ?>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
<?php else : ?>
	<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Go Shop', 'wc-key-manager' ); ?>
		</a>
		<?php esc_html_e( 'No key has been purchased yet.', 'wc-key-manager' ); ?>
	</div>
<?php endif; ?>
<?php
/**
 * Action after rendering the keys.
 *
 * @param \KeyManager\Models\Key[] $keys The keys.
 */
do_action( 'wc_key_manager_after_my_account_keys', $keys );
