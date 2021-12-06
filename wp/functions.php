<?php

// ORDINI CPT

function orders_cpt() {

  $labels = array(
    'name'                => _x( 'Ordini', 'Post Type General Name', 'mamforni' ),
    'singular_name'       => _x( 'Ordine', 'Post Type Singular Name', 'mamforni' ),
    'menu_name'           => __( 'Ordini', 'mamforni' ),
    'parent_item_colon'   => __( 'Genitore:', 'mamforni' ),
    'all_items'           => __( 'Tutti gli ordini', 'mamforni' ),
    'view_item'           => __( 'Visualizza', 'mamforni' ),
    'add_new_item'        => __( 'Aggiungi ordine', 'mamforni' ),
    'add_new'             => __( 'Aggiungi ordine', 'mamforni' ),
    'edit_item'           => __( 'Modifica', 'mamforni' ),
    'update_item'         => __( 'Aggiorna', 'mamforni' ),
    'search_items'        => __( 'Cerca', 'mamforni' ),
    'not_found'           => __( 'Nessun risultato', 'mamforni' ),
    'not_found_in_trash'  => __( 'Nessun risultato', 'mamforni' ),
  );
  $args = array(
    'label'               => __( 'ordini', 'mamforni' ),
    'description'         => __( 'Description', 'mamforni' ),
    'labels'              => $labels,
    'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes', 'excerpt'),
    'hierarchical'        => true,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 6,
    'can_export'          => true,
    'has_archive'         => true,
    'show_in_rest'        => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'capability_type'     => 'post',
  );
  register_post_type( 'ordini', $args );

}
add_action( 'init', 'orders_cpt', 0 );

function api_query_custom_allowed_args($args) {
  $args[] = 'fields';
  return $args;
}
add_filter( 'wp_query_route_to_rest_api_allowed_args', 'api_query_custom_allowed_args' );

// WP REST API

