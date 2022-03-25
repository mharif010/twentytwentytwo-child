<?php
add_action( 'wp_enqueue_scripts', 'twentytwentytwo_child_enqueue_styles');

function twentytwentytwo_child_enqueue_styles(){
    $theme = wp_get_theme();
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', 
        array(),  
        $theme->parent()->get('Version')
    );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
        array( 'parent-style' ),
        $theme->get('Version') 
    );


    if(is_page_template( 'employee-page.php' ))
    {
        wp_enqueue_style( 'employee-style', get_stylesheet_directory_uri() . '/assets/css/custom.css');
        wp_enqueue_script('employee-script', get_stylesheet_directory_uri(). '/assets/js/custom.js', array( 'jquery' ),'',true);
    }

    wp_enqueue_script('ajax', get_stylesheet_directory_uri() . '/assets/js/ajax.js', array('jquery'), NULL, true);

	wp_localize_script('ajax' , 'wp_ajax',
		array('ajax_url' => admin_url('admin-ajax.php'))
		);

}


add_action('init', 'create_custom_post_employee');

function create_custom_post_employee(){

    register_post_type( 'employee', array(
        'labels' => array(
                'name' => __('Employees'),
                'singular_name' => __('Employee')
        ),
        'supports' => array('title', 'thumbnail'),
        'public' => true,
        'register_meta_box_cb' => 'employee_id_meta_box',
        'has_archive' => true,
        'query_var'          => true,
        'rewrite' => array('slug' => 'employee'),
        'show_in_rest' => true,
    ) );

    $args = array(
        'label'        => __( 'Employee Category', 'twentytwentytwo-child' ),
        'public'       => true,
        'rewrite'      => false,
        'hierarchical' => true
    );
     
    register_taxonomy( 'employee-category', 'employee', $args );

}

function employee_id_meta_box() {

    add_meta_box(
        'employee-id',
        __( 'Employee ID', 'twentytwentytwo-child' ),
        'employee_id_meta_box_callback'
    );
    add_meta_box(
        'employee-designation',
        __( 'Employee Designation', 'twentytwentytwo-child' ),
        'employee_designation_meta_box_callback'
    );

}

function employee_id_meta_box_callback( $emp ) {
    wp_nonce_field( 'employee_id_nonce', 'employee_id_nonce' );
    $value = get_post_meta( $emp->ID, '_employee_id', true );
    echo '<input style="width:100%" id="employee_id" name="employee_id" value="'. esc_attr( $value ) .'">';
}
function employee_designation_meta_box_callback( $emp ) {
    wp_nonce_field( 'employee_designation_nonce', 'employee_designation_nonce' );
    $value_1 = get_post_meta( $emp->ID, '_employee_designation', true );
    echo '<input style="width:100%" id="employee_designation" name="employee_designation" value="'. esc_attr( $value_1 ) .'">';
}

function save_employee_id_meta_box_data( $post_id ) {

    if ( ! isset( $_POST['employee_id_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['employee_id_nonce'], 'employee_id_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'employee' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }
    if ( ! isset( $_POST['employee_id'] ) ) {
        return;
    }

    //for designation
    if ( ! isset( $_POST['employee_designation_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['employee_designation_nonce'], 'employee_designation_nonce' ) ) {
        return;
    }  
    if ( ! isset( $_POST['employee_designation'] ) ) {
        return;
    }



    $id_data = sanitize_text_field( $_POST['employee_id'] );
    $des_data = sanitize_text_field( $_POST['employee_designation'] );
    update_post_meta( $post_id, '_employee_id', $id_data );
    update_post_meta( $post_id, '_employee_designation', $des_data );
}

add_action( 'save_post', 'save_employee_id_meta_box_data' );


add_action( 'wp_ajax_nopriv_filter', 'filter_ajax' );
add_action( 'wp_ajax_filter', 'filter_ajax' );

function filter_ajax() {
?>
<div class="categories">
            <ul>
                <li class="js-filter-item"><a href="<?= home_url(); ?>">All</a></li>
                <?php 
                $categories = get_terms( array(
                    'taxonomy' => 'employee-category',
                    'hide_empty' => false,
                ) );

                foreach($categories as $cat) : ?>
                    <li class="js-filter-item"><a data-category="<?= $cat->term_id; ?>" href="<?= get_category_link($cat->term_id); ?>"><?= $cat->name; ?></a></li>
                <?php endforeach; ?>
        </ul>
    </div>

<?php
    

$category = $_POST['category'];

$args = array(
    'posts_per_page'            =>  -1,
    'post_type'              => array( 'employee' ),
    'meta_key' => '_employee_id',
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'cache_results'          => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => true,
    'tax_query' => array(
        array(
        'taxonomy' => 'employee-category',
        'field' => 'slug',
        'terms' => array($category)
        )
    )
	);

    if(isset($category)) {
        $args['tax_query'] = array(
			array(
				'taxonomy' => 'employee-category',
				'field' => 'term_id',
				'terms' => $category
			)
		);
    }

		$query = new WP_Query($args);

		
			while($query->have_posts()) : $query->the_post(); ?>

        <div class="item-box">
            <?php
            $profile_img = the_post_thumbnail('thumbnail'); 
            if ( $profile_img ) { echo $profile_img; } ?>
            <h2><?php the_title(); ?></h2>
            <p><b>Employer ID : </b><?php 
            $employee_id = esc_attr( get_post_meta( $post->ID, '_employee_id', true ) );
            echo $employee_id; ?></p>

            <p><b>Employer Designation : </b><?php 
            $employee_des = esc_attr( get_post_meta( $post->ID, '_employee_designation', true ) );
            echo $employee_des; ?></p>
        </div>
		<?php
        	endwhile;
		
			wp_reset_postdata(); 


	wp_die();
}
