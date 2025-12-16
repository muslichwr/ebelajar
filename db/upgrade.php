<?php

function xmldb_ebelajar_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Upgrade to version 2024121300: Add problem_definition and analysis_data columns
    if ($oldversion < 2024121300) {
        
        // Define table project to be modified
        $table = new xmldb_table('project');
        
        // Define field problem_definition to be added to project
        $field = new xmldb_field('problem_definition', XMLDB_TYPE_TEXT, null, null, null, null, null, 'step1_formulation');
        
        // Conditionally add field problem_definition
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field analysis_data to be added to project
        $field = new xmldb_field('analysis_data', XMLDB_TYPE_TEXT, 'long', null, null, null, null, 'problem_definition');
        
        // Conditionally add field analysis_data
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Ebelajar savepoint reached
        upgrade_mod_savepoint(true, 2024121300, 'ebelajar');
    }

    // Upgrade to version 2024121600: Add planning_data column for Syntax 2 (Jadwal Proyek)
    if ($oldversion < 2024121600) {
        
        // Define table project to be modified
        $table = new xmldb_table('project');
        
        // Define field planning_data to be added to project
        $field = new xmldb_field('planning_data', XMLDB_TYPE_TEXT, 'long', null, null, null, null, 'step2_pondation');
        
        // Conditionally add field planning_data
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Ebelajar savepoint reached
        upgrade_mod_savepoint(true, 2024121600, 'ebelajar');
    }

    return true;
}
