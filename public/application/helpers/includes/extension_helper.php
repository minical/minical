<?php
/**
 * The plugin API is located in this file, which allows for creating actions
 * and filters and hooking functions, and methods. The functions or methods will
 * then be run when the action or filter is called.
 *
 * The API callback examples reference functions, but can be methods of classes.
 * To hook methods, you'll need to pass an array one of two ways.
 *
 * Any of the syntaxes explained in the PHP documentation for the
 * {@link https://www.php.net/manual/en/language.pseudo-types.php#language.types.callback 'callback'}
 * type are valid.
 *
 * This file should have no external dependencies.
 *
 * @package miniCal
 * @subpackage Plugin
 * @since 1.5.0
 */

// Initialize the filter globals.
require __DIR__ . '/extension_hooks.php';

/** @var miniCal_Hook[] $miniCal_filter */
global $miniCal_filter;

/** @var int[] $miniCal_actions */
global $miniCal_actions;

/** @var string[] $miniCal_current_filter */
global $miniCal_current_filter;

if ( $miniCal_filter ) {
    $miniCal_filter = miniCal_Hook::build_preinitialized_hooks( $miniCal_filter );
} else {
    $miniCal_filter = array();
}

if ( ! isset( $miniCal_actions ) ) {
    $miniCal_actions = array();
}

if ( ! isset( $miniCal_current_filter ) ) {
    $miniCal_current_filter = array();
}

/**
 * Adds a callback function to a filter hook.
 *
 * miniCal offers filter hooks to allow plugins to modify
 * various types of internal data at runtime.
 *
 * A plugin can modify data by binding a callback to a filter hook. When the filter
 * is later applied, each bound callback is run in order of priority, and given
 * the opportunity to modify a value by returning a new value.
 *
 * The following example shows how a callback function is bound to a filter hook.
 *
 * Note that `$example` is passed to the callback, (maybe) modified, then returned:
 *
 *     function example_callback( $example ) {
 *         // Maybe modify $example in some way.
 *         return $example;
 *     }
 *     add_filter( 'example_filter', 'example_callback' );
 *
 * Bound callbacks can accept from none to the total number of arguments passed as parameters
 * in the corresponding apply_filters() call.
 *
 * In other words, if an apply_filters() call passes four total arguments, callbacks bound to
 * it can accept none (the same as 1) of the arguments or up to four. The important part is that
 * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
 * opted to accept. If no arguments were accepted by the callback that is considered to be the
 * same as accepting 1 argument. For example:
 *
 *     // Filter call.
 *     $value = apply_filters( 'hook', $value, $arg2, $arg3 );
 *
 *     // Accepting zero/one arguments.
 *     function example_callback() {
 *         ...
 *         return 'some value';
 *     }
 *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
 *
 *     // Accepting two arguments (three possible).
 *     function example_callback( $value, $arg2 ) {
 *         ...
 *         return $maybe_modified_value;
 *     }
 *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
 *
 * *Note:* The function will return true whether or not the callback is valid.
 * It is up to you to take care. This is done for optimization purposes, so
 * everything is as quick as possible.
 *
 * @since 0.71
 *
 * @global miniCal_Hook[] $miniCal_filter A multidimensional array of all hooks and the callbacks hooked to them.
 *
 * @param string   $hook_name     The name of the filter to add the callback to.
 * @param callable $callback      The callback to be run when the filter is applied.
 * @param int      $priority      Optional. Used to specify the order in which the functions
 *                                associated with a particular filter are executed.
 *                                Lower numbers correspond with earlier execution,
 *                                and functions with the same priority are executed
 *                                in the order in which they were added to the filter. Default 10.
 * @param int      $accepted_args Optional. The number of arguments the function accepts. Default 1.
 * @return true Always returns true.
 */
function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
    global $miniCal_filter;

    if ( ! isset( $miniCal_filter[ $hook_name ] ) ) {
        $miniCal_filter[ $hook_name ] = new miniCal_Hook();
    }

    $miniCal_filter[ $hook_name ]->add_filter( $hook_name, $callback, $priority, $accepted_args );

    return true;
}

