<?php
/*
Plugin Name: Automatic Plugins
Plugin URI: http://www.endd.eu/automatic-plugins/
Version: 1.0
Description: Inserts WP Page Navi, WP Print and WP Post Ratings Plugins in your theme from the Dashboard. No code necessary!
Author: End Soft Design
Author URI: http://www.endd.ro/
License: GPL v.2

             GNU
   GENERAL PUBLIC LICENSE
    Version 2, June 1991

Copyright (C) 2011 endd.ro    
*/

  # WP version check
  global $wp_version;
  $exit_msg='Plugin requires WordPress 2.9 or newer.<a href="http://wordpress.org/" target="_blank">Please update!</a>';
  if(version_compare ($wp_version,"2.9","<")){exit($exit_msg);}
  
  # INIT
  $plugin_directory = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
  	
	add_action('admin_menu', 'autoplug_menu');

	if( ! is_admin() ) { 		
    if(get_option('autoplug_wp_page_navi') == 1)
      wp_enqueue_style('autoplug-style', $plugin_directory . 'disable_old_navi.css');
	}
  else {
    wp_enqueue_style('autoplug-admin-style', $plugin_directory . 'admin.css');
  }
	
	# ADMIN PAGE

  function autoplug_menu() {
    add_options_page('Automatic Plugins Options', 'Automatic Plugins', 'manage_options', 'autoplug', 'autoplug_options');
  }
  
  function autoplug_options() {
    global $plugin_directory;
    if (!current_user_can('manage_options'))  {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    
    # SHOW/HIDE VARIABLES
    if(get_option("autoplug_wp_page_navi") == "") add_option( 'autoplug_wp_page_navi', 0, '', 'yes' );
    if(get_option("autoplug_wp_print") == "") add_option( 'autoplug_wp_print', 0, '', 'yes' );
    if(get_option("autoplug_wp_post_ratings") == "") add_option( 'autoplug_wp_post_ratings', 0, '', 'yes' );
    
    # PAGE NAVI POSITION VARIABLE
    if(get_option("autoplug_wp_page_navi_position") == "") add_option( 'autoplug_wp_page_navi_position', 'bottom', '', 'yes' );
    
    # WP PRINT POSITION VARIABLE
    if(get_option("autoplug_wp_print_position") == "") add_option( 'autoplug_wp_print_position', 'topright', '', 'yes' );
    
    # POST RATINGS POSITION VARIABLE
    if(get_option("autoplug_wp_post_ratings_position") == "") add_option( 'autoplug_wp_post_ratings_position', 'topleft', '', 'yes' );
    
    echo '<div class="wrap">';
    echo '  <div id="icon-options-general" class="icon32"></div>';
    echo "  <h2>Automatic Plugins Options</h2>";
    echo '  <div>';
    
    if($_POST['autoplug_action'] == "save"){
      echo '<div class="updated"><p><strong>Options saved!</strong></p></div>';
      update_option( "autoplug_wp_page_navi", $_POST['autoplug_wp_page_navi'] );
      update_option( "autoplug_wp_print", $_POST['autoplug_wp_print'] );
      update_option( "autoplug_wp_post_ratings", $_POST['autoplug_wp_post_ratings'] );
      update_option( "autoplug_wp_page_navi_position", $_POST['autoplug_wp_page_navi_position'] );
      update_option( "autoplug_wp_print_position", $_POST['autoplug_wp_print_position'] );
      update_option( "autoplug_wp_post_ratings_position", $_POST['autoplug_wp_post_ratings_position'] );
    }
    else {
    	echo '<br/>';
    }
    
    # READ SHOW/HIDE VARIABLES    
    if(get_option("autoplug_wp_page_navi"))
      $autoplug_wp_page_navi = get_option("autoplug_wp_page_navi");
    else
      $autoplug_wp_page_navi=0;
      
    if(get_option("autoplug_wp_print")) 
      $autoplug_wp_print = get_option("autoplug_wp_print"); 
    else 
      $autoplug_wp_print=0;   
      
    if(get_option("autoplug_wp_post_ratings")) 
      $autoplug_wp_post_ratings = get_option("autoplug_wp_post_ratings"); 
    else 
      $autoplug_wp_post_ratings=0;  
    
    # READ PAGE NAVI POSITION VARIABLE
    if(get_option("autoplug_wp_page_navi_position")) 
      $autoplug_wp_page_navi_position = get_option("autoplug_wp_page_navi_position"); 
    else 
      $autoplug_wp_page_navi_position="bottom";  
      
    # READ WP PRINT POSITION VARIABLE
    if(get_option("autoplug_wp_print_position")) 
      $autoplug_wp_print_position = get_option("autoplug_wp_print_position"); 
    else 
      $autoplug_wp_print_position="topright";  
      
    # READ POST RATINGS POSITION VARIABLE
    if(get_option("autoplug_wp_post_ratings_position")) 
      $autoplug_wp_post_ratings_position = get_option("autoplug_wp_post_ratings_position"); 
    else 
      $autoplug_wp_post_ratings_position="topleft";
    
  ?>
<form method="post" action="">
  <h3>WP Page Navi</h3>
  <ul>
    <li><img class="plugin-sample-img" src="<?php echo $plugin_directory;?>images/wp-pagenavi.png" alt="" /></li>
    <li>Replaces the basic <em>&larr; Older posts | Newer posts &rarr;</em> links with a more advanced paging navigation interface.</li>
    <li>
      <h4>Options</h4>
      <p>Show on site <input type="checkbox" name="autoplug_wp_page_navi" <?php if($autoplug_wp_page_navi == 1) echo 'checked="checked"';?> value="1" /> <strong>|</strong> Positioned at:
      <input type="radio" name="autoplug_wp_page_navi_position" value="top" <?php if($autoplug_wp_page_navi_position == "top") echo 'checked="checked"';?>/>Top
      <input type="radio" name="autoplug_wp_page_navi_position" value="bottom" <?php if($autoplug_wp_page_navi_position == "bottom") echo 'checked="checked"';?>/>Bottom
      <input type="radio" name="autoplug_wp_page_navi_position" value="both" <?php if($autoplug_wp_page_navi_position == "both") echo 'checked="checked"';?>/>Top &amp; bottom</p>
    </li>
    <li><p><strong>Download and more informations:</strong></p><p><i><a href="http://wordpress.org/extend/plugins/wp-pagenavi/">Visit plugin site</a></i></p></li>
  </ul>
  <p class="submit">
    <input name="save" type="submit" value="Save changes" />
  </p>
  
  <h3>WP Print</h3>
  <ul>
    <li><img class="plugin-sample-img" src="<?php echo $plugin_directory;?>images/wp-print-1.png" alt="" /></li>
    <li>Displays a printable version of your WordPress blog's post/page.</li>
    <li>
      <h4>Options</h4>
      <p>Show on site <input type="checkbox" name="autoplug_wp_print" <?php if($autoplug_wp_print == 1) echo 'checked="checked"';?> value="1" /> <strong>|</strong> Positioned at:
      <input type="radio" name="autoplug_wp_print_position" value="topleft" <?php if($autoplug_wp_print_position == "topleft") echo 'checked="checked"';?>/>Top Left
      <input type="radio" name="autoplug_wp_print_position" value="topright" <?php if($autoplug_wp_print_position == "topright") echo 'checked="checked"';?>/>Top Right
      <input type="radio" name="autoplug_wp_print_position" value="bottomleft" <?php if($autoplug_wp_print_position == "bottomleft") echo 'checked="checked"';?>/>Bottom Left
      <input type="radio" name="autoplug_wp_print_position" value="bottomright" <?php if($autoplug_wp_print_position == "bottomright") echo 'checked="checked"';?>/>Bottom Right
      </p>
    </li>
    <li><p><strong>Download and more informations:</strong></p><p><i><a href="http://wordpress.org/extend/plugins/wp-print/">Visit plugin site</a></i></p></li>
  </ul>
  <p class="submit">
    <input name="save" type="submit" value="Save changes" />
  </p>
 
  <h3>WP Post Ratings</h3>
  <ul>
    <li><img class="plugin-sample-img" src="<?php echo $plugin_directory;?>images/wp-postratings.png" alt="" /></li>
    <li>Adds an AJAX rating system for your WordPress blog's post/page.</li>
    <li>
      <h4>Options</h4>
      <p>Show on site <input type="checkbox" name="autoplug_wp_post_ratings" <?php if($autoplug_wp_post_ratings == 1) echo 'checked="checked"';?> value="1" />
      <input type="radio" name="autoplug_wp_post_ratings_position" value="topleft" <?php if($autoplug_wp_post_ratings_position == "topleft") echo 'checked="checked"';?>/>Top Left
      <input type="radio" name="autoplug_wp_post_ratings_position" value="topright" <?php if($autoplug_wp_post_ratings_position == "topright") echo 'checked="checked"';?>/>Top Right
      <input type="radio" name="autoplug_wp_post_ratings_position" value="bottomleft" <?php if($autoplug_wp_post_ratings_position == "bottomleft") echo 'checked="checked"';?>/>Bottom Left
      <input type="radio" name="autoplug_wp_post_ratings_position" value="bottomright" <?php if($autoplug_wp_post_ratings_position == "bottomright") echo 'checked="checked"';?>/>Bottom Right
      </p>
    </li>
    <li><p><strong>Download and more informations:</strong></p><p><i><a href="http://wordpress.org/extend/plugins/wp-postratings/">Visit plugin site</a></i></p></li>
  </ul>
  
  <p class="submit">
    <input name="save" type="submit" value="Save changes" />
  </p>
  <input type="hidden" name="autoplug_action" value="save" />
</form>

<form action="http://www.endd.eu/automatic-plugins/" method="post">
  <h3>Automatic Plugins PRO</h3>
  <p>We are working at PRO version of Automatic Plugins.</p>
  <p><strong>New Features:</strong></p>
    <ol>
      <li>New! Support for qTransalte Plugin.</li>
      <li>Managed Plugins Status</li>
    </ol>
    <p>It is free.</p>
  <p class="submit">
    <input name="save" type="submit" value="Download" />
  </p>
</form>

<?php
	  echo '  </div>';
	  echo '</div>';      

  }

  # FUNCTIONS
  function autoplug_add_before_post(){
    if(get_option('autoplug_wp_print') == 1) {
      if(get_option('autoplug_wp_print_position') == 'topleft') {
        if(function_exists('wp_print')) {   
          echo '<div class="wp-print alignleft">';
          print_link(); 
          echo '</div>';
        }
      } else if(get_option('autoplug_wp_print_position') == 'topright') {
        if(function_exists('wp_print')) {   
          echo '<div class="wp-print alignright">';
          print_link(); 
          echo '</div>';
        }
      }
    }
      
    if(get_option('autoplug_wp_post_ratings') == 1) {   
      if(get_option('autoplug_wp_post_ratings_position') == 'topleft') {
        if(function_exists('the_ratings')) {   
          echo '<div class="the-ratings alignleft">';
          the_ratings(); 
          echo '</div>';
        }
      } else if(get_option('autoplug_wp_post_ratings_position') == 'topright') {
        if(function_exists('the_ratings')) {   
          echo '<div class="the-ratings alignright">';
          the_ratings(); 
          echo '</div>';
        }
      }
    }
  }
  
  function autoplug_add_after_post($content){
    echo $content;
    if(get_option('autoplug_wp_post_ratings') == 1) {   
      if(get_option('autoplug_wp_post_ratings_position') == 'bottomleft') {
        if(function_exists('the_ratings')) {   
          echo '<div class="the-ratings alignleft">';
          the_ratings(); 
          echo '</div>';
        }
      } else if(get_option('autoplug_wp_post_ratings_position') == 'bottomright') {
        if(function_exists('the_ratings')) {   
          echo '<div class="the-ratings alignright">';
          the_ratings(); 
          echo '</div>';
        }
      }
    }
  }
  
  function autoplug_add_before_loop(){
    if(get_option('autoplug_wp_page_navi') == 1) {
      if(get_option('autoplug_wp_page_navi_position') == "top" || get_option('autoplug_wp_page_navi_position') == "both") {
        if(function_exists('wp_pagenavi')) { 
          wp_pagenavi(); 
        }
      }
    }
  }
  
  function autoplug_add_after_loop(){
    if(get_option('autoplug_wp_page_navi') == 1) {
      if(get_option('autoplug_wp_page_navi_position') == "bottom" || get_option('autoplug_wp_page_navi_position') == "both") {
        if(function_exists('wp_pagenavi')) { 
          wp_pagenavi(); 
        }
      }
    }
  }
  
  add_action('the_post', autoplug_add_before_post, 10);
  add_action('loop_start', autoplug_add_before_loop, 10);
  add_action('loop_end', autoplug_add_after_loop, 10);
  add_action('the_content',autoplug_add_after_post, 10, 1);
?>