<?php
/*
  Plugin Name: Spreadsheet Paste Block
  Author: forlogos
  Author URI: https://jasonjalbuena.com
  Version: 1.0
  License: GPL V3
  Description: A simple block to display data pasted from a spreadsheet
*/

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
      //address error notice for when the block is used without the user making a selection for headers
      $headers = ( !empty($block['attrs']['headers']) ? $block['attrs']['headers'] : 'headers-first-row' );
      $data = $block['attrs']['data'];
      $table_output = '<table>';

      //separate data into rows
      $data_arr = explode( "\n", $data );

      //loop thru data - use Row Key and Row Data as key value vars
      foreach( $data_arr as $rk=>$rd ) {

         //verify that there is data for this row
         if( $rd != '' && $rd != array() ) {
            $cells = explode( "\t", $rd );

            $table_output .= '<tr>';

            //loop thru cells and make the table
            //use cell count and cell data as key value vars
            foreach( $cells as $cc=>$cd ) {
               if( $rk == 0 && 
                  (
                     $headers == 'headers-first-row' || 
                     $headers == 'headers-first-row-and-column'
                  ) 
               ) {
                  $table_output .= '<th>' . $cd . '</th>';
               } elseif( $headers == 'headers-first-column' || $headers == 'headers-first-row-and-column') {
                  if( $cc == 0 ) {
                     $table_output .= '<th>' . $cd . '</th>';
                  } else {
                     $table_output .= '<td>' . $cd . '</td>';
                  }
               } else {
                  $table_output .= '<td>' . $cd . '</td>';
               }
            }
            $table_output .= '</tr>';
         }
      }
      $table_output .= '</table>';

      //replace old data with table_output
      $block_content = str_replace( $data, $table_output, $block_content);
   }

   return $block_content;
}
add_filter( 'render_block', 'filter_spreadsheet_paste_frontend', 10, 2 );