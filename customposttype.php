<?php
/**
* Plugin Name: customposttype
* Plugin URI: https://www.likonet.es/
* Description: Prueba de acceso 2.
* Version: 0.1
* Author: Jorge GL
* Author URI: https://www.likonet.es/
**/

// Enganchar al hook de acción init
add_action('init', 'tienda_custom_post_type');
 
// La función para registrar un tipo de artículo personalizado
function tienda_custom_post_type() {
 
  // Etiquetas
  $labels = array(
    'name'               => __( 'Tiendas' ),
    'singular_name'      => __( 'Tienda' ),
    'add_new'            => __( 'Agregar Tienda' ),
    'add_new_item'       => __( 'Agregar Tienda' ),
    'edit_item'          => __( 'Editar Tienda' ),
    'new_item'           => __( 'Nuevo' ),
    'all_items'          => __( 'Todos' ),
    'view_item'          => __( 'Ver' ),
    'search_items'       => __( 'Buscar' ),
    'featured_image'     => 'Imagen',
    'set_featured_image' => 'Establecer imagen'
  );
 
  // Argumentos: he elegido algunos argumentos a mi discrección
  $args = array(
    'labels'            => $labels,
    'description'       => 'Contiene información específica',
    'public'            => true,
    'supports'          => array('title', 'custom-fields'),
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'query_var'         => true,
    'capability_type'   => 'page',
    'show_in_rest' => true,
    'rest_controller_class' => 'WP_REST_Posts_Controller',
    'rest_base'             => 'tiendas',

  );
  // tengo la ruta
  // https://pezquefuma.es/wp-json/wp/v2/tiendas
  // al parecer son opcionales los dos últimos elementos del array args 
 
  // register_post_type() admite dos parámetros: 
  // uno para el tipo de post y otro para pasarle el array de argumentos

  register_post_type('tienda', $args);

  // he nombrado la función para registrar la meta box comenzando con jgl_tienda

  function jgl_tienda_register_meta_boxes()
{
    add_meta_box('tienda-info', 'Datos de la tienda', 'jgl_tienda_output_meta_box', 'tienda', 'normal', 'high');
}

// funnción que devuelve el contenido de la meta box

function jgl_tienda_output_meta_box($post)
{

    // obtenemos los campos de la bbdd

    $nombre_tienda = get_post_meta($post->ID, 'nombre_tienda', true);
    $direccion = get_post_meta($post->ID, '_direccion', true);
    $telefono = get_post_meta($post->ID, '_telefono', true);

    // campo de seguridad

    wp_nonce_field('grabacion_tienda', 'tienda_nonce');

    // mostramos los campos de la bbdd en cada campo del formulario

    echo('<label for="Nombre de la tienda">' . __('Nombre de la tienda', 'text_domain') . '</label>');
    echo('<input type="text" name="nombre_tienda" id="nombre_tienda" value="' . esc_attr($nombre_tienda) . '">');
    echo('<p><label for="Dirección">' . __('Dirección', 'text_domain') . '</label>');
    echo('<input type="text" name="direccion" id="direccion" value="' . esc_attr($direccion) . '">');
    echo('<p><label for="Teléfono">' . __('Teléfono', 'text_domain') . '</label>');
    echo('<input type="text" name="telefono" id="telefono" value="' . esc_attr($telefono) . '">');
  
}

// Añadimos la acción

add_action('add_meta_boxes', 'jgl_tienda_register_meta_boxes');

// guardar la info del formulario en la base de datos

function jgl_tienda_save_meta_boxes($post_id)
{
    // Comprueba que el campo nonce, el tipo de post y los permisos del usuario
    if ( !isset($_POST['tienda_nonce']) || ! wp_verify_nonce( $_POST['tienda_nonce'], 'grabacion_tienda') ) {
        return $post_id;
    }

    if ('tienda' != $_POST['post_type']) {
        return $post_id;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Después de comprobar todo, grabamos datos
    $nombre_tienda = sanitize_text_field($_POST['nombre_tienda']);
    update_post_meta($post_id, 'nombre_tienda', $nombre_tienda);
    $direccion = sanitize_text_field($_POST['direccion']);
    update_post_meta($post_id, 'direccion', $direccion);
    $telefono  = sanitize_text_field($_POST['telefono']);
    update_post_meta($post_id, 'telefono', $telefono);
    return true;
}

add_action('save_post', 'jgl_tienda_save_meta_boxes');

// necesario enganchar esto para llamadas ajax

add_action("wp_ajax_nopriv_get_shop_data", "get_shop_data");
add_action("wp_ajax_get_shop_data", "get_shop_data");

// incluir campos personalizados en la API

function add_nombre_tienda_to_rest() {

    register_rest_field( array('tienda') ,
        'nombre_tienda',
        array(
            'get_callback'    => 'get_field_nombre_tienda',
            'update_callback' => null,
            'schema'          => null,
        )
    );

}

add_action( 'rest_api_init', 'add_nombre_tienda_to_rest');

function add_direccion_to_rest() {

    register_rest_field( array('tienda') ,
        'direccion',
        array(
            'get_callback'    => 'get_field_direccion',
            'update_callback' => null,
            'schema'          => null,
        )
    );

}

add_action( 'rest_api_init', 'add_direccion_to_rest');

function add_telefono_to_rest() {

    register_rest_field( array('tienda') ,
        'telefono',
        array(
            'get_callback'    => 'get_field_telefono',
            'update_callback' => null,
            'schema'          => null,
        )
    );

}

add_action( 'rest_api_init', 'add_telefono_to_rest');



function get_field_nombre_tienda( $object, $field_name, $request ) {
    return get_post_meta( $object[ 'id' ], 'nombre_tienda', true );
}

function get_field_direccion( $object, $field_name, $request ) {
    return get_post_meta( $object[ 'id' ], 'direccion', true );
}

function get_field_telefono( $object, $field_name, $request ) {
    return get_post_meta( $object[ 'id' ], 'telefono', true );
}



// la función en php

function get_shop_data() {

    $api_url = "https://pezquefuma.es/wp-json/wp/v2/tiendas?_fields=nombre_tienda,direccion,telefono";
    $request = wp_remote_get($api_url);
    $body = wp_remote_retrieve_body($request);
    $output = json_encode($body, true);
    echo $output;

    die();
}


// cargar sólo en shoplist:  if ( get_page_template_slug() == 'shoplist.php' ) ...
// https://wordpress.stackexchange.com/questions/61244/wp-enqueue-style-on-specific-page-templates

function my_enqueue() {
    
     if ( get_page_template_slug() == 'shoplist.php' ) {

      wp_enqueue_script( 'ajax-script', plugins_url('customposttype/js/filename.js'), array('jquery') );
      wp_localize_script( 'ajax-script', 'my_ajax_object', array( 
          'ajax_url' => admin_url('admin-ajax.php')
          ) 
      );
    }
 }
 
 add_action( 'wp_enqueue_scripts', 'my_enqueue' );

}