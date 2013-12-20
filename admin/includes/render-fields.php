<?php
/**
 * All the functions below are meant to
 * render the different types of
 * fields available in the admin panel.
 * Each function can be used multiple times
 * for various fields. The parameters that
 * differentiate the fields have to be passed
 * in the options array.
 */

/**
 * This function will retrieve the option
 * based on the option's group.
 */
function tav_get_field_value( $field ) {

	$group = $field['group'];

	if( TAV_SHORTNAME . '_fw_options' == $group ) {

		$option = tav_get_fw_options( $field['id'] );

	} elseif( TAV_SHORTNAME . '_' . TAV_THEME_SHORTNAME . '_options' == $group ) {

		$option = tav_get_theme_options( $field['id'] );

	} elseif( 'metabox' == $group ) {

		if( !isset( $_GET['post'] ) )
			return false;

		$option = get_post_meta( $_GET['post'], '_' . TAV_THEME_SHORTNAME . '_' . $field['id'], true );

	}

	return $option;

}

function tav_get_field_name( $field ) {

	$group = $field['group'];

	if( TAV_SHORTNAME . '_fw_options' == $group || TAV_SHORTNAME . '_' . TAV_THEME_SHORTNAME . '_options' == $group ) {

		$name = $field['group'] . '[' . $field['id'] . ']';

	} elseif( 'metabox' == $group ) {

		$name = TAV_THEME_SHORTNAME . '_' . $field['id'];

	}

	return $name;

}

/* ----------------------------------------
* Output the file fields
---------------------------------------- */
function tav_file_upload( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-file-upload">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td>
			<input id="<?php echo $field['id']; ?>" name="<?php echo tav_get_field_name( $field ); ?>" type="text" value="<?php echo tav_get_field_value( $field ); ?>" class="file-input code">
			<input id="upload_image_button" value="<?php _e( 'Upload', 'themeavenue' ); ?>" class="tav_file_output" type="button">
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

<?php }

/* ----------------------------------------
* Output the image upload fields
---------------------------------------- */
function tav_image_upload_output( $field ) { ?>
	<div class="tav_optionsingle tav_upload tav_clearfix">
		<label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label>
		<input id="<?php echo $field['id']; ?>" size="36" name="<?php echo tav_get_field_name( $field ); ?>" type="url" class="medium-text" value="<?php echo tav_get_field_value( $field ); ?>" /> 
		<input id="<?php echo $field['id']; ?>_upload" value="Upload Image" class="button tav_file_upload" type="button" />
		<?php if( $field['desc'] && $field['desc'] != '' ): ?><small><?php echo $field['desc']; ?></small><?php endif; ?>
	</div>
<?php }

/* ----------------------------------------
* Output the text fields
---------------------------------------- */
function tav_text_field( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-text">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td><input type="text" id="<?php echo $field['id']; ?>" name="<?php echo tav_get_field_name( $field ); ?>" value="<?php echo tav_get_field_value( $field ); ?>" <?php tav_validation_pattern( $field ); ?> class="regular-text" /></td>
		<td><?php if( isset( $field['desc'] ) ): ?><span class="description"><?php echo $field['desc']; ?></span><?php endif; ?></td>
	</tr>

<?php }

/* ----------------------------------------
* Output the number fields
---------------------------------------- */
function tav_number_field( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-number">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td><input type="number" id="<?php echo $field['id']; ?>" name="<?php echo tav_get_field_name( $field ); ?>" value="<?php echo tav_get_field_value( $field ); ?>" <?php tav_validation_pattern( $field ); ?> class="regular-text code" /></td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

<?php }

/* ----------------------------------------
* Output the e-mail fields
---------------------------------------- */
function tav_email_field( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-email">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td><input type="email" id="<?php echo $field['id']; ?>" name="<?php echo tav_get_field_name( $field ); ?>" value="<?php echo tav_get_field_value( $field ); ?>" <?php tav_validation_pattern( $field ); ?> class="regular-text code" /></td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

<?php }

/* ----------------------------------------
* Output the URL fields
---------------------------------------- */
function tav_url_field( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-url">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td><input type="url" id="<?php echo $field['id']; ?>" name="<?php echo tav_get_field_name( $field ); ?>" value="<?php echo tav_get_field_value( $field ); ?>" <?php tav_validation_pattern( $field ); ?> class="regular-text code" /></td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

<?php }

