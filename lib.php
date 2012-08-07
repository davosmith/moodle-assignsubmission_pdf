<?php
// This file is part of the submit PDF plugin for Moodle - http://moodle.org/
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
 * This file contains the definition for the library class for pdf
 *  submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package   assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * General definitions
 */
define('ASSIGNSUBMISSION_PDF_MAXFILES', 20);
define('ASSIGNSUBMISSION_PDF_MAXSUMMARYFILES', 5);

/**
 * File areas for file submission assignment
 */
define('ASSIGNSUBMISSION_PDF_FA_COVERSHEET', 'submission_pdf_coversheet'); // Coversheet to attach
define('ASSIGNSUBMISSION_PDF_FA_DRAFT', 'submission_pdf_draft'); // Files that have been uploaded but not submitted for marking
define('ASSIGNSUBMISSION_PDF_FA_FINAL', 'submission_pdf_final'); // Generated combined PDF (with coversheet)
/*
define('ASSIGN_PDF_FILEAREA_IMAGE', 'submission_pdf_image'); // Images generated from each page of the PDF
define('ASSIGN_PDF_FILEAREA_RESPONSE', 'submission_pdf_response'); // Response generated once annotation is complete
*/
