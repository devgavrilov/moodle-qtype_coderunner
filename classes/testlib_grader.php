<?php
// This file is part of CodeRunner - http://coderunner.org.nz/
//
// CodeRunner is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// CodeRunner is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with CodeRunner.  If not, see <http://www.gnu.org/licenses/>.

/* The qtype_coderunner_testlib_grader class.
 * Uses check.cpp file from support files to dynamically check results.
 */

/**
 * @package    qtype
 * @subpackage coderunner
 * @copyright  Richard Lobb, 2013, The University of Canterbury
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_coderunner_testlib_grader extends qtype_coderunner_grader {

    public function name() {
        return 'TestLibGrader';
    }

    /*  Called to grade the output generated by a student's code for
     *  a given testcase. Returns a single TestResult object.
     *  Should not be called if the execution failed (syntax error, exception
     *  etc).
     */
    public function grade_known_good(&$output, &$testCase, qtype_coderunner_question $question) {
        $supportFiles = $question->get_files();

        if (!isset($supportFiles["check.cpp"])) {
            $testCase->abort = true;
            return new qtype_coderunner_test_result($testCase, false, 0.0, '');
        }

        $sandbox = $question->get_sandbox();
        $testLibFile = (new curl())->get("https://raw.githubusercontent.com/MikeMirzayanov/testlib/master/testlib.h");
        $result = $sandbox->execute($supportFiles["check.cpp"], "testlib", $output, [md5($testLibFile) => $testLibFile]);
        $sandbox->close();

        $isCorrect = $result == 0;
        $awardedMark = $isCorrect ? $testCase->mark : 0.0;

        return new qtype_coderunner_test_result($testCase, $isCorrect, $awardedMark, $output);
    }
}