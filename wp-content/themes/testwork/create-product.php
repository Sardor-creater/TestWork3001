<?php
/*
Template Name: CREATE PRODUCT
*/

$error_pTitle = '';
if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['co_submit']) ) {

    //Validate Product Title
    if( empty( test_input($_POST['co_productTitle']) ) ) {
        $error_pTitle = __('Вводите названия продукта');
    } else {
        $ProductTitle = test_input($_POST['co_productTitle']);
    }

    $ProductPriceValue = test_input($_POST['co_productPriceUSD']);
    $ProductCustomSelect = test_input($_POST['custom_select']);
    $ProductCustomDate = test_input($_POST['custom_date']);

    if ($error_pTitle == ''){

        $post = array(
            'post_status' => "publish",
            'post_title' => $ProductTitle,
            'post_type' => "product",
            'post_parent' => 0,
        );
        //create product for product ID
        $product_id = wp_insert_post( $post, __('Cannot create product', 'izzycart-function-code') );
        //type of product
        wp_set_object_terms($product_id, 'simple', 'product_type');

//        upload image
        if ($_FILES['image']) {
            $allowmimeType = array(
                'png' => 'image/png',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
            );

            # check if mime type
            if(in_array($_FILES['image']['type'],$allowmimeType) ){

                //require the needed files
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                require_once(ABSPATH . "wp-admin" . '/includes/media.php');

                $attach_id = media_handle_upload( 'image', $product_id );
                update_post_meta( $product_id, '_listing_image_id', $attach_id );
            }
        }

        //add product meta
        update_post_meta( $product_id, '_regular_price', $ProductPriceValue );
        update_post_meta( $product_id, '_price', $ProductPriceValue );

        update_post_meta( $product_id, 'custom_select', $ProductCustomSelect );
        update_post_meta( $product_id, 'custom_date_field', $ProductCustomDate );

        update_post_meta( $product_id, '_visibility', 'visible' );
        update_post_meta( $product_id, '_stock_status', 'instock' );

        header("Location: /create-product");
    }
}
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <header class="entry-header">
                <h1 class="entry-title">Добавить товар</h1>
            </header>

            <form method="post" action="" enctype="multipart/form-data">

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="account_display_name">Название&nbsp;<span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="co_productTitle">
                    <span><em><?php echo $error_pTitle;?></em></span>
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                    <label for="co_productPriceUSD">Цена</label>
                    <input type="number" class="woocommerce-Input woocommerce-Input--text input-text" name="co_productPriceUSD" id="co_productPriceUSD" autocomplete="given-name" value="">
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                    <label for="custom_date">Создан</label>
                    <input type="date" class="woocommerce-Input woocommerce-Input--text input-text" name="custom_date" id="custom_date" autocomplete="family-name" value="">
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                    <label for="custom_select">Тип продукта</label>
                    <select class="woocommerce-Input woocommerce-Input--text input-text" id="custom_select" name="custom_select">
                        <option>rare</option>
                        <option>frequent</option>
                        <option>unusual</option>
                    </select>
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                    <label for="image">Картинка</label>
                    <input type="file" class="woocommerce-Input woocommerce-Input--text input-text" id="image" name="image">
                </p>

                <button type="submit" class="woocommerce-Button button" name="co_submit" id="co-submit" value="Сохранить изменения">Сохранить изменения</button>

            </form>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
do_action( 'storefront_sidebar' );
get_footer();
