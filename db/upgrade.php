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

    // Upgrade to version 2024121601: Add logbook_data column for Syntax 4 (Logbook Pelaksanaan)
    if ($oldversion < 2024121601) {
        
        // Define table project to be modified
        $table = new xmldb_table('project');
        
        // Define field logbook_data to be added to project
        $field = new xmldb_field('logbook_data', XMLDB_TYPE_TEXT, 'long', null, null, null, null, 'planning_data');
        
        // Conditionally add field logbook_data
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Ebelajar savepoint reached
        upgrade_mod_savepoint(true, 2024121601, 'ebelajar');
    }

    // Upgrade to version 2024121700: Add product_data column for Syntax 5 (Product Testing)
    if ($oldversion < 2024121700) {
        
        // Define table project to be modified
        $table = new xmldb_table('project');
        
        // Define field product_data to be added to project
        $field = new xmldb_field('product_data', XMLDB_TYPE_TEXT, 'long', null, null, null, null, 'logbook_data');
        
        // Conditionally add field product_data
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Ebelajar savepoint reached
        upgrade_mod_savepoint(true, 2024121700, 'ebelajar');
    }

    // Upgrade to version 2024121703: Add presentation_data column for Syntax 6 (Presentasi Proyek)
    if ($oldversion < 2024121703) {
        
        // Define table project to be modified
        $table = new xmldb_table('project');
        
        // Define field presentation_data to be added to project
        $field = new xmldb_field('presentation_data', XMLDB_TYPE_TEXT, 'long', null, null, null, null, 'product_data');
        
        // Conditionally add field presentation_data
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Ebelajar savepoint reached
        upgrade_mod_savepoint(true, 2024121703, 'ebelajar');
    }

    return true;
}