/* ----------------------------------------
* Output colorpicker fields
---------------------------------------- */
function tav_color_picker_output( $field ) { ?>
	<div class="tav_optionsingle tav_clearfix" id="<?php echo $field['id']; ?>_cp">
		<label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label>
		<input class="colorpicker" name="<?php echo tav_get_field_name( $field ); ?>" id="<?php echo $field['id']; ?>" type="text" value="<?php echo tav_get_field_value( $field ); ?>" <?php if( isset($field['std']) ) echo 'data-default-color="'.$field['std'].'"'; ?> />	
		<?php if( $field['desc'] && $field['desc'] != '' ): ?><small><?php echo $field['desc']; ?></small><?php endif; ?>
	</div>
<?php }

/* ----------------------------------------
* Output the textarea fields
---------------------------------------- */
function tav_textarea( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-textarea">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td>
			<textarea id="<?php echo $field['id']; ?>" name="<?php echo tav_get_field_name( $field ); ?>" <?php if( isset( $field['limit'] ) ) { echo 'maxlength="' . $field['limit'] . '"'; } ?> rows="5"><?php echo tav_get_field_value( $field ); ?></textarea>
			<?php if( isset( $field['limit'] ) ): ?><p><?php printf( __( 'Content limited to <code>%s</code> characters', 'themeavenue' ), $field['limit'] ); ?></p><?php endif; ?>
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

<?php }

/* ----------------------------------------
* Output the WordPress WYSIWYG
---------------------------------------- */
function tav_full_wysiwyg( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-textarea">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td>
			<?php wp_editor( tav_get_field_value( $field ), $field['id'], array( 'textarea_name' => tav_get_field_name( $field ) ) ); ?>
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

<?php }

/* ----------------------------------------
* Output the WordPress WYSIWYG (light version)
---------------------------------------- */
function tav_light_wysiwyg( $field ) { ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-textarea">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td>
			<?php wp_editor( tav_get_field_value( $field ), $field['id'], array( 'textarea_name' => tav_get_field_name( $field ), 'teeny' => true, 'media_buttons' => false, 'quicktags' => false ) ); ?>
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

<?php }

