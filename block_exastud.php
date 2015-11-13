<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// All rights reserved
/**
 * @package moodlecore
 * @subpackage blocks
 * @copyright 2013 gtn gmbh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
*/
require_once __DIR__ . '/lib/lib.php';
require_once __DIR__ . '/../moodleblock.class.php';

class block_exastud extends block_list {
    
    const CAP_HEADTEACHER = 'headteacher';
    const CAP_USE = 'use';
    const CAP_EDIT_PERIODS = 'editperiods';
    const CAP_UPLOAD_PICTURE = 'exastud:uploadpicture';
    const CAP_ADMIN = 'admin';

	function init() {
		$this->title = block_exastud_get_string('pluginname', 'block_exastud');
	}

	function instance_allow_multiple() {
		return false;
	}

	function has_config() {
		return true;
	}

	function instance_allow_config() {
		return false;
	}

	/*
	function config_save($data) {
		print_r($data);
		die();
		// Default behavior: save all variables as $CFG properties
		foreach ($data as $name => $value) {
			set_config($name, $value);
		}
		return true;
	}
	*/
	function get_content() {
		global $CFG, $COURSE, $USER, $DB;

		if (!block_exastud_has_global_cap(block_exastud::CAP_USE)) {
			$this->content = '';
			return $this->content;
		}

		if ($this->content !== NULL) {
			return $this->content;
		}

		if (empty($this->instance)) {
			$this->content = '';
			return $this->content;
		}

		$this->content = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';

		if (block_exastud_has_course_cap(block_exastud::CAP_HEADTEACHER, $COURSE->id)) {
			$this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/klassenzuteilung.png" height="16" width="23" alt="" />';
			$this->content->items[] = '<a title="' . block_exastud_get_string('configuration', 'block_exastud') . '" href="' . $CFG->wwwroot . '/blocks/exastud/configuration.php?courseid=' . $COURSE->id . '">' . block_exastud_get_string('configuration', 'block_exastud') . '</a>';

			if(block_exastud_reviews_available()) {
				$this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/zeugnisse.png" height="16" width="23" alt="" />';
				$this->content->items[] = '<a title="' . block_exastud_get_string('report', 'block_exastud') . '" href="' . $CFG->wwwroot . '/blocks/exastud/report.php?courseid=' . $COURSE->id . '">' . block_exastud_get_string('report', 'block_exastud') . '</a>';
			}
		}

		if ($DB->count_records('block_exastudclassteachers', array('teacherid'=>$USER->id)) && block_exastud_get_active_period()) {
			$this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/beurteilung.png" height="16" width="23" alt="" />';
			$this->content->items[] = '<a title="' . block_exastud_get_string('review', 'block_exastud') . '" href="' . $CFG->wwwroot . '/blocks/exastud/review.php?courseid=' . $COURSE->id . '">' . block_exastud_get_string('review', 'block_exastud') . '</a>';
		}
		if (block_exastud_has_global_cap(block_exastud::CAP_ADMIN)) {
			$this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/eingabezeitraum.png" height="16" width="23" alt="" />';
			$this->content->items[] = '<a title="' . block_exastud_get_string('periods', 'block_exastud') . '" href="' . $CFG->wwwroot . '/blocks/exastud/periods.php?courseid=' . $COURSE->id . '">' . block_exastud_get_string('periods', 'block_exastud') . '</a>';
		}
		/*
		if (block_exastud_has_global_cap(block_exastud::CAP_UPLOAD_PICTURE)) {
			$this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/logo.png" height="16" width="23" alt="" />';
			$this->content->items[] = '<a title="' . block_exastud_get_string('pictureupload', 'block_exastud') . '" href="' . $CFG->wwwroot . '/blocks/exastud/pictureupload.php?courseid=' . $COURSE->id . '">' . block_exastud_get_string('pictureupload', 'block_exastud') . '</a>';
		}
		*/

		return $this->content;
	}

    public static function t() {
        return call_user_func_array(__CLASS__.'\\'.__FUNCTION__, func_get_args());
    }

    public static function get_string() {
        return call_user_func_array(__CLASS__.'\\'.__FUNCTION__, func_get_args());
    }
}
