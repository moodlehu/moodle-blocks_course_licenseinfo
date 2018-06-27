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

/**
 * Course licenseinfo block
 *
 * @package    block_course_licenseinfo
 * @copyright  2017 Humboldt University Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_course_licenseinfo extends block_base {
    /**
     * @var bool Flag to indicate whether the header should be hidden or not.
     */
    private $headerhidden = true;

    function init() {
        $this->title = get_string('pluginname', 'block_course_licenseinfo');
    }
    
    function has_config() {
        return true;
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
    }

    function specialization() {
        // Page type starts with 'course-view' and the page's course ID is not equal to the site ID.
        if (strpos($this->page->pagetype, PAGE_COURSE_VIEW) === 0 && $this->page->course->id != SITEID) {
            $this->title = get_string('courselicenseinfo', 'block_course_licenseinfo');
            $this->headerhidden = false;
        }
    }
    
    function get_content() {
        global $CFG, $DB, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return '';
        }

        $this->content = new stdClass();
        $options = new stdClass();
        $options->noclean = true;    // Don't clean Javascripts etc
        $options->overflowdiv = true;
        $options->newlines = true;
        $courseid = $this->page->course->id;
        $this->content->text = '';

        // license
        if ($field = $DB->get_field('local_metadata_field', 'id', ['contextlevel' => CONTEXT_COURSE, 'shortname' => 'rights_description'])) {
            if ($licenseinfo = $DB->get_field('local_metadata', 'data', ['instanceid' => $courseid, 'fieldid' => $field])) {
                $this->content->text = '<a href="' . $licenseinfo .'">' .get_string('license', 'block_course_licenseinfo') .'</a>';
            }
            else {
                $this->content->text = 'License: not defined';
            }
        } else {
            $this->content->text = 'License: not defined';
        }
        // author
        $this->content->text .='<br />';
        if ($field = $DB->get_field('local_metadata_field', 'id', ['contextlevel' => CONTEXT_COURSE, 'shortname' => 'lifecycle_contribute'])) {
            if ($contribute_info = $DB->get_field('local_metadata', 'data', ['instanceid' => $courseid, 'fieldid' => $field])) {
                $values = explode('|', $contribute_info);
                if (!empty($values[1])) {
                    if (!empty($values[2])) {
                        $this->content->text .= '<a href="' . $values[2] .'">' .get_string('author', 'block_course_licenseinfo') .'</a>';
                    }
                }
            }
        }
        // settings
        $this->content->text .='<br />';
        $serverName = $_SERVER['SERVER_NAME'];
        $is_https = isset($_SERVER['HTTPS']);
        if (empty($is_https)) {
            $setting_link = 'http://' .$serverName .'/local/metadata/index.php?id=' .$courseid .'&action=coursedata&contextlevel=50';
        } else {
            $setting_link = 'https://' .$serverName .'/local/metadata/index.php?id=' .$courseid .'&action=coursedata&contextlevel=50';
        }
        //$this->content->text .= get_string('settings', 'block_course_licenseinfo').':';
        $this->content->text .= '<a href="' . $setting_link .'">' .get_string('settings', 'block_course_licenseinfo').'</a>';
        
        $this->content->footer = '';

        return $this->content;
    }

    function hide_header() {
        return $this->headerhidden;
    }

}