/* ----------------------------------------
* Output font select field
---------------------------------------- */
function tav_font_output( $field ) {

	$current 		= tav_get_field_value( $field );
	$webfonts 		= tav_google_webfonts();
	$preview 		= array(
		'Brian is in the kitchen',
		'Le stylo est sur la table',
		'An empty purse frightens away friends',
		'An onion a day keeps everyone away',
		'Every why has a wherefore',
		'First come, first served',
		'Fool me once, shame on you; fool me twice, shame on me'
	);
	$count 			= count( $preview );
	$aleat 			= rand( 0, $count-1 );
	$standard_fonts = array(
		'Arial, "Helvetica Neue", Helvetica, sans-serif',
		'Verdana, Geneva, sans-serif',
		'TimesNewRoman, "Times New Roman", Times, Baskerville, Georgia, serif',
		'Georgia, Times, "Times New Roman", serif',
		'Tahoma, Verdana, Segoe, sans-serif',
		'"Arial Narrow", Arial, sans-serif'
	);
	?>
	<div class="font_settings_container">
		<table class="font_settings">
			<tr>
				<th colspan="5"><?php echo $field['title']; ?></th>
			</tr>
			<tr>
				<td style="width:360px;"><?php _e('Pick up a font', 'n2cpanel'); ?></td>
				<td><?php _e('Color', 'n2cpanel'); ?></td>
				<td colspan="2"><?php _e('Size &amp; Unit', 'n2cpanel'); ?></td>
				<!-- <td><?php _e('Preview', 'n2cpanel'); ?></td> -->
			</tr>
			<tr>
				<td>
					<select name="<?php echo tav_get_field_name( $field ); ?>" class="font_picker" id="<?php echo $field['id']; ?>" style="width:360px;">
						<option value="" <?php if( !$current ) { echo 'selected="selected"'; } ?>><?php _e('Keep original font', 'n2cpanel'); ?></option>
						<optgroup label="<?php _e('Standard Fonts', 'n2cpanel'); ?>">
							<?php
							foreach( $standard_fonts as $font ) { ?>
								<option value='<?php echo $font; ?>' <?php if( $current == $font ) { echo 'selected="selected"'; } ?>><?php echo $font; ?></option>
							<?php }
							?>
						</optgroup>
						<optgroup label="<?php _e('Google WebFonts', 'n2cpanel'); ?>">
							<?php
							foreach( $webfonts as $font => $opts ):
								?><option value="<?php echo $font; ?>" <?php if( $current == $font ) { echo 'selected="selected"'; } ?>><?php echo $opts['font-name']; ?></option><?php
							endforeach;
							?>
						</optgroup>
					</select>
				</td>
				<td class="tav_input_colorpicker">
					<input class="of-color font_color" name="<?php echo $field['group']; ?>[<?php echo $field['id']; ?>_color]" id="<?php echo $field['id']; ?>_color" type="text" value="<?php echo tav_get_theme_options($field['id'].'_color'); ?>" />
				</td>
				<td>
					<input name="<?php echo $field['group']; ?>[<?php echo $field['id']; ?>_size]" id="<?php echo $field['id']; ?>_size" class="font_size" pattern="[0-9]+" type="text" value="<?php echo tav_get_theme_options($field['id'].'_size'); ?>" style="width: 40px;"/>
				</td>
				<td>
					<?php
					$current_unit = tav_get_theme_options($field['id'].'_unit');
					?>
					<select name="<?php echo $field['group']; ?>[<?php echo $field['id']; ?>_unit]" id="<?php echo $field['id']; ?>_unit" class="font_unit">
						<option value="px" <?php if( $current_unit == 'px' ) { echo 'selected="selected"'; } ?>>px</option>
						<option value="pt" <?php if( $current_unit == 'pt' ) { echo 'selected="selected"'; } ?>>pt</option>
						<option value="em" <?php if( $current_unit == 'em' ) { echo 'selected="selected"'; } ?>>em</option>
						<option value="%" <?php if( $current_unit == '%' ) { echo 'selected="selected"'; } ?>>%</option>
					</select>
				</td>
				<!-- <td><a href="#" class="toggle_fontpreview" id="<?php echo $field['id']; ?>_toggleprev"><?php _e('Show', 'n2cpanel'); ?></a></td> -->
			</tr>
			<!-- <tr class="last" id="<?php echo $field['id']; ?>_last">
				<td colspan="5">
					<div class="font_preview" id="<?php echo $field['id']; ?>_preview"><?php _e('Preview:', 'n2cpanel'); ?> <?php echo $preview[$aleat]; ?></div>
				</td>
			</tr> -->
		</table>
	</div>
<?php }

/* ----------------------------------------
* Output select field
---------------------------------------- */
function tav_select_output( $field ) {

	if( !isset( $field['opts'] ) ) {
		echo '<!-- NO OPTIONS DEFINED -->';
		return;
	}

	$current = tav_get_field_value( $field ); ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-radio">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td>
			<select id="<?php echo $field['id']; ?>" name="<?php echo tav_get_field_name( $field ); ?>">

				<?php
				foreach( $field['opts'] as $key => $val ):

					/* If there are optgroups in the list */
					if( is_array( $val ) ):

						?><optgroup label="<?php echo $key; ?>"><?php

							foreach( $val as $id => $lbl ) { ?>
								<option value="<?php echo $id; ?>" <?php if( $current == $id ) { echo 'selected="selected"'; } ?>><?php echo $lbl; ?></option>
							<?php }
						
						?></optgroup><?php

					else:

						if( $key == $current )
							$selected = 'checked';
						else
							$selected = ''; ?>

						<option value="<?php echo $key; ?>" <?php if( $current == $key ) { echo 'selected="selected"'; } ?>><?php echo $val; ?></option>

					<?php endif;

				endforeach; ?>

			</select>
			
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>
<?php }

/* ----------------------------------------
* Output checkboxes
---------------------------------------- */
function tav_checkbox( $field ) {
	$current = tav_get_field_value( $field ); ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-checkbox">
		<td scope="row"><p class="tav_fake_label"><?php echo $field['title']; ?></p></td>
		<td>
			<?php foreach( $field['opts'] as $key => $val ): ?>
				<label for="<?php echo $field['id']; ?>-<?php echo $key; ?>">
				<input type="checkbox" id="<?php echo $field['id']; ?>-<?php echo $key; ?>" name="<?php echo tav_get_field_name( $field ); ?>[]" value="<?php echo $key; ?>" <?php if( is_array($current) && in_array($key, $current) ) {echo 'CHECKED'; } ?> /> <?php echo $val; ?></label>
			<?php endforeach; ?>
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

	
<?php }

