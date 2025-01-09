<?php
/**
 * ZWSSGR Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSSGR' ) ) {

	/**
	 * The main ZWSSGR class
	 */
	class ZWSSGR {
		
		public $admin,$lib,$front = null;
		public $zwssgr_admin_smtp_enabled,$zwssgr_smtp_opt,$zwssgr_general_opt;
		private static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}

		function __construct() {

			add_action( 'setup_theme',   array( $this, 'action__setup_theme' ) );
			add_action( 'plugins_loaded', array( $this, 'action__plugins_loaded' ), 1 );
			add_filter( 'plugin_action_links',array( $this,'action__zwssgr_plugin_action_links'), 10, 2 );
			
			$this->zwssgr_admin_smtp_enabled = get_option('zwssgr_admin_smtp_enabled');
			$this->zwssgr_smtp_opt = get_option( 'zwssgr_smtp_option' );
			$this->zwssgr_general_opt = get_option( 'zwssgr_general_option' );

			if( $this->zwssgr_admin_smtp_enabled == 1) {
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

			$from_email = $this->zwssgr_smtp_opt['zwssgr_from_email'];
			$from_name = $this->zwssgr_smtp_opt['zwssgr_from_name'];

			$phpmailer->From     = $from_email;
			$phpmailer->FromName = $from_name;
			$phpmailer->SetFrom( $phpmailer->From, $phpmailer->FromName );

			/* Set the SMTP Secure value */
			if ( 'none' !== $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'] ) {
				$phpmailer->SMTPSecure = $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'];
			}

			/* Set the other options */
			$phpmailer->Host = $this->zwssgr_smtp_opt['zwssgr_smtp_host'];
			$phpmailer->Port = $this->zwssgr_smtp_opt['zwssgr_smtp_port'];

			/* If we're using smtp auth, set the username & password */
			if ( 'yes' == $this->zwssgr_smtp_opt['zwssgr_smtp_auth'] ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $this->zwssgr_smtp_opt['zwssgr_smtp_username'];
				$phpmailer->Password = $this->zwssgr_smtp_opt['zwssgr_smtp_password'];
			}
			//PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
			$phpmailer->SMTPAutoTLS = false;

			//set reasonable timeout
			$phpmailer->Timeout = 10;
			$phpmailer->CharSet  = "utf-8";
		}

		function action__plugins_loaded() {
			ob_start();
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		function action__setup_theme() {
			if ( is_admin() ) {
				ZWSSGR()->admin = new ZWSSGR_Admin;
				ZWSSGR()->admin->action = new ZWSSGR_Admin_Action;
				ZWSSGR()->admin->filter = new ZWSSGR_Admin_Filter;
				
			} else {
				ZWSSGR()->front = new ZWSSGR_Front;
				ZWSSGR()->front->action = new ZWSSGR_Front_Action;
				ZWSSGR()->front->filter = new ZWSSGR_Front_Filter;
				
			}
			ZWSSGR()->lib = new ZWSSGR_Lib;
				
		}

		/**
		 * Action: plugin_action_links
		 * Add Subscription link after active links.
		 * @method action__zwssgr_plugin_action_links
		 * @return $links
		*/
		function action__zwssgr_plugin_action_links( $links, $file ) 
		{

			if ( $file != ZWSSGR_PLUGIN_BASENAME ) {
				return $links;
			}
			if ( is_plugin_active( 'smart-showcase-for-google-reviews/smart-showcase-for-google-reviews.php' )  )
			{
				$support_link = '<a href="https://support.zealousweb.com/" target="_blank">' .__( 'Support', 'smart-showcase-for-google-reviews' ). '</a>';
				$document_link = '<a href="https://store.zealousweb.com/documentation/wordpress-plugins/smart-google-review/" target="_blank">' .__( 'Document', 'smart-showcase-for-google-reviews' ). '</a>';
				$donateLink = '<a target="_blank" href="http://www.zealousweb.com/payment/">' . __( 'Donate', 'smart-showcase-for-google-reviews' ) . '</a>';

				array_unshift( $links , $support_link, $document_link, $donateLink );
			}
			return $links;
		}
	}
}

function ZWSSGR() {
	return ZWSSGR::instance();
}
ZWSSGR();
