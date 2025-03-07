<?php
/**
 * Plugin Name:       Mapog
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mapog
 *
 * @package CreateBlock
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$config_path = __DIR__ . '/config.php';
if (!file_exists($config_path)) {
    error_log('config.php not found at ' . $config_path);
} else {
    $config = include $config_path;
    if (!is_array($config)) {
        error_log('config.php did not return an array.');
    } else {
        error_log('config.php loaded successfully: ' . print_r($config, true));
    }
}

define('MAPOG_LOGIN_API', $config['MAPOG_LOGIN_API']);
define('MAPOG_MAPS_API', $config['MAPOG_MAPS_API']);





// Register activation hook
function mapog_plugin_activate() {
    // Activation logic if needed
}
register_activation_hook(__FILE__, 'mapog_plugin_activate');

// Register deactivation hook
function mapog_plugin_deactivate() {
    // Deactivation logic if needed
}
register_deactivation_hook(__FILE__, 'mapog_plugin_deactivate');
// Create admin menu
function mapog_plugin_menu() {
    add_menu_page('Mapog', 'Mapog', 'manage_options', 'mapog-embed', 'mapog_plugin_page');
}
add_action('admin_menu', 'mapog_plugin_menu');

// Enqueue scripts & styles
function mapog_enqueue_scripts($hook) {
    // Load config file
    $config = include plugin_dir_path(__FILE__) . 'config.php';

    // Ensure keys exist before using them
    $login_api = isset($config['MAPOG_LOGIN_API']) ? $config['MAPOG_LOGIN_API'] : '';
    $maps_api  = isset($config['MAPOG_MAPS_API']) ? $config['MAPOG_MAPS_API'] : '';
    $base_url  = isset($config['MAPOG_BASE_URL']) ? $config['MAPOG_BASE_URL'] : ''; // Add base URL
    // Enqueue necessary scripts
    wp_enqueue_script('jquery');
    wp_enqueue_style('mapog-style', plugin_dir_url(__FILE__) . 'mapog-style.css');
    wp_enqueue_script('mapog-js', plugin_dir_url(__FILE__) . 'mapog.js', array('jquery'), '1.0', true);

    // Debugging - Check if API values are set
    error_log('MAPOG_LOGIN_API in wp_localize_script: ' . $login_api);
    error_log('MAPOG_MAPS_API in wp_localize_script: ' . $maps_api);
    error_log('MAPOG_BASE_URL in wp_localize_script: ' . $base_url);
    // Localize script with dynamic API values
    wp_localize_script('mapog-js', 'mapog_ajax', array(
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'login_api' => $login_api,
        'maps_api'  => $maps_api,
        'base_url'  => $base_url
    ));
}
error_log('wp_localize_script Data: ' . print_r(array(
    'ajaxurl'   => admin_url('admin-ajax.php'),
    'login_api' => $login_api,
    'maps_api'  => $maps_api,
    'base_url'  => $base_url
), true));


add_action('admin_enqueue_scripts', 'mapog_enqueue_scripts');
function create_block_mapog_block_init() {
    register_block_type( __DIR__ . '/build/mapog', array(
        'render_callback' => 'render_mapog_block'
    ));
}
add_action( 'init', 'create_block_mapog_block_init' );

require_once plugin_dir_path( __FILE__ ) . 'render.php'; // Include render.php
function enqueue_mapog_frontend_script() {
    wp_enqueue_script(
        'mapog-frontend-js',
        plugins_url('build/mapog/frontend.js', __FILE__), // ✅ Ensure correct path
        array('jquery'),  
        filemtime(plugin_dir_path(__FILE__) . 'build/mapog/frontend.js'), // Prevent caching
        true  
    );
}
function mapog_add_js_variables() {
    $config = include plugin_dir_path(__FILE__) . 'config.php';

    echo '<script type="text/javascript">
        window.mapogConfig = {
            login_api: "' . esc_js($config['MAPOG_LOGIN_API']) . '",
            maps_api: "' . esc_js($config['MAPOG_MAPS_API']) . '",
            base_url: "' . esc_js($config['MAPOG_BASE_URL']) . '"
        };
    </script>';
}
add_action('admin_footer', 'mapog_add_js_variables');

add_action('wp_enqueue_scripts', 'enqueue_mapog_frontend_script');
// Admin page content
function mapog_plugin_page() {
    ?>
    <style>
        .mapog-container {
            display: flex;
            height: 100vh;
            background: linear-gradient(to right, #0072ff, #00c6ff);
            align-items: center;
            justify-content: center;
        }
        .mapog-box {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .mapog-left {
            flex: 1;
            display: none;
        }
        .mapog-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .mapog-right h2 {
            color: #0072ff;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .mapog-login {
            width: 100%;
            max-width: 350px;
        }
        .mapog-login input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .mapog-btn {
            padding: 12px;
            background: #0072ff;
            font-size: 14px;
            color: white;
            border: none;
            border-radius: 5px;
            text-align: center;
            display: block;
            margin: 10px auto;
        }
        .mapog-btn:hover {
            background: #005bb5;
        }
        .mapog-maps table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .mapog-maps th, .mapog-maps td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        .mapog-maps th {
            background-color: #f2f2f2;
        }
    </style>
    <div class="mapog-container">
        <div class="mapog-box">
            <div class="mapog-left"></div>
            <div class="mapog-right">
                <div id="mapog-content"></div>
            </div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        function checkLogin() {
            let accessToken = localStorage.getItem('mapog_access_token');
            if (!accessToken) {
                showLoginForm();
            } else {
                fetchUserMaps(accessToken);
            }
        }

        function showLoginForm() {
            $('.mapog-left').html(`
         <div class="mapog-left-inner" style="
    background: url('<?php echo plugin_dir_url(__FILE__) . "image_2025_02_18T07_08_00_214Z.png"; ?>') no-repeat center center;
    background-size: cover;
    position: relative;
    opacity: 0.9;
    width: 100%;
    height: 100%;
    z-index: 1;
">
    <div class="mapog-logo" style="
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60%;
        height: 300px; /* Ensures visibility */
        background: url('<?php echo plugins_url('Untitled design (2).png', __FILE__); ?>') no-repeat center center;
        background-size: contain;
        opacity: 2;
        z-index: 9999;
    ">
    </div>