function wp_json_v2_ordini_init() {
    
    // PROD > /var/www/vhosts/calendario.mamforni.it/httpdocs/wp/wp-content/uploads/
    // DEV > /var/www/html/calendario/wp/wp-content/uploads/ > http://qqcxg8t9wwdo2vmd.myfritz.net:12080/calendario
    
    global $upload_dir, $home_url;
    $home_url = 'https://calendario.mamforni.it';
    $upload_dir = '/var/www/vhosts/calendario.mamforni.it/httpdocs/wp/wp-content/uploads/';

    // create json-api endpoint

    add_action('rest_api_init', function () {
        
        remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
        add_filter( 'rest_pre_serve_request', function( $value ) {

            $origin = get_http_origin();
            if ( $origin && in_array( $origin, array(
                'http://localhost:8080',
                'http://calendario.mamforni.it',
                'https://calendario.mamforni.it',
                'http://qqcxg8t9wwdo2vmd.myfritz.net:12080',
                'https://qqcxg8t9wwdo2vmd.myfritz.net:12443'
            ) ) ) {
                header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
                header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
                header( 'Access-Control-Allow-Credentials: true' );
            }

            return $value;
        });
        
        register_rest_route('wp/v2', '/query', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {

                $data = $request['data'];
                $query_args = (array) $data['parametri'];
                $query_options = (array) $data['opzioni'];

                if($query_args && is_array($query_args['p'])) {
                    $p = $query_args['p'];
                    unset($query_args['p']);
                } else {
                    $p = false;
                }

                $query = new WP_Query( $query_args );

                $return = array();

                $i = 0;
                foreach($query->posts as $key => $value) {
                    if(!$p || in_array(strval($value->ID), $p)) {

                        $return[$i]['acf'] = array();

                        if($query_options['fields'] == '*') {

                            $return[$i]['ID'] = $value->ID;
                            $return[$i]['post_author'] = $value->post_author;
                            $return[$i]['post_date'] = $value->post_date;
                            $return[$i]['post_date_gmt'] = $value->post_date_gmt;
                            $return[$i]['post_title'] = $value->post_title;
                            $return[$i]['post_content'] = $value->post_content;
                            $return[$i]['post_excerpt'] = $value->post_excerpt;
                            $return[$i]['post_status'] = $value->post_status;
                            $return[$i]['post_name'] = $value->post_name;
                            $return[$i]['post_modified'] = $value->post_modified;
                            $return[$i]['post_modified_gmt'] = $value->post_modified_gmt;
                            $return[$i]['guid'] = $value->guid;
                            $return[$i]['menu_order'] = $value->menu_order;
                            $return[$i]['post_type'] = $value->post_type;

                        } else {
                            foreach($query_options['fields'] as $field) {
                                $return[$i][$field] = $value->{$field};
                            }
                        }
                        $metas = get_field_objects($value->ID);
                        foreach($metas as $meta) {
                            if($query_options['meta'] == '*') {
                                if(!is_array(get_field($meta['name'], $value->ID))) {
                                    $curr_field = get_field($meta['name'], $value->ID);
                                    $return[$i]['acf'][$meta['name']] = $curr_field;
                                }
                            } else {
                                foreach($query_options['meta'] as $field) {
                                    $return[$i]['acf'][$field] = get_field($field, $value->ID);
                                }
                            }
                        }

                        $i++;
                    }
                }
                
                return new WP_REST_Response( $return , 200);
            }
        ));
        
        register_rest_route('wp/v2', '/ordini/update', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {

                $params = $_POST;
                $now = new DateTime();

                $ordine_id = $params['id'];
                $ordine_title = $params['order-title'];
                $ordine_meta = ($params['meta']) ? $params['meta'] : array();
                
                if($ordine_meta['order-type'] == 'order' || $ordine_meta['order-type'] == 'replacement') {
                
                    if($ordine_meta['order-type'] == 'order') $ordine_slug = 'ordine-'. $ordine_meta['order-oven_id'];
                    elseif($ordine_meta['order-type'] == 'replacement') $ordine_slug = 'ricambio-'. $ordine_meta['order-oven_id'];

                    // UPDATE GALLERY
                    if($ordine_meta['order-gallery'] || $ordine_meta['order-lav-gallery']) {
                        
                        $order_gallery_images = ($ordine_meta['order-gallery']) ? $ordine_meta['order-gallery'] : [];
                        $order_gallery_lav_images = ($ordine_meta['order-lav-gallery']) ? $ordine_meta['order-lav-gallery']: [];
                        $_order_gallery_images = [];
                        $_order_gallery_lav_images = [];
                        $ordine['upload'] = false;

                        function uploadGalleryImages($image, $gallery = []) {
                            
                            if(!file_exists($GLOBALS['upload_dir'] .'ordini/'. $image['name'])) {

                                $imageBase64 = $image['path'];
                                $imageName = $image['name'];
                                $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
                                $imageUnicName = md5(uniqid(rand(), true));
                                $imagePath = "{$GLOBALS['upload_dir']}ordini/{$imageUnicName}.{$imageExt}";

                                $imageFile = fopen($imagePath, 'w+');

                                $imageData = explode(',', $imageBase64);

                                // we could add validation here with ensuring count( $data ) > 1
                                $ordine['upload'] = fwrite($imageFile, base64_decode($imageData[1]));

                                if($ordine['upload']) {
                                    $gallery = array(
                                        'default' => $image['default'],
                                        'highlight' => $image['highlight'],
                                        'name' => $imageUnicName .'.'. $imageExt,
                                        'path' => $GLOBALS['home_url'] .'/wp/wp-content/uploads/ordini/'. $imageUnicName .'.'. $imageExt
                                    );
                                }

                                // clean up the file resource
                                fclose($imageFile);
                                
                            } else {

                                $gallery = $image;
                            }
                            
                            return $gallery;
                        }

                        if($ordine_meta['order-gallery']) {
                            foreach($order_gallery_images as $image) {
                                array_push($_order_gallery_images, uploadGalleryImages($image));
                            }
                            $ordine_meta['order-gallery'] = json_encode($_order_gallery_images);
                        }
                        
                        if($ordine_meta['order-lav-gallery']) {
                            foreach($order_gallery_lav_images as $image) {
                                array_push($_order_gallery_lav_images, uploadGalleryImages($image));
                            }
                            $ordine_meta['order-lav-gallery'] = json_encode($_order_gallery_lav_images);
                        }
                    }
                    
                } elseif ($ordine_meta['order-type'] == 'note' || $ordine_meta['order-type'] == 'trip') {
                    
                    $ordine_slug = sanitize_title($ordine_title);
                    
                }

                $args = array(
                    'ID' => $ordine_id,
                    'post_author' => 163,
                    'post_title' => $ordine_title,
                    'post_status' => 'publish',
                    'post_type' => 'ordini'
                );
                if($ordine_id){
                    $ordine['id'] = wp_update_post($args, true);
                } else{
                    $args['post_name'] =  $now->getTimestamp() . $ordine_slug;
                    $ordine['id'] = wp_insert_post($args, true);
                }
                
 //               $ordine['id'] = (!$ordine_id) ? wp_insert_post($args_insert, true) : wp_update_post($args_update, true);
                
                if($ordine_meta['order-type'] == 'order') {
                    
                    $post_ordine = get_post($ordine['id']);
                    
                    $ordine_meta['order-index'] = explode('-', $post_ordine->post_name);
                    $ordine_meta['order-index'] = (count($ordine_meta['order-index']) == 3) ? intval($ordine_meta['order-index'][2]) : 0;
                }
                
                foreach($ordine_meta as $key => $value) {
                    if ($key == 'order-replacement-parts_attributes') {
    
                        $value = json_encode($value);
                        update_field($key, $value, $ordine['id']);
                        
                    } else if(!is_array($value) || (is_array($value) && isset($value[0]))) {
                        
                        if($value === 'true') $value = true;
                        if($value === 'false') $value = false;
                        update_field($key, $value, $ordine['id']);
                        
                    } else {
                        update_field($key, $value['value'], $ordine['id']);
                    }
                }
                
                $ordine['meta'] = $ordine_meta;

                return new WP_REST_Response($ordine, 200);
                
            }
        ));
        
        register_rest_route('wp/v2', '/ordini/update-status', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {
                
                $params = $_POST;
                
                $ordine['id'] = $params['id'];
                $ordine['meta'] = ($params['meta']) ? $params['meta'] : array();
                
                foreach($ordine['meta'] as $key => $value) {
                    if(!is_array($value)) {
                        
                        if($value === 'true') $value = true;
                        if($value === 'false') $value = false;
                        update_field($key, $value, $ordine['id']);
                        
                    } else {
                        
                        if($key == 'order-lav-parts') {
                            
                            update_field($key, $value, $ordine['id']);
                            
                        } else {
                            
                            update_field($key, $value['value'], $ordine['id']);
                        }
                    }
                }
                
                return new WP_REST_Response($ordine, 200);
                
            }
        ));
        
        register_rest_route('wp/v2', '/ordini/delete-gallery-images', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {
                
                $params = $_POST;
                
                $order_id = $params['id'];
                $image_index = $params['index'];
                $field_key = (isset($params['field'])) ? $params['field'] : 'order-gallery';
                
                $gallery = json_decode(get_field($field_key, $order_id));
                $gallery_original = get_field($field_key, $order_id);
                
                if($image_index >= 0) {
                    
                    $image = $GLOBALS['upload_dir'] .'ordini/'. $gallery[$image_index]->name;

                    $return['exist'] = file_exists($image);
                    $return['return'] = false;

                    if($return['exist']) {
                        
                        array_splice($gallery, $image_index, 1);
                        
                        $gallery = json_encode($gallery);
                        
                        $return['return'] = update_field($field_key, $gallery, $order_id);

                        if($return['return']) {

                            $return['return'] = unlink($image);

                            if(!$return['return']) update_field($field_key, $gallery_original, $order_id);
                        }

                    }
                }
                
                return new WP_REST_Response($return, 200);
            }
        ));
        
        register_rest_route('wp/v2', '/ordini/delete', array (
            'methods'  => 'DELETE',
            'callback' => function( $request ) {
                
                $params = $_GET;
                
                $ordine = get_post($params['id']);
                
                $return = wp_delete_post($ordine->ID, $params['force']);
                
                return new WP_REST_Response($return, 200);
            }
        ));
        
        register_rest_route('wp/v2', '/ordini/toggle-flag', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {
                
                $params = $_POST;
                
                $params['value'] = ($params['value'] == 'true') ? true : false;
                
                $return = update_field($params['field'], $params['value'], $params['id']);
                
                return new WP_REST_Response($return, 200);
            }
        ));
        
        register_rest_route('wp/v2', '/ordini/update-history', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {
                
                $params = $_POST;
                
                $order_id = intval($params['id']);
                $data = $params['data'];
                
                if($data['data']['updated']['order-note']) {
                    $data['data']['updated']['order-note']['old'] = stripcslashes($data['data']['updated']['order-note']['old']);
                    $data['data']['updated']['order-note']['new'] = stripcslashes($data['data']['updated']['order-note']['new']);
                }
                
                if($data['data']['updated']['order-attachments_note']) {
                    $data['data']['updated']['order-attachments_note']['old'] = stripcslashes($data['data']['updated']['order-attachments_note']['old']);
                    $data['data']['updated']['order-attachments_note']['new'] = stripcslashes($data['data']['updated']['order-attachments_note']['new']);
                }
                
                $order_history = json_decode(get_field('order-history', $order_id));
                
                if(!is_array($order_history)) {

                    $order_history = array($data);

                } else {

                    array_push($order_history, $data);
                }
                
                $order_history = json_encode($order_history);
                $order_history = preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $order_history);
                $order_history = html_entity_decode($order_history);
                $order_history = str_replace(array('rn<p>', 'n<p>', 'r<p>'), '<p>', $order_history);

                $return = update_field('order-history', $order_history, $order_id);
                
                return new WP_REST_Response($return, 200);
            }
        ));
        
        register_rest_route('wp/v2', '/ordini/options', array (
            'methods'  => 'GET',
            'callback' => function( $request ) {
                
                if(!is_array($_GET['field'])) {
                    
                    $key = $_GET['field'];
                
                    $field = get_field_object($key);
                    $return = ($field['choices']) ? $field['choices'] : false;
                    
                } else {
                    
                    $keys = $_GET['field'];
                    
                    foreach($keys as $key => $value) {
                        
                        if(!is_array($value)) {
                            $return[$key] = get_field_object($value)['choices'];
                        } else {
                            foreach($value as $key2 => $value2) {
                                $return[$key][$key2] = get_field_object($value2)['choices'];
                            }
                        }
                    }
                }
                
                return new WP_REST_Response($return, 200);
            }
        ));
        
        register_rest_route('wp/v2', '/acf/get-field', array (
            'methods'  => 'GET',
            'callback' => function( $request ) {
                
                $order_id = intval($_GET['id']);
                $field_slug = $_GET['field'];
                
                $return = get_field_object($field_slug, $order_id);
                
                return new WP_REST_Response($return, 200);
            }
        ));
        
        register_rest_route('wp/v2', '/acf/get-fields', array (
            'methods'  => 'GET',
            'callback' => function( $request ) {
                
                $order_id = intval($_GET['id']);
                
                $return = array();
                $acf_group = acf_get_fields_by_id(23);
                foreach($acf_group as $acf) {
                    if($acf['name'] && !$return[$acf['name']]) {
                        
                        $return[$acf['name']] = $acf;
                        
                    } else if($acf['name'] && $return[$acf['name']]) {
                        
                        if(!$return[$acf['name']][0]) {
                            $return[$acf['name']] = array($return[$acf['name']]);
                        }
                        array_push($return[$acf['name']], $acf);
                    }
                }
                
                /*
                
                $fields = get_fields($order_id);
                $return = array();
                
                foreach($fields as $key => $value) {
                    $field = get_field_object($key, $order_id);
                    $return[$field['name']] = array(
                        'label' => $field['label']
                    );
                }
                */
                
                return new WP_REST_Response($return, 200);
            }
        ));
    
        register_rest_route('wp/v2', '/auth/login', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {
                
                $params = $_POST;
                $return = array(
                    'result' => false,
                    'data' => array()
                );
    
                $creds = array(
                    'user_login' => $params['username'],
                    'user_password' => $params['password'],
                    'remember' => true
                );
    
                $user = wp_signon($creds, false);
    
                $return['result'] = (is_wp_error($user)) ? false : true;
                if($return['result']) {
                    $return['data'] = array(
                        'id' => $user->data->ID,
                        'username' => $user->data->user_login,
                        'role' => (in_array('administrator', $user->roles)) ? 'admin' : 'member',
                    );
                }
    
                return new WP_REST_Response($return, 200);
            }
        ));
    
        register_rest_route('wp/v2', '/auth/logout', array (
            'methods'  => 'POST',
            'callback' => function( $request ) {
            
                $params = $_POST;
                $return = array(
                    'result' => true,
                    'data' => array(
                        'id' => 0,
                        'username' => 'guest',
                        'role' => 'guest'
                    )
                );
    
                wp_destroy_current_session();
                wp_clear_auth_cookie();
                wp_set_current_user(0);
                do_action('wp_logout', intval($params['id']));
            
                return new WP_REST_Response($return, 200);
            }
        ));
    });
    

    flush_rewrite_rules(true);
}
add_action('init', 'wp_json_v2_ordini_init');

