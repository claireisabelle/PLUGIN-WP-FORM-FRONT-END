<?php
/*
Plugin Name: GA Submit Recipe from the Front End
Plugin URI:
Description: Allow the user to submit their recipe from the front end
Version: 1.0
Author: Claire Bourdalé
License: GPL2
License: URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


/* Displays the Form in the front end to receive the recipes, use: [ga_send_recipe] */
function ga_display_form_shortcode(){
	// Get a instance of the form
	$cmb = ga_form_values();

	// Prints the content in the page or Shortcode
	$output = '';

	// In case that an error is presended
	if(($error = $cmb->prop('submission_error')) && is_wp_error($error)){
		$output .= '<h3>' . sprintf('There was an error %s ', '<strong>' . $error->get_error_message() ). '</strong></h3>';
	}

	// If the post was submitted successfully, print a message
	if(isset($_GET['post_submitted']) && ($post = get_post(absint($_GET['post_submitted'])))){
		// Get Submitter name
		$name = get_post_meta($post->ID, 'author_recipe', 1);
		$name = $name ? '' .$name : '';

		// Print the message
		$output .= '<h3>' .  sprintf('Thanks %s your recipe was submitted, once everything is fine we will publish it', esc_html($name)) . '</h3>';

	}

	// Print the form
	$output .= cmb2_get_metabox_form($cmb, 'fake-object-id', array('save_button' => 'Send Recipe') );

	return $output;
}
add_shortcode('ga_send_recipe', 'ga_display_form_shortcode');



/* Gets an instance of the form */
function ga_form_values(){
	// Id of the metabox
	$metabox_id = 'ga_send_recipe_frontend';

	// Pass an object ID, post type is going to be added later
	$object_id = 'fake-object-id';

	// Returns an instance of the form
	return cmb2_get_metabox($metabox_id, $object_id);
}



/* All the fields for the front end form */
function ga_form_fields(){
	$cmb = new_cmb2_box(array(
		'id'	=> 'ga_send_recipe_frontend',
		'object_type' => array('page'),
		'hookup' => false, // Hookup checks if the current page should save the form
		'save_fields' => false
	));

	$cmb->add_field(array(
		'name' => 'Genetal Information of the Recipe',
		'id' => 'recipe_heading',
		'type' => 'title'
	));

	$cmb->add_field(array(
		'name' => 'Recipe Title',
		'id' => 'recipe_title',
		'type' => 'text'
	));

	$cmb->add_field(array(
		'name' => 'Recipe Subtitle',
		'id' => 'subtitle',
		'type' => 'text'
	));

	$cmb->add_field(array(
		'name' => 'Recipe',
		'id' => 'recipe_content',
		'type' => 'wysiwyg',
		'options' => array(
			'textarea_rows' => 12,
			'media_buttons' => false
		),
	));

	$cmb->add_field(array(
		'name' => 'Calories',
		'id' => 'recipe_calories',
		'type' => 'text'
	));

	$cmb->add_field(array(
		'name' => 'Recipe Image',
		'id' => 'featured_image',
		'type' => 'text',
		'attributes' => array(
			'type' => 'file'
		),
	));

	/* New section */

	$cmb->add_field(array(
		'name' => 'Extra Information:',
		'id' => 'extra_information',
		'type' => 'title'
	));

	$cmb->add_field(array(
		'name' => 'Price',
		'id' => 'price_range',
		'type' => 'taxonomy_select',
		'taxonomy' => 'price_range'
	));

	$cmb->add_field(array(
		'name' => 'Meal Type',
		'id' => 'meal_type',
		'type' => 'taxonomy_select',
		'taxonomy' => 'meal-type'
	));

	$cmb->add_field(array(
		'name' => 'Course',
		'id' => 'course',
		'type' => 'taxonomy_select',
		'taxonomy' => 'course'
	));

	$cmb->add_field(array(
		'name' => 'Mood',
		'id' => 'mood',
		'type' => 'text',
		'description' => 'Add the mood, separated by comma'
	));

	/* New section */

	$cmb->add_field(array(
		'name' => 'Author Information',
		'id' => 'author_information',
		'type' => 'title'
	));


	$cmb->add_field(array(
		'name' => 'Your Name',
		'description' => 'Add your name for the recipe',
		'id' => 'author_recipe',
		'type' => 'text'
	));

	$cmb->add_field(array(
		'name' => 'Author Email',
		'description' => 'Add your email',
		'id' => 'author_email',
		'type' => 'text_email'
	));



}
add_action('cmb2_init', 'ga_form_fields'); // Using lib CMB2 from plugin ga-cmb2 created before...


