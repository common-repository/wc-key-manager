<?php
/**
 * The template for import tools.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$list_of_fields = array(
	'key'              => __( 'The Key. Required field.', 'wc-key-manager' ),
	'product_id'       => __( 'ID of the product, the key is assigned to. Required field.', 'wc-key-manager' ),
	'valid_for'        => __( 'The keys remains valid for the number of days from the date of purchase. Optional field. Default is 0.', 'wc-key-manager' ),
	'expires_at'       => __( 'The key expires at this date. Optional field. Default is null.', 'wc-key-manager' ),
	'activation_limit' => __( 'Activation limit. Optional field. Default is 1.', 'wc-key-manager' ),
);
?>
<hr class="wp-header-end">

<div class="bk-card">
	<div class="bk-card__header"><h3><?php esc_html_e( 'CSV File Import', 'wc-key-manager' ); ?></h3></div>
	<div class="bk-card__body">
		<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<p>
				<?php
				printf(
				// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
					esc_html__( 'Import keys from a CSV file. Download %1$s this sample file %2$s to learn how to format your CSV file. Please note that only the initial 1000 keys from the CSV file will be imported.', 'wc-key-manager' ),
					'<a target="_blank" href="' . esc_url( WCKM()->get_dir_url() . 'data/sample.csv' ) . '" download>',
					'</a>'
				);
				?>
			</p>
			<h3><?php esc_html_e( 'List of Fields', 'wc-key-manager' ); ?></h3>
			<div class="bk-table">
				<table>
					<thead>
					<tr>
						<th><?php esc_html_e( 'Field', 'wc-key-manager' ); ?></th>
						<th><?php esc_html_e( 'Description', 'wc-key-manager' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $list_of_fields as $field => $description ) : ?>
						<tr>
							<td><?php echo esc_html( $field ); ?></td>
							<td><?php echo esc_html( $description ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label for="csv_file"><?php esc_html_e( 'CSV file', 'wc-key-manager' ); ?></label></th>
					<td>
						<input type="file" name="csv_file" id="csv_file" accept="text/csv" required>
						<p class="description">
							<?php esc_html_e( 'Required field. Select a CSV file to import keys.', 'wc-key-manager' ); ?>
						</p>
					</td>
				</tr>
				</tbody>
				<tfoot>
				<tr>
					<th scope="row"></th>
					<td>
						<input type="hidden" name="action" value="wc_key_manager_import_csv" accept="text/plain"/>
						<?php wp_nonce_field( 'wc_key_manager_import_csv' ); ?>
						<?php submit_button( __( 'Import', 'wc-key-manager' ), 'primary', 'submit', false ); ?>
					</td>
				</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>

<div class="bk-card">
	<div class="bk-card__header"><h3><?php esc_html_e( 'TXT File Import', 'wc-key-manager' ); ?></h3></div>
	<div class="bk-card__body">
		<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<p>
				<?php
				printf(
				// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
					esc_html__( 'Import keys from a TXT file. Download %1$s this sample file %2$s to learn how to format your TXT file.', 'wc-key-manager' ),
					'<a target="_blank" href="' . esc_url( WCKM()->get_dir_url() . 'data/sample.txt' ) . '" download>',
					'</a>'
				);
				?>
			</p>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label for="product_id"><?php esc_html_e( 'Product', 'wc-key-manager' ); ?></label></th>
					<td>
						<select id="product_id" name="product_id" class="wckm_select2" data-action="wckm_json_search" data-type="product" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Select a  product...', 'wc-key-manager' ); ?>" required>
							<option value=""><?php esc_html_e( 'Select a product...', 'wc-key-manager' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Select a product to import keys for.', 'wc-key-manager' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="activation_limit"><?php esc_html_e( 'Activation limit', 'wc-key-manager' ); ?></label></th>
					<td>
						<input type="number" name="activation_limit" id="activation_limit" value="" min="0" placeholder="0"/>
						<p class="description"><?php esc_html_e( 'Maximum number of times the key can be used to activate the software. 0 for unlimited. If the product is not software, keep it blank.', 'wc-key-manager' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="valid_for"><?php esc_html_e( 'Valid For (days)', 'wc-key-manager' ); ?></label></th>
					<td>
						<input type="number" name="valid_for" id="valid_for" value="" min="0" placeholder="0"/>
						<p class="description"><?php esc_html_e( 'Relative expiration dates from the date of purchase. Leave empty for no expiration.', 'wc-key-manager' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="txt_file"><?php esc_html_e( 'Text file', 'wc-key-manager' ); ?></label></th>
					<td>
						<input type="file" name="txt_file" id="txt_file" required>
						<p class="description">
							<?php esc_html_e( 'Single key per line. Select a TXT file to import keys.', 'wc-key-manager' ); ?>
						</p>
					</td>
				</tr>
				</tbody>
				<tfoot>
				<tr>
					<th scope="row"></th>
					<td>
						<input type="hidden" name="action" value="wc_key_manager_import_txt">
						<?php wp_nonce_field( 'wc_key_manager_import_txt' ); ?>
						<?php submit_button( __( 'Import', 'wc-key-manager' ), 'primary', 'submit', false ); ?>
					</td>
				</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>