/**
 * Calls the callback functions that have been added to a filter hook.
 *
 * This function invokes all functions attached to filter hook `$hook_name`.
 * It is possible to create new filter hooks by simply calling this function,
 * specifying the name of the new hook using the `$hook_name` parameter.
 *
 * The function also allows for multiple additional arguments to be passed to hooks.
 *
 * Example usage:
 *
 *     // The filter callback function.
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string.
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback()' function
 *      * that's hooked onto `example_filter` above.
 *      *
 *      * - 'example_filter' is the filter hook.
 *      * - 'filter me' is the value being filtered.
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @since 0.71
 *
 * @global miniCal_Hook[] $miniCal_filter         Stores all of the filters and actions.
 * @global string[]  $miniCal_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the filter hook.
 * @param mixed  $value     The value to filter.
 * @param mixed  ...$args   Additional parameters to pass to the callback functions.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters( $hook_name, $value ) {
    global $miniCal_filter, $miniCal_current_filter;

    $args = func_get_args();

    // Do 'all' actions first.
    if ( isset( $miniCal_filter['all'] ) ) {
        $miniCal_current_filter[] = $hook_name;
        _miniCal_call_all_hook( $args );
    }

    if ( ! isset( $miniCal_filter[ $hook_name ] ) ) {
        if ( isset( $miniCal_filter['all'] ) ) {
            array_pop( $miniCal_current_filter );
        }

        return $value;
    }

    if ( ! isset( $miniCal_filter['all'] ) ) {
        $miniCal_current_filter[] = $hook_name;
    }

    // Don't pass the tag name to miniCal_Hook.
    array_shift( $args );

    $filtered = $miniCal_filter[ $hook_name ]->apply_filters( $value, $args );

    array_pop( $miniCal_current_filter );

    return $filtered;
}

/**
 * Checks if any filter has been registered for a hook.
 *
 * When using the `$callback` argument, this function may return a non-boolean value
 * that evaluates to false (e.g. 0), so use the `===` operator for testing the return value.
 *
 * @since 2.5.0
 *
 * @global miniCal_Hook[] $miniCal_filter Stores all of the filters and actions.
 *
 * @param string         $hook_name The name of the filter hook.
 * @param callable|false $callback  Optional. The callback to check for. Default false.
 * @return bool|int If `$callback` is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority
 *                  of that hook is returned, or false if the function is not attached.
 */
function has_filter( $hook_name, $callback = false ) {
    global $miniCal_filter;

    if ( ! isset( $miniCal_filter[ $hook_name ] ) ) {
        return false;
    }

    return $miniCal_filter[ $hook_name ]->has_filter( $hook_name, $callback );
}

/**
 * Removes a callback function from a filter hook.
 *
 * This can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the `$callback` and `$priority` arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @since 1.2.0
 *
 * @global miniCal_Hook[] $miniCal_filter Stores all of the filters and actions.
 *
 * @param string   $hook_name The filter hook to which the function to be removed is hooked.
 * @param callable $callback  The name of the function which should be removed.
 * @param int      $priority  Optional. The exact priority used when adding the original
 *                            filter callback. Default 10.
 * @return bool Whether the function existed before it was removed.
 */
function remove_filter( $hook_name, $callback, $priority = 10 ) {
    global $miniCal_filter;

    $r = false;

    if ( isset( $miniCal_filter[ $hook_name ] ) ) {
        $r = $miniCal_filter[ $hook_name ]->remove_filter( $hook_name, $callback, $priority );

        if ( ! $miniCal_filter[ $hook_name ]->callbacks ) {
            unset( $miniCal_filter[ $hook_name ] );
        }
    }

    return $r;
}

/**
 * Removes all of the callback functions from a filter hook.
 *
 * @since 2.7.0
 *
 * @global miniCal_Hook[] $miniCal_filter Stores all of the filters and actions.
 *
 * @param string    $hook_name The filter to remove callbacks from.
 * @param int|false $priority  Optional. The priority number to remove them from.
 *                             Default false.
 * @return true Always returns true.
 */
function remove_all_filters( $hook_name, $priority = false ) {
    global $miniCal_filter;

    if ( isset( $miniCal_filter[ $hook_name ] ) ) {
        $miniCal_filter[ $hook_name ]->remove_all_filters( $priority );

        if ( ! $miniCal_filter[ $hook_name ]->has_filters() ) {
            unset( $miniCal_filter[ $hook_name ] );
        }
    }

    return true;
}

/**
 * Adds a callback function to an action hook.
 *
 * Actions are the hooks that the miniCal core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @since 1.2.0
 *
 * @param string   $hook_name       The name of the action to add the callback to.
 * @param callable $callback        The callback to be run when the action is called.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action. Default 10.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true Always returns true.
 */
