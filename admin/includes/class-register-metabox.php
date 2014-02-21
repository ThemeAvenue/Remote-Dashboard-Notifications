<?php
/** 
 * Dynamically Register Metaboxes
 * 
 * This class will dynamically register new metaboxes
 * for any custom post types. It will also register the
 * custom fields in the metaboxes, handle the saving and
 * display the metabox.
 * 
 * @author Julien Liabeuf <julien.liabeuf@n2clic.com> 
 * @copyright 2013 ThemeAvenue
 * @package ThemeAvenue Framework
 * @since 3.0
 */
class TAV_Register_Metabox {

	/**
	 * Prefix
	 *
	 * String used to prefix all custom fields input names attributes
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $prefix = 'tav_mb_';

	public function __construct( $init ) {

		/* Metabox settings */
		$this->id 			= isset( $init['id'] ) ? $init['id'] : false;
		$this->title 		= isset( $init['title'] ) ? $init['title'] : false;
		$this->post_type 	= isset( $init['post_type'] ) ? $init['post_type'] : false;
		$this->options 		= isset( $init['options'] ) ? $init['options'] : false;
		$this->context 		= isset( $init['context'] ) ? $init['context'] : 'normal';
		$this->priority 	= isset( $init['priority'] ) ? $init['priority'] : 'default';
		$this->type 		= isset( $init['type'] ) ? $init['type'] : 'std';
		$this->fake_pt 		= isset( $init['fake_pt'] ) ? $init['fake_pt'] : false;

		if( false === ( $this->id || $this->title || $this->post_type || $this->options ) )
			return;

		/* Security nonce settings */
		$this->nonce_name 	= isset( $init['nonce_name'] ) ? $init['nonce_name'] : 'tav_mb_nonce';
		$this->nonce_action = isset( $init['nonce_action'] ) ? $init['nonce_action'] : 'tav_save_cf';

		/* Hook the save function */
		add_action( 'save_post', array( $this, 'saveCustomFields' ) );

		if( 'cpt' == $this->type ) {

			/* Hook the save function */
			add_action( 'tav_after_save_custom_fields', array( $this, 'saveFakeTaxonomy' ) );

		}

		/* Register the metabox */
		add_action( 'add_meta_boxes', array( $this, 'registerMetabox' ) );

