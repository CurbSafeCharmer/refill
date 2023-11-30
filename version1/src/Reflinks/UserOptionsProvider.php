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
		foreach ( $this->generateFormStructure( $advanced ) as $option ) {
			switch ( $option['type'] ) {
				case "checkbox":
					$result .= "<li><input type='checkbox' name='{$option['name']}' id='checkbox-{$option['name']}-$suffix' ";
					if ( $option['checked'] ) {
						$result .= " checked ";
					}
					$result .= "/>";
					$result .= "<label for='checkbox-{$option['name']}-$suffix' title='{$option['description']}'>{$option['humanname']}</label></li>";
					break;
				case "hidden":
					$result .= "<input type='hidden' name='{$option['name']}' value='{$option['value']}>";
					break;
			}
		}
		$result .= "</ul>";
		return $result;
	}
	public function generateFormStructure( $advanced = false ) {
		global $I18N;
		$result = array();
		foreach( $this->options as $option => $details ) {
			switch( $details['type'] ) {
				case self::TYPE_CHECKBOX:
					if ( !$details['advanced'] || $advanced ) {
						$o = array(
							"type" => "checkbox",
							"name" => $option,
							"checked" => $details['default'],
						);
						if ( $I18N->msgExists( "option-" . $option ) ) {
							$o['humanname'] = $I18N->msg( "option-" . $option );
						} elseif ( !empty( $details['name'] ) ) {
							$o['humanname'] = $details['name'];
						} else {
							$o['humanname'] = $option;
						}
						if ( $I18N->msgExists( "option-" . $option . "-description" ) ) {
							$o['description'] = $I18N->msg( "option-" . $option . "-description" );
						} elseif ( !empty( $details['description'] ) ) {
							$o['description'] = $details['description'];
						} else {
							$o['description'] = "";
						}
						$result[] = $o;
					} elseif ( $details['default'] ) {
						$result[] = array(
							"type" => "hidden",
							"name" => $option,
							"value" => "ok",
						);
					}
					break;
			}
		}
		return $result;
	}
}


