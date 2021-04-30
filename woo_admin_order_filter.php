/*
* You can add this code inside your child theme "functions.php"
* Limited to show small order (1 product order), but can be expanded to show 4 produtcs, 10 products, big orders.. just add the value to "HAVING COUNT(*)" query
*/

add_action( 'restrict_manage_posts', 'filter_orders_by_product_amount' );
function filter_orders_by_product_amount(){
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if ('shop_order' == $type){
        $values = array(
            'All amount of items orders' => 'All', 
            'Single item orders' => '1',
        );
        ?>
        <select name="order_item_amount">
        <?php
            $current_v = isset($_GET['order_item_amount'])? $_GET['order_item_amount']:'';
            foreach ($values as $label => $value) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v? ' selected="selected"':'',
                        $label
                    );
                }
        ?>
        </select>
        <?php
    }
}

add_action( 'pre_get_posts', 'process_filter_orders_by_product_amount' );
function process_filter_orders_by_product_amount( $query ) {
    global $pagenow, $post_type, $wpdb;

    $filter_id = 'order_item_amount';
	$req = $_GET[$filter_id];

	if ($req == 1) {
		$having_param = " = 1";	
	} 		

    if ( $query->is_admin && 'edit.php' === $pagenow && 'shop_order' === $post_type
         && isset( $_GET[$filter_id] ) && $_GET[$filter_id] != '' && $_GET[$filter_id] != 'All') {

        $order_ids = $wpdb->get_col( "
          SELECT order_id
          FROM wp_woocommerce_order_items 
          where order_item_type = 'line_item'
          GROUP BY order_id
          HAVING COUNT(*) {$having_param}
        ");

        $query->set( 'post__in', $order_ids ); // Set the new "meta query"

        $query->set( 'posts_per_page', 25 ); // Set "posts per page"

        $query->set( 'paged', ( get_query_var('paged') ? get_query_var('paged') : 1 ) ); // Set "paged"
    }
}