function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
    return add_filter( $hook_name, $callback, $priority, $accepted_args );
}

/**
 * Calls the callback functions that have been added to an action hook.
 *
 * This function invokes all functions attached to action hook `$hook_name`.
 * It is possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$hook_name` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with `apply_filters()`.
 *
 * Example usage:
 *
 *     // The action callback function.
 *     function example_callback( $arg1, $arg2 ) {
 *         // (maybe) do something with the args.
 *     }
 *     add_action( 'example_action', 'example_callback', 10, 2 );
 *
 *     /*
 *      * Trigger the actions by calling the 'example_callback()' function
 *      * that's hooked onto `example_action` above.
 *      *
 *      * - 'example_action' is the action hook.
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = do_action( 'example_action', $arg1, $arg2 );
 *
 * @since 1.2.0
 * @since 5.3.0 Formalized the existing and already documented `...$arg` parameter
 *              by adding it to the function signature.
 *
 * @global miniCal_Hook[] $miniCal_filter         Stores all of the filters and actions.
 * @global int[]     $miniCal_actions        Stores the number of times each action was triggered.
 * @global string[]  $miniCal_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the action to be executed.
 * @param mixed  ...$arg    Optional. Additional arguments which are passed on to the
 *                          functions hooked to the action. Default empty.
 */
function do_action( $hook_name, ...$arg ) {
    global $miniCal_filter, $miniCal_actions, $miniCal_current_filter;

    if ( ! isset( $miniCal_actions[ $hook_name ] ) ) {
        $miniCal_actions[ $hook_name ] = 1;
    } else {
        ++$miniCal_actions[ $hook_name ];
    }

    // Do 'all' actions first.
    if ( isset( $miniCal_filter['all'] ) ) {
        $miniCal_current_filter[] = $hook_name;
        $all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
        _miniCal_call_all_hook( $all_args );
    }

    if ( ! isset( $miniCal_filter[ $hook_name ] ) ) {
        if ( isset( $miniCal_filter['all'] ) ) {
            array_pop( $miniCal_current_filter );
        }

        return;
    }

    if ( ! isset( $miniCal_filter['all'] ) ) {
        $miniCal_current_filter[] = $hook_name;
    }

    if ( empty( $arg ) ) {
        $arg[] = '';
    } elseif ( is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) ) {
        // Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
        $arg[0] = $arg[0][0];
    }

    $miniCal_filter[ $hook_name ]->do_action( $arg );

    array_pop( $miniCal_current_filter );
}

/**
 * Checks if any action has been registered for a hook.
 *
 * When using the `$callback` argument, this function may return a non-boolean value
 * that evaluates to false (e.g. 0), so use the `===` operator for testing the return value.
 *
 * @since 2.5.0
 *
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @param string         $hook_name The name of the action hook.
 * @param callable|false $callback  Optional. The callback to check for. Default false.
 * @return bool|int If `$callback` is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority
 *                  of that hook is returned, or false if the function is not attached.
 */
function has_action( $hook_name, $callback = false ) {
    return has_filter( $hook_name, $callback );
}

/**
 * Removes a callback function from an action hook.
 *
 * This can be used to remove default functions attached to a specific action
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the `$callback` and `$priority` arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @since 1.2.0
 *
 * @param string   $hook_name The action hook to which the function to be removed is hooked.
 * @param callable $callback  The name of the function which should be removed.
 * @param int      $priority  Optional. The exact priority used when adding the original
 *                            action callback. Default 10.
 * @return bool Whether the function is removed.
 */
function remove_action( $hook_name, $callback, $priority = 10 ) {
    return remove_filter( $hook_name, $callback, $priority );
}

/**
 * Removes all of the callback functions from an action hook.
 *
 * @since 2.7.0
 *
 * @param string    $hook_name The action to remove callbacks from.
 * @param int|false $priority  Optional. The priority number to remove them from.
 *                             Default false.
 * @return true Always returns true.
 */
function remove_all_actions( $hook_name, $priority = false ) {
    return remove_all_filters( $hook_name, $priority );
}

//
// Functions for handling plugins.
//

/**
 * Gets the basename of a plugin.
 *
 * This method extracts the name of a plugin from its filename.
 *
 * @since 1.5.0
 *
 * @global array $miniCal_plugin_paths
 *
 * @param string $file The filename of plugin.
 * @return string The name of a plugin.
 */
