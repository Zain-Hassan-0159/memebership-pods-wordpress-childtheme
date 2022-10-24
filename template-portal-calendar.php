<?php
/* 
*   Template Name: Portal Calendar
*   Post Type: page
*/


$course_id = isset($_GET["cs_id"]) && !empty( $_GET["cs_id"]) ? $_GET["cs_id"] : "";
$complete_day_id = isset($_GET["completed"]) && !empty( $_GET["completed"]) ? $_GET["completed"] : "";



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




// // output the title
// echo esc_html($options_r['title']);
  
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


// check Reset Variable for Calendar
if(isset($_GET["res_cal"]) && $_GET["res_cal"] !== ""){
    if($_GET["res_cal"] === 'ok'){
        global $wpdb;
        $course_level = $wpdb->get_col( "SELECT membership_id FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '" . intval( $course_id ) . "'" );

        $key = "userLevel_".$course_level[0]."_userId_".get_current_user_Id()."_courseId_".$course_id;
        $table_name = $wpdb->prefix . "portal_days_record";
        // check if key exist already it has the empty data
        // $checkKey = $wpdb->get_results("SELECT user_key FROM $table_name WHERE user_key = '$key'" );
        

        // $days_records = $wpdb->get_results("SELECT days_records FROM $table_name WHERE user_key = '$key'" ); 
        // $days_records = json_decode($days_records[0]->days_records); 
        $wpdb->delete( $table_name, array( 'user_key' => $key ) );

        // print_r($checkKey);
        // exit;
    }
}


$params = array(
    // "select" => "t.*, d.*, t.product_id as pr_id",
    "limit" => -1,
    'orderby' => 'length(d.select_the_day), d.select_the_day ASC',
    'where' => "course_name.id = ". $course_id

);
$pod = pods("course_day", $params);

// while($pod->fetch()){
//     echo $pod->field("ID");
//     echo " , ";
//     echo $pod->field("select_the_day");
//     echo "<br>";
     
// }
// exit;


// Total Days ( issue to solve: We need only the days of specific course )
$total_days = $pod->total();

// Total Weeks
$total_weeks = intval( $total_days/7 );
// Weeks structured data
$weeks_data = [];
$track_days = [];
if($total_days > 0){
    
    $week_data = [];
    $day_data = [];
    $inner_n = 0;
    $no_of_week = 0;
    $videos = [];
    // $count = 0;

    while($pod->fetch()) {
        // tracking total days of the course
        $track_days[$pod->field("select_the_day")]['completed'] = "no";
        $track_days[$pod->field("select_the_day")]['post_id'] = $pod->field("ID");

        // // testing
        // $videos[] = $pod->field("ID");
        // $count++;


        // $inner_n is used for number tracking of the post in loop
        $inner_n++;
        // If total no of days of the course are less than or equal to 7
        if( $total_days < 8 ){
            // putting the current post data into day data array
            

            $day_data['post_id'] = $pod->field("ID");
            $day_data['no_of_day'] = $pod->field("select_the_day");
            $day_data['day_title'] = $pod->field("post_title");
            $day_data['image_for_mobile_screen'] = $pod->field("image_for_mobile_screen");
            // putting the day array into the weeks array
            $week_data[] = $day_data;

            // Resteting the day data array to get the other day data for next iteration
            $day_data = [];
            // If it is last day of the course
            if($inner_n == $total_days){
                // puttng this week data into weeks array
                $weeks_data[] = $week_data;
                // Resteting the week data array
                $week_data = [];
            }
        }else{
            // grab the current day data and put into week data array
            $day_data['post_id'] = $pod->field("ID");
            $day_data['no_of_day'] = $pod->field("select_the_day");
            $day_data['day_title'] = $pod->field("post_title");
            $day_data['image_for_mobile_screen'] = $pod->field("image_for_mobile_screen");
            $week_data[] = $day_data;
            // Reset the day data array
            $day_data = [];

            // if current week is equal to last week and
            // It is last day of this week
            $current_day = $inner_n === 7 ? 0 : $inner_n;
            if( $no_of_week === $total_weeks && $total_days % 7 === $current_day  ){
                $weeks_data[] = $week_data;
                $week_data = [];
                break;
            }

            // If current no is greater than week
            // collecting the week data into weeks array
            // resteting the week array
            // resteting the current no to 0
            // Increment the no of week
            if($inner_n === 7){
                $weeks_data[] = $week_data;
                $week_data = [];
                $inner_n = 0;
                $no_of_week++;
            }
        }
    }
    
    
//     echo "<pre>";
//     var_dump($count);
//     echo "zain";
//    // print_r($videos);
//     exit;

} 



// getting pdf
$coursePod = pods("course", $course_id);
$coursePdfs = $coursePod->field("course_pdf");

// tracking days
$allDaysRecord = $track_days;

// Tracking Progress
$course_level = $wpdb->get_col( "SELECT membership_id FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '" . intval( $course_id ) . "'" );
$key = "userLevel_".$course_level[0]."_userId_".get_current_user_Id()."_courseId_".$course_id;


