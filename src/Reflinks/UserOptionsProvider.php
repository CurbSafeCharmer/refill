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
	Options provider
*/

namespace Reflinks;

class UserOptionsProvider {
	const TYPE_SPECIAL = 0;
	const TYPE_CHECKBOX = 1;
	const TYPE_SELECT = 2;
	public $options = array();
	function __construct( array $options = null ) {
		global $config;
		if ( $options !== null ) {
			$this->options = $options;
		} else {
			$this->options = $config['options'];
		}
	}
	public function getDetails( $option ) {
		if ( isset( $this->options[$option] ) ) {
			return $this->options[$option];
		} else {
			return null;
		}
	}
	public function listOptions() {
		$result = array();
		foreach( $this->options as $option => $details ) {
			$result[] = $option;
		}
		return $result;
	}
	public function generateForm( $suffix = "", $advanced = false ) {
		$result = "<ul id='form-$suffix-options' class='optionul'>";
		foreach( $this->options as $option => $details ) {
			switch( $details['type'] ) {
				case self::TYPE_CHECKBOX:
					if ( !$details['advanced'] || $advanced ) {
						$result .= "<li><input type='checkbox' name='$option' id='checkbox-$option-$suffix' ";
						if ( $details['default'] ) {
							$result .= "checked=''";
						}
						$result .= "/>";
						$result .= "<label for='checkbox-$option-$suffix' ";
						if ( !empty( $details['description'] ) ) {
							$result .= "title='{$details['description']}'";
						}
						$result .= ">{$details['name']}</label></li>";
					} elseif ( $details['default'] ) {
						$result .= "<input type='hidden' name='$option' id='checkbox-$option-$suffix' value='ok'/>";
					}
					break;
			}
		}
		$result .= "</ul>";
		return $result;
	}
}