function plugin_basename( $file ) {
    global $miniCal_plugin_paths;

    // $miniCal_plugin_paths contains normalized paths.
    $file = miniCal_normalize_path( $file );

    arsort( $miniCal_plugin_paths );

    foreach ( $miniCal_plugin_paths as $dir => $realdir ) {
        if ( strpos( $file, $realdir ) === 0 ) {
            $file = $dir . substr( $file, strlen( $realdir ) );
        }
    }

    $plugin_dir    = miniCal_normalize_path( MINICAL_PLUGIN_DIR );
    $mu_plugin_dir = miniCal_normalize_path( MINICALMU_PLUGIN_DIR );

    // Get relative path from plugins directory.
    $file = preg_replace( '#^' . preg_quote( $plugin_dir, '#' ) . '/|^' . preg_quote( $mu_plugin_dir, '#' ) . '/#', '', $file );
    $file = trim( $file, '/' );
    return $file;
}

/**
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the filesystem path of the directory that contains the plugin.
 */
function plugin_dir_path( $file ) {
    return trailingslashit( dirname( $file ) );
}

/**
 * Get the URL directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the URL path of the directory that contains the plugin.
 */
function plugin_dir_url( $file ) {
    return trailingslashit( plugins_url( '', $file ) );
}

function _miniCal_filter_build_unique_id( $tag, $function, $priority ) {
    if ( is_string( $function ) ) {
        return $function;
    }

    if ( is_object( $function ) ) {
        // Closures are currently implemented as objects.
        $function = array( $function, '' );
    } else {
        $function = (array) $function;
    }

    if ( is_object( $function[0] ) ) {
        // Object class calling.
        return spl_object_hash( $function[0] ) . $function[1];
    } elseif ( is_string( $function[0] ) ) {
        // Static calling.
        return $function[0] . '::' . $function[1];
    }
}

function trailingslashit( $string ) {
    return untrailingslashit( $string ) . '/';
}

function untrailingslashit( $string ) {
    return rtrim( $string, '/\\' );
}

function _miniCal_call_all_hook( $args ) {
    global $miniCal_filter;

    $miniCal_filter['all']->do_all_hook( $args );
}

function miniCal_normalize_path( $path ) {
    $wrapper = '';

    if ( miniCal_is_stream( $path ) ) {
        list( $wrapper, $path ) = explode( '://', $path, 2 );

        $wrapper .= '://';
    }

    // Standardise all paths to use '/'.
    $path = str_replace( '\\', '/', $path );

    // Replace multiple slashes down to a singular, allowing for network shares having two slashes.
    $path = preg_replace( '|(?<=.)/+|', '/', $path );

    // Windows paths should uppercase the drive letter.
    if ( ':' === substr( $path, 1, 1 ) ) {
        $path = ucfirst( $path );
    }

    return $wrapper . $path;
}

function miniCal_is_stream( $path ) {
    $scheme_separator = strpos( $path, '://' );

    if ( false === $scheme_separator ) {
        // $path isn't a stream.
        return false;
    }

    $stream = substr( $path, 0, $scheme_separator );

    return in_array( $stream, stream_get_wrappers(), true );
}

function plugins_url( $path = '', $plugin = '' ) {

    $path          = miniCal_normalize_path( $path );
    $plugin        = miniCal_normalize_path( $plugin );
    $mu_plugin_dir = miniCal_normalize_path( miniCalMU_PLUGIN_DIR );

    if ( ! empty( $plugin ) && 0 === strpos( $plugin, $mu_plugin_dir ) ) {
        $url = miniCalMU_PLUGIN_URL;
    } else {
        $url = miniCal_PLUGIN_URL;
    }

    $url = set_url_scheme( $url );

    if ( ! empty( $plugin ) && is_string( $plugin ) ) {
        $folder = dirname( plugin_basename( $plugin ) );
        if ( '.' !== $folder ) {
            $url .= '/' . ltrim( $folder, '/' );
        }
    }

    if ( $path && is_string( $path ) ) {
        $url .= '/' . ltrim( $path, '/' );
    }

    /**
     * Filters the URL to the plugins directory.
     *
     * @since 2.8.0
     *
     * @param string $url    The complete URL to the plugins directory including scheme and path.
     * @param string $path   Path relative to the URL to the plugins directory. Blank string
     *                       if no path is specified.
     * @param string $plugin The plugin file path to be relative to. Blank string if no plugin
     *                       is specified.
     */
    return apply_filters( 'plugins_url', $url, $path, $plugin );
}

