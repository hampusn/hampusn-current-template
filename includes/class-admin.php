<?php
/**
 * Hampusn Current Template Admin
 *
 * @package WordPress
 * @subpackage Hampusn_Current_Template
 * @since Hampusn Current Template 0.0.0
 */

/**
 * Handles every part of the admin. Mainly the admin settings page.
 *
 * @author Hampus Nordin
 **/
class Hampusn_Current_Template_Admin {
  private $_option_group = 'hampusn_current_template';
  private $_option_name = 'hampusn_current_template';

  private $_tag = 'hampusn_current_template';
  private $_name = 'Current Template';
  private $_hook_suffix = false;
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
  private $_options = array();

  public function __construct( $option_name = '' ) {
    if ( ! empty( $option_name ) ) {
      $this->_option_name = $option_name;
    }
    if ( $options = get_option( $this->_option_name ) ) {
      $this->_options = $options;
    }

    add_action('admin_menu', array($this, 'menu_items'));
    add_action('admin_init', array($this, 'init_settings'));
  }


  /**
   * undocumented function
   *
   * @return void
   * @author 
   **/
  function menu_items() {
    $this->_hook_suffix = add_options_page( 'Hampusn Current Template', 'Current Template', 'manage_options', 'current-template', array( $this, 'settings_page' ) );
  }

  /**
   * undocumented function
   *
   * @return void
   * @author 
   **/
  function settings_page() {
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
   * undocumented function
   *
   * @return void
   * @author 
   **/
  public function init_settings() {
    $name = $this->_name;
    // Register setting
    register_setting(
      $this->_option_group,
      $this->_option_name,
      array( $this, 'settings_validate' )
    );

    add_settings_section(
      $this->_tag . '_settings_section',
      'Settings',
      function () use($name) {
        echo '<p>Configuration options for the ' . esc_html($name) . ' plugin.</p>';
      },
      $this->_hook_suffix
    );
    foreach ($this->_settings AS $field_name => $field_settings) {
      $field_settings['field_name'] = $field_name;
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
   * undocumented function
   *
   * @return void
   * @author 
   **/
  public function settings_field(Array $field_settings = array()) {
    $field_name = $field_settings[ 'field_name' ];
    $id = $this->_option_name . '_' . $field_name;

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
        if ( $atts['value'] ) {
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
      <input <?php echo implode(' ', $atts); ?> />
      <?php if ( array_key_exists( 'description', $field_settings ) ) : ?>
        <?php esc_html_e( $field_settings['description'] ); ?>
      <?php endif; ?>
    </label>
    <?php
  }


  /**
   * undocumented function
   *
   * @return void
   * @author 
   **/
  public function settings_validate($input) {
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
        // Just strip tags or something else
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
  } // End Class Hampusn_Current_Template_Admin
}
