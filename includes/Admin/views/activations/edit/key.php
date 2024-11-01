<?php
/**
 * The template for key related to a activation.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Activation $activation
 */

defined( 'ABSPATH' ) || exit;

if ( $activation->key ) :
	?>
	<div class="bk-card">
		<div class="bk-card__header">
			<h2 class="bk-card__title"><?php esc_html_e( 'Key details', 'wc-key-manager' ); ?></h2>
			<a href="
			<?php
			echo esc_url(
				add_query_arg(
					array(
						'page' => 'wckm-keys',
						'tab'  => 'keys',
						'edit' => $activation->key->id,
					),
					admin_url( 'admin.php' )
				)
			);
			?>
			" target="_blank">
				<?php esc_html_e( 'View Key', 'wc-key-manager' ); ?>
			</a>
		</div>

		<div class="bk-card__body" style="--bcard-padding-x: 0;--bcard-padding-y: 0;">
			<div class="bk-table">
				<table>
					<tbody>
					<tr>
						<th><?php esc_html_e( 'Expires', 'wc-key-manager' ); ?></th>
						<td><?php echo $activation->key->get_expires() ? wp_kses_post( $activation->key->get_expires_html() ) : esc_html__( 'Never', 'wc-key-manager' ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Activation Limit', 'wc-key-manager' ); ?></th>
						<td><?php echo esc_html( absint( $activation->key->activation_limit ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Activation Count', 'wc-key-manager' ); ?></th>
						<td><?php echo esc_html( absint( $activation->key->activation_count ) ); ?></td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
endif;
