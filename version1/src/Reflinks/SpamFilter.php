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
	Spam filter
*/

namespace Reflinks;

class SpamFilter {
	const TYPE_SPAM = 1;
	const TYPE_CONFIGBL = 2;

	private $file = "";
	private $enabled = false;
	private $blacklist = array();
	
	function __construct( $file = null ) {
		global $config;
		if ( $file !== null ) {
			$this->file = $file;
			$this->enabled = true;
		} else {
			if ( $config['spam']['enable'] ) {
				$this->file = $config['spam']['file'];
			}
		}
		$this->load();
	}
	public function load() {
		if ( !$this->enabled ) {
			return -1;
		}
		$this->blacklist = array();
		$file = fopen( $this->file, "r" );
		if ( $file ) {
			while ( false !== $line = fgets( $file ) ) { 
				$this->addRegex( $line );
			}
			fclose( $file );
			return $this->count();
		} else {
			// throw new SpamBlacklistCouldNotBeLoadedException( $this->file );
			return false;
		}
	}
	public function addRegex( $line ) {
		// Remove comments from the line, and trim the whitespaces
		$line = trim( preg_replace( "/#.*$/", "", $line ) );
		if ( !empty( $line ) ) { // Okay, we've got a regex
			$this->blacklist[] = $line;
		}
	}
	public function count() {
		return count( $this->blacklist );
	}
	public function checkList( $url, $blacklist ) {
		foreach( $blacklist as $oregex ) {
			// Those entries on the list are fragments, let's complete them
			$regex = "|^https?\:\/\/[A-Za-z0-9\-\_\.]*" . $oregex . "|";
			if ( @preg_match( $regex, $url ) ) { // Gotcha!
				return true;
			}
		}
		return false;
	
	}
	public function check( $url ) {
		global $config;
		if ( $this->checkList( $url, $this->blacklist ) ) {
			return self::TYPE_SPAM;
		} elseif ( $this->checkList( $url, $config['blacklist'] ) ) {
			return self::TYPE_CONFIGBL;
		}
		return false;
	}
}
	
