<?php
/* 
*   Template Name: Portal Login
*   Post Type: page
*/

/** Make sure that the WordPress bootstrap has run before continuing. */
//require __DIR__ . '/wp-load.php';

    if(is_user_logged_in()){
        wp_redirect(get_site_url(null, '/portal/', 'https'));
        exit;
    };
    get_header("portal");
    $img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

    function get_attachment_url_by_slug( $slug ) {
        $args = array(
          'post_type' => 'attachment',
          'name' => sanitize_title($slug),
          'posts_per_page' => 1,
          'post_status' => 'inherit',
        );
        $_header = get_posts( $args );
        $header = $_header ? array_pop($_header) : null;
        return $header ? wp_get_attachment_url($header->ID) : '';
      }


    //   var_dump(get_attachment_url_by_slug( 'mobile-login-image' ));
    //   exit;
?>
<style>
    label{
        display: none;
    }
    @media (max-width: 576px){
        .portal-section{
            <?php echo get_attachment_url_by_slug( 'mobile-login-image' ) !== "" ? "background-image:url(".get_attachment_url_by_slug( 'mobile-login-image' ).") !important" : ""; ?>
        }
    }

</style>
    <!-- login-portal-section -->
<section class="portal-section" style='<?php echo $img_url !== false ? "background-image:url(".$img_url.")" : ""; ?>' >
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-lg-6 col-sm-12 mx-auto">
                <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/IIII.png" alt="monkey" class="hanging-monkey">
                <div class="login-warrper">
                    <div class="login-form">
                        <form action="<?php echo get_site_url(null, '/wp-login.php', 'https'); ?>" method="post">
                            <div class="form-logo">
                                <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/logoForm.png" alt="workout logo">
                            </div>
                        
                            <div class="">
                                <?php 
                                $args = array(
                                    'echo'           => true,
                                    'remember'       => true,
                                    'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                                    'form_id' => 'loginform',
                                    'remember' => true,
                                    'value_username' => NULL,
                                    'value_remember' => true );
                                echo wp_login_form($args); 
                                ?>
                            </div>
                        
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

  <script>
jQuery(document).ready(function(){
    jQuery('#user_login').attr('placeholder', 'User Name');
    jQuery('#user_pass').attr('placeholder', 'User Password');
});
  </script>

 <?php //echo get_footer("portal");?>