<?php
//
// Description
// -----------
// This method searchs for a APRS Entrys for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:               The ID of the tenant to get APRS Entry for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
function qruqsp_aprs_entrySearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'checkAccess');
    $rc = qruqsp_aprs_checkAccess($ciniki, $args['tnid'], 'qruqsp.aprs.entrySearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of entries
    //
    $strsql = "SELECT qruqsp_aprs_entries.id, "
        . "qruqsp_aprs_entries.decoder, "
        . "qruqsp_aprs_entries.channel, "
        . "qruqsp_aprs_entries.utc_of_traffic, "
        . "qruqsp_aprs_entries.from_call_sign, "
        . "qruqsp_aprs_entries.from_call_suffix, "
        . "qruqsp_aprs_entries.heard_call_sign, "
        . "qruqsp_aprs_entries.heard_call_suffix, "
        . "qruqsp_aprs_entries.level, "
        . "qruqsp_aprs_entries.error, "
        . "qruqsp_aprs_entries.dti, "
        . "qruqsp_aprs_entries.name, "
        . "qruqsp_aprs_entries.symbol, "
        . "qruqsp_aprs_entries.latitude, "
        . "qruqsp_aprs_entries.longitude, "
        . "qruqsp_aprs_entries.speed, "
        . "qruqsp_aprs_entries.course, "
        . "qruqsp_aprs_entries.altitude, "
        . "qruqsp_aprs_entries.frequency, "
        . "qruqsp_aprs_entries.offset, "
        . "qruqsp_aprs_entries.tone, "
        . "qruqsp_aprs_entries.system, "
        . "qruqsp_aprs_entries.status, "
        . "qruqsp_aprs_entries.telemetry, "
        . "qruqsp_aprs_entries.comment "
        . "FROM qruqsp_aprs_entries "
        . "WHERE qruqsp_aprs_entries.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR from_call_sign LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR heard_call_sign LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR comment LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR comment LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.aprs', array(
        array('container'=>'entries', 'fname'=>'id', 
            'fields'=>array('id', 'decoder', 'channel', 'utc_of_traffic', 'from_call_sign', 'from_call_suffix', 'heard_call_sign', 'heard_call_suffix', 'level', 'error', 'dti', 'name', 'symbol', 'latitude', 'longitude', 'speed', 'course', 'altitude', 'frequency', 'offset', 'tone', 'system', 'status', 'telemetry', 'comment')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['entries']) ) {
        $entries = $rc['entries'];
        $entry_ids = array();
        foreach($entries as $iid => $entry) {
            $entry_ids[] = $entry['id'];
        }
    } else {
        $entries = array();
        $entry_ids = array();
    }

    return array('stat'=>'ok', 'entries'=>$entries, 'nplist'=>$entry_ids);
}
?>
