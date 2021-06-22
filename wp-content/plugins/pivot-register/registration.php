<?php
/*
Plugin Name: Pivot Register
Description: Authenticate User and Gallery
Version: 1.0
Author: Saquib
*/
// Disallow direct access
if( !defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed.' );
}

define('MAX_UPLOAD_SIZE', 200000);
define('TYPE_WHITELIST', serialize(array(
  'image/jpeg',
  'image/png',
  'image/gif'
  )));

class pivot {

    public function __construct($file) {
		if (is_admin()) {
			add_action('init', array(&$this, 'pivot_plugin_init' ));
		} else {
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
			add_shortcode('register_code', array(&$this, 'display_register'));
			add_action('user_register', array(&$this, 'addMyCustomMeta'));    
			add_action('personal_options_update', array(&$this,'addMyCustomMeta' ));    
			add_action('edit_user_profile_update', array(&$this,'addMyCustomMeta' ));    
			add_shortcode('pivot_form', array(&$this, 'pivot_form_shortcode' ));
			add_action('init', array(&$this, 'pivot_plugin_init' ));
		}
    }
   
    public function addMyCustomMeta( $user_id ) {    
		update_user_meta( $user_id, 'region', $_POST['region'] ); 
	}

    public function pivot_plugin_init(){

		$image_type_labels = array(
		  'name' => _x('User images', 'post type general name'),
		  'singular_name' => _x('User Image', 'post type singular name'),
		  'add_new' => _x('Add New User Image', 'image'),
		  'add_new_item' => __('Add New User Image'),
		  'edit_item' => __('Edit User Image'),
		  'new_item' => __('Add New User Image'),
		  'all_items' => __('View User Images'),
		  'view_item' => __('View User Image'),
		  'search_items' => __('Search User Images'),
		  'not_found' =>  __('No User Images found'),
		  'not_found_in_trash' => __('No User Images found in Trash'), 
		  'parent_item_colon' => '',
		  'menu_name' => 'User Images'
		);
		
		$image_type_args = array(
		  'labels' => $image_type_labels,
		  'public' => true,
		  'query_var' => true,
		  'rewrite' => true,
		  'capability_type' => 'post',
		  'has_archive' => true, 
		  'hierarchical' => false,
		  'map_meta_cap' => true,
		  'menu_position' => null,
		  'supports' => array('title', 'editor', 'author', 'thumbnail')
		); 
		
		register_post_type('user_images', $image_type_args);
	  
		$image_category_labels = array(
		  'name' => _x( 'User Image Categories', 'taxonomy general name' ),
		  'singular_name' => _x( 'User Image', 'taxonomy singular name' ),
		  'search_items' =>  __( 'Search User Image Categories' ),
		  'all_items' => __( 'All User Image Categories' ),
		  'parent_item' => __( 'Parent User Image Category' ),
		  'parent_item_colon' => __( 'Parent User Image Category:' ),
		  'edit_item' => __( 'Edit User Image Category' ), 
		  'update_item' => __( 'Update User Image Category' ),
		  'add_new_item' => __( 'Add New User Image Category' ),
		  'new_item_name' => __( 'New User Image Name' ),
		  'menu_name' => __( 'User Image Categories' ),
		); 	
	  
		$image_category_args = array(
		  'hierarchical' => true,
		  'labels' => $image_category_labels,
		  'show_ui' => true,
		  'query_var' => true,
		  'rewrite' => array( 'slug' => 'user_image_category' ),
		);
		
		register_taxonomy('pivot_image_category', array('user_images'), $image_category_args);
		
		$default_image_cats = array('humor', 'landscapes', 'sport', 'people');
		
		foreach($default_image_cats as $cat){
		
		  if(!term_exists($cat, 'pivot_image_category')) wp_insert_term($cat, 'pivot_image_category');
		  
		}
		  
	}

