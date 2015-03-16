<?php
/**
 * Hampusn Current Template Admin
 *
 * @package WordPress
 * @subpackage Hampusn_Current_Template
 * @since Hampusn Current Template 0.0.0
 */

// No Direct Access!
defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

/**
 * Handles every part of the admin. Mainly the admin settings page.
 *
 * @author Hampus Nordin
 **/
class Hampusn_Current_Template_Admin {
  // Used to group this plugin's settings in the admin.
  private $_option_group = 'hampusn_current_template';
  // This is the key which will be used to save the options to 
  // the wp_options table. It's defined in the main plugin file.
  private $_option_name = 'hampusn_current_template';
  // Used as a prefix for building all the fields html IDs.
  private $_tag = 'hampusn_current_template';
  // Name of the plugin which will be used in the admin menu 
  // and on the plugin settings page.
  private $_name = 'Current Template';
  // Used as a slug for various wp functions and also for the url.
  // It's set in hook_menu_items().
  private $_hook_suffix = false;
  // Array containing the settings for this plugin.
  // The key is what the setting will be stored under in the database 
  // and the value is the field's settings.
  private $_settings = array(
    'show_in_admin_menu' => array(
      'title' => 'Show in admin menu',
      'description' => 'Output the current template in the admin menu bar.',
      'type' => 'checkbox',
      'default' => false,
    ),
    'show_modal_in_body_top' => array(
      'title' => 'Show modal in body top',
      'description' => 'Show a modal at the top of the body.',
      'type' => 'checkbox',
      'default' => false,
    ),
  );
  // The currently stored settings/options for this plugin.
  // Set in the construct.
  private $_options = array();

  /**
   * Constructor. Initiates every part of the admin.
   *
   * @author Hampus Nordin
   **/
  public function __construct( $option_name = '' ) {
    // The key which all settings/options are 
    // stored under in wp_options table.
    if ( ! empty( $option_name ) ) {
      $this->_option_name = $option_name;
    }
    // Get stored settings/options.
    if ( $options = get_option( $this->_option_name ) ) {
      $this->_options = $options;
    }

    add_action( 'admin_menu', array( $this, 'hook_menu_items' ) );
    add_action( 'admin_init', array( $this, 'hook_init_settings' ) );
  }


  /**
   * WP hook callback which adds creates the plugin settings 
   * page and adds a menu item to the amdin menu.
   * 
   * Action hook: admin_menu
   *
   * @return void
   * @author Hampus Nordin
   * @see    http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
   * @see    settings_page()
   **/
  public function hook_menu_items() {
    $this->_hook_suffix = add_options_page( 'Hampusn Current Template', 'Current Template', 'manage_options', 'current-template', array( $this, 'settings_page' ) );
  }


  /**
   * WP hook callback which registers the plugin 
   * settings and adds the settings fields.
   * 
   * Action hook: admin_init
   *
   * @return void
   * @author Hampus Nordin
   * @see    http://codex.wordpress.org/Function_Reference/register_setting
   * @see    http://codex.wordpress.org/Function_Reference/add_settings_section
   **/
  public function hook_init_settings() {
    $name = $this->_name;
    // Register setting
    register_setting(
      $this->_option_group,
      $this->_option_name,
      array( $this, 'settings_validate' )
    );

    // Adds the plugin settings section to the plugin options page.
    add_settings_section(
      $this->_tag . '_settings_section',
      'Settings',
      function () use($name) {
        echo '<p>Configuration options for the ' . esc_html($name) . ' plugin.</p>';
      },
      $this->_hook_suffix
    );
    // Loops through all settings and adds them as fields.
    foreach ($this->_settings as $field_name => $field_settings) {
      $field_settings[ 'field_name' ] = $field_name;
      add_settings_field(
        $this->_option_name . '_' . $field_name . '_setting',
        $field_settings[ 'title' ],
        array( $this, 'settings_field' ),
        $this->_hook_suffix,
        $this->_tag . '_settings_section',
        $field_settings
      );
    }
  }


