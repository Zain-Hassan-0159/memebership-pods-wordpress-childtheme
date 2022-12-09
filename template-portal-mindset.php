<?php
/* 
*   Template Name: Portal Mindset
*   Post Type: page
*/



$course_id = isset($_GET["cs_id"]) && !empty( $_GET["cs_id"]) ? $_GET["cs_id"] : "";

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
  if(!in_array($course_id, get_cs_ids())){
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
    set_transient( 'recent_course_id_' . get_level_id_of_current_user() , $course_id, 60 * DAY_IN_SECONDS );
  }
}else{
  $course_id = get_cs_ids()[0];
  set_transient( 'recent_course_id_' . get_level_id_of_current_user() , $course_id, 60 * DAY_IN_SECONDS );
}



    $params = array(
        // "select" => "t.*, d.*, t.product_id as pr_id",
        "limit" => -1,
       // 'orderby' => 'd.select_the_day DESC',
        'where' => "t.ID = ". $course_id

    );
    $pod = pods("course", $params);
    $mindsetArray = $pod->field("select_mindset_template");
    $mindsetId = $mindsetArray["ID"];
    $mindsetPod = pods("mindset", $mindsetId);
    $mindsetPdfs = $mindsetPod->field("pdf_upload");

    // print_r($mindsetPdf);
    // exit;

?>
    <!-- meal-plan-section -->
    <section class="meal-plan">
        <div class="container text-center">
        <div class="how-to-use-plan">
            <h2><?php echo !empty($mindsetArray) ? $mindsetArray['post_title'] : ""; ?></h2>
            <div class="meal-plan-btn">
              <?php
              foreach($mindsetPdfs as $mindsetPdf){
                ?>
                <a href="<?php echo $mindsetPdf['guid']; ?>" download class="meal-plan-button"><?php echo $mindsetPdf['post_title']; ?><img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/download.png" alt="" ></a>
                <?php
              }
              ?>
            </div>
        </div>
    
        </div>
        <div class="container">
        <?php echo !empty($mindsetArray) ? $mindsetArray['post_content'] : ""; ?>
        </div>
    </section>

    <?php echo get_footer("portal");?>