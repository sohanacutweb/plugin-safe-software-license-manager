<?php
function wp_lic_mgr_mail_cont_menu() {
    
   /* $options = get_option('slm_plugin_options');
    $creation_secret_key = $options['lic_creation_secret'];
    $secret_verification_key = $options['lic_verification_secret'];
	*/
	global $wpdb;
	$mail_tmp_table = SLM_TBL_LIC_MAIL_CONTENT;
	$data = $wpdb->get_results("SELECT * FROM $mail_tmp_table ", ARRAY_A);
	//echo '<pre>';
	//print_r($data);
	$errors = '';
    ?>
    
	
	<?php
	// Update mail Content
	if (isset($_POST['save_record'])) {
		$subject = $_POST['subject'];
        $from_mail = $_POST['from_mail'];
        $body_content = $_POST['body_content'];
        $id = $_POST['edit_record'];
		if($id==''){
                $errors .= __('Invalid request !', 'slm');  
		}
		if($subject==''){
			$errors .= __('Please enter subject !', 'slm');  
		}
		if($body_content==''){
			$errors .= __('Please enter body content !', 'slm');  
		}
		$fields = array();
		$fields['subject'] = $subject;
		$fields['from_mail'] = $from_mail;
		$fields['body_content'] = $body_content;
		$where = array('id'=>$id);
            $updated = $wpdb->update($mail_tmp_table, $fields, $where);
            if($updated === false){
                //TODO - log error
                $errors .= __('Update of the license key table failed!', 'slm');
            }
		
		if(empty($errors)){
            $message = "Record successfully updated!";
            echo '<div id="message" class="updated fade"><p>';
            echo $message;
            echo '</p></div>';
        }else{
            echo '<div id="message" class="error">' . $errors . '</div>';            
        }
	}
	if(isset($_GET['edit_mail'])){
		$id = $_GET['edit_mail'];
		global $wpdb;
		$mail_tmp_table = SLM_TBL_LIC_MAIL_CONTENT;
		$recivemail = $wpdb->get_row("SELECT * FROM $mail_tmp_table WHERE id = '$id'", ARRAY_A);
		?>
		<h2>Edit Mail Content </h2>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body"> 
					<div class="postbox">
						<h3 class="hndle"><label for="title">Edit Mail Contents </label></h3>
						<div class="inside">
							<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
								<table class="form-table">
									<input name="edit_record" type="hidden" value="<?php echo $id;?>" />
									<tr valign="top">
										<th scope="row">Subject</th>
										<td><input name="subject" type="text" id="subject" value="<?php echo $recivemail['subject']; ?>" size="80" required /></td>
									</tr>
									<tr valign="top">
										<th scope="row">From Mail</th>
										<td><input name="from_mail" type="text" id="from_mail" value="<?php echo $recivemail['from_mail']; ?>" size="80" required /></td>
									</tr>
									<tr valign="top">
										<th scope="row">Mail Contents (Please don't change this :[name])</th>
										<td><?php wp_editor( $recivemail['body_content'], 'body_content',array( 'media_buttons' => false ) ); ?></td>
									</tr>
								</table>
								<div class="submit">
									<input type="submit" class="button-primary" name="save_record" value="Update" />
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php

		} else{
	?>
	
	<h2>Manage Mail Contents </h2>
    <div class="wrap">
		<div id="poststuff">
			<div id="post-body"> 
				
				<div class="lic_mgr_code">
					<table class='wp-list-table widefat fixed striped posts'>
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Subject</th>
								<th>From mail</th>
								<th>Mail Content</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								if(!empty($data)){
									foreach($data as $k=>$dval){
							?>
							<tr>
								<td><?php echo $k+1; ?></td>
								<td><?php echo $dval['subject']; ?></td>
								<td><?php echo $dval['from_mail']; ?></td>
								<td><?php echo $dval['body_content']; ?></td>
								<td><a href="?page=wp_lic_mgr_mail_cont&edit_mail=<?php echo $dval['id'];?>" />Edit </a></td>
							</tr>
							
						<?php }
							} else{ ?>
							<tr><td colspan='4'>No records found</td></tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
   </div>
<?php
	}



}


?>
