<?php

/**
 * Plugin Name:       WPD Custom DB Table
 * Plugin URI:        https://shujon.dev
 * Description:       Create Custom Database Table and Insert Data
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Shujon Mahmud
 * Author URI:        https://shujon.dev/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpd-db
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

class WPD_Custom_Database_Table {
    function __construct() {
        global $wpdb;
        $this->tablename = $wpdb->prefix . "employee_history"; // wp_employee_history
        $this->charset = $wpdb->get_charset_collate();

        register_activation_hook( __FILE__, array( $this, 'create_table' ) );
        add_action('admin_menu', array($this, 'add_menu'));
    }

    function create_table() {

        $sql = "CREATE TABLE $this->tablename (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  name tinytext NOT NULL,
  position tinytext NOT NULL,
  salary mediumint(9) NOT NULL,
  PRIMARY KEY  (id)
) $this->charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

   //  Create Menu Item to Admin Menu
    function add_menu() {
      add_menu_page(
         __('Employee Details', 'wpd-db'),
         __('Employee Details', 'wpd-db'),
         'manage_options',
         'wpd_employee',
         array($this, 'output'),
         'dashicons-businesswoman',
         70
      );
    }

   //  Output
   function output() {
      ?>
      <div class="wrap">
         <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
         
         <?php
         function wpd_form_notifications($msg, $class = 'notice-error') {
            echo '<div class="notice '.esc_attr($class).' is-dismissible">' . esc_html($msg) . '</div>';
         }
         ?>

         <form action="" method="post">
            <?php wp_nonce_field('employee_details', 'nonce'); ?>
            <table class="form-table">
               <tr>
                  <th scope="row"><label for="name"><?php esc_html_e( 'Name', 'wpd-db' ); ?></label></th>
                  <td><input class="regular-text" type="text" name="name" id="name"></td>
               </tr>

               <tr>
                  <th scope="row"><label for="position"><?php esc_html_e( 'Position', 'wpd-db' ); ?></label></th>
                  <td><input class="regular-text" type="text" name="position" id="position"></td>
               </tr>

               <tr>
                  <th scope="row"><label for="salary"><?php esc_html_e( 'Salary', 'wpd-db' ); ?></label></th>
                  <td><input class="regular-text" type="number" name="salary" id="salary"></td>
               </tr>
            </table>

            <?php submit_button('Add Record'); ?>
         </form>
      </div>
      <?php

      if( isset($_POST['submit']) ) {
         $nonce = sanitize_text_field($_POST['nonce']);

         if( wp_verify_nonce($nonce, 'employee_details') ) {
            $name = sanitize_text_field($_POST['name']);
            $position = sanitize_text_field($_POST['position']);
            $salary = absint($_POST['salary']);
   
            global $wpdb;
            if( $name && $position && $salary ) {
               $wpdb->insert($this->tablename, array(
                  'time' => current_time('mysql'),
                  'name' => $name,
                  'position' => $position,
                  'salary' => $salary,
               ));
            } else {
               wpd_form_notifications( "Fields can not be empty." );
            }
            
         } else {
            wpd_form_notifications( "You are not authorized" );
         }
         
      }

      ?>
      <div class="wrap">
         <?php
         global $wpdb;
         $data = $wpdb->get_results("SELECT * from {$this->tablename}", ARRAY_A);
         ?>
      </div>
      <?php

   }

}

new WPD_Custom_Database_Table();