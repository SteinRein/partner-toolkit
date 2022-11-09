<?php

/**
 * @link              https://www.steinrein.com/
 * @since             1.0.0
 * @package           Steinrein_Partner_Toolkit_WP
 * @author            Bastian Fießinger
 *
 * @wordpress-plugin
 * Plugin Name:       SteinRein Partner Toolkit
 * Plugin URI:        https://www.steinrein.com/
 * Description:       Display various aspects of your SteinRein Partnership in your WordPress site.
 * Version:           1.0.0
 * Author:            SteinRein
 * Author URI:        https://www.steinrein.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       steinrein-toolkit
 * Domain Path:       /languages
 */

namespace SteinRein\Partner;

// Make sure this file runs only from within WordPress.
defined( 'ABSPATH' ) or die();

final class WebsiteToolkit
{
    public static $instance = null;

    private $container = [];

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param  string $prop Property to check.
	 * @return bool
	 */
	public function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
	}

	/**
	 * Magic getter method.
	 *
	 * @param  string $prop Property to get.
	 * @return mixed Property value or NULL if it does not exists.
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}

		if ( isset( $this->{$prop} ) ) {
			return $this->{$prop};
		}

		return null;
	}

	/**
	 * Magic setter method.
	 *
	 * @param mixed $prop  Property to set.
	 * @param mixed $value Value to set.
	 */
	public function __set( $prop, $value ) {
		if ( property_exists( $this, $prop ) ) {
			$this->$prop = $value;
			return;
		}

		$this->container[ $prop ] = $value;
	}

	/**
	 * Magic call method.
	 *
	 * @param  string $name      Method to call.
	 * @param  array  $arguments Arguments to pass when calling.
	 * @return mixed Return value of the callback.
	 */
	public function __call( $name, $arguments ) {
		$hash = [
			'plugin_dir'   => STEINREIN_PARTNER_TOOLKIT_PLUGIN_DIR,
			'plugin_url'   => STEINREIN_PARTNER_TOOLKIT_PLUGIN_URL,
			'includes_dir' => STEINREIN_PARTNER_TOOLKIT_PLUGIN_DIR . 'inc/',
			'modules_dir'    => STEINREIN_PARTNER_TOOLKIT_PLUGIN_DIR . 'inc/modules/',
		];

		if ( isset( $hash[ $name ] ) ) {
			return $hash[ $name ];
		}

		return call_user_func_array( $name, $arguments );
	}

	/**
	 * Retrieve main WebsiteToolkit instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @see steinrein_website_toolkit()
	 * @return SteinRein\Partner\WebsiteToolkit
     */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof WebsiteToolkit ) ) {
			self::$instance = new WebsiteToolkit();
			self::$instance->setup();
		}

		return self::$instance;
	}

    function setup() {
        $this->define_constants();
        $this->load_files();

        (new Settings())->init();
        (new Modules\Certificate())->init();
        (new Modules\InquiryForm())->init();

        $updater = new Plugin_Updater( __FILE__ );
        $updater->init();
    }

    function define_constants() {
        if (!defined('STEINREIN_PARTNER_TOOLKIT_PLUGIN_VERSION')) {
            define('STEINREIN_PARTNER_TOOLKIT_PLUGIN_VERSION', '1.0.0');
        }

        if (!defined('STEINREIN_PARTNER_TOOLKIT_PLUGIN_DIR')) {
            define('STEINREIN_PARTNER_TOOLKIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }

        if (!defined('STEINREIN_PARTNER_TOOLKIT_PLUGIN_URL')) {
            define('STEINREIN_PARTNER_TOOLKIT_PLUGIN_URL', plugin_dir_url(__FILE__));
        }

        if (!defined('STEINREIN_PARTNER_TOOLKIT_PLUGIN_FILE')) {
            define('STEINREIN_PARTNER_TOOLKIT_PLUGIN_FILE', __FILE__);
        }

        if (!defined('STEINREIN_PARTNER_TOOLKIT_PLUGIN_BASENAME')) {
            define('STEINREIN_PARTNER_TOOLKIT_PLUGIN_BASENAME', plugin_basename(__FILE__));
        }

        if (!defined('STEINREIN_PARTNER_TOOLKIT_PLUGIN_DIR_BASENAME')) {
            define('STEINREIN_PARTNER_TOOLKIT_PLUGIN_DIR_BASENAME', plugin_basename(__DIR__));
        }
    }

    function load_files() {
        require_once plugin_dir_path( __FILE__ ) . 'inc/class-settings.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/modules/class-certificate.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/modules/class-inquiry-form.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/class-updater.php';
    }
}

/**
 * Returns the main instance of WebsiteToolkit to prevent the need to use globals.
 *
 * @return WebsiteToolkit
 */
function steinrein_website_toolkit() {
	return WebsiteToolkit::get();
}

// Start it.
steinrein_website_toolkit();
