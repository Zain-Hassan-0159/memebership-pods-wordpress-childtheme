<?php
/* 
*   Template Name: Portal Workout
*   Post Type: page
*/



$course_id = isset($_GET["cs_id"]) && !empty( $_GET["cs_id"]) ? $_GET["cs_id"] : "";
$course_day_id = isset($_GET["day_id"]) && !empty($_GET["day_id"]) ? $_GET["day_id"] : "";
$vid_id = isset($_GET["vid_id"]) && !empty($_GET["vid_id"]) ? $_GET["vid_id"] : "";




// If not login or not get membership then redirect the login page
if( !is_user_logged_in() || get_level_id_of_current_user() === false  ){
    if( role_of_current_user() !== "administrator" ){
      wp_redirect(get_site_url(null, '/login/', 'https'));
        exit;
    }
}
get_header("portal");

// If not any Course is assigned yet to this subscription
if(empty(get_cs_ids())){
  die("<h1 style='text-align:center; color:red;' >Please Contact to the Admin to Add the Course to This Subscription!</h1>");
}

// If Course Id is passed in the url then set transient with that id to track courses
// else Check If a transient Exist Already
// else get the level id of user and related course id and create transient
if($course_id !== ""){
  // Check if this course is in the current subscription level or just user tried to bypass another id
  if(!in_array($course_id, get_cs_ids() )){
    die("<h1 style='text-align:center; color:red;' >Please Contact to the Admin to Add the Course to This Subscription!</h1>");
    exit;
  }
  set_transient( 'recent_course_id_' . get_level_id_of_current_user() , $course_id, 60 * DAY_IN_SECONDS );
}elseif(!empty(get_transient( 'recent_course_id_' . get_level_id_of_current_user()  ))){
  $course_id = get_transient( 'recent_course_id_' . get_level_id_of_current_user()  );
  // If previous transient id is in the currently assigned level of course
  // If not assigned the first course in the course list
  // Update The Transient
  if(!in_array($course_id, get_cs_ids())){
    $course_id = get_cs_ids()[0];
    set_transient( 'recent_course_id_' . get_level_id_of_current_user(), $course_id, 60 * DAY_IN_SECONDS );
  }
}else{
  $course_id = get_cs_ids()[0];
  set_transient( 'recent_course_id_' . get_level_id_of_current_user(), $course_id, 60 * DAY_IN_SECONDS );
}

// Tracking the Latest unseen day 

if($course_day_id ===''){
  $table_name = $wpdb->prefix . "portal_days_record";
  $course_level = $wpdb->get_col( "SELECT membership_id FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '" . intval( $course_id ) . "'" );
  $key = "userLevel_".$course_level[0]."_userId_".get_current_user_Id()."_courseId_".$course_id;
  $days_records = $wpdb->get_results("SELECT days_records FROM $table_name WHERE user_key = '$key'" ); 
  $days_records = json_decode($days_records[0]->days_records, true); 
  // echo "<pre>";
  // var_dump($course_day_id); 
  // exit;
  if($days_records !== NULL){
    foreach($days_records as $day){
      if($day['completed'] === 'no'){
        $course_day_id = $day['post_id'];
        break;
      }
    }
  }
}

$day_id = "";
$params = array(
  // "select" => "t.*, d.*, t.product_id as pr_id",
  "limit" => -1,
  'orderby' => 'd.select_the_day ASC',
  'where' => "course_name.id = ". $course_id

);
$pod = pods("course_day", $params);

// default video for day one starter
$all_videos = $pod->field("videos_for_the_day");


$defaultVideoUrl = !empty($all_videos[0]) ? $all_videos[0]["url_for_video"] : "";
// Default video description
$default_description = !empty($all_videos[0]) ? $all_videos[0]["post_content"] : "";
$default_title = !empty($all_videos[0]) ? $all_videos[0]["post_title"] : "";

// Current Day All Videos
$current_day_videos = [];
$total_days = $pod->total();

if($total_days > 0 && $course_day_id !== "" ){
  while($pod->fetch()){
    $current_day = $pod->field("ID");
    if($current_day == $course_day_id){
      $current_day_videos = $pod->field("videos_for_the_day");
      $course_day_id = $current_day;
    }
  }
}

if(!empty($current_day_videos)){
  $defaultVideoUrl = $current_day_videos[0]['url_for_video'];
  $default_title = $current_day_videos[0]['post_title'];
  $default_description = $current_day_videos[0]['post_content'];
  $all_videos = $current_day_videos;
}

