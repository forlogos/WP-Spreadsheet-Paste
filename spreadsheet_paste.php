<?php
/*
  Plugin Name: Spreadsheet Paste Block
  Plugin URI: https://spreadsheet-paste-block.jasonjalbuena.com
  Description: A simple block to display data pasted from a spreadsheet
  Version: 1.1
  Author: forlogos
  Author URI: https://jasonjalbuena.com
  License: GPL V3
*/

//freemius stuff
if ( ! function_exists( 'spb_fs' ) ) {
    // Create a helper function for easy SDK access.
    function spb_fs() {
        global $spb_fs;

        if ( ! isset( $spb_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $spb_fs = fs_dynamic_init( array(
                'id'                  => '15553',
                'slug'                => 'spreadsheet-paste-block',
                'type'                => 'plugin',
                'public_key'          => 'pk_811aef7e7148e68e970db5e1f82dd',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'first-path'     => 'plugins.php',
                    'account'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $spb_fs;
    }

    // Init Freemius.
    spb_fs();
    // Signal that SDK was initiated.
    do_action( 'spb_fs_loaded' );
}

//freemius optin for new users
function spb_fs_custom_connect_message(
  $message,
  $user_first_name,
  $plugin_title,
  $user_login,
  $site_link,
  $freemius_link
) {
  return sprintf(
    __( 'Hi %1$s' ) . ',<br>' .
    __( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'spreadsheet-paste-block' ),
    $user_first_name,
    '<b>' . $plugin_title . '</b>',
    '<b>' . $user_login . '</b>',
    $site_link,
    $freemius_link
  );
}
spb_fs()->add_filter('connect_message', 'spb_fs_custom_connect_message', 10, 6);

//freemius optin for existing users
function spb_fs_custom_connect_message_on_update(
  $message,
  $user_first_name,
  $plugin_title,
  $user_login,
  $site_link,
  $freemius_link
) {
  return sprintf(
    __( 'Hi %1$s' ) . ',<br>' .
    __( 'Never miss an important update -- opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking with freemius.com.', 'spreadsheet-paste-block' ),
    $user_first_name,
    '<b>' . $plugin_title . '</b>',
    '<b>' . $user_login . '</b>',
    $site_link,
    $freemius_link
  );
}
spb_fs()->add_filter('connect_message_on_update', 'spb_fs_custom_connect_message_on_update', 10, 6);

// wp-admin assets
function spreadsheet_paste_block() {
   wp_enqueue_script(
      'spreadsheet-paste',
      plugins_url( 'spreadsheet_paste.js', __FILE__ ),
      array( 'wp-blocks', 'wp-element' )
   );
}
add_action( 'enqueue_block_editor_assets', 'spreadsheet_paste_block' );

//filter and format output for frontend
function filter_spreadsheet_paste_frontend( $block_content, $block ) {
   if ( $block['blockName'] === 'spreadsheet-paste/paste-block' ) {

      //make vars
      $headers = ( !empty( $block['attrs']['headers'] ) ? $block['attrs']['headers'] : 'headers-first-row' );
      $data = ( !empty( $block['attrs']['data'] ) ? $block['attrs']['data'] : '' );
      $table_output = '<table>';

      //separate data into rows
      $data_arr = explode( "\n", $data );

      //loop thru data - use Row Key and Row Data as key value vars
      foreach( $data_arr as $rk=>$rd ) {

         //verify that there is data for this row
         if( $rd != '' && $rd != array() ) {

            //turn row into arrays
            $cells = explode( "\t", $rd );

            $table_output .= '<tr>';

            //loop thru cells and make the table
            //use cell count and cell data as key value vars
            foreach( $cells as $cc=>$cd ) {

              //make the 1st row as headers if the appropriate option is chosen
              if( $rk == 0 && 
                (
                  $headers == 'headers-first-row' || 
                  $headers == 'headers-first-row-and-column'
                ) 
              ) {
                $table_output .= '<th>' . $cd . '</th>';

              //check if option is chosen to set 1st column as headers
              } elseif( $headers == 'headers-first-column' || $headers == 'headers-first-row-and-column') {
                //set only the 1st column as headers
                if( $cc == 0 ) {
                  $table_output .= '<th>' . $cd . '</th>';
                } else {
                  $table_output .= '<td>' . $cd . '</td>';
                }

              //regular cell output
              } else {
                $table_output .= '<td>' . $cd . '</td>';
              }
            }
            $table_output .= '</tr>';
         }
      }
      $table_output .= '</table>';

      //replace old data with $table_output
      $block_content = str_replace( $data, $table_output, $block_content);
   }

   return $block_content;
}
add_filter( 'render_block', 'filter_spreadsheet_paste_frontend', 10, 2 );