global $wpdb;
$table_name = $wpdb->prefix . "portal_days_record";

// check if key exist already it has the empty data
$checkKey = $wpdb->get_results("SELECT user_key FROM $table_name WHERE user_key = '$key'" ); 
if(empty($checkKey)){
    // Insert the items
    $wpdb->insert($table_name, array("user_key" => $key, "days_records" => json_encode($allDaysRecord)));
}
// check if key exist already it has the data but new days records is not added yet
if(!empty($checkKey)){
    $days_records = $wpdb->get_results("SELECT days_records FROM $table_name WHERE user_key = '$key'" ); 
    $days_records = json_decode($days_records[0]->days_records); 
    
    // echo "<pre>";
    // print_r($days_records);
    // exit;
   if(count(json_decode(json_encode($days_records), true)) < count($allDaysRecord)){
        $updatedRecord = [];
        foreach($allDaysRecord as $index => $item){
            $comp_val = "";
            foreach($days_records as $inneritem){
                if($item['post_id'] === $inneritem->post_id){
                    $comp_val = $inneritem->completed;
                }
            }
            $updatedRecord[$index]["completed"] = $comp_val === "" ? $item['completed'] : $comp_val;
            $updatedRecord[$index]["post_id"]   = $item['post_id'];
        }

        // update
        $updated = $wpdb->update($table_name, array("days_records" => json_encode($updatedRecord)), array('user_key'=>$key));
   }
}


if($complete_day_id !== ""){

    $days_records = $wpdb->get_results("SELECT days_records FROM $table_name WHERE user_key = '$key'" ); 
    $days_records = json_decode($days_records[0]->days_records); 

     $updatedRecord = [];
    foreach($days_records as $index => $item){
        $updatedRecord[$index]["completed"] = $item->post_id == $complete_day_id ? "yes" : $item->completed;
        $updatedRecord[$index]["post_id"]   = $item->post_id;
    }
    // update_option($key, $updatedRecord); 
    $updated = $wpdb->update($table_name, array("days_records" => json_encode($updatedRecord)), array('user_key'=>$key));
}

  
    $days_records = $wpdb->get_results("SELECT days_records FROM $table_name WHERE user_key = '$key'" ); 
    $days_records = json_decode($days_records[0]->days_records, true); 
   // $days_records = json_decode(json_encode($days_records), true);



