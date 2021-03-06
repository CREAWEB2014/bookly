<?php
namespace Bookly\Backend\Modules\TinyMce;

use Bookly\Lib;

/**
 * Class Plugin
 * @package Bookly\Backend\Modules\TinyMce
 */
class Plugin
{
    private $vars = array();

    public function __construct()
    {
        global $PHP_SELF;
        if ( // check if we are in admin area and current page is adding/editing the post
            is_admin()  && ( strpos( $PHP_SELF, 'post-new.php' ) !== false || strpos( $PHP_SELF, 'post.php' ) !== false || strpos( $PHP_SELF, 'admin-ajax.php' ) )
        ) {
            add_action( 'admin_footer',  array( $this, 'renderPopup' ) );
            add_filter( 'media_buttons', array( $this, 'addButton' ), 50 );
        }
    }

    public function addButton( $editor_id )
    {
        // don't show on dashboard (QuickPress)
        $current_screen = get_current_screen();
        if ( $current_screen && 'dashboard' == $current_screen->base ) {
            return;
        }

        // don't display button for users who don't have access
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        }

        // do a version check for the new 3.5 UI
        $version = get_bloginfo( 'version' );

        if ( $version < 3.5 ) {
            // show button for v 3.4 and below
            echo '<a href="#TB_inline?width=640&inlineId=bookly-tinymce-popup&height=650" id="add-bookly-form" title="' . esc_attr__( 'Add Bookly booking form', 'bookly' ) . '">' . __( 'Add Bookly booking form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&amp;inlineId=bookly-tinymce-appointment-popup&amp;height=250" id="add-ap-appointment" title="' . esc_attr__( 'Add Bookly appointments list', 'bookly' ) . '">' . __( 'Add Bookly appointments list', 'bookly' ) . '</a>';
            echo '<a href="#" id="add-cancellation-confirmation" title="' . esc_attr__( 'Add appointment cancellation confirmation', 'bookly' ) . '">' . __( 'Add appointment cancellation confirmation', 'bookly' ) . '</a>';
        } else {
            // display button matching new UI
            $img = '<span class="bookly-media-icon"></span> ';
            echo '<a href="#TB_inline?width=640&inlineId=bookly-tinymce-popup&height=650" id="add-bookly-form" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly booking form', 'bookly' ) . '">' . $img . __( 'Add Bookly booking form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&amp;inlineId=bookly-tinymce-appointment-popup&amp;height=250" id="add-ap-appointment" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly appointments list', 'bookly' ) . '">' . $img . __( 'Add Bookly appointments list', 'bookly' ) . '</a>';
            echo '<a href="#" id="add-cancellation-confirmation" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add appointment cancellation confirmation', 'bookly' ) . '">' . $img . __( 'Add appointment cancellation confirmation', 'bookly' ) . '</a>';
        }
        Lib\Proxy\Shared::renderMediaButtons( $version );
    }

    public function renderPopup()
    {
        $casest = Lib\Config::getCaSeSt();

        // render
        ob_start();
        ob_implicit_flush( 0 );

        try {
            include 'templates/bookly_form.php';
            include 'templates/appointment_list.php';
            include 'templates/cancellation_confirmation.php';
        } catch ( \Exception $e ) {
            ob_end_clean();
            throw $e;
        }

        echo ob_get_clean();
        Lib\Proxy\Shared::renderTinyMceComponent();
    }

}
