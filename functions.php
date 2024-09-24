<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'MILXPLUGIN_admin_enqueue_script' ) ) {
	function MILXPLUGIN_admin_enqueue_script() {
		wp_enqueue_style( 'buddyboss-addon-admin-css', plugin_dir_url( __FILE__ ) . 'style.css' );
	}

	add_action( 'admin_enqueue_scripts', 'MILXPLUGIN_admin_enqueue_script' );
}

if ( ! function_exists( 'MILXPLUGIN_get_settings_sections' ) ) {
	function MILXPLUGIN_get_settings_sections() {

		$settings = array(
			'MILXPLUGIN_settings_section' => array(
				'page'  => 'addon',
				'title' => __( 'Add-on Settings', 'buddyboss-platform-addon' ),
			),
		);

		return (array) apply_filters( 'MILXPLUGIN_get_settings_sections', $settings );
	}
}

if ( ! function_exists( 'MILXPLUGIN_get_settings_fields_for_section' ) ) {
	function MILXPLUGIN_get_settings_fields_for_section( $section_id = '' ) {

		// Bail if section is empty
		if ( empty( $section_id ) ) {
			return false;
		}

		$fields = MILXPLUGIN_get_settings_fields();
		$retval = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

		return (array) apply_filters( 'MILXPLUGIN_get_settings_fields_for_section', $retval, $section_id );
	}
}

if ( ! function_exists( 'MILXPLUGIN_get_settings_fields' ) ) {
	function MILXPLUGIN_get_settings_fields() {

		$fields = array();

		$fields['MILXPLUGIN_settings_section'] = array(

			'MILXPLUGIN_field' => array(
				'title'             => __( 'Add-on Field', 'buddyboss-platform-addon' ),
				'callback'          => 'MILXPLUGIN_settings_callback_field',
				'sanitize_callback' => 'absint',
				'args'              => array(),
			),

		);

		return (array) apply_filters( 'MILXPLUGIN_get_settings_fields', $fields );
	}
}

if ( ! function_exists( 'MILXPLUGIN_settings_callback_field' ) ) {
	function MILXPLUGIN_settings_callback_field() {
		?>
        <input name="MILXPLUGIN_field"
               id="MILXPLUGIN_field"
               type="checkbox"
               value="1"
			<?php checked( MILXPLUGIN_is_addon_field_enabled() ); ?>
        />
        <label for="MILXPLUGIN_field">
			<?php _e( 'Enable this option', 'buddyboss-platform-addon' ); ?>
        </label>
		<?php
	}
}

if ( ! function_exists( 'MILXPLUGIN_is_addon_field_enabled' ) ) {
	function MILXPLUGIN_is_addon_field_enabled( $default = 1 ) {
		return (bool) apply_filters( 'MILXPLUGIN_is_addon_field_enabled', (bool) get_option( 'MILXPLUGIN_field', $default ) );
	}
}

/***************************** Add section in current settings ***************************************/

/**
 * Register fields for settings hooks
 * bp_admin_setting_general_register_fields
 * bp_admin_setting_xprofile_register_fields
 * bp_admin_setting_groups_register_fields
 * bp_admin_setting_forums_register_fields
 * bp_admin_setting_activity_register_fields
 * bp_admin_setting_media_register_fields
 * bp_admin_setting_friends_register_fields
 * bp_admin_setting_invites_register_fields
 * bp_admin_setting_search_register_fields
 */
if ( ! function_exists( 'MILXPLUGIN_bp_admin_setting_general_register_fields' ) ) {
    function MILXPLUGIN_bp_admin_setting_general_register_fields( $setting ) {
	    // Main General Settings Section
	    $setting->add_section( 'MILXPLUGIN_addon', __( 'Add-on Settings', 'buddyboss-platform-addon' ) );

	    $args          = array();
	    $setting->add_field( 'bp-enable-my-addon', __( 'My Field', 'buddyboss-platform-addon' ), 'MILXPLUGIN_admin_general_setting_callback_my_addon', 'intval', $args );
    }

	add_action( 'bp_admin_setting_general_register_fields', 'MILXPLUGIN_bp_admin_setting_general_register_fields' );
}

if ( ! function_exists( 'MILXPLUGIN_admin_general_setting_callback_my_addon' ) ) {
	function MILXPLUGIN_admin_general_setting_callback_my_addon() {
		?>
        <input id="bp-enable-my-addon" name="bp-enable-my-addon" type="checkbox"
               value="1" <?php checked( MILXPLUGIN_enable_my_addon() ); ?> />
        <label for="bp-enable-my-addon"><?php _e( 'Enable my option', 'buddyboss-platform-addon' ); ?></label>
		<?php
	}
}