		/* Load required resources */
		add_action( 'admin_print_scripts', array( $this, 'load_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'load_styles' ) );

	}

	/**
	 * Enqueue scripts required by the options used
	 */
	private function load_scripts() {

		$options = $this->options;

		foreach( $options as $option ) {

			$type = isset( $option['type'] ) ? $option['type'] : false;

			switch( $type ):

				case 'colorpicker':

					wp_enqueue_script( 'tav-colorpicker-script', plugins_url( 'my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );

				break;

			endswitch;

		}

	}

	/**
	 * Enqueue styles required by the options used
	 */
	private function load_styles() {

		$options = $this->options;

		foreach( $options as $option ) {

			$type = isset( $option['type'] ) ? $option['type'] : false;

			switch( $type ):

				case 'colorpicker':

					wp_enqueue_style( 'wp-color-picker' );

				break;

			endswitch;

		}

	}

	/**
	 * This function allows to dynamically
	 * register metaboxes for any post type
	 */
	public function registerMetabox() {

		/* If there are multiple post types we loop */
		if( is_array( $this->post_type ) ) {

			foreach( $this->post_type as $pt ) {

				/* Register a standard metabox with option fields */
				if( 'std' == $this->type ) {

					add_meta_box( $this->id, $this->title, array( $this, 'displayMetabox' ), $pt, $this->context, $this->priority );

				}

				/* Register a metabox with embeded media manager */
				elseif( 'media' == $this->type ) {

					add_meta_box( $this->id, $this->title, array( $this, 'MetaboxEmbedMedia' ), $pt, $this->context, $this->priority );

				}

			}
		}

		/* If not we register only one metabox */
		else {

			/* Register a standard metabox with option fields */
				if( 'std' == $this->type ) {

					/* Register the metabox */
					add_meta_box( $this->id, $this->title, array( $this, 'displayMetabox' ), $this->post_type, $this->context, $this->priority );

				}

				/* Register a metabox with embeded media manager */
				elseif( 'media' == $this->type ) {

					add_meta_box( $this->id, $this->title, array( $this, 'MetaboxEmbedMedia' ), $this->post_type, $this->context, $this->priority );

				}

				/* Register a metabox with fake taxonomy based on a CPT */
				elseif( 'cpt' == $this->type ) {

					add_meta_box( $this->id, $this->title, array( $this, 'FakeTaxonomyCPT' ), $this->post_type, $this->context, $this->priority );

				}
		}

	}

	/*public function addOption( $args ) {

		if( !isset( $args ) || ( empty( $args ) || !isset( $args['id'] ) || !isset( $args['title'] ) || !isset( $args['callback'] ) ) )
			return;

		$option = array(
			'id' 		=> $args['id'],
			'title' 	=> $args['title'],
			'callback' 	=> $args['callback'],
			'group' 	=> 'metabox'
		);

		isset( $args['desc'] ) ? $option['desc'] = $args['desc'] : false;
		isset( $args['opts'] ) ? $option['opts'] = $args['opts'] : false;
		isset( $args['level'] ) ? $option['level'] = $args['level'] : false;
		isset( $args['validate'] ) ? $option['validate'] = $args['validate'] : false;
		isset( $args['stars'] ) ? $option['stars'] = $args['stars'] : false;
		isset( $args['limit'] ) ? $option['limit'] = $args['limit'] : false;

		array_push( $this->options, $option );

	}*/

	/**
	 * Return the options list
	 */
	public function getOptions() {

		return $this->options;
	}

	/**
	 * This function will output the registered
	 * metaboxes
	 */
	public function displayMetabox() { ?>

		<table class="form-table <?php echo $this->id; ?>-metabox-table">
			<tbody>

				<?php
				$options = apply_filters( 'tav_mb_options', $this->options );

				foreach( $options as $option ) {

					/* Output the options fields */
					$this->output( $option );

				}

				/* Add security nonce */
				wp_nonce_field( $this->nonce_action, $this->nonce_name, false, true );
				?>

			</tbody>
		</table>
	<?php }

	public function MetaboxEmbedMedia() {

		if( !isset( $_GET['post'] ) ) {

			$notice = apply_filters( 'tav_media_metabox_notice', __( 'Please save the post before you can access the photo upload.', 'themeavenue' ) );

			?><div class="updated below-h2" style="width:99%;"><p><?php echo $notice; ?></p></div><?php

			return;
		}

		$tab = 'type';

		$args = array(
			'post_type'					=> 'attachment',
			'post_status'				=> 'inherit',
			'post_parent'				=> $_GET['post'],
			'posts_per_page'			=> 1,
			'fields' 					=> 'ids',
			'update_post_term_cache' 	=> false,
			'update_post_meta_cache' 	=> false,
		);

		$attachments = new WP_Query( $args );

		if( !empty( $attachments->posts ) ) {

			$tab = 'gallery';

		}

		$source = admin_url( 'media-upload.php?post_id=' . $_GET['post'] . '&width=670&height=400&tab=' . $tab );
		?>
		<iframe src="<?php echo esc_url( $source ); ?>" width="100%" height="400"><?php _e( 'Your browser does not support iFrames. Please update your browser', 'themeavenue' ); ?></iframe>
		<?php

	}

	public function FakeTaxonomyCPT() {

		/* Make sure we have the CPT to fake as a taxo */
		if( !$this->fake_pt )
			return;

		/* Get the PT object */
		$pt = get_post_type_object( $this->fake_pt );

		/* Make sure the PT actually exists */
		if( null == $pt )
			return;

		$args = array(
			'post_type' 				=> $this->fake_pt,
			'posts_per_page' 			=> -1,
			'update_post_term_cache' 	=> false,
			'update_post_meta_cache' 	=> false,

		);

		$posts = new WP_Query( $args );

		if( empty( $posts->posts ) )
			return;

		$massages = array();

		if( isset( $_GET['post'] ) )
			$massages = get_post_meta( $_GET['post'], $this->prefix . $this->id, true );
		?>

		<div id="taxonomy-<?php echo $pt->rewrite['slug']; ?>" class="categorydiv">
			<ul id="<?php echo $pt->rewrite['slug']; ?>-tabs" class="category-tabs"><li class="tabs"><?php echo $pt->labels->all_items; ?></li></ul>

			<div id="<?php echo $pt->rewrite['slug']; ?>-all" class="tabs-panel">
				<input type='hidden' name='tax_input[city][]' value='0' />
				<ul id="<?php echo $pt->rewrite['slug']; ?>checklist" >

					<?php foreach( $posts->posts as $key => $post ): ?>
						<li id='<?php echo $pt->rewrite['slug']; ?>-<?php echo $post->ID; ?>'>
							<label class="selectit">
								<input value="<?php echo $post->ID; ?>" type="checkbox" name="<?php echo $this->id; ?>[]" id="in-<?php echo $pt->rewrite['slug']; ?>-<?php echo $post->ID; ?>" <?php if( in_array( $post->ID, $massages) ) { echo 'checked="checked"'; } ?> /> <?php echo $post->post_title; ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div id="<?php echo $pt->rewrite['slug']; ?>-adder" class="wp-hidden-children">
				<h4><a id="<?php echo $pt->rewrite['slug']; ?>-add" href="<?php echo admin_url( "post-new.php?post_type=$this->fake_pt" ); ?>" class="hide-if-no-js">+ <?php echo $pt->labels->add_new_item; ?></a></h4>
			</div>
		</div>

	<?php }

	public function saveCustomFields() {

		/* We cancel loading if no POST data is submitted */
		if( !isset( $_POST['ID'] ) )
			return;

		// verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $_POST['ID'] ) ) 
			return;
		
		$name 	 = "_$this->id"; // Add the underscore to hide the custom field
		$post_id = $_POST['ID'];

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if( !isset( $_POST[$this->nonce_name] ) || isset( $_POST[$this->nonce_name] ) && !wp_verify_nonce( $_POST[$this->nonce_name], $this->nonce_action ) )
			return;

		// Check permissions
		if( is_array( $this->post_type ) && !in_array( $_POST['post_type'], $this->post_type ) ) {
			return;
		} elseif( !is_array( $this->post_type ) && $this->post_type != $_POST['post_type'] ) {
			return;
		}
		
		if( !current_user_can( 'edit_pages', $post_id ) ) {
			return;
		}

		/* Get the list of options for this metabox */
		$options = $this->getOptions();

		/* This is our options array */
		$opts = array();
		
		/* We're authenticated, let's save all the options */
		/* We loop through all the options here */
		foreach( $options as $key => $opt ) {

			/* If the option is submitted, ad it to the array */
			if ( isset( $_POST[$opt['id']] ) ) {

				$opts[$opt['id']] = $_POST[$opt['id']];
				
			}

		}

		if( empty( $opts ) )
			delete_post_meta( $post_id, $name );

		else
			update_post_meta( $post_id, $name, $opts );

		do_action( 'tav_after_save_custom_fields' );
			
		return $post_id;
	}

	public function saveFakeTaxonomy() {

		$prefix 	= $this->prefix;
		$post_id 	= $_POST['ID'];
		$opt 		= $prefix . $this->id;
		$oldval 	= get_post_meta( $post_id, $opt, true );

		if( isset( $_POST[$this->id] ) ) {			

			update_post_meta( $post_id, $opt, $_POST[$this->id] );

		} elseif( '' != $oldval ) {

			delete_post_meta( $post_id, $opt, $oldval );

		}

	}

	public function get_value( $option, $default = false, $post_id = false ) {

		$pid = isset( $_GET['post'] ) ? $_GET['post'] : $post_id;

		if( false === $pid )
			return $default;

		$set = get_post_meta( $pid, "_$this->id", true );

		return isset( $set[$option] ) ? $set[$option] : $default;

	}

	/**
	 * Output the field markup
	 *
	 * @param (array) Option arguments
	 */
	private function output( $args = array() ) {

		/* No arguments? No output! */
		if( empty( $args ) )
			return;

		/* Prepare required fields */
		$id 	= isset( $args['id'] ) ? $args['id'] : false;
		$title 	= isset( $args['title'] ) ? $args['title'] : false;
		$type 	= isset( $args['type'] ) ? $args['type'] : false;

		/* If any of the required fields isn't set we abort */
		if( false === ( $id || $title || $type ) )
			return;

		/* Prepare the possible classes */
		$container_class = isset( $args['container_class'] ) ? $args['container_class'] : array();

		/* If class is string we convert it to array */
		if( !is_array( $container_class ) && '' != $container_class )
			explode( ' ', $container_class );

		/* Get all the classes */
		$class = array_merge( array( 'tav-option' ), $container_class );

		/* Then turn it into string */
		$class = implode( ' ', $class );

		/* Get the field value */
		$value = $this->get_value( $id );

		/* Open the danse... */
		?>
		<tr valign="top" class="<?php echo $class; ?>">
			<td scope="row"><label for="<?php echo $id; ?>"><?php echo $title; ?></label></td>

		<?php
		/* Now let's find the appropriate output field */
		switch( $type ):

			case 'text': ?>

				<td>
					<input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $value; ?>" class="regular-text" />
				</td>
				<td>
					<?php if( isset( $args['desc'] ) ): ?><span class="description"><?php echo $args['desc']; ?></span><?php endif; ?>
				</td>

			<?php break;

		endswitch;
		?>

		</tr>

		<?php
	}
}