<?php

namespace SteinRein\Partner;

// Make sure this file runs only from within WordPress.
defined( 'ABSPATH' ) or die();

/**
 * Plugin Update Checker Class
 * uses Github https://github.com/SteinRein/partner-toolkit-wp to check for updates
 */
class Plugin_Updater
{
    protected $file;
    protected $plugin;
    protected $basename;
    protected $active;

    private $gh_organization = 'SteinRein';
    private $gh_repository = 'partner-toolkit-wp';
    private $gh_response;

    public function __construct($file)
    {
        $this->file = $file;
        $this->basename = steinrein_website_toolkit()->plugin_basename();

        add_action( 'plugins_loaded', [ $this, 'set_plugin_properties' ] );

        return $this;
    }

    function set_plugin_properties() {
		if( !function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

        $this->plugin = get_plugin_data( $this->file );
		$this->active = is_plugin_active( $this->basename );
    }

    function init() {
        add_filter( 'site_transient_update_plugins', [ $this, 'modify_transient' ], 10, 1 );
        add_filter( 'plugins_api', [ $this, 'plugin_popup' ], 10, 3);
        add_filter( 'upgrader_post_install', [ $this, 'after_install' ], 10, 3 );
    }

    private function get_repository_info() {
        if (is_null($this->gh_response)) {
            $request_uri = 'https://api.github.com/repos/' . $this->gh_organization . '/' . $this->gh_repository . '/releases/latest';

            $response = wp_remote_get( $request_uri );
            if (is_wp_error($response)) {
                return $response;
            }

            $this->gh_response = json_decode(wp_remote_retrieve_body($response));
        }
    }

	public function modify_transient( $transient ) {

        if ( empty( $transient->checked ) ) {
            return $transient;
        }

		$checked = $transient->checked;

		$this->get_repository_info(); // Get the repo info

		if (! $this->gh_response) {
			return $transient;
		}

		$out_of_date = version_compare( $this->gh_response->name, $checked[ $this->basename ], 'gt' ); // Check if we're out of date

		if( $out_of_date ) {

			$new_files = $this->gh_response->zipball_url; // Get the ZIP

			$slug = current( explode('/', $this->basename ) ); // Create valid slug

			$plugin = array( // setup our plugin info
				'url' => $this->plugin["PluginURI"],
				'slug' => $slug,
				'package' => $new_files,
				'new_version' => $this->gh_response->name
			);

			$transient->response[$this->basename] = (object) $plugin; // Return it in response
		}

		return $transient; // Return filtered transient
	}

	public function plugin_popup( $result, $action, $args ) {

		if( ! empty( $args->slug ) ) { // If there is a slug

			if( $args->slug == current( explode( '/' , $this->basename ) ) ) { // And it's our slug

				$this->get_repository_info(); // Get our repo info

				// Set it to an array
				$plugin = array(
					'name'				=> $this->plugin["Name"],
					'slug'				=> $this->basename,
					'requires'					=> '3.3',
					'tested'						=> '4.4.1',
					'rating'						=> '100.0',
					'num_ratings'				=> '10823',
					'downloaded'				=> '14249',
					'added'							=> '2016-01-05',
					'version'			=> $this->github_response['tag_name'],
					'author'			=> $this->plugin["AuthorName"],
					'author_profile'	=> $this->plugin["AuthorURI"],
					'last_updated'		=> $this->github_response['published_at'],
					'homepage'			=> $this->plugin["PluginURI"],
					'short_description' => $this->plugin["Description"],
					'sections'			=> array(
						'Description'	=> $this->plugin["Description"],
						'Updates'		=> $this->github_response['body'],
					),
					'download_link'		=> $this->github_response['zipball_url']
				);

				return (object) $plugin; // Return the data
			}

		}
		return $result; // Otherwise return default
	}

	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem; // Get global FS object

		$install_directory = plugin_dir_path( $this->file ); // Our plugin directory
		$wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir
		$result['destination'] = $install_directory; // Set the destination for the rest of the stack

		if ( $this->active ) { // If it was active
			activate_plugin( $this->basename ); // Reactivate
		}

		return $result;
	}

}
