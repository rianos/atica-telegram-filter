<?php
/*
Plugin Name: ATICA Telegram Auth Filter
Description: Allows to filter Telegram Authentication with WP Telegram login plugin enabled to a certain membership to a group
Author: Eduardo Ant&oacute;n Santa-Mar&iacute;a
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


function atica_admin_menu() {
	add_options_page(
		'ATICA Telegram Auth Filter',
		'ATICA Telegram',
		'manage_options',
		'atica-admin-settings-page',
		'admin_page'
	);
}


function atica_check_user_in_group( $data) {
	 $userid = $data["id"];
         //$chat_id = "-760160516";
	 //$chat_id = "-1001623256674";
	 $chat_id = get_option("atica_telegram_canalid");
	 $bot_token = get_option("wptelegram_login")["bot_token"];

         $res = wp_remote_get("https://api.telegram.org/bot" . $bot_token . "/getChatMember?chat_id=" . $chat_id . "&user_id=" . $userid);
         $res_name = wp_remote_get("https://api.telegram.org/bot" . $bot_token . "/getChat?chat_id=" . $chat_id);
         $chat_name = json_decode(wp_remote_retrieve_body($res_name));
         $body = wp_remote_retrieve_body($res);
         $respuesta = json_decode($body);
         if ($respuesta->ok){
                $status =  $respuesta->result->status;
                if ($status == "left" || $status == "kicked"){
                      throw new Exception(__("Usuario actualmente no perteneciente a " . $chat_name->result->title));
                }
         }
         else{
               throw new Exception(__('Usuario no perteneciente a ' . $chat_name->result->title));
         }
}

function admin_page(){
	?>
	<div class='wrap'>
	<H2>Configuraci&oacute;n de canal Telegram para Autenticaci&oacute;n</h2>
	<form method="post" action="options.php">
	<?php settings_fields('atica_telegram_settings') ?>
	<table class="form-table">
	<tr>
		<th><label for="first_field_id">Canal de Telegram:</label></th>
		<td>
		<input type="text" class="regular-text" id="first_field_id" name="atica_telegram_canalid" value="<?php echo get_option("atica_telegram_canalid"); ?>">
		</td>
	</tr>
	</table>
<?php
	submit_button();
	
	echo "</div>";
}

function atica_register_settings(){
	register_setting(
		'atica_telegram_settings',
		'atica_telegram_canalid'
	);



}

function print_canalid_field(){
	$text = get_option("atica-canalid");
	printf(
		'<input type="text" id="atica-canalid" name="atica-canalid" value="%s" />',
		esc_attr( $text)
	);
}

if ( is_admin() )
  add_action( 'admin_init', 'disable_password_fields', 10 );

function disable_password_fields() {
  if ( ! current_user_can( 'administrator' ) )
    $show_password_fields = add_filter( 'show_password_fields', '__return_false' );
}

add_action("init", "atica_register_settings");
add_action("admin_menu","atica_admin_menu");
add_action("wptelegram_login_pre_save_user_data", "atica_check_user_in_group");

?>
