<?php
//
// Description
// -----------
// This function will check if the user has access to the  module.
//
// Arguments
// ---------
// q:
// tnid:                        The ID of the tenant to check the session user against.
// method:                      The requested method.
//
function qruqsp_aprs_checkAccess(&$ciniki, $tnid, $method) {
    //
    // Check if the tenant is active and the module is enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'checkModuleAccess');
    $rc = ciniki_tenants_checkModuleAccess($ciniki, $tnid, 'qruqsp', 'aprs');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Sysadmins are allowed full access
    //
    if( ($ciniki['session']['user']['perms'] & 0x01) == 0x01 ) {
        return array('stat'=>'ok');
    }

    //
    // Check to makes sure the session user is a tenant operator
    //
    $strsql = "SELECT tnid, user_id "
        . "FROM ciniki_tenant_users "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND user_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "' "
        . "AND package = 'ciniki' "
        . "AND status = 10 "
        . "AND (permission_group = 'owners' OR permission_group = 'employees' OR permission_group = 'resellers') "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.core', 'user');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.1', 'msg'=>'Access denied.'));
    }
    //
    // If the user has permission, return ok
    //
    if( isset($rc['rows']) && isset($rc['rows'][0])
        && $rc['rows'][0]['user_id'] > 0 && $rc['rows'][0]['user_id'] == $ciniki['session']['user']['id'] ) {
        return array('stat'=>'ok');
    }

    //
    // By default fail
    //
    return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.2', 'msg'=>'Access denied'));
}
?>
