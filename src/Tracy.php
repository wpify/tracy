<?php

namespace Wpify\Tracy;

use Tracy\Debugger;
use Tracy\IBarPanel;
use Wpify\Tracy\Panels\WpCurrentScreenPanel;
use Wpify\Tracy\Panels\WpDbPanel;
use Wpify\Tracy\Panels\WpPanel;
use Wpify\Tracy\Panels\WpPostPanel;
use Wpify\Tracy\Panels\WpQueriedObjectPanel;
use Wpify\Tracy\Panels\WpQueryPanel;
use Wpify\Tracy\Panels\WpRewritePanel;
use Wpify\Tracy\Panels\WpRolesPanel;
use Wpify\Tracy\Panels\WpUserPanel;

class Tracy {
	public function __construct() {
		if ( defined( "DOING_AJAX" ) && DOING_AJAX ) {
			return; // for IE compatibility WordPress media upload
		}

		if ( ! defined( 'WPIFY_TRACY_ENABLE' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG === false ) {
				return;
			}

			if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY === false ) {
				return;
			}

			if ( defined( 'WPIFY_TRACY_DISABLE' ) && WPIFY_TRACY_DISABLE ) {
				return;
			}
		}

		$defaultPanelsClasses = [
			WpPanel::class,
			WpUserPanel::class,
			WpPostPanel::class,
			WpQueryPanel::class,
			WpQueriedObjectPanel::class,
			WpDbPanel::class,
			WpRolesPanel::class,
			WpRewritePanel::class,
			WpCurrentScreenPanel::class,
		];

		$panels   = apply_filters( 'wpify_tracy_panels', $defaultPanelsClasses );
		$settings = [
			"check-is-user-logged-in"  => defined( "WPIFY_TRACY_CHECK_IS_USER_LOGGED_IN" ) ? WPIFY_TRACY_CHECK_IS_USER_LOGGED_IN : "off",
			"only-for-user-id"         => defined( "WPIFY_TRACY_ONLY_FOR_USER_ID" ) ? WPIFY_TRACY_ONLY_FOR_USER_ID : null,
			"debugger-mode"            => defined( "WPIFY_TRACY_ENABLE_MODE" ) ? WPIFY_TRACY_ENABLE_MODE : "detect",
			"panels-classes"           => $panels,
			"panels-filtering-allowed" => defined( "WPIFY_TRACY_PANELS_FILTERING_ALLOWED" ) ? WPIFY_TRACY_PANELS_FILTERING_ALLOWED : "on",
		];

		if ( $settings["check-is-user-logged-in"] === "on" ) {
			$isUserLoggedIn = is_user_logged_in();
			if ( ! $isUserLoggedIn ) {
				return; // cancel for anonymous users
			}
			$onlyForUserId = $settings["only-for-user-id"];
			if ( $onlyForUserId > 0 && $onlyForUserId != get_current_user_id() ) {
				return; // cancel other users
			}
		}

		switch ( $settings["debugger-mode"] ) {
			case "development":
				$debugMode = Debugger::DEVELOPMENT;
				break;
			case "production":
				$debugMode = Debugger::PRODUCTION;
				break;
			default:
				$debugMode = Debugger::DETECT;
				break;
		}
		if ( defined( 'WPIFY_TRACY_ENABLE' ) ) {
			$debugMode = Debugger::DEVELOPMENT;
		}

		Debugger::enable( $debugMode );

		$panelsClasses = $settings["panels-classes"];
		if ( ! is_array( $panelsClasses ) ) {
			trigger_error( "\"wp-tracy-user-settings->panels-classes\" option must be type of array.", E_USER_WARNING );
			exit;
		}

		// panels (custom) filtering
		if ( $settings["panels-filtering-allowed"] === "on" ) {
			$panelsClasses = apply_filters( "wp_tracy_panels_filter", $panelsClasses );
			if ( ! is_array( $panelsClasses ) ) {
				trigger_error( "\"wp_tracy_panels_filter\" must return type of array.", E_USER_WARNING );
				exit;
			}
		}

		// panels registration
		foreach ( $panelsClasses as $className ) {
			$panel = new $className;
			if ( $panel instanceof IBarPanel ) {
				Debugger::getBar()->addPanel( new $className );
			}
		}
	}
}