if ( ! function_exists( 'MILXPLUGIN_enable_my_addon' ) ) {
	function MILXPLUGIN_enable_my_addon( $default = false ) {
		return (bool) apply_filters( 'MILXPLUGIN_enable_my_addon', (bool) bp_get_option( 'bp-enable-my-addon', $default ) );
	}
}


/**************************************** MY PLUGIN INTEGRATION ************************************/

use Google\Gemini\Client;
// require_once dirname( __FILE__ ) . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php'; // This is correct if functions.php is in the root of the plugin


/**
 * Set up the my plugin integration.
 */
function MILXPLUGIN_register_integration() {
	require_once dirname( __FILE__ ) . '/integration/buddyboss-integration.php';
	buddypress()->integrations['addon'] = new MILXPLUGIN_BuddyBoss_Integration();
}
add_action( 'bp_setup_integrations', 'MILXPLUGIN_register_integration' );


// 


// CUSTOM DEVELOPMENT BELOW

// Get Gemini API started
// have function take in the content/reply
function init_gemini($content) {
    try {
        $yourApiKey = 'AIzaSyAEJs6OTfPhp1be3l-pCIsV9RlMBS499LA';
        
        // Check if the class exists
        if (!class_exists('Gemini')) {
            throw new Exception('Gemini class is not found.');
        }

        // Set client variable to be Gemini
        $client = Gemini::client($yourApiKey);
        
        // Check if the methods exist
        if (!method_exists($client->geminiPro(), 'generateContent')) {
            throw new Exception('Method generateContent does not exist.');
        }

        $result = $client->geminiPro()->generateContent(
		"Analyze the following content and determine if it is appropriate for a corporate discussion board. "
        . "The content must not contain profanity, slurs, hate speech, or any language that promotes discrimination "
        . "or violence. Mild expressions of frustration (e.g., \"I hate when this happens\") are acceptable as long "
        . "as they are not directed at an individual or group in a harmful way. "
        . "Only respond with 'Yes' if the content is appropriate and 'No' if it contains any prohibited language or intent. "
        . "Do not provide any explanations or additional information.\n\n"
        . "Content: {$content}"
        );

        // Check for a valid response and log the result
        if ($result instanceof \Gemini\Responses\GenerativeModel\GenerateContentResponse) {
            $checker = $result->text();
            return $checker;
        } else {
            // Log unexpected response
            error_log('Unexpected response type: ' . print_r($result, true));
            return 'Unexpected response from the Gemini API.';
        }

	// If content is inaproprioate, flag content for moderation
	//

    } catch (ValueError $ve) {
        // Handle the specific ValueError
        error_log('Gemini API ValueError: ' . $ve->getMessage());
        return 'Content generation failed due to a value error.';
    } catch (Exception $e) {
        // Log any other exceptions
        error_log('Gemini API Error: ' . $e->getMessage());
        return 'An error occurred while processing your request.';
    }
}


// Hook the function to wp_loaded
// add_action('wp_loaded', 'init_gemini');

// Gemini API works now
// whenever a buddyboss comment is posted, run a function before submission
// check gemini for content appropriateness
// if gemini approves, post
// if gemini does not approve, have a modal appear that says the comment was inapropriate


// step 1, have a function called when a comment is uploaded

/**
 * Custom function to execute when a new forum reply is posted.
 *
 * @param int $reply_id The ID of the new reply.
 */
// Hook into the BuddyBoss action for when a forum reply is posted
add_action('bbp_new_reply', 'my_custom_function_on_reply', 10, 1);

function my_custom_function_on_reply($reply_id) {
    if (is_numeric($reply_id)) {
      // Get the reply and log it to the debug log
        $reply_content = strip_tags(bbp_get_reply_content($reply_id));

        error_log("A new forum reply has been posted. Reply ID: " . $reply_id);
        error_log("Reply Content: " . $reply_content);
		try{
      // Run content through gemini, if gemini returns !Yes, alert user that content must be appropriate
			$result = init_gemini($reply_content);
			if($result !== 'Yes'){
			//flag reply and send it to moderators?
			flag_inappropriate_post($reply_id);
        //wp_delete_post($reply_id, true);
				wp_die('Your content must be appropriate');
			}
			
    } catch(Exception $e){
      $word= 'canidate.safety_ratings';
            if (strpos($e, $word) !== false){
        error_log('ai already flagged it');
        if ($e instanceof ValueError) {
                error_log('ValueError encountered: ' . $e->getMessage());
                // Additional handling for ValueError if needed
            }
      }

			error_log('Gemini API Error: ' . $e->getMessage());
		};

        if (stripos($reply_content, 'Minecraft') !== false) {
            error_log("Reply validation failed (ID: " . $reply_id . "): The reply must not contain the word 'Minecraft'.");

            // Check for permission to delete
            if (!current_user_can('delete_reply', $reply_id)) {
                error_log("Current user does not have permission to delete reply with ID: " . $reply_id);
                return;
            }

            // Attempt to delete the reply
        }
    } else {
        error_log("Unexpected data type for reply ID: " . var_export($reply_id, true));
    }
}


