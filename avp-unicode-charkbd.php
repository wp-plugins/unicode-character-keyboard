<?php
/*
  Plugin Name: Unicode Characters Keyboard
  Plugin URI: http://www.alphavideoproduction.com/wordpress/
  Description: Widget for the Edit Post or Edit Page pages for inserting HTML encodings of Unicode characters into the post.
  Version: 1.0
  Author: Terry O'Brien
  Author URI: http://www.alphavideoproduction.com/
  Original design by Scott Reilly (aka coffee2code)
 */

/*
  Copyright 2012, Terry O'Brien

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if ( function_exists( 'is_admin' ) && is_admin() && ( ! class_exists( 'avp_unicode_charkbd' ) ) ) :

    class avp_unicode_charkbd
    {

        /**
         *  ****************************************************************************************************************************
         *  Initialization section
         *  ****************************************************************************************************************************
         */
        var $modulePath = '/modules';
        var $title = 'Unicode Character Keyboard';
        var $versionNumber = 'Version 1.0';
        var $characterSetArrays = array( );
        var $characterSetTypes = array( );

        /**
         * Constructor
         */
        function __construct()
        {
            /*
             *  Register activate/deactivate/uninstall hooks
             */
            register_activation_hook( __FILE__, array( &$this, 'avp_unicode_charkbd_admin_activate' ) );
            register_deactivation_hook( __FILE__, array( &$this, 'avp_unicode_charkbd_adnin_deactivate' ) );
            register_uninstall_hook( __FILE__, 'avp_unicode_charkbd_admin_uninstall' );

            /*
             *  Initialize initialization and menu modules
             */
            add_action( 'admin_init', array( &$this, 'avp_unicode_charkbd_admin_init' ) );
            add_action( 'admin_menu', array( &$this, 'avp_unicode_charkbd_admin_menu' ) );

            /*
             *  Internationalization
             */
            load_plugin_textdomain( 'avp-unicode-charkbd', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
        }

        /**
         *  ****************************************************************************************************************************
         *  Activation section
         *  ****************************************************************************************************************************
         */
        /*
         *  Install (activate) module
         */
        function avp_unicode_charkbd_admin_activate()
        {
            /*
             *  Load XML files to get the character set names
             *  (Don't need the character types here so don't call the routine to get them)
             */
            $this->avp_unicode_charkbd_loadfiles();

            /*
             *  Include database initialization and version revision logic
             */
            if ( ! get_option( 'avp_unicode_charkbd', false ) )
            {
                add_option( 'avp_unicode_charkbd', $this->versionNumber );

                foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                {
                    add_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ), ( 'Common' === $characterSetName ) );
                }
            }
            /*
             *  Update the database option entries for the new version
             *  Change to get a list of the entries directly from the DB and compare against the code list
             */
            else
            if ( get_option( 'avp_unicode_charkbd' ) < $this->versionNumber )
            {
                /*
                 *  Add New entries if they don't currently exist
                 */
                foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                {
                    if ( ! get_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ), false ) )
                    {
                        add_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ), ( 'Common' === $characterSetName ) );
                    }
                }

                /*
                 *  Delete old entries that don't exist any longer
                  foreach ( array( 'Name1', 'Name2' ) as $characterSetName )
                  {
                  delete_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) );
                  }
                 */

                update_option( 'avp_unicode_charkbd', $this->versionNumber );
            }

            return( true );
        }

        /**
         *  ****************************************************************************************************************************
         *  Deactivation section
         * 
         *  Deactivate module
         *  (Not sure there is anything to deactivate because activation doesn't do anything other than installing the registry keys
         *  ****************************************************************************************************************************
         */
        function avp_unicode_charkbd_admin_deactivate()
        {
            return( true );
        }

        /**
         *  ****************************************************************************************************************************
         *  Uninstall section
         *  ****************************************************************************************************************************
         */
        function avp_unicode_charkbd_admin_uninstall()
        {
            return( avp_unicode_charkbd_delete_options() );
        }

        /**
         *  Uninstall options
         */
        function avp_unicode_charkbd_delete_options()
        {
            /*
             *  Load XML files to get the character set names
             */
            $this->avp_unicode_charkbd_loadfiles();

            /*
             *  Delete main parameter
             */
            if ( ! delete_option( 'avp_unicode_charkbd' ) )
            {
                return( false );
            }

            /*
             *  Delete individual character set flags
             */
            foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
            {
                if ( ! delete_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) ) )
                {
                    return( false );
                }
            }

            return( true );
        }

        /**
         *  ****************************************************************************************************************************
         *  Initialization section
         *  ****************************************************************************************************************************
         */
        /**
         * Hook actions and register adding the plugins admin meta box
         */
        function avp_unicode_charkbd_admin_init()
        {
            global $pagenow;

            /*
             *  Set up the javascript and style sheet files needed later
             */
            wp_register_script( 'avp-unicode-charkbd-close', plugins_url( 'js/avp-unicode-charkbd-close.js', __FILE__ ) );
            wp_register_script( 'avp-unicode-charkbd-file', plugins_url( 'js/avp-unicode-charkbd-file.js', __FILE__ ) );
            wp_register_script( 'avp-unicode-charkbd-menu', plugins_url( 'js/avp-unicode-charkbd-menu.js', __FILE__ ) );
            wp_register_script( 'avp-unicode-charkbd-post', plugins_url( 'js/avp-unicode-charkbd-post.js', __FILE__ ) );
            wp_register_script( 'avp-unicode-charkbd-postbox', plugins_url( 'js/avp-unicode-charkbd-postbox.js', __FILE__ ) );

            wp_register_style( 'avp-unicode-charkbd-abbr', plugins_url( 'css/avp-unicode-charkbd-abbr.css', __FILE__ ) );
            wp_register_style( 'avp-unicode-charkbd-evenodd', plugins_url( 'css/avp-unicode-charkbd-evenodd.css', __FILE__ ) );
            wp_register_style( 'avp-unicode-charkbd-icons', plugins_url( 'css/avp-unicode-charkbd-icons.css', __FILE__ ) );
            wp_register_style( 'avp-unicode-charkbd-highlight', plugins_url( 'css/avp-unicode-charkbd-highlight.css', __FILE__ ) );
            wp_register_style( 'avp-unicode-charkbd-metabox', plugins_url( 'css/avp-unicode-charkbd-metabox.css', __FILE__ ) );

            /*
             *  Set up jQuery validation module
             */
            wp_register_script( 'jquery-validate', plugins_url( 'js/jquery.validate.js', __FILE__ ), array( 'jquery' ), '1.8.1', true );
            wp_register_style( 'jquery-validate-style', plugins_url( 'css/jquery-validate.css', __FILE__ ) );

            /*
             *  Limit only to the specific admin page
             */
            if ( in_array( $pagenow, array( 'admin.php', ) ) )
            {
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'avp-unicode-charkbd-close' );
                wp_enqueue_style( 'avp-unicode-charkbd-icons' );

                if ( ( 'avp-unicode-charkbd-menu' == $_GET[ 'page' ] ) )
                {
                    wp_enqueue_script( 'avp-unicode-charkbd-menu' );

                    wp_enqueue_style( 'avp-unicode-charkbd-evenodd' );

                    add_filter( 'contextual_help', array( &$this, 'avp_unicode_charkbd_admin_menu_admin_help' ), 10, 2 );
                }
                else
                if ( ( 'avp-unicode-charkbd-menu-manage' == $_GET[ 'page' ] ) )
                {
                    wp_enqueue_script( 'avp-unicode-charkbd-menu' );

                    wp_enqueue_script( 'postbox' );
                    wp_enqueue_script( 'avp-unicode-charkbd-postbox' );

                    wp_enqueue_script( 'jquery-validate' );
                    wp_enqueue_script( 'avp-unicode-charkbd-file' );
                    wp_enqueue_style( 'jquery-validate-style' );

                    wp_enqueue_script( 'postbox' );
                    wp_enqueue_script( 'avp-unicode-charkbd-postbox' );

                    wp_enqueue_style( 'avp-unicode-charkbd-evenodd' );
                }
                else
                if ( ( 'avp-unicode-charkbd-menu-display' == $_GET[ 'page' ] ) )
                {
                    wp_enqueue_script( 'avp-unicode-charkbd-menu' );

                    wp_enqueue_script( 'postbox' );
                    wp_enqueue_script( 'avp-unicode-charkbd-postbox' );

                    wp_enqueue_style( 'avp-unicode-charkbd-abbr' );
                    wp_enqueue_style( 'avp-unicode-charkbd-metabox' );
                }
                else
                if ( ( 'avp-unicode-charkbd-menu-customize' == $_GET[ 'page' ] ) )
                {
                    wp_enqueue_script( 'avp-unicode-charkbd-menu' );

                    wp_enqueue_script( 'postbox' );
                    wp_enqueue_script( 'avp-unicode-charkbd-postbox' );

                    wp_enqueue_style( 'avp-unicode-charkbd-highlight' );
                }
            }
            /*
             *  Add the meta boxes to the Page and Post edit pages
             *  Only insert the JS and CSS Scripts and Help text when on the right page
             *  (Uncertain whether 'page.php' and 'page-now.php' were once supported and only now needed for backward compatability)
             */
            else
            if ( in_array( $pagenow, array( 'page.php', 'page-new.php', 'post.php', 'post-new.php' ) ) )
            {
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'avp-unicode-charkbd-post' );
                wp_enqueue_style( 'avp-unicode-charkbd-abbr' );
                wp_enqueue_style( 'avp-unicode-charkbd-metabox' );

                add_meta_box( 'avp_unicode_charkbd', $this->title, array( &$this, 'avp_unicode_charkbd_add_meta_box' ), 'page' );
                add_meta_box( 'avp_unicode_charkbd', $this->title, array( &$this, 'avp_unicode_charkbd_add_meta_box' ), 'post' );
            }
            /*
             *  Add details to the plugin description
             *  Linked routine checks for proper plugin listing, cannot do it at this level because plugin info is not available here,
             *  so every listing will call the added routine and the routine itself must determine the correct applicaiton
             */
            else
            if ( in_array( $pagenow, array( 'plugins.php', ) ) )
            {
                add_filter( 'plugin_action_links', array( &$this, 'avp_unicode_charkbd_admin_init_links' ), -10, 2 );
            }
        }

        /*
         *  Add links to plugin description section
         */
        function avp_unicode_charkbd_admin_init_links( $links, $file )
        {
            /*
             *  Use basename for parent sub-directory name and file name, must be same
             */
            if ( ( basename( __FILE__, '.php' ) . '/' . basename( __FILE__ ) ) == $file )
            {
                /*
                 *  Using array_unshift puts the Settings link at the start of the sequence
                 *  Uses basename for $menu_slug parameter, must be same as from add_menu_page Function call
                 *  Using array_push puts the Home link at the end of the sequence
                 */
                array_unshift( $links, sprintf( '<a href="admin.php?page=avp-unicode-charkbd-menu">%s</a>', __( 'Settings', 'avp-unicode-charkbd' ) ) );
                array_push( $links, sprintf( '<a href="http://www.alphavideoproduction.com/wordpress/">%s</a>', __( 'Home', 'avp-unicode-charkbd' ) ) );
            }

            return $links;
        }

        /**
         * Hook actions and register adding the plugins admin pages
         */
        function avp_unicode_charkbd_admin_menu()
        {
            /*
             *  Build own menu
             */
            add_menu_page( $this->title, __( 'Unicode Keyboard', 'avp-unicode-charkbd' ), 'manage_options', 'avp-unicode-charkbd-menu', array( &$this, 'avp_unicode_charkbd_admin_menu_admin' ), ( plugins_url( '/images/keyboard-16.png', __FILE__ ) ) );

            /*
             *  Add submenu pages with same parent slug to ensure no duplicates
             */
            add_submenu_page( 'avp-unicode-charkbd-menu', ( $this->title . ' Settings' ), '<img src="' . plugins_url( '/images/form-input-16.png', __FILE__ ) . '" width="16" height="16" /> ' . __( 'Settings', 'avp-unicode-charkbd' ), 'manage_options', 'avp-unicode-charkbd-menu', array( &$this, 'avp_unicode_charkbd_admin_menu_admin' ) );
            add_submenu_page( 'avp-unicode-charkbd-menu', ( $this->title . ' Manage' ), '<img src="' . plugins_url( '/images/file-xml-16.png', __FILE__ ) . '" width="16" height="16" /> ' . __( 'Manage Files', 'avp-unicode-charkbd' ), 'manage_options', 'avp-unicode-charkbd-menu-manage', array( &$this, 'avp_unicode_charkbd_admin_menu_manage' ) );
            add_submenu_page( 'avp-unicode-charkbd-menu', ( $this->title . ' Display' ), '<img src="' . plugins_url( '/images/keyboard-16.png', __FILE__ ) . '" width="16" height="16" /> ' . __( 'Display Keyboards', 'avp-unicode-charkbd' ), 'manage_options', 'avp-unicode-charkbd-menu-display', array( &$this, 'avp_unicode_charkbd_admin_menu_display' ) );
            add_submenu_page( 'avp-unicode-charkbd-menu', ( $this->title . ' Customize' ), '<img src="' . plugins_url( '/images/customize-16.png', __FILE__ ) . '" width="16" height="16" /> ' . __( 'Customize', 'avp-unicode-charkbd' ), 'manage_options', 'avp-unicode-charkbd-menu-customize', array( &$this, 'avp_unicode_charkbd_admin_menu_customize' ) );
        }

        /**
         *  ****************************************************************************************************************************
         *  Menu section, admin subsection
         *  ****************************************************************************************************************************
         */
        /*
         *  Add the primary menu definition
         */
        function avp_unicode_charkbd_admin_menu_admin()
        {
            /*
             *  Load XML files to get the character set names and types
             */
            $this->avp_unicode_charkbd_loadfiles();
            $this->avp_unicode_charkbd_loadtypes();

            /*
             *  Wrap the entire page so it fits in the admin area
             */
            echo ( '<div class="wrap" id="dashc-page" >' );

            /*
             *  Start page with title 
             */
            $this->avp_unicode_charkbd_admin_menu_header( __( 'Settings', 'avp-unicode-charkbd' ) );
            $this->avp_unicode_charkbd_admin_common_check();

            /*
             *  Save changes 
             */
            if ( ( isset( $_REQUEST[ 'submit' ] ) ) && ( 'Save' == $_REQUEST[ 'submit' ] ) )
            {
                if ( ( ! isset( $_REQUEST[ '_wpnonce' ] ) ) || ( ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'admin-manage' ) ) )
                {
                    wp_die( __( 'Security check', 'avp-unicode-charkbd' ) );
                }
                /*
                 *  If no boxes are checked, automatically select 'Common' and de-select all others
                 */
                else
                if ( ! isset( $_POST[ 'checkbox' ] ) )
                {
                    foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                    {
                        update_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ), ( 'Common' === $characterSetName ) );
                    }

                    $this->avp_unicode_charkbd_admin_menu_message( 'warning', __( 'Options saved.', 'avp-unicode-charkbd' ), __( 'Warning: all character sets deactivated, must have one character set activated; Common set activated by default.', 'avp-unicode-charkbd' ) );
                }
                else
                {
                    $countArray = array( true => 0, false => 0 );
                    $fileListArray = array( );

                    foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                    {
                        if ( ( ! get_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) ) ) &&
                                ( in_array( $characterSetName, $_POST[ 'checkbox' ] ) ) )
                        {
                            update_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ), true );
                            $fileListArray[ $characterSetName ] = true;
                            ++ $countArray[ true ];
                        }
                        else
                        if ( ( get_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) ) ) &&
                                ( ! in_array( $characterSetName, $_POST[ 'checkbox' ] ) ) )
                        {
                            update_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ), false );
                            $fileListArray[ $characterSetName ] = false;
                            ++ $countArray[ false ];
                        }
                    }

                    if ( 0 == ( $countArray[ true ] + $countArray[ false ] ) )
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'warning', __( 'Options saved.', 'avp-unicode-charkbd' ), __( 'Warning: no character sets activated or deactivated.', 'avp-unicode-charkbd' ) );
                    }
                    else
                    {
                        /*
                         *  No need to sort the file list as it is already sorted in the character set array
                         */
                        $fileList = _n( 'Character set', 'Character sets', ( $countArray[ true ] + $countArray[ false ] ), 'avp-unicode-charkbd' );
                        $fileList .= ' ';

                        foreach ( array( true, false ) as $code )
                        {
                            $count = 0;
                            foreach ( $fileListArray as $key => $status )
                            {
                                if ( $code == $status )
                                {
                                    $fileList .= $key;
                                    if ( ( $countArray[ $code ] - 2 ) > $count )
                                    {
                                        $fileList .= ', ';
                                    }
                                    else
                                    if ( ( $countArray[ $code ] - 1 ) > $count )
                                    {
                                        $fileList .= sprintf( ' %s ', __( 'and', 'avp-unicode-charkbd' ) );
                                    }
                                    else
                                    {
                                        $fileList .= ' ';
                                    }
                                    ++ $count;
                                }
                            }

                            if ( ( true == $code ) && ( $countArray[ $code ] > 0 ) )
                            {
                                $fileList .= __( 'activated', 'avp-unicode-charkbd' );

                                if ( $countArray[ false ] > 0 )
                                {
                                    $fileList .= ', ';
                                }
                                else
                                {
                                    $fileList .= '.';
                                }
                            }
                            else
                            if ( ( false == $code ) && ( $countArray[ $code ] > 0 ) )
                            {
                                $fileList .= __( 'deactivated.', 'avp-unicode-charkbd' );
                            }
                        }

                        $this->avp_unicode_charkbd_admin_menu_message( 'success', __( 'Options saved.', 'avp-unicode-charkbd' ), $fileList );
                    }
                }
            }
            /*
             *  Reset changes to default state
             *  (Meaning, all sets off except for Common which is on)
             */
            else
            if ( ( isset( $_REQUEST[ 'submit' ] ) ) && ( 'Initialize' == $_REQUEST[ 'submit' ] ) )
            {
                if ( ( ! isset( $_REQUEST[ '_wpnonce' ] ) ) || ( ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'admin-manage' ) ) )
                {
                    wp_die( __( 'Security check', 'avp-unicode-charkbd' ) );
                }
                else
                {
                    foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                    {
                        update_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ), ( 'Common' === $characterSetName ) );
                    }

                    $this->avp_unicode_charkbd_admin_menu_message( 'success', __( 'Options initialized.', 'avp-unicode-charkbd' ), __( 'Common set activated, all other sets deactivated.', 'avp-unicode-charkbd' ) );
                }
            }

            /*
             *  Continue with tabs
             */
            $this->avp_unicode_charkbd_admin_menu_tablist( $this->characterSetTypes );

            /*
             *  Start entry code form
             *  Add hidden values
             */
            echo ( '<form method="post" >' );
            echo wp_nonce_field( 'admin-manage' );

            /*
             *  Build option tables based on types
             */
            for ( $typeindex = 0, $linecount = 0; ( $typeindex < count( $this->characterSetTypes ) ); $typeindex += 1, $linecount = 0 )
            {
                /*
                 *  Build header section
                 */
                ?>

                <table class="form-table nav-tab-contents" <?php printf( 'id="avp_unicode_charkbd_admin_menu_panel_%u"', $typeindex ); ?> style="display:<?php echo ( ( 0 == $typeindex ) ? '' : 'none;' ) ?>" >

                    <col width=" 5%" ><col width="15%" ><col width="80%" >
                    <thead>
                        <tr>
                            <th colspan="3"><strong><?php _e( 'Toggle the overall display of any given set of special characters by checking or unchecking the approproate checkbox below.', 'avp-unicode-charkbd' ); ?></strong></th>
                        </tr>
                        <tr>
                            <th>&#10003;</th><th><strong><?php _e( 'Font', 'avp-unicode-charkbd' ); ?></strong></th><th><strong><?php _e( 'Font Description', 'avp-unicode-charkbd' ); ?></strong></th>
                        </tr>
                    </thead>

                    <?php
                    /*
                     *  Build body section
                     */
                    echo ( '<tbody>' );

                    foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                    {
                        if ( $this->characterSetArrays[ $characterSetName ][ 'type' ] == $this->characterSetTypes[ $typeindex ] )
                        {
                            printf( '<tr valign="top" class="%s" >', ( ( 0 == ( $linecount % 2 ) ) ? 'even' : 'odd' ) );
                            $linecount += 1;

                            printf( '<td><input type="checkbox" name="checkbox[]" value="%s" %s/></td>', $characterSetName, ( get_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) ) ? 'checked="checked" ' : '' ) );

                            echo ( '<td>' );
                            if ( '' != $this->characterSetArrays[ $characterSetName ]->characterSetResource )
                                printf( '<a href="%s" title="%s" >', $this->characterSetArrays[ $characterSetName ]->characterSetResource, $this->characterSetArrays[ $characterSetName ]->characterSetResource );
                            printf( '<strong>%s</strong>', $characterSetName );
                            if ( '' != $this->characterSetArrays[ $characterSetName ]->characterSetResource )
                                printf( '</a>' );
                            echo ( '</td>' );

                            echo ( '<td>' );
                            printf( '<strong>%s</strong>', $this->characterSetArrays[ $characterSetName ][ 'description' ] );

                            echo ( '<br/>' );
                            _e( 'This character set contains the following subsets:', 'avp-unicode-charkbd' );
                            echo ( ' ' );

                            for ( $count = 0; $count < count( $this->characterSetArrays[ $characterSetName ]->characterSubset );  ++ $count )
                            {
                                echo ( $this->characterSetArrays[ $characterSetName ]->characterSubset[ $count ][ 'name' ] );
                                echo ( $this->avp_unicode_charkbd_list_grammar( $count, count( $this->characterSetArrays[ $characterSetName ]->characterSubset ) ) );
                            }

                            if ( 0 < count( $this->characterSetArrays[ $characterSetName ]->characterSetRange ) )
                            {
                                echo ( '<br />' );
                                _e( 'Character set range:', 'avp-unicode-charkbd' );
                                echo ( ' ' );

                                for ( $count = 0; $count < count( $this->characterSetArrays[ $characterSetName ]->characterSetRange );  ++ $count )
                                {
                                    echo ( $this->characterSetArrays[ $characterSetName ]->characterSetRange );
                                    echo ( $this->avp_unicode_charkbd_list_grammar( $count, count( $this->characterSetArrays[ $characterSetName ]->characterSetRange ) ) );
                                }
                            }

                            if ( 0 < count( $this->characterSetArrays[ $characterSetName ]->characterSetFont ) )
                            {
                                echo ( '<br />' );
                                _e( 'Recommended supplemental font(s) for this category:', 'avp-unicode-charkbd' );
                                echo ( ' ' );

                                for ( $count = 0; $count < count( $this->characterSetArrays[ $characterSetName ]->characterSetFont );  ++ $count )
                                {
                                    printf( '<a href="%s">%s</a>%s', $this->characterSetArrays[ $characterSetName ]->characterSetFont[ $count ][ 'url' ], $this->characterSetArrays[ $characterSetName ]->characterSetFont[ $count ][ 'name' ], $this->avp_unicode_charkbd_list_grammar( $count, count( $this->characterSetArrays[ $characterSetName ]->characterSetFont ) ) );
                                }
                            }

                            echo ( '</td>' );
                            echo ( '</tr>' );
                        }
                    }

                    /*
                     *  End table
                     */
                    echo ( '</tbody>' );
                    echo ( '</table>' );
                }

                echo ( '<hr />' );
                ?>

                <!--  Add submit and reset buttons -->
                <table>
                    <tr>
                        <td align="center"><input type="submit" name="submit" class="button-primary" value="Save" /></td>
                        <td><strong><?php _e( 'To save the character keyboard selections, click the "Save" button.', 'avp-unicode-charkbd' ); ?></strong></td>
                    </tr>
                    <tr>
                        <td align="center"><input type="reset" name="reset" value="Reset" class="button-secondary" /></td>
                        <td><strong><?php _e( 'To reset all pending changes and return the selections to the current state, click the "Reset" button.', 'avp-unicode-charkbd' ); ?></strong></td>
                    </tr>
                    <tr>
                        <td align="center"><input type="submit" name="submit" class="button-secondary" value="Initialize" onClick="return confirm( 'Are you sure you want to initialize the selections?' );" /></td>
                        <td><strong><?php _e( 'To reset the selections to the initial state, click the "Initialize" button.', 'avp-unicode-charkbd' ); ?></strong></td>
                    </tr>
                </table>

                <?php
                /*
                 *  Finish submit form
                 */
                echo ( '</form>' );

                /*
                 *  Finish page
                 */
                echo ( '</div>' );
            }

            /**
             *   Adds text to help Box
             */
            function avp_unicode_charkbd_admin_menu_admin_help( $help )
            {
                $help .= '<hr />';
                $help .= '<p>';
                $help .= sprintf( '<strong>%a</strong> - %s.', $this->title, $this->versionNumber );
                $help .= '<br />';
                $help .= 'Select character sets by checking the associated checkbox and clicking the "Save" button. ';
                $help .= 'Changes on individual tabs are all submitted at the same time so it is unnecessary to submit the changes for each separate tab.<br />';
                $help .= 'Initializing selections turns the "Common" character set <em>on</em> and all of the other character sets <em>off</em>.';
                $help .= '</p>';

                return $help;
            }

            /**
             *  ****************************************************************************************************************************
             *  Menu section, file management subsection
             *  ****************************************************************************************************************************
             */
            /**
             *  Add the menu customization definition
             */
            function avp_unicode_charkbd_admin_menu_manage()
            {
                $tabNames = array( __( 'Uploading File', 'avp-unicode-charkbd' ), __( 'Deleting File', 'avp-unicode-charkbd' ), __( 'Error Reporting', 'avp-unicode-charkbd' ), );

                /*
                 *  Load XML files to get the character set names and types
                 */
                $this->avp_unicode_charkbd_loadfiles();

                /*
                 *  Wrap the entire page so it fits in the admin area
                 */
                echo ( '<div class="wrap" id="dashc-page" >' );

                /*
                 *  Start page with title
                 */
                $this->avp_unicode_charkbd_admin_menu_header( __( 'Manage Files', 'avp-unicode-charkbd' ) );
                $this->avp_unicode_charkbd_admin_common_check();

                /*
                 *  Upload file
                 */
                if ( ( isset( $_REQUEST[ 'action' ] ) ) && ( 'upload' == $_REQUEST[ 'action' ] ) )
                {
                    $tabNumber = 0;

                    if ( ( ! isset( $_REQUEST[ '_wpnonce' ] ) ) || ( ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'manage-upload' ) ) )
                    {
                        wp_die( __( 'Security check', 'avp-unicode-charkbd' ) );
                    }
                    /*
                     *  Validate file
                     */
                    else
                    if ( 0 < $_FILES[ 'uploadFile' ][ 'error' ] )
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'error', sprintf( __( 'Error: file %s upload error %s.', 'avp-unicode-charkbd' ), $_FILES[ 'uploadFile' ][ 'name' ], $_FILES[ 'uploadFile' ][ 'error' ] ), __( 'Specified file not uploaded.', 'avp-unicode-charkbd' ) );
                    }
                    else
                    if ( $_FILES[ 'uploadFile' ][ 'type' ] != 'text/xml' )
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'error', sprintf( __( 'Error: file %s upload error.', 'avp-unicode-charkbd' ), $_FILES[ 'uploadFile' ][ 'name' ] ), __( 'File type invalid, must be "text/xml".', 'avp-unicode-charkbd' ) );
                    }
                    else
                    if ( ! $this->avp_unicode_charkbd_parse_xml( $_FILES[ 'uploadFile' ][ 'tmp_name' ], false, isset( $_POST[ 'printFile' ] ) ) )
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'error', sprintf( __( 'Error: file %s upload error.', 'avp-unicode-charkbd' ), $_FILES[ 'uploadFile' ][ 'name' ] ), __( 'File contents invalid, check error log for details.', 'avp-unicode-charkbd' ) );
                    }
                    /*
                     *  Copy file to /Custom module directory and report success or failire
                     *  Don't need to add to registry until it is time to turn it on, 
                     *  as it will get added at the first query but still come up as 'false' until set
                     */
                    else
                    if ( ! copy( $_FILES[ 'uploadFile' ][ 'tmp_name' ], plugin_dir_path( __FILE__ ) . $this->modulePath . '/Custom/' . $_FILES[ 'uploadFile' ][ 'name' ] ) )
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'error', sprintf( __( 'Error: file %s upload error.', 'avp-unicode-charkbd' ), $_FILES[ 'uploadFile' ][ 'name' ] ), __( 'File not copied into module directory.', 'avp-unicode-charkbd' ) );
                    }
                    else
                    {
                        unlink( $_FILES[ 'uploadFile' ][ 'tmp_name' ] );

                        $this->avp_unicode_charkbd_admin_menu_message( 'success', sprintf( __( 'Success: file %s uploaded.', 'avp-unicode-charkbd' ), $_FILES[ 'uploadFile' ][ 'name' ] ), sprintf( __( 'You must now go to the <a href="admin.php?page=avp-unicode-charkbd-menu">%s</a> page to activate the character set.', 'avp-unicode-charkbd' ), __( 'Settings', 'avp-unicode-charkbd' ) ) );
                    }
                }
                /*
                 *  Delete file from directory and delete register key from database
                 *      If action is completed, display confirmation message
                 *      If action is invalid, display error message
                 *  Do not allow deleting "Common.xml" (not allowed in form)
                 */
                else
                if ( ( isset( $_REQUEST[ 'action' ] ) ) && ( 'delete' == $_REQUEST[ 'action' ] ) )
                {
                    $tabNumber = 1;

                    if ( ( ! isset( $_REQUEST[ '_wpnonce' ] ) ) || ( ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'manage-delete' ) ) )
                    {
                        wp_die( __( 'Security check', 'avp-unicode-charkbd' ) );
                    }
                    else
                    if ( empty( $_POST[ 'deletebox' ] ) )
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'error', __( 'Error: no character set selected for delection.', 'avp-unicode-charkbd' ) );
                    }
                    else
                    {
                        $fileList = _n( 'Character set', 'Character sets', count( $_POST[ 'deletebox' ] ), 'avp-unicode-charkbd' );

                        for ( $count = 0; ( $count < count( $_POST[ 'deletebox' ] ) ); $count += 1 )
                        {
                            $characterSetName = $_POST[ 'deletebox' ][ $count ];

                            /*
                             *  Delete the register record
                             *  Delete (unlink) the actual file (if not on the test platform)
                             *  Delete the array record
                             */
                            delete_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) );
                            if ( 'localhost' != $_SERVER[ 'SERVER_NAME' ] )
                                unlink( $this->characterSetArrays[ $characterSetName ]->fullFileName );
                            unset( $this->characterSetArrays[ $characterSetName ] );

                            /*
                             *  Accumulate Report
                             */
                            $fileList .= ' ';
                            $fileList .= $_POST[ 'deletebox' ][ $count ];
                            if ( ( count( $_POST[ 'deletebox' ] ) - 2 ) > $count )
                            {
                                $fileList .= ',';
                            }
                            else
                            if ( ( count( $_POST[ 'deletebox' ] ) - 1 ) > $count )
                            {
                                $fileList .= sprintf( ' %s', __( 'and', 'avp-unicode-charkbd' ) );
                            }
                        }

                        $fileList .= __( 'deleted.', 'avp-unicode-charkbd' );
                        $this->avp_unicode_charkbd_admin_menu_message( 'success', $fileList );
                    }
                }
                /*
                 *  Display error log file with option to clear it
                 */
                else
                if ( ( isset( $_REQUEST[ 'action' ] ) ) && ( 'error' == $_REQUEST[ 'action' ] ) )
                {
                    $tabNumber = 2;

                    if ( ( ! isset( $_REQUEST[ '_wpnonce' ] ) ) || ( ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'manage-error' ) ) )
                    {
                        wp_die( __( 'Security check', 'avp-unicode-charkbd' ) );
                    }
                    /*
                     *  File must exist or else there will be no "delete" button to process
                     */
                    else
                    if ( ( file_exists( plugin_dir_path( __FILE__ ) . '/error.log' ) ) &&
                            ( ! unlink( plugin_dir_path( __FILE__ ) . '/error.log' ) ) )
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'error', __( 'File not deleted: unknown error on deletion.', 'avp-unicode-charkbd' ) );
                    }
                    else
                    {
                        $this->avp_unicode_charkbd_admin_menu_message( 'success', __( 'File deleted.', 'avp-unicode-charkbd' ) );
                    }
                }
                /*
                 *  Default the tab number to the first tab
                 */
                else
                {
                    $tabNumber = 0;
                }

                /*
                 *  Continue page with title and tabs
                 */
                $this->avp_unicode_charkbd_admin_menu_tablist( $tabNames, $tabNumber );

                /*
                 *  Build pages under the tabs
                 */
                ?>
                <!-- Upload section -->
                <div id="avp_unicode_charkbd_admin_menu_panel_0" style="display: <?php echo ( ( 0 == $tabNumber ) ? '' : 'none;' ) ?>" class="nav-tab-contents" >
                    <h2><?php _e( 'Uploading File', 'avp-unicode-charkbd' ); ?></h2>
                    <p><?php printf( __( 'Select a file on the local system to upload into the "%s/Custom" subdirectory. Files uploaded will then need to be activated on the Settings page.', 'avp-unicode-charkbd' ), $this->modulePath ); ?></p>
                    <p><?php _e( 'Note: the character set "Common" cannot be overwritten or overridden.', 'avp-unicode-charkbd' ); ?></p>
                    <form method="post" enctype="multipart/form-data" id="uploadFile" >
                        <?php echo wp_nonce_field( 'manage-upload' ); ?>
                        <input type="hidden" name="action" value="upload" />
                        <input type="file" name="uploadFile" size="80" access="text/xml" id="uploadFile" />
                        <table>
                            <tr>
                                <td align="center"><input type="submit" name="upload" class="button-primary" value="Upload" /></td>
                                <td><label for="upload"><strong><?php _e( 'To upload a file, enter the file name in the input box and click the "Upload" button.', 'avp-unicode-charkbd' ); ?></strong></label></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><label for="printFile"><input type="checkbox" name="printFile" value="false" /> <?php _e( 'Check here to display the line-by-line file processing report in the error log.', 'avp-unicode-charkbd' ); ?></label></td>
                            </tr>
                            <tr>
                                <td align="center"><input type="reset" name="reset" value="Reset" class="button-secondary" /></td>
                                <td><label for="reset"> <?php _e( 'Click here to clear the upload file selection.', 'avp-unicode-charkbd' ); ?></label></td>
                            </tr>
                        </table>
                    </form>
                </div>

                <!-- Delete section -->
                <div id="avp_unicode_charkbd_admin_menu_panel_1" style="display: <?php echo ( ( 1 == $tabNumber ) ? '' : 'none;' ) ?>" class="nav-tab-contents" >
                    <h2><?php _e( 'Deleting File', 'avp-unicode-charkbd' ); ?></h2>
                    <p><?php _e( 'Select the character set definition files to be deleted by checking the appropriate checkbox below and then clicking the "Delete" button.', 'avp-unicode-charkbd' ); ?></p>
                    <p><?php _e( 'Note: the character set "Common" cannot be deleted.', 'avp-unicode-charkbd' ); ?></p>
                    <form method="post" enctype="multipart/form-data" >
                        <?php echo wp_nonce_field( 'manage-delete' ); ?>
                        <input type="hidden" name="action" value="delete" />

                        <table class="form-table" >
                            <col width=" 5%" ><col width="15%" ><col width="10%" ><col width="70%" >

                            <thead>
                                <tr>
                                    <td align="center" valign="middle">&#10003;</td>
                                    <td valign="middle"><strong><?php _e( 'Character Set', 'avp-unicode-charkbd' ); ?></strong></td>
                                    <td valign="middle"><strong><?php _e( 'Character Set Group', 'avp-unicode-charkbd' ); ?></strong></td>
                                    <td valign="middle"><strong><?php _e( 'Character Set Description', 'avp-unicode-charkbd' ); ?></strong></td>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $count = 0;
                                foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                                {
                                    printf( '<tr class="%s">', ( ( 0 == ( $count % 2 ) ) ? 'even' : 'odd' ) );

                                    if ( 'Common' != $characterSetName )
                                    {
                                        printf( '<td align="center" ><input type="checkbox" name="deletebox[]" value="%s" /></td>', $characterSetName );
                                    }
                                    else
                                    {
                                        echo ( '<td></td>' );
                                    }

                                    printf( '<td><strong>%s</strong></td><td><strong>%s</strong></td><td>%s</td></tr>', $characterSetName, $this->characterSetArrays[ $characterSetName ][ 'type' ], $this->characterSetArrays[ $characterSetName ][ 'description' ] );

                                    ++ $count;
                                }
                                ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td align="center"><input type="submit" name="delete" class="button-primary" value="Delete" onClick="return confirm( 'Are you sure you want to delete the selected file(s)?' );" /></td>
                                    <td colspan="3"><label for="delete"><strong> <?php _e( 'To delete a character set file, select the file to be deleted and click the "Delete" button.', 'avp-unicode-charkbd' ); ?></strong></label></td>
                                </tr>
                                <tr>
                                    <td align="center"><input type="reset" name="reset" value="Reset" class="button-secondary" /></td>
                                    <td colspan="3"><label for="reset"><strong> <?php _e( 'To reset the selections, click the "Reset" button.', 'avp-unicode-charkbd' ); ?></strong></label></td>
                                </tr>
                            </tfoot>

                        </table>
                    </form>
                </div>

                <!-- Error reporting section -->
                <div id="avp_unicode_charkbd_admin_menu_panel_2" style="display: <?php echo ( ( 2 == $tabNumber ) ? '' : 'none;' ) ?>" class="nav-tab-contents" >
                    <h2><?php _e( 'Error Reporting', 'avp-unicode-charkbd' ); ?></h2>

                    <?php
                    if ( ( ! file_exists( plugin_dir_path( __FILE__ ) . '/error.log' ) ) ||
                            ( 0 == filesize( plugin_dir_path( __FILE__ ) . '/error.log' ) ) )
                    {
                        printf( '<h4>%s</h4>', __( 'Error log file empty, unable to display.', 'avp-unicode-charkbd' ) );
                    }
                    else
                    {
                        ?>
                        <div id="poststuff">
                            <div class="postbox" >
                                <div class="handlediv" title="Click to toggle"><br/></div>
                                <h3 class="hndle"><span>error.log</span></h3>
                                <div class="inside">
                                    <pre><?php echo ( $this->avp_unicode_charkbd_return_file_contents( plugin_dir_path( __FILE__ ) . '/error.log' ) ); ?></pre>
                                </div>
                            </div>
                        </div>
                        <form method="post" enctype="multipart/form-data" >
                            <?php echo wp_nonce_field( 'manage-error' ); ?>
                            <input type="hidden" name="action" value="error" />
                            <label for="delete"><input type="submit" name="delete" class="button-primary" value="Delete" onClick="return confirm( 'Are you sure you want to delete the log file?' );" /><strong> <?php _e( 'To delete the error log file, click the "Delete" button.', 'avp-unicode-charkbd' ); ?></strong></label>
                        </form>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         *  ****************************************************************************************************************************
         *  Menu section, display subsection
         *  ****************************************************************************************************************************
         */
        /*
         *  Add the menu display definition
         */
        function avp_unicode_charkbd_admin_menu_display()
        {
            /*
             *  Load XML files to get the character set names and types
             */
            $this->avp_unicode_charkbd_loadfiles();
            $this->avp_unicode_charkbd_loadtypes();

            /*
             *  Wrap the entire page so it fits in the admin area
             */
            echo ( '<div class="wrap" id="dashc-page" >' );

            /*
             *  Start page with title and tabs
             */
            $this->avp_unicode_charkbd_admin_menu_header( __( 'Display Keyboards', 'avp-unicode-charkbd' ) );
            $this->avp_unicode_charkbd_admin_common_check();

            echo ( '<h3>' );
            _e( 'Activated character definition sets are initially displayed, deactivated sets are not displayed; click on the header bar to toggle display.', 'avp-unicode-charkbd' );
            echo ( '</h3>' );

            $this->avp_unicode_charkbd_admin_menu_tablist( $this->characterSetTypes );

            /*
             *  Wrap all post boxes in parent 
             */
            echo ( '<div id="poststuff">' );

            /*
             *  Define each individual category
             */
            for ( $typeindex = 0; ( $typeindex < count( $this->characterSetTypes ) ); $typeindex += 1 )
            {
                printf( '<div id="avp_unicode_charkbd_admin_menu_panel_%u" class="avp_unicode_charkbd nav-tab-contents" %s>', $typeindex, ( ( 0 == $typeindex ) ? '' : 'style="display:none;" ' ) );

                foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
                {
                    if ( $this->characterSetTypes[ $typeindex ] == ( string ) $this->characterSetArrays[ $characterSetName ][ 'type' ] )
                    {
                        printf( '<div class="postbox %s avp-meta-box" >', ( ! get_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) ) ? 'closed' : '' ) );
                        printf( '<div class="handlediv" title="Click to toggle"></div>' );
                        printf( '<h3 class="hndle"><span>%s</span></h3>', $characterSetName );

                        echo ( '<div class="inside">' );

                        foreach ( $this->characterSetArrays[ $characterSetName ]->characterSubset as $subset )
                        {
                            printf( '<span class="avp-meta-box-label" >&mdash;&nbsp;%s&nbsp;&mdash;</span><br />', $subset[ 'name' ] );

                            foreach ( $subset as $array )
                            {
                                foreach ( $array as $key )
                                {
                                    printf( '<abbr title="%s &mdash; %s" >%s</abbr>', htmlspecialchars( $key[ 'character' ] ), $this->avp_unicode_charkbd_format_text( $key ), $key[ 'character' ] );
                                }

                                echo ( '<br />' );
                            }

                            echo ( '<br />' );
                        }

                        // Inner DIV for postbos interior
                        echo ( '</div>' );

                        //  Outer DIV for outside postbox
                        echo ( '</div>' );
                    }
                }

                /*
                 *  Outer DIV for panel toggling
                 */
                echo ( '</div>' );
            }

            echo ( '</div>' );
        }

        /**
         *  ****************************************************************************************************************************
         *  Menu section, customizing subsection
         *  ****************************************************************************************************************************
         */
        /**
         *  Add the menu customization definition
         */
        function avp_unicode_charkbd_admin_menu_customize()
        {
            $tabNames = array( __( 'Customize', 'avp-unicode-charkbd' ), __( 'File Elements', 'avp-unicode-charkbd' ), __( 'Set Elements', 'avp-unicode-charkbd' ), __( 'Subset Elements', 'avp-unicode-charkbd' ), __( 'File Template', 'avp-unicode-charkbd' ), __( 'File Sample', 'avp-unicode-charkbd' ), __( 'References', 'avp-unicode-charkbd' ), );

            /*
             *  Load XML files to get the character set types (character sets not needed but call necessary to load types in description)
             */
            $this->avp_unicode_charkbd_loadfiles();
            $this->avp_unicode_charkbd_loadtypes();

            /*
             *  Wrap the entire page so it fits in the admin area
             */
            echo ( '<div class="wrap" id="dashc-page" >' );

            /*
             *  Start page with title and header
             */
            $this->avp_unicode_charkbd_admin_menu_header( __( 'Customize', 'avp-unicode-charkbd' ) );
            $this->avp_unicode_charkbd_admin_common_check();
            $this->avp_unicode_charkbd_admin_menu_tablist( $tabNames );

            /*
             *  Build pages under the tabs
             */
            ?>
            <div id="avp_unicode_charkbd_admin_menu_panel_0" style="display:" class="nav-tab-contents" >
                <h2><?php _e( 'Customize Unicode Sets', 'avp-unicode-charkbd' ); ?></h2>
                <p>All of the character groups used by and provided by this plugin are defined according to and separated by their <a href="http://www.unicode.org/standard/standard.html">Unicode</a> standards designation. These standards define a series of <a href="http://en.wikipedia.org/wiki/Unicode_symbols">Unicode symbols</a> which comprise a wide variety of languages, technical symbols, <a href="http://en.wikipedia.org/wiki/Dingbat" title="Just in case you are wondering what the heck IS a dingbat">dingbats</a>, syllabaries, logographies, ideograms, pictographs and other characters designed for web browser and electronic publishing display. These standards are always evolving and expanding on a regular basis.</p>
                <h3>Unicode Character Set Definition Files</h3>
                <p>Each Unicode character set is defined by an XML definition file. Within that definition file, each HTML character code associated with that set is listed and defined, then placed into a named subset of the overall definition. Also included in the definition file are necessary definitions such as the character set name and description, as well as optional definitions such as a particular recommended font file for use with the character set being defined.</p>
                <p>Each character set definition file is contained in a subdirectory named "<?php echo $this->modulePath ?>" under the plugin directory. This plugin <em>only</em> searches for definition files in that directory and all subdirectories under it.</p>
                <p>Note: elements within the set definition files are <em>case sensitive</em>.</p>
                <h3>Creating New Unicode Character Set Definition Files</h3>
                <p>Additional character sets may be added to the existing collection by creating a new definition file for that character set. Specifics for the definition file are contained in the following tabs on this page.</p>
            </div>

            <!--
            Must escape the XML entities or else the browsers will interpret them as HTML constructs
            -->
            <div id="avp_unicode_charkbd_admin_menu_panel_1" style="display: none;" class="nav-tab-contents" >
                <h2><?php _e( 'Character Set File Elements', 'avp-unicode-charkbd' ); ?></h2>
                <h3>Required Elements</h3>
                <p>Each character set file uses a specific structure:</p>
                <h4>File Header</h4>
                <pre class="highlight"><?php echo htmlentities( '<?xml version="1.0" encoding="UTF-8" ?>' ) ?></pre>
                <div style="clear: both;"></div>
                <p>Each character file must begin with this element.</p>
                <h3>Optional Elements</h3>
                <p>The following elements are optional:</p>
                <h4>Comment</h4>
                <pre class="highlight"><?php echo htmlentities( '<!-- Comment -->' ) ?></pre>
                <div style="clear: both;"></div>
                <p>Comments are ignored by the parser where ever they appear in the character file.</p>
            </div>

            <div id="avp_unicode_charkbd_admin_menu_panel_2" style="display: none;" class="nav-tab-contents" >
                <h2><?php _e( 'Character Set Elements', 'avp-unicode-charkbd' ); ?></h2>
                <h3>Required Elements</h3>
                <pre class="highlight"><?php echo htmlentities( '<characterSet name="" description="" type=""></characterSet>' ) ?></pre>
                <div style="clear: both;"></div>
                <p>Each character file must start and end with this element.</p>
                <h4>Name</h4>
                <p>Each characer set must have a unique name.</p>
                <h4>Description</h4>
                <p>Each character set must have a complete description of the character set.</p>
                <h4>Type</h4>
                <p>Each character set must have a type definition. Character sets are collected by the type of the character set and by the use of the character set. All character sets must be associated with a specific type, but are not restricted to the existing types.</p>
                <p>Current character set types are:&nbsp;
                    <?php
                    for ( $typeIndex = 0; ( $typeIndex < count( $this->characterSetTypes ) );  ++ $typeIndex )
                    {
                        echo ( '<strong>' . $this->characterSetTypes[ $typeIndex ] . '</strong>' );
                        echo ( $this->avp_unicode_charkbd_list_grammar( $typeIndex, count( $this->characterSetTypes ) ) );
                    }
                    ?>
                </p>
                <h3>Optional Elements</h3>
                <p>The following elements are optional and may be ignored.</p>
                <h4>Character Set Font</h4>
                <pre class="highlight"><?php echo htmlentities( '<characterSetFont name="" url="" />' ) ?></pre>
                <div style="clear: both;"></div>
                <p>The name of a particular font that contains the characters described by this character set and a URL where this particular font can be downloaded. Multiple entries of this element are valid.</p>
                <h4>Character Set Range</h4>
                <pre class="highlight"><?php echo htmlentities( '<characterSetRange></characterSetRange>' ) ?></pre>
                <div style="clear: both;"></div>
                <p>The numeric range and optional description of the character set or subset within the ISO definition. Any number of ranges may be defined in the file.</p>
                <h4>Character Set Resource</h4>
                <pre class="highlight"><?php echo htmlentities( '<characterSetResource></characterSetResource>' ) ?></pre>
                <div style="clear: both;"></div>
                <p>The URL of a web page or PDF file that describes this character set. Only one such definition is valid.</p>
            </div>

            <div id="avp_unicode_charkbd_admin_menu_panel_3" style="display: none;" class="nav-tab-contents" >
                <h2><?php _e( 'Character Subset and Character Definition Elements', 'avp-unicode-charkbd' ); ?></h2>
                <p>All character definition elements are required.</p>
                <h3>Character Subset</h3>
                <pre class="highlight"><?php echo htmlentities( '<characterSubset name="" ></characterSubset>' ) ?></pre>
                <div style="clear: both;"></div>
                <p>Each character subset comprises a cohesive collection of characters within a character set. The name of the subset is specified by the attribute within the element.</p>
                <h4>Character Subset Array</h4>
                <pre class="highlight"><?php echo htmlentities( '<characterSubsetArray></characterSubsetArray>' ) ?></pre>
                <div style="clear: both;"></div>
                <p>Each character subset array comprises the division within subsets. Each array within the subset is presented on a separate line or lines within the subset. This separates rows of characters into easily recoginizable display lines.</p>
                <h4>Character Definition</h4>
                <pre class="highlight"><?php echo htmlentities( '<characterDefinition character="" ></characterDefinition>' ) ?></pre>
                <div style="clear: both;"></div>
                <p>Each character is defined by this element. Character elements can be stated in the numeric HTML character equivilance, either decimal or hexidecimal. HTML literals can be substituted for the numeric character codes but the XML parser requires the use of the "<?php echo htmlentities( '&amp;' ) ?>" to escape the preceeding "<?php echo htmlentities( '&' ) ?>".</p>
                <p>Note: invalid character definitions cause the XML parser to return an invalid result, resulting in the file parser rejecting the entire XML file. Invalid character definitions include incorrect formatting of the character value, improper hexidecimal values, etc.</p>
            </div>

            <div id="avp_unicode_charkbd_admin_menu_panel_4" style="display: none;" class="nav-tab-contents" >
                <h2><?php _e( 'Character Set Definition File Templates', 'avp-unicode-charkbd' ); ?></h2>
                <p>The following character set sample template files are included with the distribution:</p>
                <div id="poststuff">
                    <div class="postbox" >
                        <div class="handlediv" title="Click to toggle"><br/></div>
                        <h3 class="hndle"><span>template.xml</span></h3>
                        <div class="inside">
                            <pre><?php echo $this->avp_unicode_charkbd_return_file_contents( plugin_dir_path( __FILE__ ) . '/template.xml' ); ?></pre>
                        </div>
                    </div>
                    <div class="postbox" >
                        <div class="handlediv" title="Click to toggle"><br/></div>
                        <h3 class="hndle"><span>template.dtd</span></h3>
                        <div class="inside">
                            <pre><?php echo $this->avp_unicode_charkbd_return_file_contents( plugin_dir_path( __FILE__ ) . '/template.dtd' ); ?></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div id="avp_unicode_charkbd_admin_menu_panel_5" style="display: none;" class="nav-tab-contents" >
                <h2><?php _e( 'Character Set Definition File Sample', 'avp-unicode-charkbd' ); ?></h2>
                <p>The following character set file is included with (and required by) the distribution:</p>
                <div id="poststuff">
                    <div class="postbox" >
                        <div class="handlediv" title="Click to toggle"><br/></div>
                        <h3 class="hndle"><span>Common.xml</span></h3>
                        <div class="inside">
                            <pre><?php echo $this->avp_unicode_charkbd_return_file_contents( plugin_dir_path( __FILE__ ) . $this->modulePath . '/Common.xml' ); ?></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div id="avp_unicode_charkbd_admin_menu_panel_6" style="display: none;" class="nav-tab-contents" >
                <h2><?php _e( 'Unicode References', 'avp-unicode-charkbd' ); ?></h2>
                <p>The following are just a few of the many references for the several and varied HTML character code sets and corresponding fonts available.</p>
                <ul>
                    <li><a href="http://en.wikipedia.org/wiki/Unicode_symbols">Unicode Symbols</a> at Wikipedia</li>
                    <li><a href="http://www.unicode.org/standard/standard.html">Unicode Standards</a> Unicode, Incorporated, is the organization which standardizes the various Unicode settings and encodings.</li>
                    <li><a href="http://www.unicode.org/charts/index.html">Unicode Standards Character Code Charts</a>: <em>all</em> of the Unicode character sets are listed in PDF format.</li>
                    <li><a href="http://www.alanwood.net/demos/ent4_frame.html">HTML 4.01 Character Entity Reference</a></li>
                    <li><a href="http://www.w3schools.com/tags/ref_entities.asp">HTML ISO-8859-1 Reference</a></li>
                    <li><a href="http://tlt.psu.edu/suggestions/international/web/codehtml.html">HTML - Special Entity Codes</a></li>
                    <li><a href="http://webdesign.about.com/library/bl_htmlcodes.htm">Language-specific HTML entity codes</a></li>
                </ul>
                <h3>Font References and Resources</h3>
                <ul>
                    <li><a href="http://www.alanwood.net/unicode/fonts_windows.html">Unicode Fonts for Windows</a></li>
                    <li><a href="http://www.alanwood.net/unicode/fonts_macosx.html">Unicode Fonts for Mac OSX</a></li>
                    <li><a href="http://www.alanwood.net/unicode/fonts_unix.html">Unicode Fonts for Unix</a></li>
                    <li><a href="http://www.math.utah.edu/~beebe/fonts/unicode.html">Fonts for the Unicode Character Set</a></li>
                    <li><a href="http://www.quivira-font.com">Quivira Font</a>: the source for the Quivira font.</li>
                    <li><a href="http://greekfonts.teilar.gr/">Fonts for Ancient Scripts</a>: the source for the Symbola font.</li>
                </ul>
                <h3>Software</h3>
                <p></p>
                <ul>
                    <li><a href="http://www.babelstone.co.uk/Software/BabelMap.html">BabelMap: A Unicode character map utility for Windows</a></li>
                </ul>
            </div>

            <?php
            echo ( '</div>' );
        }

        /**
         *  ****************************************************************************************************************************
         *  Menu section, child subroutine section
         *  ****************************************************************************************************************************
         */
        function avp_unicode_charkbd_admin_menu_header( $pagename )
        {
            printf( '<div id="icon-avp-keyboard" class="icon32"></div>' );
            printf( '<h2>%s - %s</h2>', $this->title, $pagename );
        }

        /*
         *  Test for "Common" definition set
         */
        function avp_unicode_charkbd_admin_common_check()
        {
            if ( ! array_key_exists( 'Common', $this->characterSetArrays ) )
            {
                $this->avp_unicode_charkbd_admin_menu_message( 'error', __( 'Error: Common definition set not found.', 'avp-unicode-charkbd' ), __( 'The Common set is required for proper operation, please re-install the plugin to restore the definition set.', 'avp-unicode-charkbd' ) );
            }
        }

        /*
         *  One routine to print error or success messages for menus
         *  $type can be "error", "warning" or "success"
         */
        function avp_unicode_charkbd_admin_menu_message( $type, $message, $information = '&nbsp;' )
        {
            printf( '<div class="%s avp-message-box" >', ( ( "error" == $type ) ? "error" : "updated fade" ) );
            printf( '<a style="float:right" href="javascript:void( 0 )" >%s</a>', __( 'Close', 'avp-unicode-charkbd' ) );
            printf( '<div id="icon-avp-%s" class="icon32"></div>', $type );
            printf( '<p>%s<br />%s</p>', $message, $information );
            printf( '</div>' );
        }

        /*
         *  Single routine to write menu tabs
         */
        function avp_unicode_charkbd_admin_menu_tablist( $tabNames, $tabNumber = 0 )
        {
            echo ( '<h3 class="nav-tab-wrapper">' );

            for ( $tabindex = 0; ( $tabindex < count( $tabNames ) ); $tabindex += 1 )
            {
                printf( '<a title="%s" rel="avp_unicode_charkbd_admin_menu_panel_%u" href="javascript:void( 0 );" class="nav-tab %s" >%s</a>', sprintf( '%s %s', __( 'Click to change display to', 'avp-unicode-charkbd' ), $tabNames[ $tabindex ] ), $tabindex, ( ( $tabNumber == $tabindex ) ? 'nav-tab-active' : '' ), $tabNames[ $tabindex ] );
            }

            echo ( '</h3>' );
        }

        /**
         *  ****************************************************************************************************************************
         *  Metabox section
         *  ****************************************************************************************************************************
         */
        /**
         * Adds the meta Box
         */
        function avp_unicode_charkbd_add_meta_box()
        {
            /*
             *  Load XML files to get the character set information
             */
            $this->avp_unicode_charkbd_loadfiles();
            $this->avp_unicode_charkbd_loadtypes();

            /**
             *  Define the headers and the related links
             *
             *  Define each section from the parent category description
             */
            foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
            {
                if ( get_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) ) )
                {
                    printf( '<a title="%s" rel="avp_unicode_charkbd_%s" class="avp-meta-box-control" href="javascript:void( 0 );" >', sprintf( __( 'Click to toggle the display of %s special characters', 'avp-unicode-charkbd' ), $characterSetName ), $this->avp_unicode_charkbd_setting_name( $characterSetName ) );
                    if ( 'Common' == $characterSetName )
                        echo ( '<strong>' );
                    echo ( $characterSetName );
                    if ( 'Common' == $characterSetName )
                        echo ( '</strong>' );
                    echo ( '</a> - ' );
                }
            }

            /**
             *  Define the help link at the End of the category list
             */
            echo ( ' <a class="avp-meta-box-control" title="Click to toggle display of Help" style="cursor:help;" rel="avp_unicode_charkbd_Help" >' );
            _e( 'Help', 'avp-unicode-charkbd' );
            echo ( '</a>' );

            /*
             *  Define each category according to the parent category description
             *  Define the help first so that it appears directly beneath the header
             */
            echo ( '<div id="avp_unicode_charkbd_Help" style="text-align:justify; cursor:help; display: none;" >' );
            echo ( '<hr />' );
            echo ( '<p>Click the key representation to insert the indicated character into post. Mouse-over characters for more info.<br />' );
            echo ( '<span style="color:#FF0000;">Caution: some characters may not display or display incorrectly in some fonts or in older browsers.</span>' );
            echo ( '</p>' );
            echo ( '</div>' );

            /*
             *  Define each individual category
             */
            foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
            {
                if ( get_option( $this->avp_unicode_charkbd_setting_name( $characterSetName ) ) )
                {
                    printf( '<div class="avp-meta-box" style="display:none;" id="avp_unicode_charkbd_%s" >', $this->avp_unicode_charkbd_setting_name( $characterSetName ) );
                    printf( '<hr /><h3><strong>%s</strong></h3><br />', $characterSetName );

                    foreach ( $this->characterSetArrays[ $characterSetName ]->characterSubset as $subset )
                    {
                        printf( '<span class="avp-meta-box-label">&mdash;&nbsp;%s&nbsp;&mdash;</span><br/>', $subset[ 'name' ] );

                        foreach ( $subset as $array )
                        {
                            foreach ( $array as $key )
                            {
                                printf( '<abbr title="%s &mdash; %s" onClick="javascript:send_to_editor( \'%s\' );" >%s</abbr>', htmlspecialchars( $key[ 'character' ] ), $this->avp_unicode_charkbd_format_text( $key ), $key[ 'character' ], $key[ 'character' ] );
                            }

                            echo ( '<br />' );
                        }

                        echo ( '<br />' );
                    }

                    echo ( '</div>' );
                }
            }
        }

        /**
         *  ****************************************************************************************************************************
         *  XML handling section
         *  ****************************************************************************************************************************
         */
        /*
         *  Load XML files
         */
        function avp_unicode_charkbd_loadfiles()
        {
            /*
             *  Add module files from recursive sub-directory search to populate the codes table
             *  Check Custom sub-directory last so that any attempts to overwrite existing files from Custom are flagged
             */
            $this->avp_unicode_charkbd_getdir( plugin_dir_path( __FILE__ ) . $this->modulePath );
            $this->avp_unicode_charkbd_getdir( plugin_dir_path( __FILE__ ) . $this->modulePath . '/Custom' );
            ksort( $this->characterSetArrays );
        }

        /*
         *  Parse XML file data for the character set type list
         */
        function avp_unicode_charkbd_loadtypes()
        {
            /*
             *  Build list of font entry types
             */
            foreach ( array_keys( $this->characterSetArrays ) as $characterSetName )
            {
                $this->characterSetTypes[ ] = ( string ) $this->characterSetArrays[ $characterSetName ][ 'type' ];
            }

            $this->characterSetTypes = array_unique( $this->characterSetTypes );
            sort( $this->characterSetTypes );
        }

        /*
         *  Scan directories recursively to process all XML files there
         */
        function avp_unicode_charkbd_getdir( $directory )
        {
            if ( ( $handle = @opendir( $directory ) ) )
            {
                while ( false !== ( $filename = @readdir( $handle ) ) )
                {
                    //  Ignore current and parent directory entries
                    if ( is_dir( $filename ) )
                    {
                        continue;
                    }
                    //  Ignore Custom sub-directory until specifically called as a single directory
                    else
                    if ( 'Custom' == $filename )
                    {
                        continue;
                    }
                    //  Recursively process sub-directories
                    else
                    if ( is_dir( $directory . '/' . $filename ) )
                    {
                        $this->avp_unicode_charkbd_getdir( $directory . '/' . $filename );
                    }
                    //  No other types of files allowed in the modules directory structure
                    else
                    if ( ! preg_match( '/\.xml/i', $filename ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, 'Invalid data file in module directory' );
                    }
                    //   Empty files are not allowed
                    else
                    if ( 0 == filesize( $directory . '/' . $filename ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, 'Invalid (empty) data file in module directory' );
                    }
                    else
                    //  Process the XML files
                    if ( ! $this->avp_unicode_charkbd_parse_xml( ( $directory . '/' . $filename ) ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, 'Unable to process XML data file' );
                    }
                }

                @closedir( $handle );
            }

            return;
        }

        /*
         *  Parse individual XML files
         *  Use SimpleXML for file conversion
         */
        function avp_unicode_charkbd_parse_xml( $filename, $addArray = true, $printFile = false )
        {
            if ( false === ( $characterSet = $this->avp_unicode_charkbd_load_xml( $filename ) ) )
            {
                return( false );
            }

            /*
             *  Print file into report/error log when requested during upload
             */
            if ( $printFile )
                avp_unicode_charkbd_print_xml( $filename );

            /*
             *  Check <characterSet> parameters
             */
            if ( ( '' == ( string ) $characterSet[ 'name' ] ) || ( '' == ( string ) $characterSet[ 'description' ] ) || ( '' == ( string ) $characterSet[ 'type' ] ) )
            {
                $this->avp_unicode_charkbd_error_log( $filename, 'Error: missing set name, description or type parameters in <characterSet> element.' );
                return( false );
            }
            /*
             *  Allow only letters, numbers and spaces in name field
             *  otherwise the registry settings can get thrown off
             */
            else
            if ( ! preg_match( '/^([a-z0-9 ]+)$/iu', ( string ) $characterSet[ 'name' ] ) )
            {
                $this->avp_unicode_charkbd_error_log( $filename, 'Error: invalid characters in set name parameter in <characterSet> element, only letters, numbers and spaces permitted.' );
                return( false );
            }
//  Do not allow modules to overwrite previous entries
            else
            if ( ! empty( $this->characterSetArrays[ ( string ) $characterSet[ 'name' ] ] ) )
            {
                $this->avp_unicode_charkbd_error_log( $filename, 'Error: duplicate <characterSet> definition sets are not permitted.' );
                return( false );
            }
//  "Help" keyword is reserved for the metabox help function
            else
            if ( 'Help' == ( string ) $characterSet[ 'name' ] )
            {
                $this->avp_unicode_charkbd_error_log( $filename, 'Error: "Help" is a prohibited name parameter in <characterSet> element.' );
                return( false );
            }
            /*
             *  Ensure the validity of the other information
             */
            else
            if ( ( ! ctype_print( ( string ) $characterSet[ 'description' ] ) ) || ( ! ctype_print( ( string ) $characterSet[ 'type' ] ) ) )
            {
                $this->avp_unicode_charkbd_error_log( $filename, 'Error: invalid characters in description or type parameter in <characterSet> element, only printable characters permitted.' );
                return( false );
            }

            /*
             *  Check <characterSetFont> parameters
             */
            if ( isset( $characterSet->characterSetFont ) )
            {
                for ( $count = 0; $count < count( $characterSet->characterSetFont );  ++ $count )
                {
                    if ( ( '' == ( string ) $characterSet->characterSetFont[ $count ][ 'name' ] ) || ( '' == ( string ) $characterSet->characterSetFont[ $count ][ 'url' ] ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, 'Error: missing font name or URL parameters in <characterSetFont> element.' );
                        return( False );
                    }
                    /*
                     *  Validate parameters
                     *  Name must printable characters
                     *  Url value must be a properly defined HTML URL
                     */
                    else
                    if ( ! preg_match( '/^([a-z0-9]+)$/iu', ( string ) $characterSet->characterSetFont[ $count ][ 'name' ] ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, sprintf( 'Error: invalid font name %s in <characterSetFont> element.', $characterSet->characterSetFont[ $count ][ 'name' ] ), 'Font names nust consist only of letters and numbers' );
                        return( False );
                    }
                    else
                    if ( ! filter_var( ( string ) $characterSet->characterSetFont[ $count ][ 'url' ], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, sprintf( 'Error: invalid URL %s in <characterSetFont> element.', $characterSet->characterSetFont[ $count ][ 'url' ] ) );
                        return( False );
                    }
                }
            }

            /*
             *  Check <characterSetRange> parameters
             */
            if ( isset( $characterSet->characterSetRange ) )
            {
                for ( $count = 0; $count < count( $characterSet->characterSetRange );  ++ $count )
                {
                    if ( ( '' == ( string ) $characterSet->characterSetRange[ $count ] ) || ( ! ctype_print( ( string ) $characterSet->characterSetRange[ $count ] ) ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, 'Error: missing or invalid font range parameter value in <characterSetRange> element.' );
                        return( false );
                    }
                }
            }

            /*
             *  Check <characterSetResource> parameters
             */
            if ( isset( $characterSet->characterSetResource ) )
            {
                /*
                 *  Duplicate entries here are not permitted
                 */
                if ( 1 < count( $characterSet->characterSetResource ) )
                {
                    $this->avp_unicode_charkbd_error_log( $filename, 'Error: duplicate set resource parameters in <characterSetResource> element.' );
                    return( false );
                }
                else
                if ( '' == ( string ) $characterSet->characterSetResource )
                {
                    $this->avp_unicode_charkbd_error_log( $filename, 'Warning: missing set resource URL parameter in <characterSetResource> element.' );
                    unset( $characterSet->characterSetResource );
                }
                //  URL value must be a properly defined HTML url
                else
                if ( ! filter_var( ( string ) $characterSet->characterSetResource, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) )
                {
                    $this->avp_unicode_charkbd_error_log( $filename, 'Warning: invalid set resource URL parameter in <characterSetResource> element.' );
                    unset( $characterSet->characterSetResource );
                }
            }

            /*
             *  Check <characterSubset> / <characterSubsetArray>
             */
            if ( ( ! isset( $characterSet->characterSubset ) ) || ( 0 == count( $characterSet->characterSubset ) ) )
            {
                $this->avp_unicode_charkbd_error_log( $filename, 'Error: missing or empty <characterSubset> element.' );
                return( false );
            }
            else
            {
                foreach ( $characterSet->characterSubset as $subset )
                {
                    $subsetName = ( string ) $subset[ 'name' ];

                    //  Test subset name
                    if ( ( '' == $subsetName ) || ( ! ctype_print( $subsetName ) ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, 'Error: missing or invalid name parameter in <characterSubset> element.' );
                        return( false );
                    }
                    else
                    if ( 0 == count( $subset ) )
                    {
                        $this->avp_unicode_charkbd_error_log( $filename, 'Error: empty <characterSubsetArray> element.' );
                        return( false );
                    }

                    foreach ( $subset as $array )
                    {
                        foreach ( $array as $key )
                        {
                            if ( ( '' == ( string ) $key[ 'character' ] ) || ( '' == ( string ) $key ) || ( ! ctype_print( ( string ) $key ) ) )
                            {
                                $this->avp_unicode_charkbd_error_log( $filename, 'Error: missing or invalid character name or character definition parameters in <characterDefinition> element.' );
                                return( false );
                            }
                        }
                    }
                }
            }

            /*
             *  Initialize all set array variables
             *  (Also add variable to structure to hold the file name for the deletion function)
             */
            $characterSetName = ( string ) $characterSet[ 'name' ];
            $characterSet[ 'fullFileName' ] = $filename;

            if ( $addArray )
                $this->characterSetArrays[ $characterSetName ] = $characterSet;
            return( true );
        }

        /*
         *  Load the XML file and perform initial error checking
         */
        function avp_unicode_charkbd_load_xml( $filename )
        {
            $use_errors = libxml_use_internal_errors( true );
            $characterSet = simplexml_load_file( $filename );

            if ( false === $characterSet )
            {
                $errorList = array( LIBXML_ERR_WARNING => 'Warning', LIBXML_ERR_ERROR => 'Error', LIBXML_ERR_FATAL => 'Fatal Error' );

                foreach ( libxml_get_errors() as $error )
                {
                    $this->avp_unicode_charkbd_error_log( $filename, sprintf( '%s code %u: %s', $errorList[ $error->level ], $error->code, $error->message ), $error->line );
                }
            }

            libxml_clear_errors();
            libxml_use_internal_errors( $use_errors );

            return( $characterSet );
        }

        /*
         *  Print out XML file
         *  This routine loads the XML file and parses it, then prints out the parsed version:
         *  Any apparent discrepancies between the original and parsed version should be errors
         */
        function avp_unicode_charkbd_print_xml( $filename )
        {
            /*
             *  Open and parse the XML source file
             *  Limited error checking in these functions
             */
            $data = implode( '', file( $filename ) );
            $parser = xml_parser_create( 'UTF-8' );
            $values = array( );
            xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, false );
            xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, true );
            xml_parse_into_struct( $parser, $data, $values );
            xml_parser_free( $parser );

            /*
             *  Parse each tag
             *      tag:        XML element name
             *      level:      XML nesting level
             *      type:       open / close / complete type of element
             *      value:      internal contents (<element></element>) of element (unless it contains other elements)
             *      attributes: internal attributes (<element name="">) if any and their values within the element
             *
             *  Lines are spaced to reflect nesting level 
             *  If level goes beyong expected levels, lines are marked with '**'
             */
            for ( $index = 0; ( $index < count( $values ) );  ++ $index )
            {
                $message = substr( '------**', 0, ( 2 * ( $values[ $index ][ 'level' ] - 1 ) ) );

                if ( 'open' == $values[ $index ][ 'type' ] )
                {
                    $message .= sprintf( '<%s ', $values[ $index ][ 'tag' ] );
                    if ( isset( $values[ $index ][ 'attributes' ] ) )
                    {
                        $message .= str_replace(
                                array( '       ', '      ', '     ', '    ', '   ', '  ', "\n", 'Array( ', ')', '[', ']', ), array( ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', '"', '"', ), print_r( $values[ $index ][ 'attributes' ], true ) );
                    }
                    $message .= '>';
                }
                else
                if ( 'close' == $values[ $index ][ 'type' ] )
                    $message .= sprintf( '</%s> ', $values[ $index ][ 'tag' ] );
                else
                if ( 'complete' == $values[ $index ][ 'type' ] )
                {
                    $message .= sprintf( '<%s ', $values[ $index ][ 'tag' ] );

                    if ( isset( $values[ $index ][ 'attributes' ] ) )
                    {
                        $message .= str_replace(
                                array( '       ', '      ', '     ', '    ', '   ', '  ', "\n", 'Array( ', ')', ), array( ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ), print_r( $values[ $index ][ 'attributes' ], true ) );
                    }
                    $message .= '>';

                    if ( isset( $values[ $index ][ 'value' ] ) )
                        $message .= $values[ $index ][ 'value' ];
                    $message .= sprintf( '<%s/>', $values[ $index ][ 'tag' ] );
                }

                $this->avp_unicode_charkbd_error_log( $filename, $message, ( $index + 1 ) );
            }

            return( true );
        }

        /*
         *  Error Logging
         */
        function avp_unicode_charkbd_error_log( $filename, $message, $line = 0 )
        {
            if ( 0 < $line )
            {
                error_log( sprintf( "(%s) line %3u: %s\n", basename( $filename ), $line, $message ), 3, ( plugin_dir_path( __FILE__ ) . '/error.log' ) );
            }
            else
            {
                error_log( sprintf( "(%s) %s\n", basename( $filename ), $message ), 3, ( plugin_dir_path( __FILE__ ) . '/error.log' ) );
            }
        }

        /**
         *  ****************************************************************************************************************************
         *  Utility section
         *  ****************************************************************************************************************************
         */
        /*
         *  Add comma, "and" or period after list entries
         */
        function avp_unicode_charkbd_list_grammar( $count, $arrayCount )
        {
            if ( ( $arrayCount - 2 ) > $count )
            {
                return( ', ' );
            }
            else
            if ( ( $arrayCount - 1 ) > $count )
            {
                return( sprintf( ' %s ', __( 'and', 'avp-unicode-charkbd' ) ) );
            }
            else
            if ( ( $arrayCount - 1 ) == $count )
            {
                return( '.' );
            }
        }

        /*
         *  Parse entity code into numeric equivalent
         */
        function avp_unicode_charkbd_unicode_entity( $c ) //m. perez
        {
            $h = ord( $c{0} );

            if ( ( $h > 0xBF ) && ( $h <= 0xDF ) )
            {
                $h = ($h & 0x1F) << 6 | (ord( $c{1} ) & 0x3F);
                $c = '&#' . $h . ';';
            }
            else
            if ( ( $h > 0xDF ) && ( $h <= 0xEF ) )
            {
                $h = ($h & 0x0F) << 12 | (ord( $c{1} ) & 0x3F) << 6 | (ord( $c{2} ) & 0x3F);
                $c = '&#' . $h . ';';
            }
            else
            if ( ( $h > 0xEF ) && ( $h <= 0xF4 ) )
            {
                $h = ($h & 0x0F) << 18 | (ord( $c{1} ) & 0x3F) << 12 | (ord( $c{2} ) & 0x3F) << 6 | (ord( $c{3} ) & 0x3F);
                $c = '&#' . $h . ';';
            }

            return( $c );
        }

        /*
         *  Reformat description to enforce consistent appearance
         */
        function avp_unicode_charkbd_format_text( $text )
        {
            return( str_replace(
                array( 'Apl', 'And ', 'For ', 'In ', 'On ', 'To ', 'With ', 'Without ' ), 
                array( 'APL', 'and ', 'for ', 'in ', 'on ', 'to ', 'with ', 'without ' ), ucwords( strtolower( $text ) ) ) );
        }

        /*
         *  Return given file text contents filtered for HTML display with wordwrapping
         */
        function avp_unicode_charkbd_return_file_contents( $file )
        {
            $fp = @fopen( $file, 'r' );
            $fc = @fread( $fp, @filesize( $file ) );
            @fclose( $fp );

            return( htmlentities( wordwrap( $fc, 125 ) ) );
        }

        /*
         *  Modify the character set name by changing spaces to underscores so that the register key doesn't have spaces
         *  Also used to set the metabox ID/REL pairing
         */
        function avp_unicode_charkbd_setting_name( $name )
        {
            return( 'avp_unicode_charkbd_' . str_replace( array( ' ', ), array( '_', ), $name ) );
        }

    }

// End avp_unicode_charkbd

    $avp_unicode_charkbd = new avp_unicode_charkbd;

endif; // End if !class_exists()
?>
