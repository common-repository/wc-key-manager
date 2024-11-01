<?php

namespace KeyManager\Emails;

use KeyManager\Handlers\Emails;
use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * CustomerKeys class.
 *
 * @extends \WC_Email
 *
 * @since 1.0.0
 */
class CustomerKeys extends \WC_Email {

	/**
	 * Keys.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $keys = array();

	/**
	 * Constructor.
	 *
	 * Set email defaults.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id             = 'customer_order_completed';
		$this->customer_email = true;
		$this->title          = __( 'Keys Details', 'wc-key-manager' );
		$this->description    = __( 'This email is sent to the customer when an order containing keys is completed.', 'wc-key-manager' );
		$this->heading        = __( 'Your Key(s) from {site_title}', 'wc-key-manager' );
		$this->subject        = __( 'Your Key(s) from {site_title}', 'wc-key-manager' );
		$this->template_html  = 'emails/customer-keys.php';
		$this->template_plain = 'emails/plain/customer-keys.php';
		$this->template_base  = WCKM()->get_template_path();

		add_action( 'wc_key_manager_send_customer_keys_notification', array( $this, 'trigger' ) );

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @since 0.1
	 * @return void
	 */
	public function trigger( $order_id ) {
		$this->setup_locale();

		if ( $order_id ) {
			$this->object    = wc_get_order( $order_id );
			$this->recipient = $this->object->get_billing_email();
			$this->keys      = Key::results(
				array(
					'order_id'        => $order_id,
					'customer_id'     => $this->object->get_customer_id(),
					'order_id__exits' => true,
					'limit'           => - 1,
				)
			);

			if ( ! $this->is_enabled() || ! $this->get_recipient() || ! wckm_order_has_products( $order_id ) ) {
				return;
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}


	/**
	 * Get content html.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
				'keys'               => $this->keys,
				'columns'            => Emails::get_email_keys_columns(),
			),
			'',
			$this->template_base,
		);
	}

	/**
	 * Get content plain.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
				'keys'               => $this->keys,
				'columns'            => Emails::get_email_keys_columns(),
			),
			'',
			$this->template_base,
		);
	}

	/**
	 * Initialize Settings Form Fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'            => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes',
			),
			'subject'            => array(
				'title'       => 'Subject',
				'type'        => 'text',
				'description' => sprintf( /* translators: 1: Email subject */ __( 'This controls the email subject line. Leave blank to use the default subject: %s.', 'wc-key-manager' ), $this->get_default_subject() ),
				'desc_tip'    => true,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'            => array(
				'title'       => 'Email Heading',
				'type'        => 'text',
				'description' => sprintf( /* translators: 1: Email heading */ __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: %s.', 'wc-key-manager' ), $this->get_default_heading() ),
				'desc_tip'    => true,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'additional_content' => array(
				'title'       => __( 'Additional content', 'wc-key-manager' ),
				'description' => __( 'Text to appear below the main email content. Available placeholders: {site_title}, {site_address}, {site_url}, {order_date}, {order_number}', 'wc-key-manager' ),
				'desc_tip'    => true,
				'type'        => 'textarea',
				'css'         => 'width:400px;',
				'placeholder' => __( 'N/A', 'wc-key-manager' ),
				'default'     => $this->get_default_additional_content(),
			),
			'email_type'         => array(
				'title'       => __( 'Email type', 'wc-key-manager' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wc-key-manager' ),
				'desc_tip'    => true,
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
			),
		);
	}
}
