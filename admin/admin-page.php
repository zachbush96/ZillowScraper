<?php
// Fetch Zillow listings
$listings = new WP_Query([
    'post_type' => 'zillow_listing',
    'posts_per_page' => -1,
]);

?>
<div class="wrap">
    <h1>Zillow Listings</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($listings->have_posts()) : ?>
                <?php while ($listings->have_posts()) : $listings->the_post(); ?>
                    <tr>
                        <td><?php the_title(); ?></td>
                        <td><?php the_date(); ?></td>
                        <td><?php echo get_post_status(); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3">No listings found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