	public function pivot_form_shortcode(){

		if(!is_user_logged_in()){

		  return '<p>You need to be logged in to submit an image.</p>';    
		}
	  
		global $current_user;
		  
		if(isset( $_POST['pivot_upload_image_form_submitted'] ) && wp_verify_nonce($_POST['pivot_upload_image_form_submitted'], 'pivot_upload_image_form') ){  
		  
		  $result = $this->pivot_parse_file_errors($_FILES['pivot_image_file'], $_POST['pivot_image_caption']);
		  $region = $_POST['region'];
		  if($result['error']){
		  
			echo '<p>ERROR: ' . $result['error'] . '</p>';
		  
		  }else{
	  
			$user_image_data = array(
				'post_title' => $result['caption'],
			  'post_status' => 'pending',
			  'post_author' => $current_user->ID,
			  'post_type' => 'user_images'     
			);
			
			if($post_id = wp_insert_post($user_image_data)){
			  
			  $this->pivot_process_image('pivot_image_file', $post_id, $result['caption'],$region);
	  
			  if ($_FILES['pivot_image_file_gallery']['size'] == 0 && $_FILES['pivot_image_file_gallery']['error'] == 0)
				 {
				   exit;
				 }
				 else {
				  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				  require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	  
				  $files = $_FILES["pivot_image_file_gallery"]; 
				  foreach ($files['name'] as $key => $value) {            
							if ($files['name'][$key]) { 
								$file = array( 
									'name' => $files['name'][$key],
									'type' => $files['type'][$key], 
									'tmp_name' => $files['tmp_name'][$key], 
									'error' => $files['error'][$key],
									'size' => $files['size'][$key]
								); 
								$_FILES = array ("pivot_image_file_gallery" => $file); 
								$i=1;
								foreach ($_FILES as $file => $array) {  
									// $newupload = $this->pivot_process_image($file, $post_id, $result['caption']);            
									$attachment_id = media_handle_upload($file, $post_id);
									//var_dump($attachment_id);
									$vv[] = $attachment_id;
									$i++;
								}
							} 
						  }
						  update_post_meta($post_id, 'pivot_image_file_gallery', json_encode($vv)); 
				   }
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  wp_set_object_terms($post_id, (int)$_POST['pivot_image_category'], 'pivot_image_category');
			
			}
		  }
		}  
	  
		if (isset( $_POST['pivot_form_delete_submitted'] ) && wp_verify_nonce($_POST['pivot_form_delete_submitted'], 'pivot_form_delete')){
	  
		  if(isset($_POST['pivot_image_delete_id'])){
		  
			if($user_images_deleted = $this->pivot_delete_user_images($_POST['pivot_image_delete_id'])){        
			
			  echo '<p>' . $user_images_deleted . ' images(s) deleted!</p>';
			  
			}
		  }
		}
		
	  
		echo $this->pivot_get_upload_image_form($pivot_image_caption = $_POST['pivot_image_caption'], $pivot_image_category = $_POST['pivot_image_category']);
		
		if($user_images_table = $this->pivot_get_user_images_table($current_user->ID)){
		
		  echo $user_images_table;
		  
		}
	  
	}

