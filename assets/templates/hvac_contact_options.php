<div class="wrap">
  <h1>HVAC Contact Options</h1>

  <form method="post" action="options.php">
    <?php settings_fields( 'hvac-contact' ); ?>
    <?php do_settings_sections( 'hvac-contact' ); ?>

    <table class="form-table">
        <tr valign="top">
        <th scope="row">Email</th>
        <td><input type="text" name="hvac_contact_admin_email" value="<?php echo esc_attr( get_option('hvac_contact_admin_email') ); ?>" /></td>
        </tr>        
    </table>
    
    <?php submit_button(); ?>

    </form>
</div>