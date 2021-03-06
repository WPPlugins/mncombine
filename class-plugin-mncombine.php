<?php
/**
 * MnCombine
 *
 * @package   MnCombine
 * @author    Michael Neil <mneil@mneilsworld.com>
 * @copyright 2013 MneilsWorld
 * @license   GPL-2.0+
 * @link      http://mneilsworld.com/
 */

/**
 * MnCombine
 *
 * @package MnCombine
 * @author  Michael Neil <mneil@mneilsworld.com>
 */
class MnCombine {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.1.6';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'mn-combine';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;
  
  /**
   * Stores errors for display
   * 
   * @since    1.0.0
   * 
   * @var      object
   */
  protected $errors = null;//error|updated
  
  /**
   * Stores a reference to our upload directory
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $upload_dir = "mn_combine";
  
  /**
   * Stores a reference to wp upload directory
   * 
   * @since 1.0.0
   * 
   * @var array
   */
  protected $uploads = array();
  
  /**
   * Temporarily stores a directory path for matching css paths
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $dir = "";
  
  /**
   * Stores the combined assets and their handles for lookup on compress
   * 
   * @since 1.0.0
   * 
   * @var array
   */
  protected $combined = array();
  
  /**
   * Stores the default compression mode
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $compression_engine = 'google_closure';
  
  /**
   * Stores the default compile mode
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $compile_mode = 'production';
  
  /**
   * Stores the default force combine option
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $force_combine = 'none';
  
  /**
   * Stores the default css_compression option value
   * 
   * @since 1.0.3
   */
  protected $css_compression = '0';
  
  /**
   * Stores the default compress_js_single option value
   * 
   * @since 1.0.3
   */
  protected $compress_js_single = '0';
  
  /**
   * A regex to match REQUEST_URI to exclude combining css on
   * 
   * @since 1.1.0
   */
  protected $exclude_css_regex = "";
  
  /**
   * A regex to match REQUEST_URI to exclude combining js on
   * 
   * @since 1.1.0
   */
  protected $exclude_js_regex = "";
  
  /**
   * Stores the default parsing structure for stored data
   * 
   * @since 1.0.0
   * 
   * @var array
   */
  protected $default = array(
    'combine' => array(
      'css'=>array(), 
      'js'=>array()
    ),
    'compress' => array(
      'css'=>array(), 
      'js'=>array()
    )
  );
   

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
    
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Enqueue admin styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Enqueue public style and scripts.
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		if( is_admin() )
      return;
    
		add_action( 'wp_print_scripts', array( $this, 'wp_print_scripts' ), 99999 );//we want to do this dead last 
    add_action( 'wp_print_styles', array( $this, 'wp_print_styles' ), 99999 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
    
    $this->uploads = wp_upload_dir();
	}