	public function pivot_delete_user_images($images_to_delete){

		$images_deleted = 0;
	  
		foreach($images_to_delete as $user_image){
	  
		  if (isset($_POST['pivot_image_delete_id_' . $user_image]) && wp_verify_nonce($_POST['pivot_image_delete_id_' . $user_image], 'pivot_image_delete_' . $user_image)){
		  
			if($post_thumbnail_id = get_post_thumbnail_id($user_image)){
	  
			  wp_delete_attachment($post_thumbnail_id);      
	  
			}  
	  
			wp_trash_post($user_image);
			
			$images_deleted ++;
	  
		  }
		}
	  
		return $images_deleted;
	  
	  }
	  
	  
	  public function pivot_get_user_images_table($user_id){
	  
		$args = array(
		  'author' => $user_id,
		  'post_type' => 'user_images',
		  'post_status' => 'pending'    
		);
		
		$user_images = new WP_Query($args);
	  
		if(!$user_images->post_count) return 0;
		
		$out = '<div class="col-sm-6">';
		$out .= '<p>Your unpublished images - Click to see full size</p>';
		
		$out .= '<form method="post" action="">';
		
		$out .= wp_nonce_field('pivot_form_delete', 'pivot_form_delete_submitted');  
		
		$out .= '<table id="user_images">';
		$out .= '<thead><th>Image</th><th>Caption</th><th>Category</th><th>Delete</th></thead>';
		  
		foreach($user_images->posts as $user_image){
		
		  $user_image_cats = get_the_terms($user_image->ID, 'pivot_image_category');
		  
		  foreach($user_image_cats as $cat){
		  
			$user_image_cat = $cat->name;
		  
		  }
		  
		  $post_thumbnail_id = get_post_thumbnail_id($user_image->ID);   
	  
		  $out .= wp_nonce_field('pivot_image_delete_' . $user_image->ID, 'pivot_image_delete_id_' . $user_image->ID, false); 
			 
		  $out .= '<tr>';
		  $out .= '<td>' . wp_get_attachment_link($post_thumbnail_id, 'thumbnail') . '</td>';    
		  $out .= '<td>' . $user_image->post_title . '</td>';
		  $out .= '<td>' . $user_image_cat . '</td>';    
		  $out .= '<td><input type="checkbox" name="pivot_image_delete_id[]" value="' . $user_image->ID . '" /></td>';          
		  $out .= '</tr>';
		  
		}
	  
		$out .= '</table>';
		  
		$out .= '<input type="submit" name="pivot_delete" value="Delete Selected Images" />';
		$out .= '</form></div>';  
		
		return $out;
	  
	  }
	  
	  
	  public function pivot_process_image($file, $post_id, $caption, $region ){
	   
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	   
		$attachment_id = media_handle_upload($file, $post_id);
	  
		update_post_meta($post_id, '_thumbnail_id', $attachment_id);
		update_post_meta($post_id, '_region', $region);
		
	  
		$attachment_data = array(
			'ID' => $attachment_id,
		  'post_excerpt' => $caption
		);
		
		wp_update_post($attachment_data);
	  
		return $attachment_id;
	  
	  }
	  
	  
	  public function pivot_parse_file_errors($file = '', $image_caption){
	  
		$result = array();
		$result['error'] = 0;
		
		if($file['error']){
		
		  $result['error'] = "No file uploaded or there was an upload error!";
		  
		  return $result;
		
		}
	  
		$image_caption = trim(preg_replace('/[^a-zA-Z0-9\s]+/', ' ', $image_caption));
		
		if($image_caption == ''){
	  
		  $result['error'] = "Your caption may only contain letters, numbers and spaces!";
		  
		  return $result;
		
		}
		
		$result['caption'] = $image_caption;  
	  
		$image_data = getimagesize($file['tmp_name']);
		
		if(!in_array($image_data['mime'], unserialize(TYPE_WHITELIST))){
		
		  $result['error'] = 'Your image must be a jpeg, png or gif!';
		  
		}elseif(($file['size'] > MAX_UPLOAD_SIZE)){
		
		  $result['error'] = 'Your image was ' . $file['size'] . ' bytes! It must not exceed ' . MAX_UPLOAD_SIZE . ' bytes.';
		  
		}
		  
		return $result;
	  
	  }
	  
	  
	  
	  public function pivot_get_upload_image_form($pivot_image_caption = '', $pivot_image_category = 0){
	    global $current_user;
		$uid = $current_user->ID;
		$region = get_user_meta($uid,'region',true);
		$out = '<div class="row"><div class="col-sm-6">';
		$out .= '<form id="pivot_upload_image_form" method="post" action="" enctype="multipart/form-data">';
	  
		$out .= wp_nonce_field('pivot_upload_image_form', 'pivot_upload_image_form_submitted');
		
		$out .= '<label for="pivot_image_caption">Image Caption - Letters, Numbers and Spaces</label><br/>';
		$out .= '<input type="text" id="pivot_image_caption" name="pivot_image_caption" value="' . $pivot_image_caption . '"/><br/>';
		$out .= '<label for="pivot_image_category">Image Category</label><br/>';  
		$out .= $this->pivot_get_image_categories_dropdown('pivot_image_category', $pivot_image_category) . '<br/>';
		$out .= '<input type="hidden" name="region" value="DBX" /><br/>';  
		$out .= '<label for="pivot_image_file">Select Your Featured Image - ' . MAX_UPLOAD_SIZE . ' bytes maximum</label><br/>';  
		$out .= '<input type="file" size="60" name="pivot_image_file" id="pivot_image_file" >';
		$out .= '<label for="pivot_image_file">Select Your Gallery Image (Multiple Choose Option) - ' . MAX_UPLOAD_SIZE . ' bytes maximum</label><br/>';
		$out .= '<input type="file" size="60" name="pivot_image_file_gallery[]" id="pivot_image_file_gallery" multiple="multiple"><br/>';
		  
		$out .= '<input type="submit" id="pivot_submit" name="pivot_submit" value="Upload Image">';
	  
		$out .= '</form></div>';
	  
		return $out;
		
	  }
	  
	  
	  public function pivot_get_image_categories_dropdown($taxonomy, $selected){
	  
		return wp_dropdown_categories(array('taxonomy' => $taxonomy, 'name' => 'pivot_image_category', 'selected' => $selected, 'hide_empty' => 0, 'echo' => 0));
	  
	  }