</div>
`).show();
$('#mapog-content').html(`
                <h2 class="heading">Login with Mapog</h2>
                <div id="login-message"></div>
                <div class="mapog-login">
                    <form id="mapog-login-form">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit" class="mapog-btn login">Login</button>
                    </form>
                </div>
            `);

            $(document).on('submit', '#mapog-login-form', function(e) {
                e.preventDefault();
                var formData = {
                    email: $('input[name="email"]').val(),
                    password: $('input[name="password"]').val()
                };

                $.ajax({
                    url: mapog_ajax.login_api,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        if (response.token) {
                            localStorage.setItem('mapog_access_token', response.token);
                            location.reload();
                        } else {
                            $('#login-message').html('<p class="error">Login failed</p>');
                        }
                    },
                    error: function() {
                        $('#login-message').html('<p class="error">Login request failed</p>');
                    }
                });
            });
        }

        function fetchUserMaps(accessToken) {
            $('.mapog-left').hide(); 

            $('#mapog-content').html(`
    <div class="mapog-maps">
        <h2>Your Maps</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Serial No</th>
                    <th style="width: 30%;">Map Name</th>
                    <th style="width: 80%;">Description</th>
                </tr>
            </thead>
            <tbody id="map-table-body"></tbody>
        </table>
        <button id="mapog-logout" class="mapog-btn mapog-logout">Logout</button>
    </div>
`);
$.ajax({url: mapog_ajax.maps_api,
                type: 'GET',
                headers: {
                    'Authorization': accessToken
                },
                success: function(response) {
                    console.log("Fetched Maps:", response);
                    if (response.data && response.data.data.length > 0) {
                        let tableBody = '';
                        response.data.data.forEach(function(map, index) {
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${map.map_name}</td>
                                    <td>${map.mapdescription || 'No description available'}</td>
                                </tr>
                            `;
                        });
                        $('#map-table-body').html(tableBody);
                    } else {
                        $('#map-table-body').html('<tr><td colspan="3">No maps found.</td></tr>');
                    }
                }
            });

            $('#mapog-logout').on('click', function() {
                localStorage.removeItem('mapog_access_token');
                location.reload();
            });
        }

        checkLogin();
    });
    </script>
    <?php
}
?>