/* ----------------------------------------
* Output rating field
---------------------------------------- */
function tav_rating( $field ) {

	$current = tav_get_field_value( $field );
	$stars 	 = isset( $field['stars'] ) ? $field['stars'] : 5;
	?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-rating">
		<td scope="row"><p class="tav_fake_label"><?php echo $field['title']; ?></p></td>
		<td>
			<?php
			for( $s = 1; $s <= $stars; $s++ ) { 

				if( $s == $current )
					$selected = 'checked';
				else
					$selected = '';
				?>

				<input type="radio" id="star<?php echo $s; ?>" name="<?php echo tav_get_field_name( $field ); ?>" value="<?php echo $s; ?>" class="tav-star" <?php echo $selected; ?>>
			<?php }
			?>
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>
	
<?php }

/* ----------------------------------------
* Output radios
---------------------------------------- */
function tav_radio( $field ) {

	if( !isset( $field['opts'] ) ) {
		echo '<!-- NO OPTIONS DEFINED -->';
		return;
	}

	$current = tav_get_field_value( $field ); ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-radio">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td>
			<?php
			foreach( $field['opts'] as $key => $val ):

				if( $key == $current )
					$selected = 'checked';
				else
					$selected = ''; ?>

				<div class="n2_radio_field">
					<input type="radio" id="<?php echo $field['id'].$key; ?>" name="<?php echo tav_get_field_name( $field ); ?>" value="<?php echo $key; ?>" <?php echo $selected; ?>>
					<label for="<?php echo $field['id'].$key; ?>"><span></span><?php echo $val; ?></label>
				</div>

			<?php endforeach; ?>
			
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

	
<?php }

/* ----------------------------------------
* Output sidebar side selection
---------------------------------------- */
function tav_sidebar( $field ) {

	if( !isset( $field['opts'] ) ) {
		echo '<!-- NO OPTIONS DEFINED -->';
		return;
	}

	$current = tav_get_field_value( $field ); ?>

	<tr valign="top" class="level<?php if( isset( $field['level'] ) ) echo '-' . $field['level']; ?> tav-radio">
		<td scope="row"><label for="<?php echo $field['id']; ?>"><?php echo $field['title']; ?></label></td>
		<td>
			<?php
			foreach( $field['opts'] as $key => $val ):

				if( $key == $current )
					$selected = 'checked';
				else
					$selected = ''; ?>

				<div class="n2_radio_field">
					<input type="radio" id="<?php echo $field['id'].$key; ?>" name="<?php echo tav_get_field_name( $field ); ?>" value="<?php echo $key; ?>" <?php echo $selected; ?>>
					<label for="<?php echo $field['id'].$key; ?>"><span></span><?php echo $val; ?></label>
				</div>

			<?php endforeach; ?>
			
		</td>
		<?php if( isset( $field['desc'] ) ): ?><td><span class="description"><?php echo $field['desc']; ?></span></td><?php endif; ?>
	</tr>

	
<?php }

/* ----------------------------------------
* Display information
---------------------------------------- */
function tav_info_output( $field ) { ?>
	<div <?php if( $field['opts'] ): ?>class="<?php echo $field['opts']; ?>"<?php endif; ?>>
		<?php echo $field['desc']; ?>
	</div>
	<!-- .tav_info, .tav_success, .tav_warning, .tav_error, .tav_validation -->
<?php }

/* ----------------------------------------
* Media Uploader inside a Metabox
---------------------------------------- */
function tav_media_uploader() {

	if( !isset( $_GET['post'] ) )
		return;

	$tab = 'type';

	$args = array(
		'post_type'			=> 'attachment',
		'post_status'		=> 'inherit',
		'post_parent'		=> $_GET['post'],
		'posts_per_page'	=> 1
	);

	$attachments = new Wp_Query( $args );

	if( !empty($attachments->posts) ) {

		$tab = 'dtmedia';

	}

	$u_href = admin_url( 'media-upload.php?post_id=' . $_GET['post'] . '&width=670&height=400&tab=' . $tab );
	?>
	<iframe src="<?php echo esc_url( $u_href ); ?>" width="100%" height="560">The Error!!!</iframe>
	<?php
}
?>