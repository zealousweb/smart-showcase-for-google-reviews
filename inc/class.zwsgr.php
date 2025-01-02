<?php
/**
 * ZWSGR Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR' ) ) {

	/**
	 * The main ZWSGR class
	 */
	class ZWSGR {
		
		public $admin,$lib,$front = null;
		public $zwsgr_admin_smtp_enabled,$zwsgr_smtp_opt,$zwsgr_general_opt;
		private static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}

		function __construct() {

			add_action( 'setup_theme',   array( $this, 'action__setup_theme' ) );
			add_action( 'plugins_loaded', array( $this, 'action__plugins_loaded' ), 1 );
			add_filter( 'plugin_action_links',array( $this,'action__zwsgr_plugin_action_links'), 10, 2 );
			
			$this->zwsgr_admin_smtp_enabled = get_option('zwsgr_admin_smtp_enabled');
			$this->zwsgr_smtp_opt = get_option( 'zwsgr_smtp_option' );
			$this->zwsgr_general_opt = get_option( 'zwsgr_general_option' );

			if( $this->zwsgr_admin_smtp_enabled == 1) {
				add_action( 'phpmailer_init', array( $this, 'action__init_smtp_mailer' ), 9999 );
			}
			
			
		}

		/**
		 * Action: phpmailer_init
		 *
		 * Set SMTP parameters if SMTP mailer.
		 *
		 * @method action__init_smtp_mailer
		 *
		 * @param  mailer object  $phpmailer
		 *
		 */
		function action__init_smtp_mailer(  $phpmailer ) {

			$phpmailer->IsSMTP();

			$from_email = $this->zwsgr_smtp_opt['zwsgr_from_email'];
			$from_name = $this->zwsgr_smtp_opt['zwsgr_from_name'];

			$phpmailer->From     = $from_email;
			$phpmailer->FromName = $from_name;
			$phpmailer->SetFrom( $phpmailer->From, $phpmailer->FromName );

			/* Set the SMTP Secure value */
			if ( 'none' !== $this->zwsgr_smtp_opt['zwsgr_smtp_ency_type'] ) {
				$phpmailer->SMTPSecure = $this->zwsgr_smtp_opt['zwsgr_smtp_ency_type'];
			}

			/* Set the other options */
			$phpmailer->Host = $this->zwsgr_smtp_opt['zwsgr_smtp_host'];
			$phpmailer->Port = $this->zwsgr_smtp_opt['zwsgr_smtp_port'];

			/* If we're using smtp auth, set the username & password */
			if ( 'yes' == $this->zwsgr_smtp_opt['zwsgr_smtp_auth'] ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $this->zwsgr_smtp_opt['zwsgr_smtp_username'];
				$phpmailer->Password = $this->zwsgr_smtp_opt['zwsgr_smtp_password'];
			}
			//PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
			$phpmailer->SMTPAutoTLS = false;

			//set reasonable timeout
			$phpmailer->Timeout = 10;
			$phpmailer->CharSet  = "utf-8";
		}

		function action__plugins_loaded() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		function action__setup_theme() {
			if ( is_admin() ) {
				ZWSGR()->admin = new ZWSGR_Admin;
				ZWSGR()->admin->action = new ZWSGR_Admin_Action;
				ZWSGR()->admin->filter = new ZWSGR_Admin_Filter;
				
			} else {
				ZWSGR()->front = new ZWSGR_Front;
				ZWSGR()->front->action = new ZWSGR_Front_Action;
				ZWSGR()->front->filter = new ZWSGR_Front_Filter;
				
			}
			ZWSGR()->lib = new ZWSGR_Lib;
				
		}

		/**
		 * Action: plugin_action_links
		 * Add Subscription link after active links.
		 * @method action__zwsgr_plugin_action_links
		 * @return $links
		*/
		function action__zwsgr_plugin_action_links( $links, $file ) 
		{

			if ( $file != ZWSGR_PLUGIN_BASENAME ) {
				return $links;
			}
			if ( is_plugin_active( 'smart-google-reviews/smart-google-reviews.php' )  )
			{
				$support_link = '<a href="https://support.zealousweb.com/" target="_blank">' .__( 'Support', 'smart-google-reviews' ). '</a>';
				$document_link = '<a href="https://store.zealousweb.com/documentation/wordpress-plugins/smart-google-review/" target="_blank">' .__( 'Document', 'smart-google-reviews' ). '</a>';
				$donateLink = '<a target="_blank" href="http://www.zealousweb.com/payment/">' . __( 'Donate', 'smart-google-reviews' ) . '</a>';

				array_unshift( $links , $support_link, $document_link, $donateLink );
			}
			return $links;
		}
	}
}

function ZWSGR() {
	return ZWSGR::instance();
}
ZWSGR();
