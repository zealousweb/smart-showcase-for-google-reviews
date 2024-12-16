<?php

	$message = $message_smtp = '';
	$error = array();
	$zwsgr_smtp_option = get_option( 'zwsgr_smtp_option' );
	$zwsgr_general_option = get_option( 'zwsgr_general_option' );

	if ( isset( $_POST['zwsgr_smtp_test_submit'] ) ) {

		// check nounce
		if ( ! check_admin_referer( plugin_basename( __FILE__ ), '_smtptest_nonce_name' ) ) {
			$error[] =  __( 'Nonce check failed.', 'zw-smart-google-reviews' );
		}
		global $wp_version;
		if ( version_compare( $wp_version, '5.5.1', '>=' ) ) {
			// WordPress version is greater than 4.3
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			$mail = new PHPMailer\PHPMailer\PHPMailer();
            
		}  else {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			$mail = new PHPMailer( true );
		}

		$to_email = sanitize_email( $_POST['zwsgr_test_to_email'] );
		$subject = sanitize_text_field( $_POST['zwsgr_test_subject'] );
		$body = sanitize_textarea_field( $_POST['zwsgr_test_message'] );
		$ret = array();

		try {

			$charset       = get_bloginfo( 'charset' );
			$mail->CharSet = $charset;
			$from_name  = $this->zwsgr_smtp_opt['zwsgr_from_name'];
			$from_email = $this->zwsgr_smtp_opt['zwsgr_from_email'];

			$mail->IsSMTP();

			// send plain text test email
			$mail->ContentType = 'text/plain';
			$mail->IsHTML( false );

			/* If using smtp auth, set the username & password */
			if ( 'yes' === $this->zwsgr_smtp_opt['zwsgr_smtp_auth'] ) {
				$mail->SMTPAuth = true;
				$mail->Username = $this->zwsgr_smtp_opt['zwsgr_smtp_username'];
				$mail->Password = $this->zwsgr_smtp_opt['zwsgr_smtp_password'];
			}

			/* Set the SMTPSecure value, if set to none, leave this blank */
			if ( 'none' !== $this->zwsgr_smtp_opt['zwsgr_smtp_ency_type'] ) {
				$mail->SMTPSecure = $this->zwsgr_smtp_opt['zwsgr_smtp_ency_type'];
			}

			/* PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate. */
			$mail->SMTPAutoTLS = false;

			/* Set the other options */
			$mail->Host = $this->zwsgr_smtp_opt['zwsgr_smtp_host'];
			$mail->Port = $this->zwsgr_smtp_opt['zwsgr_smtp_port'];

			$mail->SetFrom( $from_email, $from_name );
			//This should set Return-Path header for servers that are not properly handling it, but needs testing first
			//$mail->Sender		 = $mail->From;
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AddAddress( $to_email );
			global $debug_msg;
			$debug_msg = '';
			$mail->Debugoutput = function ( $str, $level ) {
				global $debug_msg;
				$debug_msg .= $str.'<br>';
			};
			$mail->SMTPDebug = 1;
			//set reasonable timeout
			$mail->Timeout = 10;

			/* Send mail and return result */
			$mail->Send();
			$mail->ClearAddresses();
			$mail->ClearAllRecipients();
            //print_r($mail->ErrorInfo)
			if ( $mail->ErrorInfo != ""){
				$success = 0;
				$ret['error'] = $mail->ErrorInfo;
			} else { 
				$success = 1;
			}
		} catch ( Exception $e ) {
			$success = 0;
			$ret['error'] = $mail->ErrorInfo;
		}

		$ret['debug_log'] = $debug_msg;

		if( $success == 0 ) {
			$error[] = __( 'Error on send mail.', 'zw-smart-google-reviews' );
			$error[] = $ret['error'];
			$error[] = $ret['debug_log'];
		}

		if ( empty( $error ) ) {
			$message .= __( 'Test email was successfully sent.', 'zw-smart-google-reviews' );
		}

	}

	if ( isset( $_POST['zwsgr_smtp_submit'] ) ) {
		// check nounce
		if ( ! check_admin_referer( plugin_basename( __FILE__ ), '_smtp_nonce_name' ) ) {
			$error .= ' ' . __( 'Nonce check failed.', 'zw-smart-google-reviews' );
		}

		if ( isset( $_POST['zwsgr_from_email'] ) ) {
			if ( is_email( $_POST['zwsgr_from_email'] ) ) {
				$zwsgr_smtp_option['zwsgr_from_email'] = sanitize_email( $_POST['zwsgr_from_email'] );
			} else {
				$error .= ' ' . __( "Please enter a valid email address in the 'From Email Address' field.", 'zw-smart-google-reviews' );
			}
		}
		$zwsgr_smtp_option['zwsgr_from_name'] = sanitize_text_field( $_POST['zwsgr_from_name'] );
		$zwsgr_smtp_option['zwsgr_smtp_host'] = stripslashes( $_POST['zwsgr_smtp_host'] );
		$zwsgr_smtp_option['zwsgr_smtp_ency_type'] = isset( $_POST['zwsgr_smtp_ency_type'] ) ? sanitize_text_field($_POST['zwsgr_smtp_ency_type']) : 'none';
		$zwsgr_smtp_option['zwsgr_smtp_auth'] = isset( $_POST['zwsgr_smtp_auth'] ) ? sanitize_text_field($_POST['zwsgr_smtp_auth']) : 'no';


		$zwsgr_smtp_option['zwsgr_smtp_port']	= '25';
		/* Check value from "SMTP port" option */
		if ( isset( $_POST['zwsgr_smtp_port'] ) ) {
			if ( empty( $_POST['zwsgr_smtp_port'] ) || 1 > intval( $_POST['zwsgr_smtp_port'] ) || ( ! preg_match( '/^\d+$/', $_POST['zwsgr_smtp_port'] ) ) ) {
				$zwsgr_smtp_option['zwsgr_smtp_port']	= '25';
				$error                                   .= ' ' . __( "Please enter a valid port in the 'SMTP Port' field.", 'zw-smart-google-reviews' );
			} else {
				$zwsgr_smtp_option['zwsgr_smtp_port'] = sanitize_text_field( $_POST['zwsgr_smtp_port'] );
			}
		}
		$zwsgr_smtp_option['zwsgr_smtp_username']       = stripslashes( $_POST['zwsgr_smtp_username'] );
		$zwsgr_smtp_option['zwsgr_smtp_password'] = sanitize_text_field($_POST['zwsgr_smtp_password']);

		/* Update settings in the database */
		if ( empty( $error ) ) {
			update_option( 'zwsgr_smtp_option', $zwsgr_smtp_option );
			$message_smtp .= __( 'SMTP Settings saved.', 'zw-smart-google-reviews' );
		} else {
			$error .= ' ' . __( 'SMTP Settings are not saved.', 'zw-smart-google-reviews' );
		}

	}
    if( !empty( $message_smtp ) )  { ?>
		<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
			<p><strong><?php echo esc_html( $message_smtp ); ?></strong></p>
		</div>
		<?php } ?>

		<form autocomplete="off" class="zwsgr-setting-form" method="post" action="">
            <table class="form-table">
				<tbody>

				<tr valign="top">
					<th scope="row">
						<?php _e( 'From Email Address', 'zw-smart-google-reviews' ); ?>
					</th>
					<td>
						<input
							id="zwsgr-from-email"
							name="zwsgr_from_email"
							type="email"
							class="zwsgr-input-text"
							required
							value="<?php echo isset( $zwsgr_smtp_option['zwsgr_from_email'] ) ? esc_attr( $zwsgr_smtp_option['zwsgr_from_email'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwsgr-from-name">
							<?php _e( 'From Name', 'zw-smart-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwsgr-from-name"
							name="zwsgr_from_name"
							type="text"
							class="zwsgr-input-text"
							value="<?php echo isset( $zwsgr_smtp_option['zwsgr_from_name'] ) ? esc_attr( $zwsgr_smtp_option['zwsgr_from_name'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwsgr-smtp-host">
							<?php _e( 'SMTP Host', 'zw-smart-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwsgr-smtp-host"
							name="zwsgr_smtp_host"
							type="text"
							class="zwsgr-input-text"
							required
							value="<?php echo isset( $zwsgr_smtp_option['zwsgr_smtp_host'] ) ? esc_attr( $zwsgr_smtp_option['zwsgr_smtp_host'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwsgr-smtp-ency-type">
							<?php _e( 'Type of Encryption', 'zw-smart-google-reviews' ); ?>
						</label>
					</th>
					<td>
					<?php
					$zwsgr_smtp_option['zwsgr_smtp_ency_type'] = isset( $zwsgr_smtp_option['zwsgr_smtp_ency_type'] ) ? $zwsgr_smtp_option['zwsgr_smtp_ency_type'] : 'none';
					?>
						<label for="zwsgr_smtp_ency_type_1">
							<input id="zwsgr_smtp_ency_type_1" name="zwsgr_smtp_ency_type" type="radio" value="none"  <?php checked( $zwsgr_smtp_option['zwsgr_smtp_ency_type'], 'none' ); ?> ><?php _e( 'None', 'zw-smart-google-reviews' ); ?>
						</label>

						<label for="zwsgr_smtp_ency_type_2">
							<input id="zwsgr_smtp_ency_type_2" name="zwsgr_smtp_ency_type" type="radio" value="ssl"  <?php checked( $zwsgr_smtp_option['zwsgr_smtp_ency_type'], 'ssl' ); ?> ><?php _e( 'SSL', 'zw-smart-google-reviews' ); ?>
						</label>

						<label for="zwsgr_smtp_ency_type_3">
							<input id="zwsgr_smtp_ency_type_3" name="zwsgr_smtp_ency_type" type="radio" value="tls"  <?php checked( $zwsgr_smtp_option['zwsgr_smtp_ency_type'], 'tls' ); ?> ><?php _e( 'TLS', 'zw-smart-google-reviews' ); ?>
						</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwsgr-smtp-port">
							<?php _e( 'Port', 'zw-smart-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwsgr-smtp-port"
							name="zwsgr_smtp_port"
							type="text"
							class="zwsgr-input-text"
							required
							value="<?php echo isset( $zwsgr_smtp_option['zwsgr_smtp_port'] ) ? esc_attr( $zwsgr_smtp_option['zwsgr_smtp_port'] ) : 25; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="zwsgr-smtp-auth">
							<?php _e( 'SMTP Authentication', 'zw-smart-google-reviews' ); ?>
						</label>
					</th>
					<td>
					<?php
					$zwsgr_smtp_option['zwsgr_smtp_auth'] = isset( $zwsgr_smtp_option['zwsgr_smtp_auth'] ) ? $zwsgr_smtp_option['zwsgr_smtp_auth'] : 'yes';
					?>
						<label for="zwsgr_smtp_auth_1">
							<input id="zwsgr_smtp_auth_1" name="zwsgr_smtp_auth" type="radio" value="no" <?php checked( $zwsgr_smtp_option['zwsgr_smtp_auth'], 'no' ); ?> ><?php _e( 'No', 'zw-smart-google-reviews' ); ?>
						</label>

						<label for="zwsgr_smtp_auth_2">
							<input id="zwsgr_smtp_auth_2" name="zwsgr_smtp_auth" type="radio" value="yes" <?php checked( $zwsgr_smtp_option['zwsgr_smtp_auth'], 'yes' ); ?> ><?php _e( 'Yes', 'zw-smart-google-reviews' ); ?>
						</label>
					</td>
				</tr>

				<tr valign="top" class="zwsgr-smtp-auth-enable">
					<th scope="row" valign="top">
						<label for="zwsgr-smtp-username">
							<?php _e( 'SMTP Username', 'zw-smart-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwsgr-smtp-username"
							name="zwsgr_smtp_username"
							type="text"
							class="zwsgr-input-text"
							STARTTLS
							value="<?php echo isset( $zwsgr_smtp_option['zwsgr_smtp_username'] ) ? esc_attr( $zwsgr_smtp_option['zwsgr_smtp_username'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top" class="zwsgr-smtp-auth-enable">
					<th scope="row" valign="top">
						<label for="zwsgr-smtp-password">
							<?php _e( 'SMTP Password', 'zw-smart-google-reviews' ); ?>
						</label>
					</th>
					<td>
						<input
							id="zwsgr-smtp-password"
							name="zwsgr_smtp_password"
							type="password"
							class="zwsgr-input-text"
							required
							value="<?php echo isset( $zwsgr_smtp_option['zwsgr_smtp_password'] ) ? esc_attr_e( $zwsgr_smtp_option['zwsgr_smtp_password'] ) : ''; ?>"
						/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" valign="top">
					</th>
					<td>
						<input
							name="zwsgr_smtp_submit"
							type="submit"
							class="button zwsgr-submit-btn"
							value="<?php _e( 'Save SMTP Settings', 'zw-smart-google-reviews' ); ?>"
						/>
						<?php wp_nonce_field( plugin_basename( __FILE__ ), '_smtp_nonce_name' ); ?>
					</td>
				</tr>

				</tbody>
			</table>
		</form>

	<?php
    //} //elseif ( $current == 'test-mail' ) { ?>
		<?php if( !empty( $message ) )  { ?>
		<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
			<p><strong><?php echo esc_html( $message ); ?></strong></p>
		</div>
		<?php } ?>

		<?php if( !empty( $error ) )  { ?>
		<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible">
			<?php
			foreach( $error as $key=>$val) {
				echo '<p><strong>'.  ( $val ) .'</strong></p>';
			}
			?>
		</div>
		<?php } ?>

		<h2 class="zwsgr-h2"><?php _e( 'Test Mail', 'zw-smart-google-reviews' ); ?></h2>

		<form class="zwsgr-setting-form" method="post" action="">
			<table class="form-table tooltip-table">
				<tbody>

					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'To Email', 'zw-smart-google-reviews' ); ?>
						</th>
						<td>
							<input
								id="zwsgr-test-to-email"
								name="zwsgr_test_to_email"
								type="email"
								class="zwsgr-input-text"
								required
								value=""
							/>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Subject', 'zw-smart-google-reviews' ); ?>
						</th>
						<td>
							<input
								id="zwsgr-test-subject"
								name="zwsgr_test_subject"
								type="text"
								class="zwsgr-input-text"
								required
								value=""
							/>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Message', 'zw-smart-google-reviews' ); ?>
						</th>
						<td>
							<textarea
								id="zwsgr-test-message"
								name="zwsgr_test_message"
								class="zwsgr-textarea"
								rows="5"
								required
							></textarea>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" valign="top">
						</th>
						<td>
							<input
								name="zwsgr_smtp_test_submit"
								type="submit"
								class="button zwsgr-submit-btn"
								value="<?php _e( 'Send Test Email', 'zw-smart-google-reviews' ); ?>"
							/>
							<?php wp_nonce_field( plugin_basename( __FILE__ ), '_smtptest_nonce_name' ); ?>
						</td>
					</tr>

				</tbody>
			</table>
		</form>
<?php