  /**
   * Outputs the settings sections to the plugin options page.
   *
   * @return void
   * @author Hampus Nordin
   * @see    hook_menu_items()
   **/
  public function settings_page() {
?>
<div class="wrap">
  <?php screen_icon(); ?>

  <h2>Hampusn Current Template</h2>

  <form method="post" action="options.php">
      <?php settings_fields( $this->_option_group ); ?>
      <?php do_settings_sections( $this->_hook_suffix ); ?>
      <?php submit_button(); ?>
  </form>
</div>
<?php
  }


  /**
   * Helper function which prepares and outputs a field in html.
   *
   * @return void
   * @author Hampus Nordin
   **/
  public function settings_field( Array $field_settings = array() ) {
    $field_name = $field_settings[ 'field_name' ];
    $id = $this->_option_name . '_' . $field_name;
    // html attributes.
    $atts = array(
      'id' => $id,
      'name' => $this->_option_name . '[' . $field_name . ']',
      'type' => ( isset( $field_settings[ 'type' ] ) ? $field_settings[ 'type' ] : 'text' ),
      'class' => '',
      'value' => ( isset( $field_settings[ 'default' ] ) ? $field_settings[ 'default' ] : null ),
    );

    // Set input value to stored value if a stored value exists.
    if ( isset( $this->_options[ $field_name ] ) ) {
      $atts[ 'value' ] = $this->_options[ $field_name ];
    }
    // Set placeholder text
    if ( isset( $field_settings[ 'placeholder' ] ) ) {
      $atts[ 'placeholder' ] = $field_settings[ 'placeholder' ];
    }

    // Input type specific settings.
    switch ( $field_settings[ 'type' ] ) {
      case 'checkbox':
        if ( $atts[ 'value' ] ) {
          $atts[ 'checked' ] = 'checked';
        }
        $atts[ 'value' ] = 1;
        break;
    }

    // Convert attribute array items to html attributes.
    array_walk( 
      $atts, 
      function( &$item, $key ) {
        $item = esc_attr( $key ) . '="' . esc_attr( $item ) . '"';
      }
    );
    ?>
    <label for="<?php echo $id; ?>">
      <input <?php echo implode( ' ', $atts ); ?> />
      <?php if ( array_key_exists( 'description', $field_settings ) ) : ?>
        <?php esc_html_e( $field_settings[ 'description' ] ); ?>
      <?php endif; ?>
    </label>
    <?php
  }


  /**
   * Validate callback which validates and fixes the 
   * settings/options before they are stored to wp_options table.
   *
   * @return array $input The sanitized settings/options
   * @author Hampus Nordin
   * @see    http://codex.wordpress.org/Function_Reference/register_setting ( $santize_callback )
   **/
  public function settings_validate( $input ) {
    $errors = array();
    foreach ( $this->_settings as $key => $field_settings) {
      if ( '' == $input[ $key ] ) {
        // How every type handles empty values.
        switch ( $field_settings[ 'type' ] ) {
          case 'checkbox':
            $input[ $key ] = 0;
            break;
          default:
            unset( $input[ $key ] );
            break;
        }
      } elseif ( isset( $field_settings[ 'validator' ] ) ) {
        // Validate
        switch ( $field_settings[ 'validator' ] ) {
          case 'integer':
            if ( is_numeric( $input[ $key ] ) ) {
              $input[ $key ] = intval( $input[ $key ] );
            } else {
              $errors[] = $key . ' must be an integer.';
            }
            break;
        }
      } else {
        // Crude filtering
        $input[ $key ] = strip_tags( $input[ $key ] );
      }
    }

    if ( count( $errors ) > 0 ) {
      add_settings_error(
        $this->_tag,
        $this->_tag,
        implode( '<br />', $errors ),
        'error'
      );
    }
    return $input;
  }
} // End Class Hampusn_Current_Template_Admin