function my_max_posts_per_page($max) {
    return -1; // Default 50
}
add_filter( 'wp_query_route_to_rest_api_max_posts_per_page', 'my_max_posts_per_page' );

// ORDINI OPTIONS PAGE

if( function_exists('acf_add_options_page') ) {
    
	$args = array(
	
        /* (string) The title displayed on the options page. Required. */
        'page_title' => 'Opzioni Calendario Ordini',

        /* (string) The title displayed in the wp-admin sidebar. Defaults to page_title */
        'menu_title' => 'Opzioni',

        /* (string) The URL slug used to uniquely identify this options page. 
        Defaults to a url friendly version of menu_title */
        'menu_slug' => 'ordini_options',

        /* (string) The capability required for this menu to be displayed to the user. Defaults to edit_posts.
        Read more about capability here: http://codex.wordpress.org/Roles_and_Capabilities */
        'capability' => 'edit_posts',

        /* (int|string) The position in the menu order this menu should appear. 
        WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
        Risk of conflict can be reduced by using decimal instead of integer values, e.g. '63.3' instead of 63 (must use quotes).
        Defaults to bottom of utility menu items */
        'position' => false,

        /* (string) The slug of another WP admin page. if set, this will become a child page. */
        'parent_slug' => 'edit.php?post_type=ordini',

        /* (string) The icon class for this menu. Defaults to default WordPress gear.
        Read more about dashicons here: https://developer.wordpress.org/resource/dashicons/ */
        'icon_url' => false,

        /* (boolean) If set to true, this options page will redirect to the first child page (if a child page exists). 
        If set to false, this parent page will appear alongside any child pages. Defaults to true */
        'redirect' => true,

        /* (int|string) The '$post_id' to save/load data to/from. Can be set to a numeric post ID (123), or a string ('user_2'). 
        Defaults to 'options'. Added in v5.2.7 */
        'post_id' => 'ordini_options',

        /* (boolean)  Whether to load the option (values saved from this options page) when WordPress starts up. 
        Defaults to false. Added in v5.2.8. */
        'autoload' => false,

        /* (string) The update button text. Added in v5.3.7. */
        'update_button'		=> __('Aggiorna', 'acf'),

        /* (string) The message shown above the form on submit. Added in v5.6.0. */
        'updated_message'	=> __("Impostazioni aggiornate", 'acf'),

    );
    acf_add_options_page($args);
}

?>
