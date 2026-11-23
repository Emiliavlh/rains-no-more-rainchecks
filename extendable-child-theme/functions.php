<?php
// Kør funktionen, når WordPress indlæser CSS og JavaScript
add_action( 'wp_enqueue_scripts',
            'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
  $parenthandle = 'parent-style';
  $theme = wp_get_theme();

  // 1. Først mother themets style.css
  wp_enqueue_style( $parenthandle,
    get_template_directory_uri() . '/style.css',
    array(),
    $theme->parent()->get( 'Version' )
  );

  // 2. Derefter child themets style.css
  wp_enqueue_style( 'child-style',
    get_stylesheet_uri(),
    array( $parenthandle ),
    $theme->get( 'Version' )
  );
}

/* ==========================================
   RAINS RAINCHECK - TILMELDINGER OG COUNTER
   ========================================== */

/* Opret databasetabel */

function rains_create_table() {
    global $wpdb;

    $table = $wpdb->prefix . 'rains_entries';

    $charset = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        email varchar(190) NOT NULL,
        raincheck text NOT NULL,
        discount int NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $charset;";

    dbDelta($sql);
}

add_action('init', 'rains_create_table_once');

function rains_create_table_once() {
    if (get_option('rains_table_version') !== '1') {
        rains_create_table();
        update_option('rains_table_version', '1');
    }
}


/* Registrer API */

add_action('rest_api_init', function () {

    register_rest_route('rains/v1', '/count', array(
        'methods' => 'GET',
        'callback' => 'rains_get_count',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('rains/v1', '/signup', array(
        'methods' => 'POST',
        'callback' => 'rains_signup',
        'permission_callback' => '__return_true'
    ));

});


/* Hent antal tilmeldinger */

function rains_get_count() {
    global $wpdb;

    $table = $wpdb->prefix . 'rains_entries';

    $count = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM $table"
    );

    $response = rest_ensure_response(array(
        'count' => $count
    ));

    $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');

    return $response;
}


/* Registrer ny tilmelding */

function rains_signup($request) {
    global $wpdb;

    $table = $wpdb->prefix . 'rains_entries';

    $email = sanitize_email($request->get_param('email'));

    $raincheck = sanitize_textarea_field(
        $request->get_param('raincheck')
    );

    /* Simpelt honeypot-felt mod bots */

    $website = $request->get_param('website');

    if (!empty($website)) {
        return new WP_Error(
            'invalid_submission',
            'Tilmeldingen kunne ikke gennemføres.',
            array('status' => 400)
        );
    }

    if (!is_email($email)) {
        return new WP_Error(
            'invalid_email',
            'Indtast en gyldig e-mailadresse.',
            array('status' => 400)
        );
    }

    if (strlen($email) > 190) {
        return new WP_Error(
            'email_too_long',
            'E-mailadressen er for lang.',
            array('status' => 400)
        );
    }

    if (empty($raincheck) || mb_strlen($raincheck) > 500) {
        return new WP_Error(
            'invalid_raincheck',
            'Skriv dit Raincheck (maks. 500 tegn).',
            array('status' => 400)
        );
    }

    /* Begræns gentagne forsøg fra samme IP */

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $rate_key = 'rains_rate_' . md5($ip);

    $attempts = (int) get_transient($rate_key);

    if ($attempts >= 10) {
        return new WP_Error(
            'too_many_requests',
            'For mange forsøg. Prøv igen senere.',
            array('status' => 429)
        );
    }

    set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);

    /* Tjek om e-mail allerede er registreret */

    $existing = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table WHERE email = %s",
            $email
        )
    );

    if ($existing) {
        return new WP_Error(
            'already_registered',
            'Denne e-mailadresse er allerede tilmeldt.',
            array('status' => 409)
        );
    }

    /* Tilfældig rabat svarende til lykkehjulet */

    $discounts = array(20, 30, 20, 25, 20, 30, 20, 25);

    $index = random_int(0, 7);

    $discount = $discounts[$index];

    /* Gem tilmelding */

    $inserted = $wpdb->insert(
        $table,
        array(
            'email' => $email,
            'raincheck' => $raincheck,
            'discount' => $discount,
            'created_at' => current_time('mysql')
        ),
        array('%s', '%s', '%d', '%s')
    );

    if (!$inserted) {
        return new WP_Error(
            'signup_failed',
            'Tilmeldingen kunne ikke gemmes. Prøv igen.',
            array('status' => 500)
        );
    }

    $count = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM $table"
    );

    $response = rest_ensure_response(array(
        'success' => true,
        'count' => $count,
        'discount' => $discount,
        'segment' => $index
    ));

    $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');

    return $response;
}