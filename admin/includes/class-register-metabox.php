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
		$this->id 			= $init['id'];
		$this->title 		= $init['title'];
		$this->post_type 	= $init['post_type'];
		$this->context 		= isset( $init['context'] ) ? $init['context'] : 'normal';
		$this->priority 	= isset( $init['priority'] ) ? $init['priority'] : 'default';
		$this->type 		= isset( $init['type'] ) ? $init['type'] : 'std';
		$this->fake_pt 		= isset( $init['fake_pt'] ) ? $init['fake_pt'] : false;

		/* Metabox Custom Fields */
		$this->options 		= array();

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

	public function addOption( $args ) {

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

	}

	public function getOptions() {

		return $this->options;
	}

	/**
	 * This function will output the registered
	 * metaboxes
	 */
	public function displayMetabox() { ?>

		<table class="form-table <?php echo $this->prefix . '-metabox-table'; ?>">
			<tbody>

				<?php
				/* Output the options fields */
				$this->outputOptionsFields();

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

	protected function outputOptionsFields() {

		$options = $this->options;

		foreach( $options as $key => $option ) {

			$option['callback']( $option );

		}

	}

	public function saveCustomFields() {

		/* We cancel loading if no POST data is submitted */
		if( !isset( $_POST['ID'] ) )
			return;

		// verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $_POST['ID'] ) ) 
			return;
		
		$prefix       = $this->prefix;
		$post_id      = $_POST['ID'];

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

		$options = $this->getOptions();
		
		/* We're authenticated, let's save all the options */
		/* We loop through all the options here */
		foreach( $options as $key => $opt ) {

			$option = $prefix . $opt['id'];
			$oldval = get_post_meta( $post_id, $option, true );

			/* In case it is a checkbox and no value is checked, the field won't be set in the post. Hence we need to delete the row */
			if ( !isset( $_POST[TAV_THEME_SHORTNAME . '_' . $opt['id']] ) ) {
				
				/* If there is a previous value */
				if( '' != $oldval ) {

					/* We delete it */
					delete_post_meta( $post_id, $option );

				}

				/* Continue the loop */
				continue;
			}

			/* The current value that has been passed in the POST */
			$data = $_POST[TAV_THEME_SHORTNAME . '_' . $opt['id']];

			/* If there is no value for this option in DB */
			if( '' == $oldval ) {

				/* If there is a non empty value passed in the POST */
				if( '' != $data ) {

					/* We add the option */
					update_post_meta($post_id, $option, $data);

				}

			}

			/* If there is an existing value in DB */
			else {

				/* If the current value passed for this option is empty */
				if( !$data || $data == '' ) {

					/* We delete the previous value from DB */
					delete_post_meta( $post_id, $option );

				}

				/* If thee is a non empty value */
				else {

					/* We update this value in DB */
					update_post_meta( $post_id, $option, $data, $oldval );

				}
			}
		}

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
}



/* ----------------------------------------
* This function allows to dynamically
* register metaboxes for any post type
---------------------------------------- */
function n2_register_metaboxes( $options, $post_type, $callback = 'n2_loop_mb_options', $nonce_name = 'n2_nonce', $nonce_action = 'mb_nonce' ) {
	if( !function_exists('tav_save_metabox_custom_fields') ) {
		return 'The function required to save all the custom fields hasn\'t been found.';
	}

	$nonce = array($nonce_name, $nonce_action);

	foreach( $options as $mb => $opts ) {
		add_meta_box($mb, $opts['title'], $callback, $post_type, $opts['context'], $opts['priority'], array($opts['options'], $nonce));
	}

	add_action('save_post','tav_save_metabox_custom_fields', 10, 4);
	do_action('save_post', $post_type, $nonce, $options);
}

/* ----------------------------------------
* This function will output the registered
* metaboxes
---------------------------------------- */
function n2_loop_mb_options( $post, $options ) {
	if( !function_exists('n2_loop_through_options') ) {
		return 'The required function for parsing the options doesn\'t exist';
	} ?>
	<table class="form-table">
	  <tbody>
		<?php
		/* We get the first field */
		n2_loop_through_options($options['args'][0]);
		$nonce_name 	= $options['args'][1][0];
		$nonce_action 	= $options['args'][1][1];
		wp_nonce_field( $nonce_action, $nonce_name, false, true );
		?>
	  </tbody>
	</table>
 <?php }

/* ----------------------------------------
* Here is an example of how to correctly 
* register the metaboxes with their options
---------------------------------------- */
/*
$option_list = array(
	'metabox_name' => array(
		'title' 	=> __('Metabox Title', 'n2'),
		'context' 	=> 'normal',
		'priority' 	=> 'default',
		'options' 	=> array(
			array(
				'name' 		=> 'tagline',
				'type' 		=> 'text',
				'std' 		=> '',
				'desc' 		=> __('Will appear in the page header', 'n2'),
				'title' 	=> __('Tagline', 'n2')
			),
			array(
				'name' 		=> 'wip',
				'type' 		=> 'checkbox',
				'std' 		=> '',
				'desc' 		=> __('Is this work in progress?', 'n2'),
				'title' 	=> __('WIP', 'n2'),
				'options' 	=> array('Yes')
			),
		)
	),
);
*/
?>