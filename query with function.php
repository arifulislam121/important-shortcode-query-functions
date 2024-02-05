<?php
/*This file is part of DiviChild, Divi/Divi child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

if ( ! function_exists( 'suffice_child_enqueue_child_styles' ) ) {
	function DiviChild_enqueue_child_styles() {
	    // loading parent style
	    wp_register_style(
	      'parente2-style',
	      get_template_directory_uri() . '/style.css'
	    );

	    wp_enqueue_style( 'parente2-style' );
	    // loading child style
	    wp_register_style(
	      'childe2-style',
	      get_stylesheet_directory_uri() . '/style.css'
	    );
	    wp_enqueue_style( 'childe2-style');
	 }
}
add_action( 'wp_enqueue_scripts', 'DiviChild_enqueue_child_styles' );

/*Write here your own functions */

function usp_display_posts_divi($attr = array(), $content = null) {
	
	global $post;
	
	extract(shortcode_atts(array(
		
		'userid'    => 'all',
		'post_type' => 'post',
		'numposts'  => -1,
		
	), $attr));
	
	if (ctype_digit($userid)) {
		
		$args = array(
			'author'         => $userid,
			'posts_per_page' => $numposts,
			'post_type'      => $post_type,
			'meta_key'       => 'is_submission',
			'meta_value'     => '1'
		);
		
	} elseif ($userid === 'all') {
		
		$args = array(
			'posts_per_page' => $numposts,
			'post_type'      => $post_type,
			'meta_key'       => 'is_submission',
			'meta_value'     => '1'
		);
		
	} elseif ($userid === 'current') {
		
		$args = array(
			'author'         => get_current_user_id(),
			'posts_per_page' => $numposts,
			'post_type'      => $post_type,
			'meta_key'       => 'is_submission',
			'meta_value'     => '1'
		);
		
	} else {
		
		$args = array(
			'posts_per_page' => $numposts,
			'post_type'      => $post_type,
			
			'meta_query' => array(
				
				'relation' => 'AND',
				
				array(
					'key'    => 'is_submission',
					'value'  => '1'
				),
				array(
					'key'    => 'user_submit_name',
					'value'  => $userid
				)
			)
		);
		
	}
	
	$args = apply_filters('usp_display_posts_args', $args);
	
	$submitted_posts = get_posts($args);
	
	$display_posts = '<ul>';
	
	foreach ($submitted_posts as $post) {
		
		setup_postdata($post);
		
		//$display_posts .= '<li><a href="'. get_the_permalink() .'" title="'. esc_attr__('View full post', 'usp') .'">'. get_the_title() .'</a></li>';
		$display_posts .= '<li><div class="outer_post_div">		
		<div class="post_feature_div">
		<img src="'.get_the_post_thumbnail_url().'"> 
		</div>		
		<div class="post_content_div">				
		<div class="post_title"><a href="'. get_the_permalink() .'" title="'. esc_attr__('View full post', 'usp') .'"><h3>'. get_the_title() .'</h4></a></div>
		<div class="post_description">'.get_the_content( 'Read more' ).'</div>
		</div>
		</div></li>';
	}
	
	$display_posts .= '</ul>';
	
	wp_reset_postdata();
	
	return $display_posts;
	
}
add_shortcode('usp_display_posts_divi', 'usp_display_posts_divi');

add_action( 'wp_head', 'acf_form_hed' );
function acf_form_hed(){
	acf_form_head();
}
add_shortcode('acf_custom_form', 'acf_custom_form_func');
function acf_custom_form_func(){
	ob_start();
			   acf_form(array(
       'post_id'       => 'new_post',
       'post_title'    => true,
       'post_content'  => true,
	   'new_post'      => array(
     'post_type'     => 'products',
		   'post_status'   => 'publish',
	   ),
	   'return' => home_url(),
       'submit_value'  => 'Send',
		'html_before_fields' => '<div class="acf_form">',
		'html_after_fields' => '</div>',
				
    ));
	return ob_get_clean();
}
add_shortcode('acf_custom_post', 'acf_custom_post_func');
function acf_custom_post_func(){
$Posts = array(	
 'post_type'=>'products',
 'posts_per_page' => 5,	 
);
$the_query = new WP_Query( $Posts );
// 	echo'<pre>';
// print_r($the_query);
// 	echo'</pre>';
$content = '<div class="main-div-posts">';
	while ( $the_query->have_posts() ) {
$posts = $the_query->the_post();
$post_id = get_the_ID(); 
$meta =	get_post_meta($post_id);
// echo'<pre>';
// print_r($meta);
// echo'</pre>';
	$content .=	'<div class="outer_post_div">
			<div class="post_feature_div">
				<img src="'. get_field('my_image') .'">	
			</div>
		 <div class="post_content_div">
			 <h4> <a href=" '. get_permalink() .'">'. get_the_title() .'</a> </h4>';
		 $excerpt = get_the_excerpt();
		 $excerpt = substr($excerpt, 0, 100);
        $result = substr($excerpt, 0, strrpos($excerpt, ' '));
	$content .=	'<p class="des-post">'. $result .'</p>

			 <div class="btn-sec">
		 			 '. do_shortcode('[wp_ulike for="post" id= "' . $post_id . '" style="wpulike-heart"]') .'
			 <a href=" '. get_permalink() .'">Read More</a>
			 </div>
			</div></div>';

}
	 do_shortcode('[ajax_load_more post_type="products" posts_per_page="5" button_label="Load More"]');
 the_posts_pagination( array( 'mid_size' => 2 ) );
			
	$content .=	'</div>';
	
	return $content;
}

