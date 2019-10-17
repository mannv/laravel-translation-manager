<?php

$callBack = [];
if (config('app.env') != 'local') {
    $callBack = ['translations:git'];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Routes group config
    |--------------------------------------------------------------------------
    |
    | The default group settings for the elFinder routes.
    |
    */
    'route' => [
        'prefix' => 'backend/translations',
        'middleware' => ['web', 'auth'],
    ],

    /**
     * Enable deletion of translations
     *
     * @type boolean
     */
    'delete_enabled' => true,

    /**
     * Exclude specific groups from Laravel Translation Manager.
     * This is useful if, for example, you want to avoid editing the official Laravel language files.
     *
     * @type array
     *
     *    array(
     *        'pagination',
     *        'reminders',
     *        'validation',
     *    )
     */
    'exclude_groups' => [],

    /**
     * Exclude specific languages from Laravel Translation Manager.
     *
     * @type array
     *
     *    array(
     *        'fr',
     *        'de',
     *    )
     */
    'exclude_langs' => [],

    /**
     * Export translations with keys output alphabetically.
     */
    'sort_keys ' => false,

    'trans_functions' => [
        'trans',
        'trans_choice',
        'Lang::get',
        'Lang::choice',
        'Lang::trans',
        'Lang::transChoice',
        '@lang',
        '@choice',
        '__',
        '\$trans.get',
        '\$t'//vuejs
    ],
    'file_extension' => ['*.php', '*.vue', '*.js'],
    'exclude_extension' => ['blade.php'],
    'exclude_folder' => ['storage', 'vendor', 'docker', 'Nuxtjs/node_modules'],
    'vuejs_locale_path' => 'Nuxtjs/locales',
    'publish_callback' => $callBack
];
