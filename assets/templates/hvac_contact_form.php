<?php
/**
 * The template for hvac contact form.
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

$response = "";

//response messages
$not_human       = "Human verification incorrect.";
$missing_content = "Please supply all information.";
$email_invalid   = "Email Address Invalid.";
$message_unsent  = "Message was not sent. Try Again.";
$message_sent    = "Thanks! Your message has been sent.";
 
//user posted variables
$name = esc_sql(sanitize_text_field($_POST['message_name']));
$email = esc_sql(sanitize_text_field(sanitize_email($_POST['message_email'])));
$message = esc_sql(sanitize_text_field($_POST['message_text']));
$phone = esc_sql(sanitize_text_field($_POST['message_phone']));

$human = $_POST['message_human'];

//php mailer variables
$to = get_option('hvac_contact_admin_email');
$subject = "Someone sent a message from " . get_bloginfo('name');
$headers = 'From: '. $name . "\r\n" .
  'Reply-To: ' . $email . "\r\n";

//function to generate response
function my_contact_form_generate_response($type, $message) {
  global $response;

  if( $type == "success" ) {
    $response = "<div class='success'>{$message}</div>";
  } 
  else {
    $response = "<div class='error'>{$message}</div>";
  }
}

// On send - works but prob not best practice https://codex.wordpress.org/Plugin_API/Action_Reference/admin_post_(action)
if (!empty($_POST) && !empty($_POST['g-recaptcha-response'])) {
  //validate email
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    my_contact_form_generate_response("error", $email_invalid);
  }
  else {
    //validate presence of name and message
    if(empty($message || empty($email))) {
      my_contact_form_generate_response("error", $missing_content);
    }
    else {
      $sent = wp_mail($to, $subject, strip_tags($message), $headers);
      
      hvac_contact_create_entry($name, $email, $message, $phone);

      if($sent) {
        my_contact_form_generate_response("success", $message_sent); //message sent!
      } 
      else {
        my_contact_form_generate_response("error", $message_unsent); //message wasn't sent
      } 
    }
  }
}
else {
    if ( empty($_POST['g-recaptcha-response']) ) {
      // Recaptcha fail
      my_contact_form_generate_response("error", 'Are you a robot?');
    }
}

function hvac_contact_create_entry($name, $email, $message, $phone) {
  global $wpdb;
  $table_name = $wpdb->prefix . "hvac_contact";

  $wpdb->insert(
		$table_name, 
		array( 
			'name' => $name,
			'email' => $email,
      'message' => $message,
      'phone' => $phone,
			'time' => current_time( 'mysql' )
		) 
	);
}

get_header(); 

?>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="wrap">
  <div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
      <style type="text/css">
      .error{
        padding: 5px 9px;
        border: 1px solid red;
        color: red;
        border-radius: 3px;
      }

      .success{
        padding: 5px 9px;
        border: 1px solid green;
        color: green;
        border-radius: 3px;
      }

      form span{
        color: red;
      }
      </style>

      <div id="respond">
        <?php echo $response; ?>
        <form action="<?php the_permalink(); ?>" method="post">
          <p><label for="name">Name: <span>*</span> <br><input type="text" name="message_name" value="<?php echo esc_attr($_POST['message_name']); ?>"></label></p>
          <p><label for="message_email">Email: <span>*</span> <br><input type="text" name="message_email" value="<?php echo esc_attr($_POST['message_email']); ?>"></label></p>
          <p><label for="message_phone">Phone: <span>*</span> <br><input type="text" name="message_phone"></label></p>
          <p><label for="message_text">Message: <span>*</span> <br><textarea type="text" name="message_text"><?php echo esc_textarea($_POST['message_text']); ?></textarea></label></p>
          <div class="g-recaptcha" data-sitekey="6LcoSHIUAAAAADAHcuRJosjiW9WDkf0xsrrqNNxs"></div>
          <input type="hidden" name="submitted" value="1">
          <p><input type="submit"></p>
        </form>
      </div>
    </main>
  </div>
</div>

<?php get_footer();