/** Handles form submission on save, save on the database and redirect or set an error message in the error_submission **/
function ga_insert_recipe(){
	// Return false if the user doesn't submit anything
	if(empty($_POST) || !isset($_POST['submit-cmb'], $_POST['object_id'])){
		return false;
	}

	// Get an instance of the form
	$cmb = ga_form_values();

	// $post_data is the content of the post that will be submitted
	$post_data = array();

	// Check security
	if(!isset($_POST[$cmb->nonce()]) || ! wp_verify_nonce($_POST[$cmb->nonce()], $cmb->nonce())){
		return $cmb->prop('submission_error', new WP_Error('security_fail', 'Security check failed'));
	}

	// Check that the post title is not empty
	if(empty($_POST['recipe_title'])){
		return $cmb->prop('submission_error', new WP_Error('post_data_missing', 'Title is required'));
	}

	/** Sanitize Data **/
	$sanitized_values = $cmb->get_sanitized_values($_POST);

	// Add info to the post_data
	$post_data['post_title'] = $sanitized_values['recipe_title'];
	unset($sanitized_values['recipe_title']);

	$post_data['post_content'] = $sanitized_values['recipe_content'];
	unset($sanitized_values['recipe_content']);

	$mood = explode(',', $sanitized_values['mood']);

	$post_data['tax_input'] = array(
		'price_range' => $sanitized_values['price_range'],
		'meal-type'   => $sanitized_values['meal_type'],
		'course'	  => $sanitized_values['course'],
		'mood'		  => $mood
	);

	$post_data['meta_input'] = array(
		'input-metabox' => $sanitized_values['recipe_calories'],
		'textarea-metabox' => $sanitized_values['subtitle']
	);


	// Set the POST TYPE
	$post_data['post_type'] = 'recipes';

	// Insert the post
	$new_id = wp_insert_post($post_data, true);

	// If there's an error, print id
	if(is_wp_error($new_id)){
		return $cmb->prop('submission_error', $new_id);
	}

	// Save all the data into the POST
	$cmb->save_fields($new_id, 'post', $sanitized_values);

	
	// Save the featured image
	$img_id = ga_send_featured_image($new_id, $post_data);


	// If there're no errors, upload the image and set as featured image
	if($img_id && !is_wp_error($img_id)){
		set_post_thumbnail($new_id, $img_id);
	}


	// Redirect if the post is submitted (this prevents duplicated post)
	wp_redirect(esc_url_raw(add_query_arg('post_submitted', $new_id)));
	exit;
}
add_action('cmb2_after_init', 'ga_insert_recipe');


// Fonction pour ajouter l'image
function ga_send_featured_image($post_id, $attachment_post_data = array()){

	// Make sure that a file were submitted
	if(empty($_FILES) || !isset($_FILES['featured_image']) && 0 !== $_FILES['featured_image']['error']){
		return;
	}

	// Filter empty array values
	$file = array_filter($_FILES['featured_image']);

	// Check that a file was submitted
	if(empty($file)){
		return;
	}

	// Use the media uploader in the frontend
	if(!function_exists('media_handle_upload')){
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
	}

	// Upload the file and set return back the attachment id
	return media_handle_upload('featured_image', $post_id, $attachment_post_data);
}