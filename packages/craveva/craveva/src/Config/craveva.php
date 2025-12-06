<?php
// Updates folder
$product = 'craveva';

// Craveva id
$craveva_item_id = 20052522;

// Product Url from craveva
$productUrl = 'https://craveva.net/item/craveva-project-management-system/20052522?ref=ajay138';

$updatesDomain = '';

$verifyDomain = '';

return [

    /*
     * Model name of where purchase code is stored
     */
    'setting' => \App\Models\GlobalSetting::class,

    /*
     * Add redirect route here route('login') will be used
     */
    'redirectRoute' => 'login',

    'craveva_item_id' => $craveva_item_id,

    'craveva_product_name' => $product,

    'craveva_product_url' => $productUrl,

    /*
    * Temp folder to store update before to install it.
    */
    'tmp_path' => storage_path() . '/app',
    /*
    * URL where your updates are stored ( e.g. for a folder named 'updates', under http://site.com/yourapp ).
    */
    'update_baseurl' => $updatesDomain . '/' . $product,
    /*
    * URL to verify your purchase code
    */
    'verify_url' => $verifyDomain . '/verify-purchase',

    /*
     *
     */
    'updater_file_path' => $updatesDomain . '/' . $product . '/laraupdater.json',

    /*
    * Set a middleware for the route: updater.update
    * Only 'auth' NOT works (manage security using 'allow_users_id' configuration)
    */
    'middleware' => ['web', 'auth'],

    /*
    * Set which users can perform an update;
    * This parameter accepts: ARRAY(user_id) ,or FALSE => for example: [1]  OR  [1,3,0]  OR  false
    * Generally, ADMIN have user_id=1; set FALSE to disable this check (not recommended)
    */

    'allow_users_id' => false,
    
    
    /*
   * Set which where you want to use the script tag
   * Make sure you prevent in demo or do not expose to normal user
   */

    'xss_ignore_index' => ['widget_code']
];