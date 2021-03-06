<?php
//
// Description
// -----------
// This function returns the settings for the module and the main menu items and settings menu items
//
// Arguments
// ---------
// q:
// tnid:      
// args: The arguments for the hook
//
function qruqsp_aprs_hooks_uiSettings(&$ciniki, $tnid, $args) {
    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'menu_items'=>array(), 'settings_menu_items'=>array());

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['tenant']['modules']['qruqsp.aprs'])
        && (isset($args['permissions']['operators'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>5000,
            'label'=>'APRS',
            'helpcontent'=>'Manage APRS settings and view APRS packets received',
            'edit'=>array('app'=>'qruqsp.aprs.main'),
            );
        //
        // There is currently nothing to show in the APRS module
        //
//        $rsp['menu_items'][] = $menu_item;
    }

    return $rsp;
}
?>
