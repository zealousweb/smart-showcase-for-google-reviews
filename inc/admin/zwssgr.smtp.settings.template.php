<?php
/**
 * SMTP Settings Template
 *
 * Handles the SMTP functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

	$zwssgr_message 	   = $zwssgr_message_smtp = $zwssgr_debug_msg = $zwssgr_success = '';
	$zwssgr_custom_error   = array();
	$zwssgr_smtp_option    = get_option( 'zwssgr_smtp_option' );
	$zwssgr_general_option = get_option( 'zwssgr_general_option' );
	$zwssgr_smtp_option    = is_array($zwssgr_smtp_option) ? $zwssgr_smtp_option : []; 
	
	if (!is_array($zwssgr_smtp_option)) {
		$zwssgr_smtp_option = [];
	}

	if ( isset( $_POST['zwssgr_smtp_test_submit'] ) ) {

		// check nounce
		if ( ! check_admin_referer( ZWSSGR_PLUGIN_BASENAME, '_smtptest_nonce_name' ) ) {
			$zwssgr_custom_error [] =  __( 'Nonce check failed.', 'smart-showcase-for-google-reviews' );
		}

		global $wp_version;
		if ( version_compare( $wp_version, '5.5.1', '>=' ) ) {
			// WordPress version is greater than 4.3
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			$zwssgr_mail = new PHPMailer\PHPMailer\PHPMailer();
            
		}  else {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			$zwssgr_mail = new PHPMailer( true );
		}

		$zwssgr_to_email = isset( $_POST['zwssgr_test_to_email'] ) ? sanitize_email( wp_unslash( $_POST['zwssgr_test_to_email'] ) ) : '';
		$zwssgr_subject = isset( $_POST['zwssgr_test_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_test_subject'] ) ) : '';
		$zwssgr_body = isset( $_POST['zwssgr_test_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['zwssgr_test_message'] ) ) : '';
		$zwssgr_ret = array();

		try {

			$zwssgr_smtp_opt = get_option('zwssgr_smtp_option',[]);
			
			if(!empty($zwssgr_smtp_opt)){
				$zwssgr_charset       = get_bloginfo( 'charset' );
				$zwssgr_mail->CharSet = $zwssgr_charset;

				$zwssgr_from_name  = $this->zwssgr_smtp_opt['zwssgr_from_name'];
				$zwssgr_from_email = $this->zwssgr_smtp_opt['zwssgr_from_email'];

				$zwssgr_mail->IsSMTP();

				// send plain text test email
				$zwssgr_mail->ContentType = 'text/plain';
				$zwssgr_mail->IsHTML( false );

				/* If using smtp auth, set the username & password */
				if ( 'yes' === $this->zwssgr_smtp_opt['zwssgr_smtp_auth'] ) {
					$zwssgr_mail->SMTPAuth = true;
					$zwssgr_mail->Username = $this->zwssgr_smtp_opt['zwssgr_smtp_username'];
					$zwssgr_mail->Password = $this->zwssgr_smtp_opt['zwssgr_smtp_password'];
				}

				/* Set the SMTPSecure value, if set to none, leave this blank */
				if ( 'none' !== $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'] ) {
					$zwssgr_mail->SMTPSecure = $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'];
				}

				/* PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate. */
				$zwssgr_mail->SMTPAutoTLS = false;

				/* Set the other options */
				$zwssgr_mail->Host = $this->zwssgr_smtp_opt['zwssgr_smtp_host'];
				$zwssgr_mail->Port = $this->zwssgr_smtp_opt['zwssgr_smtp_port'];

				$zwssgr_mail->SetFrom( $zwssgr_from_email, $zwssgr_from_name );
				//This should set Return-Path header for servers that are not properly handling it, but needs testing first
				//$zwssgr_mail->Sender		 = $zwssgr_mail->From;
				$zwssgr_mail->Subject = $zwssgr_subject;
				$zwssgr_mail->Body    = $zwssgr_body;
				$zwssgr_mail->AddAddress( $zwssgr_to_email );

				global $zwssgr_debug_msg;
				$zwssgr_debug_msg = '';
				$zwssgr_mail->Debugoutput = function ( $zwssgr_str, $zwssgr_level ) {
					global $zwssgr_debug_msg;
					$zwssgr_debug_msg .= $zwssgr_str.'<br>';
				};
				$zwssgr_mail->SMTPDebug = 1;
				//set reasonable timeout
				$zwssgr_mail->Timeout = 10;

				/* Send mail and return result */
				$zwssgr_mail->Send();
				$zwssgr_mail->ClearAddresses();
				$zwssgr_mail->ClearAllRecipients();
				if ( $zwssgr_mail->ErrorInfo != ""){
					$zwssgr_success = 0;
					$zwssgr_ret['error'] = $zwssgr_mail->ErrorInfo;
				} else { 
					$zwssgr_success = 1;
				}
			}else{
				$zwssgr_custom_error [] = __( 'First, configure and save the SMTP settings.', 'smart-showcase-for-google-reviews' );
			}
		} catch ( Exception $e ) {
			$zwssgr_success = 0;
			$zwssgr_ret['error'] = $zwssgr_mail->ErrorInfo;
		}

		$zwssgr_ret['debug_log'] = $zwssgr_debug_msg;

		if( $zwssgr_success == 0 ) {
			$zwssgr_custom_error [] = __( 'Error on send mail.', 'smart-showcase-for-google-reviews' );
			$zwssgr_custom_error [] = $zwssgr_ret['error'];
			$zwssgr_custom_error [] = $zwssgr_ret['debug_log'];
		}

		if ( empty( $zwssgr_custom_error  ) ) {
			$zwssgr_message .= __( 'Test email was successfully sent.', 'smart-showcase-for-google-reviews' );
		}
	}

	if ( isset( $_POST['zwssgr_smtp_submit'] ) ) {

		if ( ! check_admin_referer( ZWSSGR_PLUGIN_BASENAME, '_smtp_nonce_name' ) ) {
			$zwssgr_custom_error[]  .= ' ' . __( 'Nonce check failed.', 'smart-showcase-for-google-reviews' );
		}

		
		if ( isset( $_POST['zwssgr_from_email'] )) {
			$email = sanitize_email(wp_unslash( $_POST['zwssgr_from_email'] )); 
			if ( is_email( $email ) ) {
				$zwssgr_smtp_option['zwssgr_from_email'] = sanitize_email( $email );
			} 
		}
		
		$zwssgr_smtp_option['zwssgr_admin_smtp_enabled'] = isset( $_POST['zwssgr_admin_smtp_enabled'] ) && sanitize_text_field(wp_unslash($_POST['zwssgr_admin_smtp_enabled'] ) ) == '1' ? 1 : 0;
		$zwssgr_smtp_option['zwssgr_from_name'] 		 = isset( $_POST['zwssgr_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_from_name'] ) ) : '';
		$zwssgr_smtp_option['zwssgr_smtp_host'] 		 = isset( $_POST['zwssgr_smtp_host'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_smtp_host'] ) ) : '';
		$zwssgr_smtp_option['zwssgr_smtp_ency_type'] 	 = isset( $_POST['zwssgr_smtp_ency_type'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_smtp_ency_type'] ) ) : 'none';
		$zwssgr_smtp_option['zwssgr_smtp_auth'] 		 = isset( $_POST['zwssgr_smtp_auth'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_smtp_auth'] ) ) : 'no';

		
		$zwssgr_smtp_option['zwssgr_smtp_port']	= '25';
		/* Check value from "SMTP port" option */
		if ( isset( $_POST['zwssgr_smtp_port'] ) ) {
			$smtp_port = sanitize_text_field( wp_unslash( $_POST['zwssgr_smtp_port'] ) );
		
			// Validate that the port is a number and greater than zero
			if ( empty( $smtp_port ) || 1 > intval( $smtp_port ) || ! preg_match( '/^\d+$/', $smtp_port ) ) {
				$zwssgr_smtp_option['zwssgr_smtp_port'] = '25';
				$zwssgr_custom_error .= ' ' . __( "Please enter a valid port in the 'SMTP Port' field.", 'smart-showcase-for-google-reviews' );
			} else {
				$zwssgr_smtp_option['zwssgr_smtp_port'] = $smtp_port;
			}
		}
		
		$zwssgr_smtp_option['zwssgr_smtp_username'] = isset( $_POST['zwssgr_smtp_username'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_smtp_username'] ) ) : '';
		$zwssgr_smtp_option['zwssgr_smtp_password'] = isset( $_POST['zwssgr_smtp_password'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_smtp_password'] ) ) : '';

		/* Update settings in the database */
		if ( empty( $zwssgr_custom_error  ) && $zwssgr_smtp_option['zwssgr_admin_smtp_enabled'] !== 0) {
			update_option( 'zwssgr_smtp_option', $zwssgr_smtp_option );
			$zwssgr_message_smtp .= __( 'SMTP Settings saved.', 'smart-showcase-for-google-reviews' );
		} else {
			$zwssgr_message_smtp  .= ' ' . __( 'SMTP Settings saved.', 'smart-showcase-for-google-reviews' );
		}

		$zwssgr_smtp_option['zwssgr_admin_smtp_enabled'] = isset( $_POST['zwssgr_admin_smtp_enabled'] ) && sanitize_text_field(wp_unslash($_POST['zwssgr_admin_smtp_enabled'] ) ) == '1' ? 1 : 0;

		if ( isset( $_POST['zwssgr_admin_smtp_enabled'] ) && $_POST['zwssgr_admin_smtp_enabled'] == '1' ) {
			$zwssgr_smtp_option['zwssgr_admin_smtp_enabled'] = 1;
			
		} else {
			$zwssgr_smtp_option['zwssgr_admin_smtp_enabled'] = 0;
			update_option( 'zwssgr_smtp_option', '');
		}
		
		update_option('zwssgr_admin_smtp_enabled', $zwssgr_smtp_option['zwssgr_admin_smtp_enabled']);

	}

	if( !empty( $zwssgr_message_smtp ) )  { ?>
		<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
			<p><strong><?php echo esc_html( $zwssgr_message_smtp ); ?></strong></p>
		</div>
		<?php } ?>

		<form autocomplete="off" class="zwssgr-setting-form" method="post" action="">
			<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<?php esc_html_e( 'Enable SMTP', 'smart-showcase-for-google-reviews' ); ?>
					</th>
					<td>
						<label class="switch zwssgr-switch">
							<input type="checkbox" id="zwssgr_admin_smtp_enabled" name="zwssgr_admin_smtp_enabled"
								value="1" <?php checked( isset( $zwssgr_smtp_option['zwssgr_admin_smtp_enabled'] ) && $zwssgr_smtp_option['zwssgr_admin_smtp_enabled'] == 1 ); ?>>
							<span class="slider zwssgr-toggle-slider"></span>
						</label>
					</td>
				</tr>
			</tbody>
			<tbody class="zwssgr-admin-enable-smtp">
				<tr valign="top">
					<th scope="row">
						<?php esc_html_e( 'From Email Address', 'smart-showcase-for-google-reviews' ); ?>
					</th>
					<td>
						<input
							id="zwssgr-from-email"
							name="zwssgr_from_email"
							type="email"
							class="zwssgr-input-text"
							required
							value="<?php echo isset( $zwssgr_smtp_option['zwssgr_from_email'] ) ? esc_attr( $zwssgr_smtp_option['zwssgr_from_email'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwssgr-from-name">
							<?php esc_html_e( 'From Name', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwssgr-from-name"
							name="zwssgr_from_name"
							type="text"
							class="zwssgr-input-text"
							value="<?php echo isset( $zwssgr_smtp_option['zwssgr_from_name'] ) ? esc_attr( $zwssgr_smtp_option['zwssgr_from_name'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwssgr-smtp-host">
							<?php esc_html_e( 'SMTP Host', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwssgr-smtp-host"
							name="zwssgr_smtp_host"
							type="text"
							class="zwssgr-input-text"
							required
							value="<?php echo isset( $zwssgr_smtp_option['zwssgr_smtp_host'] ) ? esc_attr( $zwssgr_smtp_option['zwssgr_smtp_host'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwssgr-smtp-ency-type">
							<?php esc_html_e( 'Type of Encryption', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<?php
						$zwssgr_smtp_option['zwssgr_smtp_ency_type'] = isset( $zwssgr_smtp_option['zwssgr_smtp_ency_type'] ) ? $zwssgr_smtp_option['zwssgr_smtp_ency_type'] : 'none';
						?>
						<label for="zwssgr_smtp_ency_type_1">
							<input id="zwssgr_smtp_ency_type_1" name="zwssgr_smtp_ency_type" type="radio" value="none"  <?php checked( $zwssgr_smtp_option['zwssgr_smtp_ency_type'], 'none' ); ?> ><?php esc_html_e( 'None', 'smart-showcase-for-google-reviews' ); ?>
						</label>

						<label for="zwssgr_smtp_ency_type_2">
							<input id="zwssgr_smtp_ency_type_2" name="zwssgr_smtp_ency_type" type="radio" value="ssl"  <?php checked( $zwssgr_smtp_option['zwssgr_smtp_ency_type'], 'ssl' ); ?> ><?php esc_html_e( 'SSL', 'smart-showcase-for-google-reviews' ); ?>
						</label>

						<label for="zwssgr_smtp_ency_type_3">
							<input id="zwssgr_smtp_ency_type_3" name="zwssgr_smtp_ency_type" type="radio" value="tls"  <?php checked( $zwssgr_smtp_option['zwssgr_smtp_ency_type'], 'tls' ); ?> ><?php esc_html_e( 'TLS', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwssgr-smtp-port">
							<?php esc_html_e( 'Port', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwssgr-smtp-port"
							name="zwssgr_smtp_port"
							type="number"
							class="zwssgr-input-text"
							required
							value="<?php echo isset( $zwssgr_smtp_option['zwssgr_smtp_port'] ) ? esc_attr( $zwssgr_smtp_option['zwssgr_smtp_port'] ) : 25; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwssgr-smtp-auth">
							<?php esc_html_e( 'SMTP Authentication', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<?php
						$zwssgr_smtp_option['zwssgr_smtp_auth'] = isset( $zwssgr_smtp_option['zwssgr_smtp_auth'] ) ? $zwssgr_smtp_option['zwssgr_smtp_auth'] : 'yes';
						?>
						<label for="zwssgr_smtp_auth_1">
							<input id="zwssgr_smtp_auth_1" name="zwssgr_smtp_auth" type="radio" value="no" <?php checked( $zwssgr_smtp_option['zwssgr_smtp_auth'], 'no' ); ?> ><?php esc_html_e( 'No', 'smart-showcase-for-google-reviews' ); ?>
						</label>

						<label for="zwssgr_smtp_auth_2">
							<input id="zwssgr_smtp_auth_2" name="zwssgr_smtp_auth" type="radio" value="yes" <?php checked( $zwssgr_smtp_option['zwssgr_smtp_auth'], 'yes' ); ?> ><?php esc_html_e( 'Yes', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</td>
				</tr>

				<tr valign="top" class="zwssgr-smtp-auth-enable">
					<th scope="row" valign="top">
						<label for="zwssgr-smtp-username">
							<?php esc_html_e( 'SMTP Username', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwssgr-smtp-username"
							name="zwssgr_smtp_username"
							type="text"
							class="zwssgr-input-text"
							STARTTLS
							value="<?php echo isset( $zwssgr_smtp_option['zwssgr_smtp_username'] ) ? esc_attr( $zwssgr_smtp_option['zwssgr_smtp_username'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top" class="zwssgr-smtp-auth-enable">
					<th scope="row" valign="top">
						<label for="zwssgr-smtp-password">
							<?php esc_html_e( 'SMTP Password', 'smart-showcase-for-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwssgr-smtp-password"
							name="zwssgr_smtp_password"
							type="password"
							class="zwssgr-input-text"
							required
							value="<?php echo isset( $zwssgr_smtp_option['zwssgr_smtp_password'] ) ? esc_attr( $zwssgr_smtp_option['zwssgr_smtp_password'] ) : ''; ?>"
						/>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr valign="top">
					<th scope="row" valign="top"></th>
					<td>
						<input
							name="zwssgr_smtp_submit"
							type="submit"
							class="button zwssgr-submit-btn"
							value="<?php esc_attr_e( 'Save SMTP Settings', 'smart-showcase-for-google-reviews' ); ?>"
						/>
						<?php wp_nonce_field( ZWSSGR_PLUGIN_BASENAME, '_smtp_nonce_name' ); ?>
					</td>
				</tr>
			</tbody>
			</table>
		</form>

		<?php
		if( !empty( $zwssgr_message ) )  { ?>
			<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
				<p><strong><?php echo esc_html( $zwssgr_message ); ?></strong></p>
			</div>
		<?php } ?>

		<?php if( !empty( $zwssgr_custom_error  ) )  { 
			if ( is_array( $zwssgr_custom_error ) ) { ?>
			<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible">
				<?php
				foreach( $zwssgr_custom_error  as $key=>$val) {
					echo '<p><strong>'.  esc_html( $val ) .'</strong></p>';
				}
			}
			?>
			</div>
		<?php } ?>

		<h2 class="zwssgr-page-title zwssgr-admin-enable-smtp"><?php esc_html_e( 'Test Mail', 'smart-showcase-for-google-reviews' ); ?></h2>

		<form class="zwssgr-setting-form" method="post" action="">
			<table class="form-table tooltip-table">
				<tbody class="zwssgr-admin-enable-smtp">
					<tr valign="top">
						<th scope="row" valign="top">
							<?php esc_html_e( 'To Email', 'smart-showcase-for-google-reviews' ); ?>
						</th>
						<td>
							<input
								id="zwssgr-test-to-email"
								name="zwssgr_test_to_email"
								type="email"
								class="zwssgr-input-text"
								required
								value=""
							/>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" valign="top">
							<?php esc_html_e( 'Subject', 'smart-showcase-for-google-reviews' ); ?>
						</th>
						<td>
							<input
								id="zwssgr-test-subject"
								name="zwssgr_test_subject"
								type="text"
								class="zwssgr-input-text"
								required
								value=""
							/>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" valign="top">
							<?php esc_html_e( 'Message', 'smart-showcase-for-google-reviews' ); ?>
						</th>
						<td>
							<textarea
								id="zwssgr-test-message"
								name="zwssgr_test_message"
								class="zwssgr-textarea"
								rows="5"
								required
							></textarea>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" valign="top"></th>
						<td>
							<input
								name="zwssgr_smtp_test_submit"
								type="submit"
								class="button zwssgr-submit-btn"
								value="<?php esc_attr_e( 'Send Test Email', 'smart-showcase-for-google-reviews' ); ?>"
							/>
							<?php wp_nonce_field( ZWSSGR_PLUGIN_BASENAME, '_smtptest_nonce_name' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
