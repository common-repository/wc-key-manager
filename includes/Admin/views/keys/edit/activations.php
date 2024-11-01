<?php
/**
 * The template key activations.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Key $key
 */

defined( 'ABSPATH' ) || exit;

$activations = $key->activations()->set( 'status', 'active' )->set( 'limit', 5 )->get_results();
?>
<div class="bk-card">
	<div class="bk-card__header">
		<h2 class="bk-card__title"><?php esc_html_e( 'Recent Activations', 'wc-key-manager' ); ?></h2>

		<?php if ( ! empty( $activations ) ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wckm-keys&key_id=' . $key->id . '&tab=activations' ) ); ?>" class="bk-card__link"><?php esc_html_e( 'View all', 'wc-key-manager' ); ?></a>
		<?php endif; ?>

	</div>
	<div class="bk-card__body" style="padding: 0">
		<div class="bk-table">
			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Instance', 'wc-key-manager' ); ?></th>
						<th><?php esc_html_e( 'IP Address', 'wc-key-manager' ); ?></th>
						<th><?php esc_html_e( 'Activation Date', 'wc-key-manager' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wc-key-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $activations ) ) : ?>
						<?php foreach ( $activations as $activation ) : ?>
							<tr>
								<td>
									<?php if ( $activation->instance ) : ?>
										<?php printf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wckm-keys&tab=activations&edit=' . $activation->id ) ), esc_html( $activation->instance ) ); ?>
									<?php else : ?>
										<?php esc_html_e( 'N/A', 'wc-key-manager' ); ?>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $activation->ip_address ); ?></td>
								<td><?php echo esc_html( $activation->created_at ); ?></td>
								<td><?php echo wp_kses_post( $activation->get_status_html() ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="4"><?php esc_html_e( 'No activations found.', 'wc-key-manager' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