// For Test Page


add_action('acf/save_post', 'my_acf_save_post', 20);
function my_acf_save_post( $post_id ) {

    // Get previous values.
    $prev_values = get_fields( $post_id );

    // Get submitted values.
    $values = $_POST['acf'];
	$tag = array($_POST['acf']['field_64f71bf5c69a7']); 
    wp_set_post_terms( $post_id, $tag, 'platform' ); 
	
        wp_redirect(home_url('submit-product'));

}


add_shortcode('acf_custom_tax', 'acf_custom_tax_func');
function acf_custom_tax_func(){
	ob_start();
$terms = get_terms( array(
    'taxonomy'   => 'platform',
    'hide_empty' => false,
) );
	?>
    <div class="tax-list-main-div">
	 <?php
	foreach($terms as $term){
		$tax_img = 	get_field('taxonomy_image', $term->taxonomy . '_' . $term->term_id);
		$tax_name = $term->name;
		$tax_slug = $term->slug;
		$tax_url = get_term_link($tax_slug, 'platform');
		?> 
		<div class="tax-list">
		<img src="<?= $tax_img ?>">	
		<h3>
		<a href="<?= $tax_url?>"><?= $tax_name ?></a>
	    </h3>
		</div>
		<?php
	}
	?>
	   </div>
  <?php
	return ob_get_clean();
}


add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
  if (!current_user_can('administrator') && !is_admin()) {
    show_admin_bar(false);
  }
}



// upvote posts
add_shortcode('acf_upvote_post', 'acf_upvote_post_func');
function acf_upvote_post_func(){
	ob_start();
$likes =	wp_ulike_get_most_liked_posts( 10, 'products','post','all');
	?>
	<div class="main-div-trend-posts">
		<h3>TRENDING</h3>
	<?php
		foreach($likes as $like){
		$title = $like->post_title;
		$content = $like->post_content;
		$ID = $like->ID;
		$Meta_list = get_post_meta($ID);
	    $attachment_id = $Meta_list['my_image']['0'];
		$img = wp_get_attachment_image_src($attachment_id);
		$img_src = $img['0'];
		$url = $like->guid;	
		?>	
		<div class="outer_trend_post_div">
		 <div class="trend_post_content_div">
			 <h4><a href="<?= $url?>"><?= $title ?> </a></h4>
		<?php
		 $content = substr($content, 0, 100);
        $result = substr($content, 0, strrpos($content, ' '));	
	    ?>
	    <p class="des-post"><?= $result ?></p>
			<div class="btn-sec">
			<?php	
			echo do_shortcode('[wp_ulike type="post" id= " '. $ID .' "  style="wpulike-heart"]'); 
			?>
			 <a href="<?= $url?>">Read More</a>
			 </div>
			</div>
			<div class="trend_post_feature_div">
			 <img src="http://thenextai.com/wp-content/uploads/2023/09/Mask-group-3.png">
			</div>
		</div>
		<?php
	}
?>
</div>
<?php
	return ob_get_clean();
}
	



// Top posts
add_shortcode('acf_top_post', 'acf_top_post_func');
function acf_top_post_func(){
	ob_start();
$likes =	wp_ulike_get_most_liked_posts( 10, 'products','post','all');
	?>
	<div class="main-div-posts">
	<?php
		foreach($likes as $like){
		$title = $like->post_title;
		$content = $like->post_content;
		$ID = $like->ID;
		$Meta_list = get_post_meta($ID);
	    $attachment_id = $Meta_list['my_image']['0'];
		$img = wp_get_attachment_image_src($attachment_id, $size = 'full');
		$img_src = $img['0'];
		$url = $like->guid;	
		?>	
		<div class="outer_post_div">
		 <div class="post_feature_div">
			 		<img src="<?= $img_src ?>">	
			</div>
		 <div class="post_content_div">
			 <h4> <a href=" <?= $url ?>"><?= $title ?></a></h4>
		<?php
		  $content = substr($content, 0, 100);
        $result = substr($content, 0, strrpos($content, ' '));
			?>
		<p class="des-post"><?= $result ?></p>

			 <div class="btn-sec">
				 <?php
		 	echo do_shortcode('[wp_ulike for="post" id= "' . $ID  . '" style="wpulike-heart"]');
			?>
			 <a href=" <?= $url ?>">Read More</a>
			 </div>
			</div>
		</div>
			 
			 

		<?php
	}
?>
</div>
<?php
	return ob_get_clean();
}

// $new_args = array(
//     'post_type' => 'products',
// 	'post_status'    => 'publish',
//     'posts_per_page' => 5,
//     'meta_key'       => 'like_amount',
//     'orderby'        => 'meta_value_num',
//     'order'          => 'DESC',
// );
// $the_query = new WP_Query( $new_args );

// while ( $the_query->have_posts() ) {
// $posts = $the_query->the_post();
// $post_id = get_the_ID(); 

	
// }
// }
