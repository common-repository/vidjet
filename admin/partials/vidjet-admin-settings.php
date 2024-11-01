<?php

if (!defined('ABSPATH')) {
    exit;
}

$settings_key = 'vidjet_settings';

$options = get_option($settings_key);
$site_id = esc_html($options['site_id']);
$auth_token = esc_html($options['auth_token']);

if ( $error=filter_input( INPUT_GET,'error' )  ) {
        $error == 500 ? $error_message = 'Please verify your keys' : $error_message = 'Please contact admin'; 
        add_settings_error('settings_error', '', $error_message, 'error');
        settings_errors( 'settings_error' );
}

if(!empty($site_id) && !empty($auth_token) ){
    ?>  
    <form name="vidjet-settings" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="integrate_account" />

        <?php
        do_settings_sections($settings_key);
        
        ?>
          <input type="submit" value="integrate the account">

    </form>
    <?php
    ?>
    <form name="vidjet-settings" method="POST" action="options.php">
          <p class="submit">
            <a href="https://app.vidjet.io/login?authToken=<?php echo esc_html($options['auth_token']) ?>" style="margin-left: 10px;">
                <?php echo esc_html__('Go to app.vidjet.io', 'vidjet') ?>
            </a>
        </p>
    </form>
    <?php

}else{
    ?>
    
    <form name="vidjet-settings" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="integrate_account" />

        <?php
       // settings_fields($settings_key);
        do_settings_sections($settings_key);
        
        ?>
          <input type="submit" value="integrate the account">

    </form>
   
    <h4>create an account</h4>
    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
    <input type="hidden" name="action" value="create_account" />
    <input type="submit" value="Create an account">
   
          

    </form>
  
   
<?php
 
 }
    
   
   

