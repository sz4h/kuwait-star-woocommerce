<?php
/**
 * Class WC_Email_Admin_Customer_Completed_Order file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Email_Admin_Customer_Completed_Order', false ) ) :

	/**
	 * Customer Completed Order Email.
	 *
	 * Order complete emails are sent to the admin when the customer's order is marked complete and usual indicates that the order has been shipped.
	 *
	 * @class       WC_Email_Admin_Customer_Completed_Order
	 * @version     2.0.0
	 * @package     WooCommerce\Classes\Emails
	 * @extends     WC_Email
	 */
	class WC_Email_Admin_Customer_Completed_Order extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'admin_customer_completed_order';
			$this->customer_email = false;
			$this->title          = __( 'Admin Completed order', SPWKS_TD );
			$this->description    = __( 'Order complete emails are sent to admin when customers orders are marked completed and usually indicate that their orders have been shipped.', 'woocommerce' );
			$this->template_html  = 'emails/admin-customer-completed-order.php';
			$this->template_plain = 'emails/plain/admin-customer-completed-order.php';
			$this->template_base  = SPWKS_PATH . 'mail/';

			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Triggers for this email.
//			add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor.
			parent::__construct();
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
		public function trigger( int $order_id, WC_Order|bool $order = false ): void {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html(): string {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => true,
					'plain_text'         => false,
					'email'              => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain(): string {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => true,
					'plain_text'         => true,
					'email'              => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject(): string {
			return __( '{site_title} order is now complete', 'woocommerce' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading(): string {
			return __( 'Thanks for shopping with us', 'woocommerce' );
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_additional_content(): string {
			return __( 'Thanks for shopping with us.', 'woocommerce' );
		}
	}

endif;

return new WC_Email_Admin_Customer_Completed_Order();
