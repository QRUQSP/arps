<?php
//
// Description
// -----------
// This method will add a new aprs entry for the station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:        The ID of the station to add the APRS Entry to.
//
function qruqsp_aprs_entryAdd(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'decoder'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Decoder'),
        'channel'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Channel'),
        'utc_of_traffic'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'datetimetoutc', 'name'=>'Time'),
        'from_call_sign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'From Call Sign'),
        'heard_call_sign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Heard Call Sign'),
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
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'aprs', 'private', 'checkAccess');
    $rc = qruqsp_aprs_checkAccess($q, $args['station_id'], 'qruqsp.aprs.entryAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'explodeCallSign');
    if( isset($args['from_call_sign']) ) {
        $rc = qruqsp_core_explodeCallSign($q, $args['from_call_sign']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $args['from_call_sign'] = $rc['call_sign'];
        $args['from_call_suffix'] = $rc['call_suffix'];
    }
    
    if( isset($args['heard_call_sign']) ) {
        $rc = qruqsp_core_explodeCallSign($q, $args['heard_call_sign']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $args['heard_call_sign'] = $rc['call_sign'];
        $args['heard_call_suffix'] = $rc['call_suffix'];
    }

    //
    // Start transaction
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionStart');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionRollback');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionCommit');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbAddModuleHistory');
    $rc = qruqsp_core_dbTransactionStart($q, 'qruqsp.aprs');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the aprs entry to the database
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectAdd');
    $rc = qruqsp_core_objectAdd($q, $args['station_id'], 'qruqsp.aprs.entry', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.aprs');
        return $rc;
    }
    $entry_id = $rc['id'];

    //
    // Commit the transaction
    //
    $rc = qruqsp_core_dbTransactionCommit($q, 'qruqsp.aprs');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the station modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'updateModuleChangeDate');
    qruqsp_core_updateModuleChangeDate($q, $args['station_id'], 'qruqsp', 'aprs');

    //
    // Update the web index if enabled
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'hookExec');
    qruqsp_core_hookExec($q, $args['station_id'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.aprs.entry', 'object_id'=>$entry_id));

    return array('stat'=>'ok', 'id'=>$entry_id);
}
?>