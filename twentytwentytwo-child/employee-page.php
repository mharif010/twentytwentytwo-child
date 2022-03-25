<?php
/**
 * Template Name: Employee Page
 * The template for displaying the content for the Employee page.
 * 
 */
get_header(); ?>

<h1>Welcome employee page </h1>

<section>
    <div class="employee-container js-filter" id="employee-board">

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
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $args = array(  
            'post_type' => 'employee',
            'post_status' => 'publish',
            'paged' => $paged, 
            'meta_key' => '_employee_id',
            'orderby' => 'meta_value',
            'order' => 'ASC'
        );
    
        $loop = new WP_Query( $args ); 
            
        while ( $loop->have_posts() ) : $loop->the_post(); ?>
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
        endwhile; ?>
        <div class="pagination">
            <?php
                $big = 999999999;
                echo paginate_links( array(
                    'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
                    'format' => '?paged=%#%',
                    'current' => max( 1, get_query_var('paged') ),
                    'total' => $loop->max_num_pages,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;'
                ) );
            ?>
        </div>
    <?php  wp_reset_postdata(); ?>

    </div>
</section>






<?php 
 get_footer(); 