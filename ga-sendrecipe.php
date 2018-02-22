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
	$cmb = ga_form_values();

	$output = '';

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
		'type' => 'taxonomy_multicheck',
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
		'name' => 'Author Inofmration',
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