<?php header('Location: http://qqcxg8t9wwdo2vmd.myfritz.net:12080/calendario/'); ?>

<?php
/*
$loop = new WP_Query( array(
    'post_type' => 'ordini',
    'posts_per_page' => -1
) );
while($loop->have_posts()) : $loop->the_post();

    $order_type = get_field('order-type');
    $order_date = get_field('order-date');
    $order_year = explode('-', $order_date)[0];

    if($order_type == 'order' && $order_date && $order_year == '2019') {
        
        $order_date = get_field('order-date');
        
        var_dump($order_date);
    }

endwhile;
*/

/*
function get_all_log_ids( ) {

    $query = new WP_Query( array(
        'post_type'              => 'wp-rest-api-log',
        'fields'                 => 'ids',
        'posts_per_page'         => -1,
    ) );
    
    return $query->posts;
}

var_dump(get_all_log_ids())
*/