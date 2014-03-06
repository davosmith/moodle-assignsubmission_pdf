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
 * Version information
 *
 * @package    assignsubmission_pdf
 * @copyright  2012 Davo Smith
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$plugin->version   = 2014022700;
$plugin->release   = "2.5+ (Build 2014022700)";
$plugin->requires  = 2013051400;
$plugin->component = 'assignsubmission_pdf';
$plugin->maturity  = MATURITY_STABLE;
$plugin->cron      = 0;
$plugin->dependencies = array('assignfeedback_pdf' => 2013061200);