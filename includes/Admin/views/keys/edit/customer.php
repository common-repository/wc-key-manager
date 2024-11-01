<?php
/**
 * The template for customer related to a key.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Key $key
 */

defined( 'ABSPATH' ) || exit;
?>

<?php if ( $key->get_customer() ) : ?>

	<div class="bk-card">
		<div class="bk-card__header">
			<h2 class="bk-card__title"><?php esc_html_e( 'Customer details', 'wc-key-manager' ); ?></h2>
			<a href="<?php echo esc_url( get_edit_user_link( $key->customer_id ) ); ?>" target="_blank">
				<?php esc_html_e( 'View Customer', 'wc-key-manager' ); ?>
			</a>
		</div>

		<div class="bk-card__body" style="--bcard-padding-x: 0;--bcard-padding-y: 0;">
			<div class="bk-table">
				<table>
					<tbody>
					<tr>
						<th><?php esc_html_e( 'Name', 'wc-key-manager' ); ?></th>
						<td>
							<?php echo empty( $key->get_order()->get_formatted_billing_full_name() ) ? esc_html__( 'N/A', 'wc-key-manager' ) : esc_html( $key->get_order()->get_formatted_billing_full_name() ); ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Email', 'wc-key-manager' ); ?></th>
						<td>
							<?php echo esc_html( $key->get_order()->get_billing_email() ); ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Phone', 'wc-key-manager' ); ?></th>
						<td>
							<?php echo esc_html( $key->get_order()->get_billing_phone() ); ?>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<?php
endif;
