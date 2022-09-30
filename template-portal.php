<?php
/* 
*   Template Name: Portal Dashboard
*   Post Type: page
*/



// var_dump(!is_user_logged_in());
// If not login or not get membership then redirect the login page
if( !is_user_logged_in() ){
    wp_redirect(get_site_url(null, '/login/', 'https'));
    exit;
}elseif(is_user_logged_in()){
    if(get_level_id_of_current_user() === false && role_of_current_user() !== "administrator"){
        wp_redirect( home_url() );
        exit;
    }
}
get_header("portal");

// If not any Course is assigned yet to this subscription
if(empty(get_cs_ids())){
  die("<h1 style='text-align:center; color:red;' >Please Contact to the Admin to Add the Course to This Subscription!</h1>");
}


    $post = get_post( $post->ID );
    $excerpt = ( $post->post_excerpt ) ? $post->post_excerpt : "ourselves";

    global $post, $wpdb;
    // Return the all membership levels
	$membership_levels = pmpro_getAllLevels( true, true );
	$membership_levels = pmpro_sort_levels_by_order( $membership_levels );



    $query = new WP_Query(array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));



?>


<!-- work-courses -->
<section class="courses-cards">
<div class="container text-center courses-container">
    <div class="courses-heading">
        <h2 class="f-60"><?php echo $excerpt;  ?></h2>
    </div>
    <div class="row">
        <?php
        if($query->have_posts()){
            while($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                foreach(get_cs_ids() as $id){
                    // Tracking Progress
                    $page_levels = $wpdb->get_col( "SELECT membership_id FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '" . intval( $post_id ) . "'" );
                    $key = "userLevel_".$page_levels[0]."_userId_".get_current_user_Id()."_courseId_".$post_id;

                    // get courses against current subscription ids
                    if($id == $post_id){
                        ?>
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <a href="<?php echo get_site_url(null, '/portal/calendar/?cs_id='.$post_id, 'https');?>" class="course-card">
                                <div class="course-thumbnail">
                                <?php if (has_post_thumbnail( $post_id ) ): ?>
                                    <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' ); ?>
                                    <img src="<?php echo $image[0]; ?>" alt="courses image">
                                <?php endif; ?>
                                    
                                </div>
                                <div class="course-info">
                                       <div class="full-progress-info">
                                                <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo get_progress_of_course($key); ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo get_progress_of_course($key); ?>%"></div>
                                    </div>
                                    <span><?php echo get_progress_of_course($key); ?>%</span>
                                    </div>
                                    <h3 class="f-25"><?php echo get_the_title(); ?></h3>
                                </div>
                            </a>
                        </div>
                        <?php
                    }
                }

                // echo "<pre>";
                // print_r(get_level_id_of_current_user());
                //   exit;
            }
        }   
        wp_reset_query();
        ?>
    </div>
</div>
</section>

<?php echo get_footer("portal");?>