// Filter The Video If it is selected
if($vid_id !== ""){
  $result = array_filter($all_videos, function ($value) use ($vid_id) {
    return ($value["ID"] === $vid_id);
  });
  
  foreach($result as $key => $val){
    $defaultVideoUrl = $val['url_for_video'];
    $default_title = $val['post_title'];
    $default_description = $val['post_content'];
    unset($all_videos[$key]);

    break;
  }
}else{
  array_shift($all_videos);
}


$no_of_week = "";
$no_of_day = "";
if($course_day_id !==''){
  global $wpdb;

  // Tracking Progress
  $course_level = $wpdb->get_col( "SELECT membership_id FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '" . intval( $course_id ) . "'" );
  $key = "userLevel_".$course_level[0]."_userId_".get_current_user_Id()."_courseId_".$course_id;

  $table_name = $wpdb->prefix . "portal_days_record";
  $days_records = $wpdb->get_results("SELECT days_records FROM $table_name WHERE user_key = '$key'" ); 
  $days_records = json_decode($days_records[0]->days_records, true); 

  if(!empty($days_records)){
    foreach($days_records as $index1 => $day){

      if($day['post_id'] == $course_day_id){
        $no_of_day = $index1;
      }
    }
  }

}


if($no_of_day > 0 && $no_of_day < 8){
  $no_of_week = 1;
}elseif($no_of_day > 7){
  $no_of_week = ceil($no_of_day/7);
}




?>
      <!-- courses-videos -->
      <section class="course-videos">
        <div class="container text-center">
          <div class="courses-main-heading">
            <h2><?php echo "week ".$no_of_week." / day ".$no_of_day; ?></h2>
          </div>
          <?php 
          // echo "<pre>";
          // print_r(strtoupper(get_the_title($course_day_id)));
          // exit;
          if(strtoupper(get_the_title($course_day_id)) !== "RESTDAY" && strtoupper(get_the_title($course_day_id)) !== "REST DAY" && strtoupper(get_the_title($course_day_id)) !== "REST-DAY"){ ?>
          <div class="row">
            <div class="col-lg-8">
              <div class="course-video-section">
              <iframe width="890" height="400" src="<?php echo $defaultVideoUrl; ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <div class="video-title">
                  <h3><?php echo $default_title; ?></h3>
                  <!-- <p><span>14430 views</span><span>2 month ago</span></p> -->
                </div>
              </div>

              <div class="lecture-description">
                <?php echo get_post_field('post_content', $course_day_id); ?>
                <?php echo $default_description;  ?>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="aside-related-videos" id="style">
                <?php
                  if(count($all_videos) > 0){
                    foreach($all_videos as $video){
                      $parameters = !empty($course_day_id) ? "?day_id=".$course_day_id."&vid_id=".$video['ID'] : "?vid_id=".$video['ID'];

                      ?>
                      <a style="text-decoration: none;" href="<?php echo get_permalink(); echo $parameters;  ?>">
                        <div class="related-course-video">
                          <?php
                            $img_url = get_the_post_thumbnail_url($video['ID'], 'full');
                            if($img_url !== false){
                              ?>
                              <img src="<?php echo $img_url; ?>" alt="course-aside">
                              <?php
                            }
                          ?>
                          <div class="related-course-details">
                            <h3><?php echo $video['post_title'] ?></h3>
                            <p><?php echo $video['post_excerpt'] ?></p>
                              <!-- <span class="course-name">Bitcraftx<i class="fa-solid fa-square-check"></i></span> -->
                              <!-- <p><span>102k view</span> <span class="duration">2 month ago</span></p> -->
                          </div>
                        </div>
                      </a>
                      <?php
                    }
                  }
                ?>
              </div>
            </div>
          </div>
          <?php }else{
            ?>
            <h3><?php  echo get_the_title($course_day_id); ?></h3>
            <?php
            $img_url = get_the_post_thumbnail_url($course_day_id, 'full');
            if($img_url !== false){
              ?>
              <img src="<?php echo $img_url; ?>" alt="Rest Day">
              <?php
            }
          } ?>
          <div class="course-complete">
            <a href="<?php echo get_site_url(null, '/portal/calendar/?completed='.$course_day_id, 'https');?>" class="complete-btn">COMPLETED</a>
            <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/IIII.png" alt="">
          </div>
        </div>
      </section>

      <?php echo get_footer("portal");?>