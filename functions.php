<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	wp_enqueue_style( 'portal-dashboard-css', get_stylesheet_directory_uri() . '/assets/css/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	wp_enqueue_style( 'portal-responsive-css', get_stylesheet_directory_uri() . '/assets/css/responsive.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	wp_enqueue_style( 'portal-bootstrap-css', '//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	wp_enqueue_style( 'portal-fontawsome-css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	// if(
    //     is_page_template( 
    //         array( 'template-portal.php', 'template-portal-login.php', 'template-portal-workout.php', 'template-portal-nutrition.php', 'template-portal-calendar.php' )
    //         )
    //     ){
	// 	wp_dequeue_style( 'astra-theme-css' );
	// 	wp_dequeue_style( 'astra-google-fonts' );
	// 	wp_dequeue_style( 'astra-menu-animation' );
	// 	wp_dequeue_style( 'wc-blocks-vendors-style' );
	// 	wp_dequeue_style( 'wc-blocks-style' );
	// 	wp_dequeue_style( 'global-styles-inline' );
	// 	wp_dequeue_style( 'woocommerce-layout' );
	// 	wp_dequeue_style( 'woocommerce-smallscreen' );
	// 	wp_dequeue_style( 'woocommerce-general' );
	// 	wp_dequeue_style( 'woocommerce-general-inline' );
	// 	wp_dequeue_style( 'powerpack-frontend' );

	// }

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
add_post_type_support( 'page', 'excerpt' );


 
// Add the custom columns to the book post type:
add_filter( 'manage_course_day_posts_columns', 'set_custom_edit_book_columns' );
function set_custom_edit_book_columns($columns) {
    $columns['select_the_day'] = "Day";
    $columns['course_name'] =   "Course";

    return $columns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_course_day_posts_custom_column' , 'custom_book_column', 10, 2 );
function custom_book_column( $column, $post_id ) {
    switch ( $column ) {

        case 'select_the_day' :
            $pod =  pods("course_day", $post_id);
            echo  $pod->field("select_the_day");
            break;

        case 'course_name' :
            $pod = pods("course_day", $post_id);
            echo $pod->field("course_name")["post_title"];echo " "; echo $pod->field("course_name")["ID"];
            break;

    }
}

function get_level_id_of_current_user()
{
    global $wpdb;
    $current_user = wp_get_current_user();
    if(!empty($current_user->membership_levels)){
        $subscription_array = $current_user->membership_levels;
        $subscription_id = "";
        foreach($subscription_array as $key => $sub){
            $subscription_id .= $sub->ID."-";
        }
        return trim(preg_replace("![^a-z0-9]+!i", "-", $subscription_id), '-');
    }
    return false;
}

function role_of_current_user()
{
    global $wpdb;
    $current_user = wp_get_current_user();
    $current_user_role = $current_user->roles;
    if(in_array("administrator",$current_user_role)){
        return "administrator";
    }
}
// Getting all the course ids that are assigned to current subscription of the user
function get_cs_ids()
{
	global $post, $wpdb;
    $level_ids_of_user = explode("-",get_level_id_of_current_user());
    $page_ids = [];
    foreach($level_ids_of_user as $lvl_id){
        $page_id = $wpdb->get_col( "SELECT page_id FROM {$wpdb->pmpro_memberships_pages} WHERE membership_id = '" . intval( $lvl_id ) . "'" );
        $page_ids = array_merge($page_ids, $page_id);
    }
   // return $page_ids;

	$assigned_cs_ids = [];
	if(!empty($page_ids)){
	  foreach($page_ids as $id){
		if(get_post_type($id) === 'course'){
		  $assigned_cs_ids[] = $id;
		}
	  }
	}
	return $assigned_cs_ids;
}

function get_progress_of_course($key){
  global $wpdb;
  $table_name = $wpdb->prefix . "portal_days_record";

  $noOfCompleteDays = [];
  $progressScore = "";
  
  $days_records = $wpdb->get_results("SELECT days_records FROM $table_name WHERE user_key = '$key'" ); 
  $days_records = json_decode($days_records[0]->days_records, true); 

  
  
  foreach($days_records as $complete){
      if($complete['completed'] === "yes"){
          $noOfCompleteDays[] = $complete;
      }
  }
  if(!empty($noOfCompleteDays)){
      $progressScore = count($noOfCompleteDays)/count($days_records);
      $progressScore = $progressScore * 100;
  }else{
      $progressScore = 0;
  }

  return round($progressScore, 2);
}