function flag_inappropriate_post($reply_id) {
	update_post_meta($reply_id, 'flagged_for_moderation', true);
	 $reply_data = array(
        'ID' => $reply_id,
        'post_status' => 'pending', // Set the status to 'pending'
    );
    wp_update_post($reply_data);
	error_log('flagged');
}

function filter_flagged_comments($query) {
    // Only show flagged comments in the admin
    if (is_admin()) {
        $meta_query = array(
            array(
                'key'     => 'flagged_for_moderation',
                'value'   => true,
                'compare' => '='
            ),
        );
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_comments', 'filter_flagged_comments');
add_action('admin_menu', 'my_flagged_replies_menu');

function my_flagged_replies_menu() {
    add_menu_page(
        'Flagged Replies',
        'Flagged Replies',
        'manage_options',
        'flagged-replies',
        'my_flagged_replies_page'
    );
}

function my_flagged_replies_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Flagged Replies', 'textdomain'); ?></h1>
        <table class="wp-list-table">
            <thead>
                <tr>
                    <th><?php _e('Reply ID', 'textdomain'); ?></th>
                    <th><?php _e('Content', 'textdomain'); ?></th>
                    <th><?php _e('Actions', 'textdomain'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'reply',
                    'post_status' => 'pending', // Only fetch replies with 'pending' status
                    'meta_query' => array(
                        array(
                            'key'     => 'flagged_for_moderation',
                            'value'   => '1',
                            'compare' => '=',
                        ),
                    ),
                );
                $flagged_replies = get_posts($args);

                if (empty($flagged_replies)) {
                    echo '<tr><td colspan="3">' . __('No flagged replies found.', 'textdomain') . '</td></tr>';
                } else {
                    foreach ($flagged_replies as $reply) {
                        echo '<tr>';
                        echo '<td>' . esc_html($reply->ID) . '</td>';
                        echo '<td>' . esc_html($reply->post_content) . '</td>';
                        echo '<td>';
                        echo '<a href="' . admin_url('admin.php?action=approve_flagged_reply&reply_id=' . $reply->ID) . '">' . __('Approve', 'textdomain') . '</a> | ';
                        echo '<a href="' . admin_url('admin.php?action=disapprove_flagged_reply&reply_id=' . $reply->ID) . '">' . __('Disapprove', 'textdomain') . '</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Add the disapproval handling function
function disapprove_flagged_reply($reply_id) {
    // Check if the reply ID is valid
    if (!$reply_id || !is_numeric($reply_id)) {
        error_log('Invalid reply ID provided: ' . var_export($reply_id, true));
        return;
    }

    // Remove the flagged status
    delete_post_meta($reply_id, 'flagged_for_moderation');

    // Optionally, change the post status or take other actions
    // For example: wp_update_post(['ID' => $reply_id, 'post_status' => 'draft']); 

    // Log disapproval action
    error_log('Disapproved reply ID ' . $reply_id);
}

// Handle the disapprove action
function handle_disapprove_flagged_reply() {
    if (isset($_GET['reply_id']) && is_numeric($_GET['reply_id'])) {
        $reply_id = intval($_GET['reply_id']);
        disapprove_flagged_reply($reply_id);

        // Redirect back to the flagged replies page
        wp_redirect(admin_url('admin.php?page=flagged-replies'));
        exit;
    } else {
        error_log('Invalid or missing reply_id parameter for disapproval.');
    }
}
add_action('admin_init', 'handle_disapprove_flagged_reply');



function approve_flagged_reply($reply_id) {
    // Check if the reply ID is valid
    if (!$reply_id || !is_numeric($reply_id)) {
        error_log('Invalid reply ID provided: ' . var_export($reply_id, true));
        return;
    }

    // Update the reply status to 'publish'
    $update_post = array(
        'ID' => $reply_id,
        'post_status' => 'publish', // Change this to 'approved' if that's how your setup works
    );

    // Update the post
    wp_update_post($update_post);

    // Remove the flagged status
    delete_post_meta($reply_id, 'flagged_for_moderation');

    // Log approval action
    error_log('Approved reply ID ' . $reply_id);
}

// Handle the approve action
function handle_approve_flagged_reply() {
    if (isset($_GET['reply_id']) && is_numeric($_GET['reply_id'])) {
        $reply_id = intval($_GET['reply_id']);
        approve_flagged_reply($reply_id);

        // Redirect back to the flagged replies page
        wp_redirect(admin_url('admin.php?page=flagged-replies'));
        exit;
    } else {
        error_log('Invalid or missing reply_id parameter for approval.');
    }
}
add_action('admin_init', 'handle_approve_flagged_reply');








