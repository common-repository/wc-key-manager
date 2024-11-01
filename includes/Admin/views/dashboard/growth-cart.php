<?php
/**
 * Admin view showing the growth cart.
 *
 * @since 1.0.0
 * @package KeyManager\Admin\Views
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$current_month_start = wp_date( 'Y-m-01 00:00:00' );
$current_month_end   = wp_date( 'Y-m-t 23:59:59' );
$results             = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT
        DATE(COALESCE(ordered_at, created_at, expires_at)) as date,
        SUM(CASE WHEN ordered_at IS NOT NULL THEN price ELSE 0 END) AS sales,
        COUNT(DISTINCT CASE WHEN order_id > 0 THEN order_id ELSE NULL END) AS orders,
        COUNT(CASE WHEN order_id > 0 THEN order_id ELSE NULL END) AS sold,
        COUNT(DISTINCT CASE WHEN customer_id > 0 THEN customer_id ELSE NULL END) AS customers,
        COUNT(CASE WHEN created_at IS NOT NULL THEN created_at ELSE NULL END) AS created,
        COUNT(CASE WHEN expires_at IS NOT NULL THEN expires_at ELSE NULL END) AS expired
    FROM {$wpdb->prefix}wckm_keys
    WHERE ordered_at BETWEEN %s AND %s
       OR created_at BETWEEN %s AND %s
       OR expires_at BETWEEN %s AND %s
    GROUP BY DATE(COALESCE(ordered_at, created_at, expires_at))",
		$current_month_start,
		$current_month_end,
		$current_month_start,
		$current_month_end,
		$current_month_start,
		$current_month_end
	),
	ARRAY_A
);

// Fill in missing days.
$days = wp_date( 't' );
for ( $i = 1; $i <= $days; $i++ ) {
	$date_str = wp_date( 'Y-m-' . sprintf( '%02d', $i ) );
	$found    = false;
	foreach ( $results as $result ) {
		if ( $result['date'] === $date_str ) {
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		$results[] = array(
			'date'      => $date_str,
			'sales'     => 0,
			'orders'    => 0,
			'sold'      => 0,
			'customers' => 0,
			'created'   => 0,
			'expired'   => 0,
		);
	}
}
usort(
	$results,
	function ( $a, $b ) {
		return strtotime( $a['date'] ) - strtotime( $b['date'] );
	}
);

$labels  = array_map(
	function ( $i ) {
		return wp_date( 'F j', strtotime( $i ) );
	},
	wp_list_pluck( $results, 'date' ),
);
$dataset = array(
	array(
		'label'           => esc_html__( 'Sales', 'wc-key-manager' ),
		'borderColor'     => '#2271b1',
		'backgroundColor' => '#2271b1',
		'data'            => array_map( 'floatval', wp_list_pluck( $results, 'sales' ) ),
	),
);
?>

<div class="bk-stack">
	<div class="bk-card" style="margin-bottom:0">
		<div class="bk-card__header">
			<div>
				<h2><?php esc_html_e( 'Growth', 'wc-key-manager' ); ?></h2>
			</div>
			<div>
				<?php echo esc_html( wp_date( 'F, Y' ) ); ?>
			</div>
		</div>
		<div class="bk-card__body">
			<canvas id="wckm-growth-chart" style="min-height: 300px;"></canvas>
		</div>
	</div>
	<div class="bk-stats stats--3">
		<div class="bk-stat">
			<div class="bk-stat__label"><?php esc_html_e( 'Sales', 'wc-key-manager' ); ?></div>
			<div class="bk-stat__value">
				<?php echo wp_kses_post( wc_price( array_sum( wp_list_pluck( $results, 'sales' ) ) ) ); ?>
			</div>
		</div>
		<div class="bk-stat">
			<div class="bk-stat__label"><?php esc_html_e( 'Orders', 'wc-key-manager' ); ?></div>
			<div class="bk-stat__value">
				<?php echo esc_html( array_sum( wp_list_pluck( $results, 'orders' ) ) ); ?>
			</div>
		</div>
		<div class="bk-stat">
			<div class="bk-stat__label"><?php esc_html_e( 'Customers', 'wc-key-manager' ); ?></div>
			<div class="bk-stat__value">
				<?php echo esc_html( array_sum( wp_list_pluck( $results, 'customers' ) ) ); ?>
			</div>
		</div>
		<div class="bk-stat">
			<div class="bk-stat__label"><?php esc_html_e( 'Keys Sold', 'wc-key-manager' ); ?></div>
			<div class="bk-stat__value">
				<?php echo esc_html( array_sum( wp_list_pluck( $results, 'sold' ) ) ); ?>
			</div>
		</div>
		<div class="bk-stat">
			<div class="bk-stat__label"><?php esc_html_e( 'Keys Added', 'wc-key-manager' ); ?></div>
			<div class="bk-stat__value">
				<?php echo esc_html( array_sum( wp_list_pluck( $results, 'created' ) ) ); ?>
			</div>
		</div>
		<div class="bk-stat">
			<div class="bk-stat__label"><?php esc_html_e( 'Keys Expired', 'wc-key-manager' ); ?></div>
			<div class="bk-stat__value">
				<?php echo esc_html( array_sum( wp_list_pluck( $results, 'expired' ) ) ); ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	window.onload = function () {
		var ctx = document.getElementById('wckm-growth-chart').getContext('2d');
		new Chart(ctx, {
			type: 'line',
			data: {
				labels: <?php echo wp_json_encode( $labels ); ?>,
				datasets: <?php echo wp_json_encode( $dataset ); ?>
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				legend: {
					display: true,
					position: 'bottom',
				},
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true,
							callback: function (value, index, values) {
								return value.toLocaleString();
							}
						}
					}]
				},
				tooltips: {
					mode: 'index',
					intersect: false,
					callbacks: {
						label: function (tooltipItem, data) {
							var label = data.datasets[tooltipItem.datasetIndex].label || '';
							if (label) {
								label += ': ';
							}
							label += tooltipItem.yLabel.toLocaleString();
							return label;
						}
					}
				}
			}
		});
	};
</script>
