<?php
// when Data Added
// Get Product Download Link
require_once('../../../../wp-load.php');
//$fp = fopen("log22.txt","a+");
		//fwrite($fp, serialize($_GET)."\r\n");
//fwrite($fp, serialize($_REQUEST)."\r\n");
$mod = $_REQUEST['mode'];
if(isset($mod) and !empty($mod)){
	switch($mod) {
		case 'add':
		//case 'modify':
			$slm_options = get_option('slm_plugin_options');
			//'secret_key' =>'582c094e396445.41301863',
			//$url = 'http://acutweb.com/nanacast/';
			$url = home_url().'/';
			$fields = array(
				'slm_action' => urlencode('slm_create_new'),
				'secret_key' =>$slm_options['lic_creation_secret'],
				'first_name' => $_REQUEST['u_firstname'],
				'last_name' => $_REQUEST['u_lastname'],
				'company_name' => $_REQUEST['item_name'],
				'email' => $_REQUEST['u_email'],
				'paypal_email' => $_REQUEST['u_paypal_email'],
				'u_access_code'=>$_REQUEST['u_access_code'], 
				'nanacast_id'=>$_REQUEST['id'],
				'txn_id' => $_REQUEST['u_paypal_trans_id'], 
				'max_allowed_domains'=>$_REQUEST['u_custom_2'],
				'lic_status'=>'active',
				'date_created'=> $_REQUEST['u_start_date']
			);
			
			//url-ify the data for the POST
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');

			//open connection
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//execute post
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
			$responseData = json_decode($result);
			//fwrite($fp, serialize($responseData)."\r\n");
			if($responseData->result=='success'){
				$uname = $_REQUEST['u_firstname'];
				
			// Send an Mail to Customer with License Key
				$licenceKey = $responseData->key;
				$downloadkey = $licenceKey.$_REQUEST['u_paypal_trans_id'];
				$downloadLink = site_url().'/wp-content/uploads/plugin-1.0.0/The-Protector.zip?key='.$downloadkey;
				global $wpdb;
				$mailtabl = $wpdb->prefix . "safe_mail_template";
				$id = 1;
				$recivemail = $wpdb->get_row("SELECT * FROM $mailtabl WHERE id = '$id'", ARRAY_A);
				$pname = $_REQUEST['item_name'];
				$amount = $_REQUEST['u_first_price'];
				$subject = $recivemail['subject'];
				$bodyContent = str_replace('[name]',$uname,$recivemail['body_content']);
				$frommail = $recivemail['from_mail'];
				$mailtem = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#fff">';
				$mailtem .='<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff"><tbody><tr><td><table align="center" width="750px" border="0" cellspacing="0" cellpadding="0" bgcolor="#557da1" style="width:750px!important"><tbody>
				<tr><td><h1 style="color:#ffffff;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:center">'.$subject.'</h1>';
				$mailtem .='</td></tr></tbody></table></td></tr>
				<tr>
					<td align="center" valign="top"><p>'.$bodyContent.'</p></td>
				</tr>
				<tr><td align="center" valign="top"><p><strong>License Key: </strong>'.$licenceKey.'</p></td>
					</tr>
				
				<tr><td align="center" valign="top"><p><strong>Download Link: </strong><a href="'.$downloadLink.'">'.$pname.'</a></p></td></tr>
				<tr>
					<td colspan="2" align="center" valign="top"><strong>Order Amount: <strong>'.$amount.'</td>
				</tr>
				<tr>
					<td colspan="2" align="center" valign="top">&nbsp;</td>
					
				</tr>
				</tbody></table>';
				$mailtem .='</div>';
				
				
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

				// Additional headers
				//fwrite($fp, 'From mail:',$frommail."\r\n");
				$headers .= 'From: NanaCast<'.$frommail.'>' . "\r\n";
				if(mail($_REQUEST['u_email'],$subject,$mailtem,$headers)){
					$successMsg = 'Email has sent successfully.';
					//fwrite($fp, $successMsg."\r\n");
				}
			}
			break;
		case 'decline':
		//Sent when an automatic subscription payment attempt has failed, but the client hasn't been cancelled yet. Subscription payments are attempted on the expiration date, then 2 days later, then 4 days later, and then for the last time 6 days after the expiration date. The first 3 attempts will send a "decline" status, but on the 4th try (6 days after the expiration date), a "suspend" notification is sent (see "suspend" below)
		// Custom Query i have now integrated 
		global $wpdb;
		$lickeytbl = $wpdb->prefix . "safe_lic_key_tbl";
		$nanacast_id = $_REQUEST['id'];
		$oldData = $wpdb->get_row("SELECT * FROM $lickeytbl WHERE nanacast_id = '$nanacast_id'", ARRAY_A);
		//echo '<pre>';
		//print_r($oldData);
		if(!empty($oldData)){
			// Update Status
			//$keyId = $oldData['id'];
			$data = array('lic_status' => 'blocked');
			$where = array('id' => $oldData['id']);
			$updated = $wpdb->update($lickeytbl, $data, $where);
			// Send Mail to Customer
			$subject = "The Protector License Key Deactivation ";
			$message = 'Hello '.$oldData['first_name'].', your license key has been blocked. Your license key: '.$oldData['license_key'];
			$frommail = 'info@acutweb.com';
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			// Additional headers
			$headers .= 'From: NanaCast<'.$frommail.'>' . "\r\n";
			mail($oldData['email'],$subject,$message,$headers);
		} 
		break;
		//
		case 'suspend';
			global $wpdb;
			$lickeytbl = $wpdb->prefix . "safe_lic_key_tbl";
			$nanacast_id = $_REQUEST['id'];
			$oldData = $wpdb->get_row("SELECT * FROM $lickeytbl WHERE nanacast_id = '$nanacast_id'", ARRAY_A);
			if(!empty($oldData)){
			// Update Status
			//$keyId = $oldData['id'];
			$data = array('lic_status' => 'expired');
			$where = array('id' => $oldData['id']);
			$updated = $wpdb->update($lickeytbl, $data, $where);
			// Send Mail to Customer
			$subject = "The Protector License Key Suspended ";
			$message = 'Hello '.$oldData['first_name'].', your license key has been suspended. Your license key: '.$oldData['license_key'];
			$frommail = 'info@acutweb.com';
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			// Additional headers
			$headers .= 'From: NanaCast<'.$frommail.'>' . "\r\n";
			mail($oldData['email'],$subject,$message,$headers);
		}
			
		break;
		case 'reactivate';
			global $wpdb;
			$lickeytbl = $wpdb->prefix . "safe_lic_key_tbl";
			$nanacast_id = $_REQUEST['id'];
			$oldData = $wpdb->get_row("SELECT * FROM $lickeytbl WHERE nanacast_id = '$nanacast_id'", ARRAY_A);
			if(!empty($oldData)){
			
				$data = array('lic_status' => 'active');
				$where = array('id' => $oldData['id']);
				$updated = $wpdb->update($lickeytbl, $data, $where);
				// Send Mail to Customer
				$subject = "The Protector License Key Reactivate ";
				$message = 'Hello '.$oldData['first_name'].', your license key has been re-activated. Your license key: '.$oldData['license_key'];
				$frommail = 'info@acutweb.com';
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				// Additional headers
				$headers .= 'From: NanaCast<'.$frommail.'>' . "\r\n";
				mail($oldData['email'],$subject,$message,$headers);
			} 
		break;
	}
}else{
	die('Soory, you can not access this page directly');
} 
 ?>