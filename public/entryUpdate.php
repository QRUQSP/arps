<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
function qruqsp_aprs_entryUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'APRS Entry'),
        'decoder'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Decoder'),
        'channel'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Channel'),
        'utc_of_traffic'=>array('required'=>'no', 'blank'=>'no', 'type'=>'datetimetoutc', 'name'=>'Time'),
        'from_call_sign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'From Call Sign'),
        'from_call_suffix'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'From Call Suffix'),
        'heard_call_sign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Heard Call Sign'),
        'heard_call_suffix'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Heard Call Suffix'),
        'level'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Level'),
        'error'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Error'),
        'dti'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'DTI'),
        'name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Name'),
        'symbol'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Symbol'),
        'latitude'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Latitude'),
        'longitude'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Longitude'),
        'speed'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Speed'),
        'course'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Course'),
        'altitude'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Altitude'),
        'frequency'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Frequency'),
        'offset'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Offset'),
        'tone'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tone'),
        'system'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'System'),
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'telemetry'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Telemetry'),
        'comment'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Comment'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'checkAccess');
    $rc = qruqsp_aprs_checkAccess($ciniki, $args['tnid'], 'qruqsp.aprs.entryUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'explodeCallSign');
    if( isset($args['from_call_sign']) ) {
        $rc = ciniki_core_explodeCallSign($ciniki, $args['from_call_sign']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $args['from_call_sign'] = $rc['call_sign'];
        $args['from_call_suffix'] = $rc['call_suffix'];
    }
    
    if( isset($args['heard_call_sign']) ) {
        $rc = ciniki_core_explodeCallSign($ciniki, $args['heard_call_sign']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $args['heard_call_sign'] = $rc['call_sign'];
        $args['heard_call_suffix'] = $rc['call_suffix'];
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'qruqsp.aprs');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the APRS Entry in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'qruqsp.aprs.entry', $args['entry_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.aprs');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'qruqsp.aprs');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'qruqsp', 'aprs');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.aprs.entry', 'object_id'=>$args['entry_id']));

    return array('stat'=>'ok');
}
?>
