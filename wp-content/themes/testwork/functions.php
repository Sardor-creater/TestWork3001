<?php

add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_fields' );
function woo_add_custom_fields() {
    echo '<div class="options_group">';// Группировка полей
    woocommerce_wp_text_input( array(
        'id'                => 'custom_date_field',
        'label'             => __( 'создан продукт', 'woocommerce' ),
        'type'              => 'date',
    ));

    // Выбор значения
    woocommerce_wp_select( array(
        'id'      => 'custom_select',
        'label'   => 'Выпадающий список',
        'options' => array(
            'rare'   => __( 'rare', 'woocommerce' ),
            'frequent'   => __( 'frequent', 'woocommerce' ),
            'unusual' => __( 'unusual', 'woocommerce' ),
        ),
    ) );

    ?>

    <p class="form-field">
        <button type="button" id="clear-btn" class="button">Clear</button>
    </p>

    <script>
        jQuery(document).ready(function ($) {
            $( "#clear-btn" ).after($("#publishing-action :input")).on( "click", function() {
                $("#custom_date_field").val("");
                $("#custom_select").prop("selectedIndex", -1);
            } );

            // Uploading files
            var file_frame;

            $.fn.upload_listing_image = function( button ) {
                var button_id = button.attr('id');
                var field_id = button_id.replace( '_button', '' );

                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: $( this ).data( 'uploader_title' ),
                    button: {
                        text: $( this ).data( 'uploader_button_text' ),
                    },
                    multiple: false
                });

                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $("#"+field_id).val(attachment.id);
                    $("#listingimagediv img").attr('src',attachment.url);
                    $( '#listingimagediv img' ).show();
                    $( '#' + button_id ).attr( 'id', 'remove_listing_image_button' );
                    $( '#remove_listing_image_button' ).text( 'Remove listing image' );
                });

                // Finally, open the modal
                file_frame.open();
            };

            $('#listingimagediv').on( 'click', '#upload_listing_image_button', function( event ) {
                event.preventDefault();
                $.fn.upload_listing_image( $(this) );
            });

            $('#listingimagediv').on( 'click', '#remove_listing_image_button', function( event ) {
                event.preventDefault();
                $( '#upload_listing_image' ).val( '' );
                $( '#listingimagediv img' ).attr( 'src', '' );
                $( '#listingimagediv img' ).hide();
                $( this ).attr( 'id', 'upload_listing_image_button' );
                $( '#upload_listing_image_button' ).text( 'Set listing image' );
            });
        });
    </script>
        <?php

    echo '</div>';

}


//save
function woo_custom_fields_save( $post_id ) {

//    $woocommerce_text_field = $_POST['custom_date_field'];
//    if ( ! empty( $woocommerce_text_field ) ) {
//        update_post_meta( $post_id, '_text_field', esc_attr( $woocommerce_text_field ) );
//    }

//    $woocommerce_select = $_POST['custom_select'];
//    if ( ! empty( $woocommerce_select ) ) {
//        update_post_meta( $post_id, '_select', esc_attr( $woocommerce_select ) );
//    }

    // Вызываем объект класса
    $product = wc_get_product( $post_id );

    // Сохранение date поля
    $text_field = isset( $_POST['custom_date_field'] ) ? sanitize_text_field( $_POST['custom_date_field'] ) : '';
    $product->update_meta_data( 'custom_date_field', $text_field );


    // Сохранение выпадающего списка
    $select_field = isset( $_POST['custom_select'] ) ? sanitize_text_field( $_POST['custom_select'] ) : '';
    $product->update_meta_data( 'custom_select', $select_field );

    $product->save();
}
add_action( 'woocommerce_process_product_meta', 'woo_custom_fields_save', 10);


//add metabox set img and rm

add_action( 'add_meta_boxes', 'listing_image_add_metabox' );
function listing_image_add_metabox () {
    add_meta_box( 'listingimagediv', __( 'Custom Image', 'text-domain' ), 'listing_image_metabox', 'product', 'side', 'low');
}

function listing_image_metabox ( $post ) {
    global $content_width, $_wp_additional_image_sizes;

    $image_id = get_post_meta( $post->ID, '_listing_image_id', true );

    $old_content_width = $content_width;
    $content_width = 254;

    if ( $image_id && get_post( $image_id ) ) {

        if ( ! isset( $_wp_additional_image_sizes['post-thumbnail'] ) ) {
            $thumbnail_html = wp_get_attachment_image( $image_id, array( $content_width, $content_width ) );
        } else {
            $thumbnail_html = wp_get_attachment_image( $image_id, 'post-thumbnail' );
        }

        if ( ! empty( $thumbnail_html ) ) {
            $content = $thumbnail_html;
            $content .= '<p class="hide-if-no-js"><a href="javascript:;" id="remove_listing_image_button" >' . esc_html__( 'Remove listing image', 'text-domain' ) . '</a></p>';
            $content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="' . esc_attr( $image_id ) . '" />';
        }

        $content_width = $old_content_width;
    } else {

        $content = '<img src="" style="width:' . esc_attr( $content_width ) . 'px;height:auto;border:0;display:none;" />';
        $content .= '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set listing image', 'text-domain' ) . '" href="javascript:;" id="upload_listing_image_button" id="set-listing-image" data-uploader_title="' . esc_attr__( 'Choose an image', 'text-domain' ) . '" data-uploader_button_text="' . esc_attr__( 'Set listing image', 'text-domain' ) . '">' . esc_html__( 'Set listing image', 'text-domain' ) . '</a></p>';
        $content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="" />';

    }

    echo $content;
}

add_action( 'save_post', 'listing_image_save', 10, 1 );
function listing_image_save ( $post_id ) {
    if( isset( $_POST['_listing_cover_image'] ) ) {
        $image_id = (int) $_POST['_listing_cover_image'];
        update_post_meta( $post_id, '_listing_image_id', $image_id );
    }
}