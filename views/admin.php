<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package    MnCombine
 * @author     Michael Neil
 * @license    GPL-2.0+
 * @link       http://mneilsworld.com/mncombine
 * @since      1.0.0
 */
?>
<div class="wrap">

	<?php screen_icon(); ?>
	<h2 class="nav-tab-wrapper">
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug"); ?>" class="nav-tab <?php if( !isset($_GET['action'] ) ) echo 'nav-tab-active'; ?>"><?php echo _e('General Settings', 'mn-combine'); ?></a>
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug&action=cache"); ?>" class="nav-tab <?php if( isset($_GET['action'] ) && 'cache' === $_GET['action'] ) echo 'nav-tab-active'; ?>"><?php echo _e('Cache', 'mn-combine'); ?></a>
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug&action=js"); ?>" class="nav-tab <?php if( isset($_GET['action'] ) && 'js' === $_GET['action'] ) echo 'nav-tab-active'; ?>"><?php echo _e('Javascript', 'mn-combine'); ?></a>
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug&action=css"); ?>" class="nav-tab <?php if( isset($_GET['action'] ) && 'css' === $_GET['action'] ) echo 'nav-tab-active'; ?>"><?php echo _e('CSS', 'mn-combine'); ?></a>
  </h2>
	
  <?php 
    if( is_wp_error($this->errors) ):
      $errors = $this->errors->get_error_messages();
      ?>
      <div id="setting-error-settings" class="<?php echo $this->errors->get_error_data(); ?> settings-error">
        <?php if( is_array($errors) && !empty($errors) )
        foreach( $errors as $error ): ?>
        <p>
          <?php echo _e( $error, 'mn-combine'); ?>
        </p>
        <?php endforeach; ?>
      </div>
      <?php
    endif;
  ?>   

  <?php 
  $assets = $this->find_assets(); 
  $current = get_option( 'mn_comine_assets', $this->default );
  $compression = get_option( 'mn_compression_engine', $this->compression_engine );
  $compile_mode = get_option( 'mn_compile_mode', $this->compile_mode );
  $force_combine = get_option( 'mn_force_combine', $this->force_combine );
  $css_compression = get_option( 'mn_css_compression', $this->css_compression );
  $exclude_css_regex = get_option( 'mn_exclude_css_regex', $this->exclude_css_regex );
  $exclude_js_regex = get_option( 'mn_exclude_js_regex', $this->exclude_js_regex );
  //$compress_js_single = get_option( 'mn_compress_js_single', $this->compress_js_single );
  $id = 0;
  ?>
  
  <form action="" method="post" accept-charset="utf-8">
    
    <?php wp_nonce_field('mn_combine_update', 'mn_combine');?>
    
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><?php echo _e('Javascript Compression Engine', 'mn-combine'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php echo _e('choose which javascript engine to use when compressing', 'mn-combine'); ?></span>
              </legend>
              <label for="none">
                <input name="compression_engine" type="radio" id="none" value="none" <?php if( $compression == "none" )echo 'checked="checked"'; ?>/>
                <?php echo _e('No Compression', 'mn-combine'); ?>
              </label>
              <br/>
              <label for="closure">
                <input name="compression_engine" type="radio" id="closure" value="google_closure" <?php if( $compression == "google_closure" )echo 'checked="checked"'; ?>/>
                <?php echo _e('Google Closure', 'mn-combine'); ?> <a href="https://developers.google.com/closure/compiler/" target="_blank"><?php echo _e('learn more', 'mn-combine'); ?></a>
              </label>
              <br/>
              <label for="jsmin">
                <input name="compression_engine" type="radio" id="jsmin" value="js_min" <?php if( $compression == "js_min" )echo 'checked="checked"'; ?>/>
                <?php echo _e('JSMin', 'mn-combine'); ?> <small><?php echo _e('Not recommended but it still works', 'mn-combine'); ?></small> <a href="https://github.com/rgrove/jsmin-php/" target="_blank"><?php echo _e('learn more', 'mn-combine'); ?></a>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <?php /* not ready for primetime. This will fail miserably with dependency order ?>
        <tr valign="top">
          <th scope="row">Compress Javascript Individually</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>choose whether to combine javascript then compress or compress then combine</span>
              </legend>
              <label for="compress_js_single_0">
                <input name="compress_js_single" type="radio" id="compress_js_single_0" value="0" <?php if( $compress_js_single == "0" )echo 'checked="checked"'; ?>/>
                No <small>(combine files then compress all js at once)</small>
              </label>
              <br/>
              <label for="compress_js_single_1">
                <input name="compress_js_single" type="radio" id="compress_js_single_1" value="1" <?php if( $compress_js_single == "1" )echo 'checked="checked"'; ?>/>
                Yes <small>(compress each js file separately then combine)</small>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <?php */ ?>
        <tr valign="top">
          <th scope="row"><?php echo _e('Compress CSS', 'mn-combine'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php echo _e('choose whether or not to compress the css', 'mn-combine'); ?></span>
              </legend>
              <label for="css_compress_0">
                <input name="css_compression" type="radio" id="css_compress_0" value="0" <?php if( $css_compression == "0" )echo 'checked="checked"'; ?>/>
                <?php echo _e('No', 'mn-combine'); ?>
              </label>
              <br/>
              <label for="css_compress_1">
                <input name="css_compression" type="radio" id="css_compress_1" value="1" <?php if( $css_compression == "1" )echo 'checked="checked"'; ?>/>
                <?php echo _e('Yes', 'mn-combine'); ?>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php echo _e('Mode', 'mn-combine'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php echo _e('Choose a mode to determine when to compress', 'mn-combine'); ?></span>
              </legend>
              <label for="none">
                <input name="compile_mode" type="radio" id="none" value="development" <?php if( $compile_mode == "development" )echo 'checked="checked"'; ?>/>
                <?php echo _e('Development', 'mn-combine'); ?>
              </label>
              <br/>
              <label for="closure">
                <input name="compile_mode" type="radio" id="closure" value="production" <?php if( $compile_mode == "production" )echo 'checked="checked"'; ?>/>
                <?php echo _e('Production', 'mn-combine'); ?>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php echo _e('Force Combine', 'mn-combine'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php echo _e('Force scripts queued to load in the header or footer only', 'mn-combine'); ?></span>
              </legend>
              <label for="none">
                <input name="force_combine" type="radio" id="none" value="none" <?php if( $force_combine == "none" )echo 'checked="checked"'; ?>/>
                <?php echo _e('Do not force', 'mn-combine'); ?>
              </label>
              <br/>
              <label for="header">
                <input name="force_combine" type="radio" id="header" value="header" <?php if( $force_combine == "header" )echo 'checked="checked"'; ?>/>
                <?php echo _e('In the header', 'mn-combine'); ?> <a href="#" class="read-help"><?php echo _e('learn more', 'mn-combine'); ?></a>
              </label>
              <br/>
              <label for="footer">
                <input name="force_combine" type="radio" id="footer" value="footer" <?php if( $force_combine == "footer" )echo 'checked="checked"'; ?>/>
                <?php echo _e('In the footer', 'mn-combine'); ?> <a href="#" class="read-help"><?php echo _e('learn more', 'mn-combine'); ?></a>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php echo _e('Don\'t combine css on regex', 'mn-combine'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php echo _e('Don\'t combine css files on specific pages', 'mn-combine'); ?></span>
              </legend>
              <input name="exclude_css_regex" type="text" id="exclude_css_regex" value="<?php echo $exclude_css_regex; ?>" class="regular-text" placeholder="<?php echo _e('/\/$|\/about$/ : exclude home and about', 'mn-combine'); ?>"/>
              <br />
              <p class="description">
                <?php echo _e('A regex matching REQUEST_URI.', 'mn-combine'); ?>
              </p>
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php echo _e('Don\'t combine js on regex', 'mn-combine'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php echo _e('Don\'t combine js files on specific pages', 'mn-combine'); ?></span>
              </legend>
              <input name="exclude_js_regex" type="text" id="exclude_js_regex" value="<?php echo $exclude_js_regex; ?>" class="regular-text" placeholder="<?php echo _e('/\/$|\/about$/ : exclude home and about', 'mn-combine'); ?>"/>
              <br />
              <p class="description">
                <?php echo _e('A regex matching REQUEST_URI.', 'mn-combine'); ?>
              </p>
              
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    
    
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo _e('Save Changes'); ?>"></p>
  </form>

</div>
