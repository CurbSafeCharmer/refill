<?php
/*
	Copyright (c) 2014, Zhaofeng Li
	All rights reserved.
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	* Redistributions of source code must retain the above copyright notice, this
	list of conditions and the following disclaimer.
	* Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
	CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
	OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
	OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/*
	PHP syntax tests
*/

class LintTest extends PHPUnit_Framework_TestCase {
	// false == success, string == failure
	public function checkSyntax( $file ) {
		$output = shell_exec( "php -l '$file'" );
		if ( strpos( $output, "Errors parsing" ) !== false ) {
			return $output;
		} else {
			return false;
		}
	}
	
	public function listPhpFiles( $path, $result = array() ) {
		$list = scandir( $path );
		foreach( $list as $entry ) {
			$fullpath = $path . "/" . $entry;
			if ( is_dir( $fullpath ) && substr( $entry, 0, 1 ) != "." ) { // directories
				$result = $this->listPhpFiles( $fullpath, $result );
			} else { // regular files
				if ( fnmatch( "*.php", $entry ) ) {
					$result[] = $fullpath;
				}
			}
		}
		return $result;
	}
	
	public function testLint() {
		$files = $this->listPhpFiles( __DIR__ . "/.." );
		foreach( $files as $file ) {
			$this->assertFalse( $this->checkSyntax( $file ) );
		}
	}
}
