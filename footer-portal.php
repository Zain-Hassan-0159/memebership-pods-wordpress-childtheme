      <!-- footer-section -->
      <?php
 if(!is_page_template( array( 'template-portal-login.php', 'template-portal-recoverPassword.php' ) )){
      ?>
      <footer>
        <div class="container">
            <div class="footer-logo">
              <?php
              if(is_page_template( array( 'template-portal.php') )){
                ?>
                <a href="<?php echo get_site_url(null, '/shop/', 'https'); ?>">
                  <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/footer-logo.png" alt="footer-logo">
                </a>
                <?php
              }else{
                ?>
                <a href="<?php echo get_site_url(null, '/portal/', 'https'); ?>">
                  <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/footer-logo.png" alt="footer-logo">
                </a>
                <?php
              }
              ?>

            </div>
        </div>
      </footer>
      <style>
        .wrapper {
          border: 1px solid #000000;
          height: calc(100vh - 2px);
          margin: 0 auto;
          overflow-x: hidden;
          overflow-y: scroll;
          position: relative;
          width: calc(100vw - 2.55px);
        }
        .scroll-top {
          background-image: linear-gradient(
            240deg,
            hsl(0deg 84% 45%) 0%,
            hsl(0deg 85% 44%) 6%,
            hsl(0deg 86% 43%) 19%,
            hsl(0deg 88% 41%) 42%,
            hsl(0deg 90% 40%) 71%,
            hsl(0deg 92% 39%) 92%,
            hsl(0deg 98% 37%) 100%
          );
          border: 0;
          border-radius: 50%;
          bottom: 0;
          cursor: pointer;
          height: 50px;
          margin: 15px;
          position: fixed;
          right: -70px;
          transition: right 0.2s ease-in-out;
          width: 50px;
          z-index: 99;
        }
        .scroll-top.visible {
          right: 0;
        }

        .arrow {
          border: solid #fff;
          border-width: 0 3px 3px 0;
          display: inline-block;
          margin-top: 5px;
          padding: 4px;
        }
        .arrow.up {  
          transform: rotate(-135deg);
        }
      </style>
      <button class="scroll-top">
        <div class="arrow up"></div>
      </button>
      <script>
        // NOTICE: This pen may appear to not work on mobile devices, but it is  due to the codepen footer and the browser's bottom menu bar that hide the button. It should work fine when implemented for your website

          // used to avoid using 255, thus generating white-ish backgrounds that make text unreadable
          const colorMax = 192;

          // gets the breakpoint at which the scroll-to-top button should appear
          const scrollBreakpoint = window.innerHeight * 0.9;

          document.addEventListener('DOMContentLoaded', () => {
            randomizeBackgrounds();
            setupScrollListener();
            setupScrollEvent();  
          });

          // scrolls window to top
          function setupScrollEvent() {
            const scrollButton = document.querySelector('.scroll-top');
            
            scrollButton.addEventListener('click', (e) => {
              // not the best solution until Safari/Edge support scroll behavior
              // window.scrollTo({ top: 0, behavior: 'smooth' });
              console.log(scrollButton.parentElement);
              // Thanks to Geroge Daniel https://stackoverflow.com/questions/51229742/javascript-window-scroll-behavior-smooth-not-working-in-safari
              smoothVerticalScrolling(scrollButton.parentElement, 200, "top");
              
            });
          }

          // prepares the window for a scroll event to show the scroll button
          function setupScrollListener() {  
            window.addEventListener('scroll', (e) => {
              const scrollButton = document.querySelector('.scroll-top');
              
              // const scrollOffset = document.scrollingElement.scrollTop;
              const scrollOffset = window.scrollY;
              
              if (scrollOffset >= scrollBreakpoint) {
                scrollButton.classList.add('visible');
              } else if (scrollOffset <= 0) {
                scrollButton.classList.remove('visible');
              }    
            });
          }

          function randomizeBackgrounds() {
            // get all the content containers
            const contentContainers = document.querySelectorAll('.content-container');
            
            [].forEach.call(contentContainers, container => {
              // assign random background
              container.style.background = `rgb(${randVal(colorMax)},${randVal(colorMax)},${randVal(colorMax)})`;
            });
          }

          // random between 0 to max
          function randVal(max) {
            return Math.floor(Math.random() * Math.floor(max));
          }

          // uses a timeout to scroll to top
          function smoothVerticalScrolling(e, time, where) {
            // gets the element's top position relative to the viewport
            const eTop = e.getBoundingClientRect().top;
            
            
            // divides the top offset into 100 steps to be ellapsed
            const eAmt = eTop / 100;
            
            // starting time
            let curTime = 0;
            window.setTimeout(SVS_B, curTime, eTop, where);
            
          }

          function SVS_B(eAmt, where) {
            // scroll by half the hundredth of the top offset if destination is not top (since to center only involves scrolling either in the top or bottom half of the window)
              window.scrollBy(0, eAmt);
   
          }
      </script>
      <?php
    }
    ?>
      <?php wp_footer(); ?>
    <!-- Option 1: Bootstrap Bundle with Popper -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" ></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
  </body>
</html>