?>

    <!-- workout-calender-shedule -->
    <section class="workout-shedule">
        <div class="container text-center">
            <div class="workout-heading">
                <h2 class="f-60 resp-20"><?php echo get_the_title($course_id) ?></h2>
            </div>
        </div>
        <div class="shedule-header">
            <div class="container">
                <div class="workout-progressbar">
                    <div class="progressbar-container">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo get_progress_of_course($key); ?>"
                            aria-valuemin="0" aria-valuemax="100" style="width:<?php echo get_progress_of_course($key); ?>%">
                                
                            </div>
                        </div>
                        <span><?php echo get_progress_of_course($key); ?>%</span>
                    </div>
                    <div class="workout-buttons"> 
                        <div class="row">
                            <div class="col-md-4 offset-md-4 offset-3 col-6">
                                <div class="calender-month text-center">
                                    <?php

                                    ?>
                                    <i id="previous_month_button" class="fa-solid fa-chevron-left" onClick="previousMonth(event)"></i>
                                    <h3 id="current_month_button" class="f-40" data-current-month="1" data-total-months="<?php echo count($weeks_data) > 4 ? ceil(count($weeks_data)/4) : 1 ?>">MONTH # 1</h3>
                                    <i id="next_month_button" class="fa-solid fa-chevron-right" onClick="nextMonth(event)"></i>
                                </div>
                            </div>
                            <div class="col-md-4 col-4">
                            </div>
                        </div>
                    </div>

                    <div class="mobile-calender">
                    <?php
                    foreach($weeks_data as $key => $week){
                        $month =  $key+1 > 4 ? " month_".ceil(($key+1)/4) : " month_1";
                        // return $week['image_for_mobile_screen'] null

                        ?>
                        <ul class="days week-shedule weekId_<?php echo $key+1; echo " d-none "; echo $month; echo ($key+1)%4 === 0 ? " last_week " : ""; ?>"> 
                        <?php
                            foreach($week as $day){
                                $workCompleted = $days_records[$day['no_of_day']]['completed'] === "yes" ? "workout-completed" : "";
                                ?>
                                <li class="<?php echo $workCompleted; ?>">
                                    <a href="<?php echo get_site_url(null, '/portal/workout/?day_id='.$day['post_id'], 'https');?>" >
                                        <?php echo $day['no_of_day'];
                                         ?>
                                        
                                    </a>
                                </li>
                                <?php
                            }
                            ?> 
                        </ul> 
                        <?php
                    }
                    ?>    
                    </div>
                </div>
            </div>
            
        </div>
        <div class="workout-calender">
            <?php
            foreach($weeks_data as $key => $week){
                $month =  $key+1 > 4 ? " month_".ceil(($key+1)/4) : " month_1";
                ?>
                <div class="week-shedule weekId_<?php echo $key+1; echo " d-none "; echo $month; ?>">
                    <span class="workout-week">WEEK <?php echo $key+1 ?></span>
                    <div class="days-shedule">
                        <?php
                        foreach($week as $day){
                            $workCompleted = $days_records[$day['no_of_day']]['completed'] === "yes" ? "workout-completed" : "";
                            $mobile_image = $day['image_for_mobile_screen'] ? $day['image_for_mobile_screen']['guid'] : "";
                            ?>
                            <a  href="<?php echo get_site_url(null, '/portal/workout/?day_id='.$day['post_id'], 'https');?>">
                                <div class="workout-day large_screen workout-active <?php echo $workCompleted; echo has_post_thumbnail($day['post_id']) ? ' incomplete' : ''; ?>" style="background-image: url(<?php echo has_post_thumbnail( $day['post_id'] ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $day['post_id'] ), 'thumbnail' )[0] : ""; ?>);"  >
                                    <span class="f-40 resp-20"><?php echo $day['no_of_day']; ?></span>
                                    <h3 class="f-25"><?php echo $day['day_title']; ?></h3>
                                </div>
                                <div class="workout-day small_screen workout-active <?php echo $workCompleted; echo has_post_thumbnail($day['post_id']) ? ' incomplete' : ''; ?>" style="display:none; background-image: url(<?php echo $mobile_image; ?>);" >
                                    <span class="f-40 resp-20"><?php echo $day['no_of_day']; ?></span>
                                    <h3 class="f-25"><?php echo $day['day_title']; ?></h3>
                                </div>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="container">
                <div class="cotainer-flex">
                    <div class="flex-box">
                        <?php
                            foreach($coursePdfs as $coursePdf){
                                ?>
                                <a class="pdf-btn" href="<?php echo  $coursePdf["guid"]; ?>" download >
                                    <button class="pdf-button"><?php echo $coursePdf["post_title"]; ?></button>
                                    <div class="download-icon">
                                        <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/download.png" alt="" >
                                    </div>
                                </a>
                                <?php
                            }
                        ?>
                    </div>
                    <form class="reset_button" action="" method="get">
                        <input type="hidden" name="res_cal" value="ok">
                        <input class="submit_button" type="submit" value="Reset Calendar">
                    </form>
                </div>
            </div>
        </div>
        
            <?php
             
                $pod = pods("course", $course_id);
                $bg_special = $pod->field("background_image_for_course_days");
                $bg_special = $bg_special['guid'];

            ?>
        <div class="workout-plan" style='<?php echo $bg_special !== NULL ? "background-image:url(".$bg_special.")" : ""; ?>'>
            <div class="container">
            <?php echo get_post_field('post_content', $course_id); ?>
            </div>
        </div>
    </section>
    <script>
         document.querySelectorAll(".month_1").forEach(item=>{
            item.classList.remove("d-none");
        });

        function nextMonth(event){

            var total_months = document.querySelector("#current_month_button").getAttribute("data-total-months");
            var current_month = document.querySelector("#current_month_button").getAttribute("data-current-month");
            var total_months = parseInt(total_months);
            var current_month = parseInt(current_month);
            
            if(current_month < total_months){
                document.querySelectorAll(".week-shedule").forEach(item=>{
                    item.classList.add("d-none");
                });
                document.querySelectorAll(".month_" + (current_month + 1)).forEach(item=>{
                    item.classList.remove("d-none");
                });
                document.querySelector("#current_month_button").setAttribute('data-current-month', current_month+1);
                document.querySelector("#current_month_button").innerHTML = "MONTH # "+ (current_month+1);
            }

        }

        function previousMonth(event){

            var total_months = document.querySelector("#current_month_button").getAttribute("data-total-months");
            var current_month = document.querySelector("#current_month_button").getAttribute("data-current-month");
            var total_months = parseInt(total_months);
            var current_month = parseInt(current_month);
            
            if(current_month <= total_months && current_month > 1){
                document.querySelectorAll(".week-shedule").forEach(item=>{
                    item.classList.add("d-none");
                });
                document.querySelectorAll(".month_" + (current_month - 1)).forEach(item=>{
                    item.classList.remove("d-none");
                });
                document.querySelector("#current_month_button").setAttribute('data-current-month', current_month-1);
                document.querySelector("#current_month_button").innerHTML = "MONTH # "+ (current_month-1);
            }

        }

        function updateCalendarToDo(event){
            document.querySelectorAll(".mobile-calender .last_week").forEach((item, key)=>{
            
               // console.log(item.children().every(val => val.classList === "workout-completed"));

                var array1 = item.children;
                var checkSequence = Object.values(array1).every(currentValue => currentValue.classList.value  === "workout-completed");
                if(checkSequence === true){
                    document.querySelector("#next_month_button").click();
                }
            })
        }
        window.addEventListener('DOMContentLoaded', updateCalendarToDo);
    </script>


<?php echo get_footer("portal"); ?>