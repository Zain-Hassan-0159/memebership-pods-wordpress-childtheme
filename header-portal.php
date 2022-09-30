<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- custom-style-sheets -->
    <?php wp_head();
    ?>

  </head>
  <body>
    <?php
 if(!is_page_template( array( 'template-portal-login.php' ) )){
      ?>
      <!-- header-start -->
      <header>
        <!-- mobile-header -->
        <div class="mobile-header">
            <div class="container">
                <div class="mobile-contact">
                    <div class="menu-title">
                        <span>MENU</span>
                    </div>
                    <div class="login-wrapper">
                        <a href="<?php echo get_site_url() ?>/portal/login" class="login-btn">LOGIN</a>
                    </div>
                </div>
            
            </div>
        </div>
        <div class="header-main">
            <nav class="navbar navbar-expand-lg ">
                <div class="container resp-flex-direction">
                  <a class="navbar-brand" href="<?php echo get_site_url()."/" ?>">
                    <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/LogoMakr-2WVj5d.png" alt="logo" class="mobile-logo">
                  </a>
                  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars"></i>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                      <li class="nav-item">
                        <a class="nav-link <?php echo is_page_template( 'template-portal.php' ) ? 'active' : '';?> " aria-current="page" href="<?php echo get_site_url() ?>/portal">DASHBOARD</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link <?php echo is_page_template( 'template-portal-calendar.php' ) ? 'active' : '';?>" href="<?php echo get_site_url() ?>/portal/calendar">CALNEDER</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link <?php echo is_page_template( 'template-portal-nutrition.php' ) ? 'active' : '';?>" href="<?php echo get_site_url() ?>/portal/nutrition">NUTRION</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link <?php echo is_page_template( 'template-portal-workout.php' ) ? 'active' : '';?>" href="<?php echo get_site_url() ?>/portal/workout">WORKOUT</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </nav>
        </div>
        <div class="container">
            <div class="top-header">
                <div class="profile-user">
                   <?php
                   $current_user = wp_get_current_user();
                   echo get_avatar( $current_user->ID, 64 );
                   ?>
                   <div class="customer-name">
                    <?php 
                    if($current_user->user_firstname !== ""){
                     ?>
                      <p><?php echo $current_user->user_firstname; echo " ";  echo $current_user->user_lastname;  ?></p>
                      <?php }elseif($current_user->display_name !== ""){ ?>
                      <p><?php echo $current_user->display_name;  ?></p>
                      <?php 
                    } ?>
                   </div>
                   
                </div>
                <div>
                
                </div>
                <div class="header-monkey">
                    <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/IIII.png" alt="header-monkey">
                </div>
            </div>
        </div>
      </header>  <!-- header-end -->
      <?php
    }
    ?>