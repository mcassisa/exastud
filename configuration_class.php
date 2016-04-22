<?php
// This file is part of Exabis Student Review
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Student Review is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

require __DIR__.'/inc.php';

$courseid = optional_param('courseid', 1, PARAM_INT); // Course ID
$action = optional_param('action', '', PARAM_TEXT);
require_login($courseid);

block_exastud_require_global_cap(block_exastud\CAP_MANAGE_CLASSES);

$classid = required_param('classid', PARAM_INT);
$class = block_exastud\get_teacher_class($classid);

$url = '/blocks/exastud/configuration_classes.php';
$PAGE->set_url($url);

if ($action == 'delete') {
	if (!optional_param('confirm', false, PARAM_BOOL)) {
		throw new moodle_exception('not confirmed');
	}

	$DB->delete_records('block_exastudclass', ['id' => $class->id]);

	redirect(new moodle_url('/blocks/exastud/configuration_classes.php?courseid='.$courseid));
}

$output = block_exastud\get_renderer();
echo $output->header('configuration_classes');

echo $output->print_subtitle($class->title, $CFG->wwwroot . '/blocks/exastud/configuration_class_info.php?courseid='.$courseid.'&classid='.$class->id);

/* Print the Students */
echo html_writer::tag("h2",\block_exastud\get_string('members', 'block_exastud'));
$table = new html_table();

$table->head = array (\block_exastud\get_string('firstname'), \block_exastud\get_string('lastname'), \block_exastud\get_string('email'));
$table->align = array ("left", "left", "left");
$table->attributes['style'] = "width: 75%;";
$table->size = ['33%', '33%', '33%'];

$classstudents = \block_exastud\get_class_students($class->id);

foreach($classstudents as $classstudent) {
	$table->data[] = array ($classstudent->firstname, $classstudent->lastname, $classstudent->email);
}

//echo html_writer::table($table);
echo $output->table($table);

echo $OUTPUT->single_button($CFG->wwwroot . '/blocks/exastud/configuration_classmembers.php?courseid='.$courseid.'&classid='.$class->id,
		\block_exastud\get_string('editclassmemberlist', 'block_exastud'), 'get');

/* Print the Classes */
echo html_writer::tag("h2",\block_exastud\get_string('teachers', 'block_exastud'));
$table = new html_table();

$table->head = array (\block_exastud\trans('de:Fachbezeichnung'), \block_exastud\get_string('firstname'), \block_exastud\get_string('lastname'), \block_exastud\get_string('email'));
$table->align = array ("left", "left", "left", "left");
$table->size = ['25%', '25%', '25%', '25%'];

$classteachers = block_exastud\get_class_teachers($class->id);

foreach($classteachers as $classteacher) {
	$table->data[] = array ($classteacher->subject_title ?: \block_exastud\trans('de:nicht zugeordnet'), $classteacher->firstname, $classteacher->lastname, $classteacher->email);
}

//echo html_writer::table($table);
echo $output->table($table);

echo $OUTPUT->single_button($CFG->wwwroot . '/blocks/exastud/configuration_classteachers.php?courseid='.$courseid.'&classid='.$class->id,
		\block_exastud\get_string('editclassteacherlist', 'block_exastud'), 'get');

/* Print the categories */
// disabled for now
/*
echo html_writer::tag("h2",\block_exastud\get_string('categories', 'block_exastud'));

$table = new html_table();

$table->align = array("left");
$table->attributes['style'] = "width: 50%;";

$categories = block_exastud_get_class_categories($class->id);

foreach($categories as $category) {
	$table->data[] = array($category->title);
}

echo $output->table($table);

echo $OUTPUT->single_button($CFG->wwwroot . '/blocks/exastud/configuration_categories.php?courseid='.$courseid.'&classid='.$class->id,
		\block_exastud\get_string('editclasscategories', 'block_exastud'), 'get');
*/

echo $output->footer();
