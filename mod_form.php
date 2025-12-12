<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_ebelajar_mod_form extends moodleform_mod {
    
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('title_act', 'mod_ebelajar'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'desc', get_string('description', 'mod_ebelajar'), 'wrap="virtual" rows="10" cols="80"');
        $mform->setType('desc', PARAM_TEXT);
        $mform->addRule('desc', null, 'required', null, 'client');
        

        $mform->addElement('textarea', 'case_study', get_string('case_study', 'mod_ebelajar'), 'wrap="virtual" rows="10" cols="80"');
        $mform->setType('case_study', PARAM_TEXT);
        $mform->addRule('case_study', null, 'required', null, 'client');        

        $options = range(0, 15);
        $mform->addElement('select', 'total_grup', get_string('totalgrup', 'mod_ebelajar'), $options);
        $mform->addRule('total_grup', null, 'required', null, 'client');
        $mform->setType('total_grup', PARAM_INT);

        $mform->addElement('hidden', 'course', $this->current->course);
        $mform->setType('course', PARAM_INT);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

}
