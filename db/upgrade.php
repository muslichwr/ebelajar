<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_ebelajar_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2024100800) {
        
        // Define table project_indicators to be created.
        $table = new xmldb_table('project_indicators');
        
        // Adding fields to table project_indicators.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('project_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('indicator_name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('analysis', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('references', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('is_valid', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('updated_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        
        // Adding keys to table project_indicators.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('projectfk', XMLDB_KEY_FOREIGN, ['project_id'], 'project', ['id']);
        
        // Conditionally launch create table for project_indicators.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Add field is_leader to table groupstudentproject.
        $table = new xmldb_table('groupstudentproject');
        $field = new xmldb_field('is_leader', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'jobdesk');
        
        // Conditionally launch add field is_leader.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add field teacher_scenario to table ebelajar.
        $table = new xmldb_table('ebelajar');
        $field = new xmldb_field('teacher_scenario', XMLDB_TYPE_TEXT, null, null, null, null, null, 'case_study');
        
        // Conditionally launch add field teacher_scenario.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Ebelajar savepoint reached.
        upgrade_mod_savepoint(true, 2024100800, 'ebelajar');
    }
    
    return true;
}