   public function enqueue_scripts() { 
        $plugin_url = trailingslashit(plugins_url('', $plugin = __FILE__));
        wp_enqueue_style( 'style-pivot', esc_url($plugin_url . 'css/register.css') );
        wp_register_script('registerjs', esc_url($plugin_url . 'js/register.js'), array('jquery'), '1.4.0', false);
        wp_enqueue_script('registerjs');
   }

   public function my_handle_attachment($file_handler,$post_id,$set_thu=false) {
	  // check to make sure its a successful upload
	  if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();
	  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	  require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	  $attach_id = media_handle_upload( $file_handler, $post_id );
	  return $attach_id;
	}
   
   public function display_register() { ?>
	<div class="col-md-6 col-sm-6">
	<div id="login-register-password">

	<?php global $user_ID, $user_identity; if (!$user_ID) { ?>

	<ul class="tabs_login">
		<li class="active_login"><a href="#tab1_login">Login</a></li>
		<li><a href="#tab2_login">Register</a></li>
		<li><a href="#tab3_login">Forgot?</a></li>
	</ul>
	<div class="tab_container_login">
		<div id="tab1_login" class="tab_content_login">

			<?php $register = $_GET['register']; $reset = $_GET['reset']; if ($register == true) { ?>

			<h3>Success!</h3>
			<p>Check your email for the password and then return to log in.</p>

			<?php } elseif ($reset == true) { ?>

			<h3>Success!</h3>
			<p>Check your email to reset your password.</p>

			<?php } else { ?>

			<h3>Have an account?</h3>
			<p>Log in or sign up! It&rsquo;s fast &amp; <em>free!</em></p>

			<?php } ?>

			<form method="post" action="<?php bloginfo('url') ?>/wp-login.php" class="wp-user-form">
				<div class="username">
					<label for="user_login"><?php _e('Username'); ?>: </label>
					<input type="text" name="log" value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="20" id="user_login" tabindex="11" />
				</div>
				<div class="password">
					<label for="user_pass"><?php _e('Password'); ?>: </label>
					<input type="password" name="pwd" value="" size="20" id="user_pass" tabindex="12" />
				</div>
				<div class="login_fields">
					<div class="rememberme">
						<label for="rememberme">
							<input type="checkbox" name="rememberme" value="forever" checked="checked" id="rememberme" tabindex="13" /> Remember me
						</label>
					</div>
					<?php do_action('login_form'); ?>
					<input type="submit" name="user-submit" value="<?php _e('Login'); ?>" tabindex="14" class="user-submit" />
					<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
					<input type="hidden" name="user-cookie" value="1" />
				</div>
			</form>
		</div>

		<div id="tab2_login" class="tab_content_login" style="display:none;">
			<h3>Register for this site!</h3>
			<p>Sign up now for the good stuff.</p>
			<form method="post" action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" class="wp-user-form">
				<div class="username">
					<label for="user_login"><?php _e('Username'); ?>: </label>
					<input type="text" name="user_login" value="" size="20" id="user_login" tabindex="101" />
				</div>
				<div class="email">
					<label for="user_email"><?php _e('Your Email'); ?>: </label>
					<input type="text" name="user_email" value="" size="25" id="user_email" tabindex="102" />
				</div>
				<div class="region">
					<label for="region"><?php _e('Your Region'); ?>: </label>
					<select name="region">
						<option value="IND">INDIA</option>
						<option value="DBX">DUBAI</option
					</select>
				</div>
				

				<div class="login_fields">
					<?php do_action('register_form'); ?>
					<input type="submit" name="user-submit" value="<?php _e('Sign up!'); ?>" class="user-submit" tabindex="103" />
					<?php $register = $_GET['register']; if($register == true) { echo '<p>Check your email for the password!</p>'; } ?>
					<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?register=true" />
					<input type="hidden" name="user-cookie" value="1" />
				</div>
			</form>
		</div>

		<div id="tab3_login" class="tab_content_login" style="display:none;">
			<h3>Lose something?</h3>
			<p>Enter your username or email to reset your password.</p>
			<form method="post" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post') ?>" class="wp-user-form">
				<div class="username">
					<label for="user_login" class="hide"><?php _e('Username or Email'); ?>: </label>
					<input type="text" name="user_login" value="" size="20" id="user_login" tabindex="1001" />
				</div>
				<div class="login_fields">
					<?php do_action('login_form', 'resetpass'); ?>
					<input type="submit" name="user-submit" value="<?php _e('Reset my password'); ?>" class="user-submit" tabindex="1002" />
					<?php $reset = $_GET['reset']; if($reset == true) { echo '<p>A message will be sent to your email address.</p>'; } ?>
					<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?reset=true" />
					<input type="hidden" name="user-cookie" value="1" />
				</div>
			</form>
		</div>		
	</div>

	<?php } else { // is logged in ?>

	<div class="sidebox">
		<h3>Welcome, <?php echo $user_identity; ?></h3>
		<div class="usericon">
			<?php global $userdata; echo get_avatar($userdata->ID, 60); ?>

		</div>
		<div class="userinfo">
			<p>You&rsquo;re logged in as <strong><?php echo $user_identity; ?></strong></p>
			<p>
				<a href="<?php echo wp_logout_url('index.php'); ?>">Log out</a> | 
				<?php if (current_user_can('manage_options')) { 
					echo '<a href="' . admin_url() . '">' . __('Admin') . '</a>'; } else { 
					echo '<a href="' . admin_url() . 'profile.php">' . __('Profile') . '</a>'; } ?>

			</p>
		</div>
	</div>

	<div class="tab_content_gallery">
		<?php
	        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			global $current_user;
			$uid = $current_user->ID;
			$region = get_user_meta($uid, 'region', true);
			$args = array(
                'posts_per_page' => 2,
        		'paged' => $paged,
                'post_type' => array( 'user_images' ),
                'orderby'   => 'meta_value_num',
                'meta_key'  => 'pivot_image_file_gallery',
                'order'     => 'ASC',
                'meta_query'    => array(
                    array(
                        'key'       => '_region',
                        'value'     => $region,
                        'compare'   => '=',
                    ),
                ),
            );

			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) : ?>
			<?php 
			  while ( $the_query->have_posts() ) : $the_query->the_post(); 
				     $image_gal = get_post_meta(get_the_ID(),'pivot_image_file_gallery', true);
					 $gal_img = json_decode($image_gal);
					 $List = implode(', ', $gal_img);
					 echo '<div class="row">';
					 echo do_shortcode('[gallery ids="'.$List.'"]');
			     	 echo '</div>';
			  endwhile; 
			?>
			<!-- end of the loop -->
			<div class="pagination">
				<?php 
					echo paginate_links( array(
						'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'total'        => $the_query->max_num_pages,
						'current'      => max( 1, get_query_var( 'paged' ) ),
						'format'       => '?paged=%#%',
						'show_all'     => false,
						'type'         => 'plain',
						'end_size'     => 2,
						'mid_size'     => 1,
						'prev_next'    => true,
						'prev_text'    => sprintf( '<i></i> %1$s', __( 'Newer Posts', 'text-domain' ) ),
						'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Posts', 'text-domain' ) ),
						'add_args'     => false,
						'add_fragment' => '',
					) );
				?>
			</div>
		
			<?php wp_reset_postdata(); ?>
		
		    <?php else : ?>
			   <p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
		    <?php endif; ?>
	<?php } ?>

   </div>
   </div>

<?php  } } ?>
<?php 
$start = new pivot(__FILE__);
 ?>