function set_url_scheme( $url, $scheme = null ) {
    $orig_scheme = $scheme;

    if ( ! $scheme ) {
        $scheme = is_ssl() ? 'https' : 'http';
    } elseif ( 'admin' === $scheme || 'login' === $scheme || 'login_post' === $scheme || 'rpc' === $scheme ) {
        $scheme = is_ssl() || force_ssl_admin() ? 'https' : 'http';
    } elseif ( 'http' !== $scheme && 'https' !== $scheme && 'relative' !== $scheme ) {
        $scheme = is_ssl() ? 'https' : 'http';
    }

    $url = trim( $url );
    if ( substr( $url, 0, 2 ) === '//' ) {
        $url = 'http:' . $url;
    }

    if ( 'relative' === $scheme ) {
        $url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
        if ( '' !== $url && '/' === $url[0] ) {
            $url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
        }
    } else {
        $url = preg_replace( '#^\w+://#', $scheme . '://', $url );
    }

    /**
     * Filters the resulting URL after setting the scheme.
     *
     * @since 3.4.0
     *
     * @param string      $url         The complete URL including scheme and path.
     * @param string      $scheme      Scheme applied to the URL. One of 'http', 'https', or 'relative'.
     * @param string|null $orig_scheme Scheme requested for the URL. One of 'http', 'https', 'login',
     *                                 'login_post', 'admin', 'relative', 'rest', 'rpc', or null.
     */
    return apply_filters( 'set_url_scheme', $url, $scheme, $orig_scheme );
}

function is_ssl() {
    if ( isset( $_SERVER['HTTPS'] ) ) {
        if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
            return true;
        }

        if ( '1' == $_SERVER['HTTPS'] ) {
            return true;
        }
    } elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}

function force_ssl_admin( $force = null ) {
    static $forced = false;

    if ( ! is_null( $force ) ) {
        $old_forced = $forced;
        $forced     = $force;
        return $old_forced;
    }

    return $forced;
}

function extension_install_log ($data) {
    if ($data['is_installed'] == 1) {
        $CI = &get_instance();
        $CI->load->model('Extension_log_model');
        
        $log_data = array(

            'extension_name' => $data['extension_name'],
            'vendor_id' => $data['vendor_id'],
            'company_id' => $data['company_id'],
            'user_id' =>$CI->user_id,
            'status' => 'Installed',
            'date_time' =>  gmdate('Y-m-d H:i:s')
        );

        $CI->Extension_log_model->create_extension_log($log_data);
        do_action('extension_installed', $log_data);
    }
}


function extension_uninstall_log ($data) {
    if ($data['is_installed'] == 0) {
        $CI = &get_instance();
        $CI->load->model('Extension_log_model');
        
        $log_data = array(

            'extension_name' => $data['extension_name'],
            'vendor_id' => $data['vendor_id'],
            'company_id' => $data['company_id'],
            'user_id' =>$CI->user_id,
            'status' => 'Uninstalled',
            'date_time' =>  gmdate('Y-m-d H:i:s')
        );
        $CI->Extension_log_model->create_extension_log($log_data);
        do_action('extension_uninstalled', $log_data);
    }
}

function extension_deactivated_log ($data) {
    if ($data['is_active'] == 0) {
        $CI = &get_instance();
        $CI->load->model('Extension_log_model');
        
        $log_data = array(

            'extension_name' => $data['extension_name'],
            'vendor_id' => $data['vendor_id'],
            'company_id' => $data['company_id'],
            'user_id' => $CI->user_id,
            'status' => 'Deactivated',
            'date_time' =>  gmdate('Y-m-d H:i:s')
        );

        $CI->Extension_log_model->create_extension_log($log_data);
        do_action('extension_deactivated', $log_data);
    }
}

function extension_activated_log ($data) {
    if ($data['is_active'] == 1) {
        $CI = &get_instance();
        $CI->load->model('Extension_log_model');
        $log_data = array(

            'extension_name' => $data['extension_name'],
            'vendor_id' => $data['vendor_id'],
            'company_id' => $data['company_id'],
            'user_id' =>$CI->user_id,
            'status' => 'Activated',
            'date_time' =>  gmdate('Y-m-d H:i:s')
        );

        $CI->Extension_log_model->create_extension_log($log_data);
        do_action('extension_activated', $log_data);
    }
}