	/**
	 * Enqueue admin-specific style sheets.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), $this->version );
		}

	}

	/**
	 * Enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		}

	}

	/**
	 * Enqueue public-facing style sheets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), $this->version );
	}

	/**
	 * Enqueues public-facing script files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_plugins_page(
			__('Mn Combine', 'mn-combine'),
			__('Asset Combine', 'mn-combine'),
			'edit_posts',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
    
    add_action("load-{$this->plugin_screen_hook_suffix}", array( $this, 'add_help_tab' ));
    //add_action("load-{$this->plugin_screen_hook_suffix}", array( $this, 'add_screen_options' ));
	}
  public function add_help_tab()
  {
    /*
     * Check if current screen is My Admin Page
     * Don't add help tab if it's not
     */
    $screen = get_current_screen();
    if ( $screen->id != $this->plugin_screen_hook_suffix )
      return;

    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab( array(
        'id'  => 'mn_combine_description',
        'title' => __('Description', 'mn-combine'),
        'content' => '<p>' . __( 'Finds all possible .js and .css files from a WP install available and allows 
        you to combine and/or compress the files to reduce load time. The plugin can monitor file changes in 
        "development" mode (by hashing file mtime) which allows the plugin to recompile the files when a 
        file changes. Or, it can cache the files in "production" mode so that files are only recompiled 
        if they are not found or are deleted manually from the cache folder. Additionally, this plugin will 
        allow you to force the inclusion of javascript files into either the head or the foot of the page.', 'mn-combine' ) . '</p>' . '<p>' . 
        __( 'There are two modes, development and production, the ability to force the files to print in the header or footer*, 
        the use of Google Closure as a JS compiler, and finally the ability to pick and choose which files, 
        including dependencies, should be combined.', 'mn-combine' ) . '</p>'. '<p>' . 
        __( '*forcing head compiles can fail on JS files queued after the call to wp_head(). The plugin will, 
        in this case, render the late queued files in the footer as originally intended.', 'mn-combine' ) . '</p>',
    ) );
    $screen->add_help_tab( array(
      'id'  => 'mn_combine_general',
      'title' => __('General Settings', 'mn-combine'),
      'content' => '<p>' . '<strong>' . __('Javascript Compression Engine ', 'mn-combine') . '</strong>' . __( ': determine
        the compression engine to use when compressing javascript files' , 'mn-combine') . '</p>' . '<p>'
         . '<strong>' . __('Compress CSS ', 'mn-combine') . '</strong>' . 
         __( ' :  determines whether or not to compress the compiled css. This is done using a regex which, in 
         most cases, does a great job compressing css by removing whitespaces and newlines. This can, however, cause
         errors in some css. If it does, please contact us and let us know what css caused the error.', 'mn-combine') . '</p>'
         . '<strong>' . __('Mode ', 'mn-combine') . '</strong>' . 
        __( ' : Prodution mode will only
        compile the files neccessary for a page on the first request and cache those files.
        All subsequent requests will serve those cache files until either a new dependency
        is queued or the cache file is removed. Development mode will monitor the files
        last change time and recompile the assets on any page request where the files data
        has been modified.' ) . '<em><strong>' . __(' NOTE: ', 'mn-combine') . '</strong>' . __(' development mode will not monitor changes
        made to css files that are included by an @import statement ', 'mn-combine') . '</em></p>'
         . '<strong>' . __('Force combine ', 'mn-combine') . '</strong>' .
        __( ' : footer will force all javascript to load in the footer while header
        will force all queued javascript to be loaded in the footer. Forcing files queued for the header into the footer
        can cause some scripts to fail or dependencies to be missed if javascript is written inline in. 
        Forcing scripts into the header can cause scripts queued late to still remain in the footer.
        Use this to get the best load times possible but beware that it can break your site when enabled and probably isn\'t necessary.' , 'mn-combine') . '</p>',
    ) );
  }
  /**
   * Adds screen options
   * 
   * @since 1.0.0
   */
  public function add_screen_options()
  {     
    /*
     * Check if current screen is My Admin Page
     * Don't add help tab if it's not
     */
    $screen = get_current_screen();
    if ( $screen->id != $this->plugin_screen_hook_suffix )
      return;
    
    $args = array(
      'label' => __('Members per page', 'mn-combine'),
      'default' => 10,
      'option' => 'some_option'
    );
    add_screen_option( 'per_page', $args );
  }

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
	  if ( !empty($_POST) )
    {
      if( wp_verify_nonce( $_POST['mn_combine'], 'mn_combine_update' ) )
        $this->save_data();
      else
       $this->errors = new WP_Error('mn_combine', 'Sorry, your nonce did not verify.', 'error');
    }
    if( !isset( $_GET['action'] ) )
		  include_once( 'views/admin.php' );
    elseif( "js" === $_GET['action'] )
      include_once( 'views/js.php' );
    elseif( "css" === $_GET['action'] )
      include_once( 'views/css.php' );
    elseif( "cache" === $_GET['action'] )
      include_once( 'views/cache.php' );
    
	}
  
  /**
   * Saves admin form data
   * 
   * @since    1.0.0
   */
  protected function save_data()
  {
    $this->errors = new WP_Error();
    $save = $this->default;
    
    $assets = get_option( 'mn_comine_assets', $this->default );
    $this->errors->add('mn_combine', 'Settings updated', 'updated');
    
    if( isset( $_POST['combine'] ) )
      foreach( $_POST['combine'] as $c )
        foreach( $c as $key => $val )
          if( $val === "1" )
            if( strstr( $key, '.css' ) )
              $save['combine']['css'][] = $key;
            else
              $save['combine']['js'][] = $key;

    if( isset( $_POST['compress'] ) )
      foreach( $_POST['compress'] as $c )
        foreach( $c as $key => $val )
          if( $val === "1" )
            if( strstr( $key, '.css' ) )
              $save['compress']['css'][] = $key;
            else
              $save['compress']['js'][] = $key;
    
    if( !isset( $_GET['action'] ) )
    {
      update_option( 'mn_compression_engine', $_POST['compression_engine'] );
      update_option( 'mn_compile_mode', $_POST['compile_mode'] );
      update_option( 'mn_force_combine', $_POST['force_combine'] );
      update_option( 'mn_css_compression', $_POST['css_compression'] );
      update_option( 'mn_exclude_css_regex', stripslashes($_POST['exclude_css_regex']) );
      update_option( 'mn_exclude_js_regex', stripslashes($_POST['exclude_js_regex']) );
      //update_option( 'mn_compress_js_single', $_POST['mn_compress_js_single'] );
    }
    elseif( "js" === $_GET['action'] )
    {
      $save['combine']['css'] = $assets['combine']['css'];
      $save['compress']['css'] = $assets['compress']['css'];
      update_option( 'mn_comine_assets', $save );
    }
    elseif( "css" === $_GET['action'] )
    {
      $save['combine']['js'] = $assets['combine']['js'];
      $save['compress']['js'] = $assets['compress']['js'];
      update_option( 'mn_comine_assets', $save );
    }
    elseif( "cache" === $_GET['action'] )
    {
      if( !empty( $_POST['delete'] ) )
        foreach( $_POST['delete'] as $file => $delete )
          if( !@unlink( $file ) )
            $this->errors->add('mn_combine', "Unable to remove $file", 'error');    
    }

  }
  /**
   * Grabs the cache files
   * 
   * @since 1.0.0
   */
  private function find_cache()
  {
    if( !is_dir($this->uploads['basedir'] . '/' . $this->upload_dir) )
      return;
    
    $directory = new RecursiveDirectoryIterator( $this->uploads['basedir'] . '/' . $this->upload_dir );
    
    $filter = new DirnameFilter($directory, '/^(?!\.)/');
    $filter = new DirnameFilter($filter, '/^(?!\.\.)/');
    // Filter css/js files . although in this case these should be all that exist
    $filter = new FilenameFilter($filter, '/\.(?:css|js)$/');
    $cache = array();
        
    foreach(new RecursiveIteratorIterator($filter) as $file)
    {
      if($f = fopen($file, 'r'))
      {
        $line = fgets($f);
        fclose($f);
      }      
      if( strstr($file, ".css") )
        $cache['css'][] = array( 'file' => str_replace("\\", "/", $file ) . PHP_EOL, 'compiled' => $line );
      else
        $cache['js'][] = array( 'file' => str_replace("\\", "/", $file ) . PHP_EOL, 'compiled' => $line );
          
    }
    return $cache;
  }
  /**
   * Recursively scours the wp plugins and theme folder for assets we can use
   * 
   * @since 1.0.0
   */
  private function find_assets()
  {
    $directory = new RecursiveDirectoryIterator(WP_PLUGIN_DIR);
    // Filter out ". and .. and this plugin" folders
    $filter = new DirnameFilter($directory, '/^(?!'.str_replace( "/", "\/", dirname( plugin_basename( __FILE__ ) ) ).')/');
    //get plugins list
    $plugins = get_plugins();
    //loop the plugins and check for inactive ones to exclude
    foreach( $plugins as $plugin => $data )
      if( !is_plugin_active($plugin) )
        $filter = new DirnameFilter($filter, '/^(?!'.dirname($plugin).')/');
    
    $filter = new DirnameFilter($filter, '/^(?!\.)/');
    $filter = new DirnameFilter($filter, '/^(?!\.\.)/');
    // Filter css/js files 
    $filter = new FilenameFilter($filter, '/(?:\.css|\.js)$/');
    $assets = array();
    
    foreach(new RecursiveIteratorIterator($filter) as $file) {
      if( strstr($file, ".css") )
        $assets['css'][] = str_replace("\\", "/", $file ) . PHP_EOL;
      
      else
        $assets['js'][] = str_replace("\\", "/", $file ) . PHP_EOL;
    }
    $assets = $this->find_theme_assets( $assets );
    $assets = $this->find_wp_assets( $assets );
   
    return $assets;
  }
  /**
   * Finds asset files in the current activated theme
   * 
   * @since 1.0.0
   * 
   * @return array
   */
  private function find_theme_assets( $assets)
  {
    //recurse the active theme
    $directory = new RecursiveDirectoryIterator(get_stylesheet_directory());
    // Filter out ". and .. and this plugin" folders
    $filter = new DirnameFilter($directory, '/^(?!'.str_replace( "/", "\/", dirname( plugin_basename( __FILE__ ) ) ).')/');
    $filter = new DirnameFilter($filter, '/^(?!\.)/');
    $filter = new DirnameFilter($filter, '/^(?!\.\.)/');
    // Filter css/js files 
    $filter = new FilenameFilter($filter, '/(?:\.css|\.js)$/');
    
    foreach(new RecursiveIteratorIterator($filter) as $file) {
      if( strstr($file, ".css") )
        $assets['css'][] = str_replace("\\", "/", $file ) . PHP_EOL;
      
      else
        $assets['js'][] = str_replace("\\", "/", $file ) . PHP_EOL;
    }

    return $assets;
  }
  /**
   * Finds asset files included from wp
   * 
   * @since 1.0.0
   * 
   * @return array
   */
  private function find_wp_assets( $assets )
  {
    $directory = new RecursiveDirectoryIterator( ABSPATH . "wp-includes" );
    $filter = new DirnameFilter($directory, '/^(?!\.)/');
    $filter = new DirnameFilter($filter, '/^(?!\.\.)/');
    // Filter css/js files 
    $filter = new FilenameFilter($filter, '/\.(?:css|js)$/');
    
    foreach(new RecursiveIteratorIterator($filter) as $file) {
      if( strstr($file, ".css") )
        $assets['css'][] = str_replace("\\", "/", $file ) . PHP_EOL;
      
      else
        $assets['js'][] = str_replace("\\", "/", $file ) . PHP_EOL;
    }

    return $assets;
  }
  /**
   * Action hook for wp_print_scripts
   * 
   * @since 1.0.0
   */
  public function wp_print_scripts()
  {
    $regex = get_option( 'mn_exclude_js_regex', $this->exclude_js_regex );
    if( $regex !== "" && preg_match($regex, $_SERVER["REQUEST_URI"]) ) return;
    
    do_action('mn_print_scripts');
    global $wp_scripts, $auto_compress_scripts;
         
    $header = array();
    $footer = array();
    $localize = array();//store the localize scripts data
    $assets = get_option( 'mn_comine_assets', $this->default );//get the list of files we can compress/combine
    $force_combine = get_option( 'mn_force_combine', $this->force_combine );
    $compile_mode = get_option( 'mn_compile_mode', $this->compile_mode );
    $mtimes = array('header' => array(), 'footer' => array());
  
    $url = get_bloginfo("wpurl");//we need the blogs url to assist in comparisons later

    //if nothing is registered then stop this madness
    if( !is_object($wp_scripts) || count( $wp_scripts->registered ) === 0 || count( $assets['combine']['js'] ) === 0 )
      return false;
    
    $queue = $wp_scripts->queue;
    $wp_scripts->all_deps($queue);
    $to_do = $wp_scripts->to_do;
    
    //loop over the registered scripts for this page rquest
    foreach ($to_do as $key => $handle) 
    {
      //if the data is empty then die.
      if( !isset($wp_scripts->registered[$handle]) )
        continue;
      
      //store the src
      $src = $use = $wp_scripts->registered[$handle]->src;
      //check if the source has the full wp site url in it and remove it if it doest
      if( strstr($use, $url) )
        $use = str_replace( $url, "", $use );
      
      //store whether or not this file matches a file to combine
      $match = false;
      //loop the files list to combine
      if( $use )
        foreach($assets['combine']['js'] as $js )
          //if the file is in the list
          if( @strstr( $js, $use ) )
          {
            //we have a match, we'll continue below
            $match = true;
            break;
          }
      //file isn't in the combine list
      if( !$match )
        continue;    
            
      //store the handle and full file path for lookup later on compression
      $this->combined[$handle] = $js;
      /* used to pass up externals but now any file that gets included must be on the server to get found */
      //if( preg_match( "*(http://|https://)(?!".$_SERVER["SERVER_NAME"].")*", $src ) )
        //continue;
      
      //check for localize scripts data
      if( isset( $wp_scripts->registered[$handle]->extra['data'] ) )
        $localize[] = $wp_scripts->registered[$handle]->extra['data'];
      
      if( "development" === $compile_mode )
      {
        $dev_src = $this->local_path( $src );
        $mtime = filemtime( $dev_src );
      }
      
      //Footer scripts
      if( isset( $wp_scripts->registered[$handle]->extra['group'] ) )
      {
        if( "development" === $compile_mode )
          $mtimes['footer'][] = $mtime;
        $footer[$handle] = (object)array( 'src' => $src );
      }
      //header scripts
      else
      {
        if( "development" === $compile_mode )
          $mtimes['header'][] = $mtime;
        $header[$handle] = (object)array( 'src' => $src );
      }
      
      //remove this file from wp's registered script list and dequeue it next
      foreach( $wp_scripts->to_do as $key => $h )
        if( $h === $handle )
          unset($wp_scripts->to_do[$key]);//we're explicitly unsetting this because we'll use this list below to remove dependencies
      
      wp_deregister_script( $handle );
      wp_dequeue_script( $handle );
    }
    //remove dependencies that were compiled
    foreach( $wp_scripts->to_do as $handle )
      foreach( $wp_scripts->registered[$handle]->deps as $key => $dep )//loop the remaining to_do dependencies
        if( !in_array( $dep, $wp_scripts->to_do ) )//if the dependency isn't in to_do still then it gets compiled with the rest
          unset( $wp_scripts->registered[$handle]->deps[$key] );//remove the dependency, it's already queued
    
    if( "header" === $force_combine )
    {
      $header = array_merge( $header, $footer );
      $footer = array();
    }
    elseif( "footer" === $force_combine )
    {
      $footer = array_merge( $header, $footer );
      $header = array();
    }
    
    //hash the scripts by name
    $footerHash = md5( implode( ',', array_keys( $footer ) ) . implode( ',', $mtimes['footer'] ) );
    $headerHash = md5( implode( ',', array_keys( $header ) ) . implode( ',', $mtimes['header'] ) );
    
    //give these files a full path
    $footerFile = $this->uploads['basedir'] . '/' . $this->upload_dir . '/'  . $footerHash . ".js";
    $headerFile = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $headerHash . ".js";
    
    //make sure we have a place to put this file
    if( !is_dir( dirname( $footerFile ) ) )
      mkdir( dirname( $footerFile ), 0755, true );
    
    if( !is_file( $headerFile ) )
      $this->write_script_cache( $headerHash, $header, false, $localize );
    
    else 
      $this->enqueue_packed_script( $headerHash, false, $localize );
    
    /* If the files don't exist them build them*/
    if( !is_file( $footerFile ) )
      $this->write_script_cache( $footerHash, $footer, true, $localize );
    
    else 
      $this->enqueue_packed_script( $footerHash, true, $localize );
    
  }
  /**
   * Hooks to print footer scripts which calls our footer scripts
   * 
   * @since 1.0.0
   */
  function print_footer_scripts(){}
  /**
   * Finds the files absolute path on the server
   * 
   * @since 1.0.0
   * 
   * @param string $path
   */
  function local_path( $path )
  {    
    $src = $path;
    $path = ( substr( $src, 0, 1) == "/" )? ABSPATH . substr( $src, 1 ): $src;
    $path = ( strstr( $path, get_bloginfo("wpurl") ) ) ? ABSPATH . str_replace( get_bloginfo("wpurl")."/", "", $src ) : $path; 
    
    return $path;  
  }
  /**
   * Combines the files and puts them into a cached file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to write to
   * @param array $data List of file objects with src and path info
   * @param boolean $footer Whether or not to enqueue in the footer
   * @param array $localize any localize script data
   */
  private function write_script_cache( $file, $data, $footer = false, $localize = array() )
  {
    $cache = $file;
    $path = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $file . ".js";
    $assets = get_option( 'mn_comine_assets', $this->default );//get the list of files we can compress/combine
    $compression = get_option( 'mn_compression_engine', $this->compression_engine );//get the list of files we can compress/combine
    //$all_js = array('compressed' => array(), 'uncompressed' => array());
    //$implode just stores a nice comma separated list of files that were combined in this file
    $implode = array_keys( (array)$data );
    $implode = implode( ", ", $implode );
    //clear the cache file if for some reason it existed already; but it shouldn't
    if( is_file( $path ) )
      file_put_contents( $path, "" );

    //loop over our file data
    foreach( $data as $key => $f )
    {
      /* We're looking for our files on the server; converting url to location */
      $src = $this->local_path( $f->src );
      //can we find this file?
      if( !is_file( $src ) )
        continue;
      
      //get the file contents
      $content = file_get_contents( $src );
      //check if we're going to compress this or not
      if( in_array( str_replace("\\", "/", $src ), $assets['compress']['js'] ) )
      {
        $content = $this->{"_$compression"}($content);
      }
      file_put_contents( $path, $content . ";", FILE_APPEND | LOCK_EX );
    }
    //get the path of the newly created file
    if( !is_file( $path ) )
      return;
    
    else
    {
      $contents = file_get_contents( $path );
      if( empty( $contents ) )
        return;
      
      file_put_contents( $path, "/*$implode*/\n\n" . $contents );
    }
    $this->enqueue_packed_script( $cache, $footer, $localize );
  }
  /**
   * Sends javascript to google closure to minify
   * 
   * @since 1.0.0
   * 
   * @var string $js
   */
  private function _google_closure($js)
  {    
    $ch = curl_init('http://closure-compiler.appspot.com/compile');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $opts = 'output_info=compiled_code&output_format=json&compilation_level=SIMPLE_OPTIMIZATIONS&js_code=' . urlencode($js);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $opts);
    $output = curl_exec($ch);
    
    $output = json_decode($output);
    
    //errors?
    if( isset($output->errors) || isset($output->serverErrors) || $error = curl_error($ch) )
    {
      $errors = array();
      
      if( !empty($error) )
        $errors[] = "Curl Error: $error";
      
      //Do some error handling to tell user what's up
      if( !empty($output->serverErrors) )
        foreach($output->serverErrors as $error )
          $errors[] = $error->error;
        
      if( !empty($output->errors) )
        foreach($output->serverErrors as $error )
          $errors[] = $error->error . " on line " . $error->lineno . " character " . $error->charno;
      
      $this->handleError( $errors );
      curl_close($ch);
      //return the original js. Uncompressed js is better than no js
      return $js;
    }
    
    curl_close($ch);
    return $output->compiledCode;
  }
  /**
   * Executes JSMin minification
   * 
   * @since 1.0.0
   * 
   * @param string $js
   */
  private function _js_min($js)
  {
    if( !class_exists('JSMin') )
      include( plugin_dir_path(__FILE__) . "jsmin.php" );
      
    return JSMin::minify($js);
  }
  /**
   * Empty wrapper for no minification
   * 
   * @since 1.0.0
   * 
   * @param string $js
   */
  private function _none($js)
  {
    return $js;
  }
  /**
   * Enqueue a cached script file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to enqueue
   * @param boolean $footer Whether or not to enqueue in the footer
   * @param array $localize any localize script data
   */
  private function enqueue_packed_script( $file, $footer = false, $localize = array() )
  {
    $extra = "";
    //do localize first
    if( !empty( $localize ) )
      foreach( $localize as $s )
        $extra .= "$s\n";
    
    //prints wp_localise_script data in the header
    if( !empty( $extra ) && ( "footer" === get_option( 'mn_force_combine', $this->force_combine ) || $footer === false ) )
    {
      ?><script type="text/javascript" charset="utf-8"><?php echo $extra; ?></script><?php
    }
    
    $this->handle = 'mn_cache_' . uniqid();
    $path = preg_replace("|https?:|","",$this->uploads['baseurl']) . '/' . $this->upload_dir . '/' . $file . ".js";

    wp_enqueue_script( $this->handle, $path, null, 0, $footer );
    /**
     * If we're unqueueing the header scripts then we need to 
     * print them out immediately
     */
    if( !$footer )
    {
      global $wp_scripts;
      if ( ! is_a( $wp_scripts, 'WP_Scripts' ) )
        $wp_scripts = new WP_Scripts();
      
      $wp_scripts->all_deps($wp_scripts->queue); 
      $wp_scripts->to_do = array_values($wp_scripts->to_do);
      $wp_scripts->to_do = array_flip( $wp_scripts->to_do );
      unset($wp_scripts->to_do[$this->handle]);
      $wp_scripts->to_do = array_flip( $wp_scripts->to_do );
      array_unshift($wp_scripts->to_do, $this->handle);
      $wp_scripts->do_items(false, 0);
    }
    
  }
  /**
   * Action hook for wp_print_styles
   * 
   * @since 1.0.0
   */
  function wp_print_styles()
  {
    $regex = get_option( 'mn_exclude_css_regex', $this->exclude_css_regex );
    if( $regex !== "" && preg_match($regex, $_SERVER["REQUEST_URI"]) ) return;    
    
    global $wp_styles;
    
    $compile_mode = get_option( 'mn_compile_mode', $this->compile_mode );
    $assets = get_option( 'mn_comine_assets', $this->default );//get the list of files we can compress/combine      
    $mtimes = array();
    
    $url = get_bloginfo("wpurl");//we need the blogs url to assist in comparisons later
    
    /* Make sure we have something to do here */
    if( count( $wp_styles->registered ) == 0 || count( $assets['combine']['css'] ) === 0 )
      return false;

    /* Let's get down to the styles we need for the page */
    $queue = $wp_styles->queue;
    $wp_styles->all_deps($queue);
    $to_do = $wp_styles->to_do;
    
    $styles = array();
    foreach ($to_do as $key => $handle) 
    {
      $src = $use = $wp_styles->registered[$handle]->src;
      /* This is an external script. We may not be able to grab it. Let's not deal with it */
      if( preg_match( "*(http://)(?!".$_SERVER["SERVER_NAME"].")*", $src ) )
        continue;
      
      $styles[$handle] = (object)array( 'src' => $src );
      //check if the source has the full wp site url in it and remove it if it doest
      if( strstr($use, $url) )
        $use = str_replace( $url, "", $use );
      
      //store whether or not this file matches a file to combine
      $match = false;
      //loop the files list to combine
      foreach($assets['combine']['css'] as $css )
        //if the file is in the list
        if( strstr( $css, $use ) )
        {
          //we have a match, we'll continue below
          $match = true;
          break;
        }
      //file isn't in the combine list
      if( !$match )
        continue;    
      
      if( "development" === $compile_mode )
      {
        $dev_src = $this->local_path( $src );
        $mtimes[] = filemtime( $dev_src );
      }
      
      unset( $wp_styles->registered[$handle] );
      wp_dequeue_style( $handle );
    }
    /* We dequeued, but really make sure we're not going to get these styles in here again */
    foreach ($wp_styles->queue as $key => $handle)
      if ( isset( $styles[$handle] ) )
        unset( $wp_styles->queue[$key] );
    
    $keys = implode( ',', array_keys( $styles ) ) . implode( ',', $mtimes );
    $hash = md5( $keys );
    $file = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $hash . ".css";
    
    //make sure we have a place to put this in case we wipe out the whole cache file to do a quick clear
    if( !is_dir( dirname( $file ) ) )
      mkdir( dirname( $file ), 0755, true );
  
    if( !is_file( $file ) )
      $this->write_style_cache( $hash, $styles );
    
    else 
      $this->enqueue_packed_style( $hash );
    
  }
  /**
   * Combines the files and puts them into a cached file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to write to
   * @param array $data List of file objects with src and path info
   * @param boolean $footer Whether or not to enqueue in the footer
   */
  function write_style_cache( $file, $data )
  {
    $cache = $file;
    $path = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $file . ".css";
    
    $implode = array_keys( (array)$data );
    $implode = implode( ", ", $implode );
    //clear the cache file if for some reason it existed already; but it shouldn't
    if( is_file( $path ) )
      file_put_contents( $path, "" );
    
    foreach( $data as $key => $info )
    {
      $f = $info;
      /* We're looking for our files on the server; converting url to location */
      $src = $this->local_path( $f->src );
      
      if( !is_file( $src ) )
        continue;
            
      $content = file_get_contents( $src );
      $content = $this->compress_css($content, $path, $src);
      file_put_contents( $path, "/*$key*/\n$content\n\n", FILE_APPEND | LOCK_EX );
    }
    //get the path of the newly created file
    if( !is_file( $path ) )
      return;
    
    else
    {
      $contents = file_get_contents( $path );
      if( empty( $contents ) )
        return;
      
      file_put_contents( $path, "/*$implode*/\n\n" . $contents );
    }
    $this->enqueue_packed_style( $cache );
  }
  /**
   * Enqueue a cached script file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to enqueue
   */
  function enqueue_packed_style( $file )
  {
    $handle = 'mn_cache_' . uniqid();
    $path = $this->uploads['baseurl'] . '/' . $this->upload_dir . '/' . $file . ".css";
    
    wp_enqueue_style( $handle, preg_replace("|https?:|","",$path), null, 0);
    
    global $wp_styles;
    if ( ! is_a( $wp_styles, 'WP_Styles' ) )
      $wp_styles = new WP_Styles();
  
    $wp_styles->do_items();
  }
  /**
   * Compresses css
   * 
   * @since 1.0.0
   * 
   * @param string $css
   * @param string $path
   * @param string $src
   */
  function compress_css($css, $path, $src) 
  {
    //fix urls in the css before handling imports
    $css = $this->url_css($css, $path, $src);
    //find any imports
    $css = $this->import_css($css, $path, $src);
    
    // remove comments, tabs, spaces, newlines, etc. if css_compress == 1
    if( '1' == get_option( 'mn_css_compression', $this->css_compression ) )
    {
      $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
      $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
    }
    return $css;
  }
  /**
   * Handles css url paths
   * 
   * @since 1.0.0
   * 
   * @param string $css
   * @param string $path
   * @param string $src
   */
  function url_css($css, $path, $src)
  {
    $this->dir = dirname($src).'/';
    $css = preg_replace_callback(
      '|url\(\'?"?([^\"\')]*)\'?"?\)|',
      array( $this, 'url_css_callback' ),
      $css
    );
    return $css;
  }
  /**
   * callback for replacing found css urls
   * 
   * @param array $matches
   * 
   * @since 1.0.0
   */
  function url_css_callback($matches)
  {
    $path = $this->dir . $matches[1];
    if( strstr( $path, "./" ) )
      $path = $this->canonicalize( $path );
    
    $path = str_replace( ABSPATH, "/", $path );
    
    return "url(\"$path\")";
  }
  /**
   * Handle css @import
   * 
   * @since 1.0.0
   * 
   * @param string $css
   * @param string $path
   * @param string $src
   */
  function import_css($css, $path, $src)
  {
    //Find import statements
    if( preg_match_all('/@import\s+(.*)/', $css, $matches) )
      foreach( $matches[1] as $match )
        $css_imports[] = $match;
    
    //if we're importing into this css file then we need to find 
    if( !empty($css_imports) )
      foreach($css_imports as $file )
      {
        $file = preg_replace( '/[;\'"]/', '', $file );
        $file = dirname($src) . '/' . $file;
        if( strstr( $src, "./" ) )
          $file = $this->canonicalize( $file ); 
        
        //get the imported file contents and put it where the @import statement was
        if( $content = @file_get_contents( $file ) ) 
        {
          //run the content through the same filters in case we have nested imports / urls
          $content = $this->url_css($content, $path, $file);
          $content = $this->import_css($content, $path, $file);
          //finally replace the import statement with the file's contents
          $css = preg_replace( '/@import\s+.*/', "\n" . $content . "\n", $css, 1 );
        }
      }
    return $css;
  }
  /**
   * Display error messages in the admin to users
   * TODO: Log compile or include errors and display them in the admin for more usability
   * 
   * @since 1.0.0
   * 
   * @var mixed $errors
   */
  protected function handleError( $errors )
  {
    //do something here with errors
    var_dump($errors);
    exit;
  }
  /**
   * find a css url realpath
   * 
   * @since 1.0.0
   * 
   * @param string $address
   */
  function canonicalize($address)
  {
    $address = explode('/', $address);
    $keys = array_keys($address, '..');

    foreach($keys AS $keypos => $key)
      array_splice($address, $key - ($keypos * 2 + 1), 2);

    $address = implode('/', $address);
    $address = str_replace('./', '', $address);
    
    return $address;
  }
}
/**
 * Class to override php RecursiveRegexIterator for finding file extensions
 * 
 * @since 1.0.0
 * 
 * @var RecursiveIterator $it
 * @var string $regex
 */
abstract class FilesystemRegexFilter extends RecursiveRegexIterator {
    protected $regex;
    public function __construct(RecursiveIterator $it, $regex) {
        $this->regex = $regex;
        parent::__construct($it, $regex);
    }
}
/**
 * Filter file extensions found by regex
 * 
 * @since 1.0.0
 * 
 * @var RecursiveIterator $it
 * @var string $regex
 */
class FilenameFilter extends FilesystemRegexFilter {
    /**
     * Filter files against the regex
     * 
     * @since 1.0.0
     */ 
    public function accept() {
        return ( ! $this->isFile() || preg_match($this->regex, $this->getFilename()));
    }
}
/**
 * Filter out folders by name in regex
 * 
 * @since 1.0.0
 * 
 * @var RecursiveIterator $it
 * @var string $regex
 */
class DirnameFilter extends FilesystemRegexFilter {
    /**
     * Filter directories against the regex
     * 
     * @since 1.0.0
     */
    public function accept() {
        return ( ! $this->isDir() || preg_match($this->regex, $this->getFilename()));
    }
}