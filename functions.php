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

	wp_enqueue_style( 'portal-bootstrap-css', '//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	wp_enqueue_style( 'portal-fontawsome-css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

  
	wp_enqueue_style( 'portal-dashboard-css', get_stylesheet_directory_uri() . '/assets/css/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	wp_enqueue_style( 'portal-responsive-css', get_stylesheet_directory_uri() . '/assets/css/responsive.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

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

  return round($progressScore, 1);
}



function rd_duplicate_post_as_draft(){

    /*
   * get the original post id
   */
  $post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
  /*
   * and all the original post data then
   */
  $post = get_post( $post_id );

if ($post->post_type=='video'){
  global $wpdb;
  if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
    wp_die('No post to duplicate has been supplied!');
  }
  /*
   * Nonce verification
   */
  if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
    return;

  /*
   * if you don't want current user to be the new post author,
   * then change next couple of lines to this: $new_post_author = $post->post_author;
   */
  $current_user = wp_get_current_user();
  $new_post_author = $current_user->ID;
  /*
   * if post data exists, create the post duplicate
   */
  if (isset( $post ) && $post != null) {
    /*
     * new post data array
     */
    $args = array(
      'comment_status' => $post->comment_status,
      'ping_status'    => $post->ping_status,
      'post_author'    => $new_post_author,
      'post_content'   => $post->post_content,
      'post_excerpt'   => $post->post_excerpt,
      'post_name'      => $post->post_name,
      'post_parent'    => $post->post_parent,
      'post_password'  => $post->post_password,
      'post_status'    => 'draft',
      'post_title'     => $post->post_title,
      'post_type'      => $post->post_type,
      'to_ping'        => $post->to_ping,
      'menu_order'     => $post->menu_order
    );
    /*
     * insert the post by wp_insert_post() function
     */
    $new_post_id = wp_insert_post( $args );
    /*
     * get all current post terms ad set them to the new post draft
     */
    $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
    foreach ($taxonomies as $taxonomy) {
      $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
      wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
    }
    /*
     * duplicate all post meta just in two SQL queries
     */
    $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
    if (count($post_meta_infos)!=0) {
      $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
      foreach ($post_meta_infos as $meta_info) {
        $meta_key = $meta_info->meta_key;
        if( $meta_key == '_wp_old_slug' ) continue;
        $meta_value = addslashes($meta_info->meta_value);
        $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
      }
      $sql_query.= implode(" UNION ALL ", $sql_query_sel);
      $wpdb->query($sql_query);
    }


    // getting video url
    $videoUrl = pods("video", $post_id);

    update_post_meta($new_post_id , 'url_for_video', $videoUrl->field("url_for_video"));


    /*
     * finally, redirect to the edit post screen for the new draft
     */
    wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
}

  exit;
} else {
  wp_die('Post creation failed, could not find original post: ' . $post_id);
}
}
add_action( 'admin_action_rd_duplicate_post_as_draft', 'rd_duplicate_post_as_draft' );
/*
* Add the duplicate link to action list for post_row_actions
*/
function rd_duplicate_post_link( $actions, $post ) {
  if ($post->post_type === 'video'){
    if (current_user_can('edit_posts')) {
      $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=rd_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
    }
  }
  return $actions;
}
add_filter( 'post_row_actions', 'rd_duplicate_post_link', 10, 2 );


function add_search_form($items, $args) {
  $items .= '<li><a href="' . wc_get_cart_url() . '" style="font-size:20px;" class="misha-cart"><i class="fas fa-shopping-cart"></i><span>' . wc()->cart->get_cart_contents_count() . '</span></a></li>';

return $items;
}
add_filter('wp_nav_menu_items', 'add_search_form', 10, 2);


// Sortable Columns

// Add the custom columns to the book post type:
add_filter( 'manage_course_day_posts_columns', 'set_custom_edit_book_columns' );
function set_custom_edit_book_columns($columns) {
    $order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'asc' : 'desc';
    $columns['select_the_day'] = "Day";
    $columns['course_name'] =  '<a href="https://workoutmonkeys.com/wp-admin/edit.php?s&post_status=all&post_type=course_day&orderby=course_name&order='.$order.'"><span>Course</span><span class="sorting-indicator"></span></a>';

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

function my_sort_custom_column_query( $query ) {
  if( ! is_admin() )
      return;

  $orderby = $query->get('orderby');

  if( 'course_name' == $orderby ) {
      $query->set('meta_key','course_name');
      $query->set('orderby','meta_value'); // "meta_value_num" is used for numeric sorting
                                            // "meta_value"     is used for Alphabetically sort.
      // We can user any query params which used in WP_Query.
  }

  if(isset($_GET['course_name']) && $_GET['course_name'] != ''){
    $query->set('meta_query', array(
      array(
          'key' => 'course_name',
          'value' => $_GET['course_name'],
          'compare'   => '='
      )
    ));
  }


}


global $pagenow;

if ( is_admin() && 'edit.php' == $pagenow && 'course_day' == $_GET['post_type'] ) {
  // set query to sort
  add_action( 'pre_get_posts', 'my_sort_custom_column_query' );

}

  // Dropdown filter

add_action('restrict_manage_posts','location_filtering',10);
function location_filtering($post_type){
    if('course_day' !== $post_type){
      return; //filter your post
    }
    //get unique values of the meta field to filer by.
	$meta_key = 'course_name';
	global $wpdb;
	$results = $wpdb->get_col( 
		$wpdb->prepare( "
			SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s' 
			AND p.post_status IN ('publish', 'draft')
			ORDER BY pm.meta_value", 
			$meta_key
		) 
  );
   //build a custom dropdown list of values to filter by
    echo '<select id="my-loc" name="course_name">';
    echo '<option value="0">' . __( 'Show all Courses', 'my-custom-domain' ) . ' </option>';
    foreach($results as $location){
      $selected = '';
      $request_attr = 'course_name';
      if ( isset($_GET[$request_attr]) ) {
        $selected = $_GET[$request_attr];
      }
      $select = $selected == $location ? ' selected="selected"' : '';

      $courseName = get_the_title($location);

      echo '<option value="'.$location.'" '.$select.'>' . $courseName . ' </option>';
    }
    echo '</select>';
  }
    