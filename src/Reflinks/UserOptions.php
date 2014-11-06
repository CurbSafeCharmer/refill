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
	Options
*/

namespace Reflinks;

use Reflinks\Exceptions\OptionsException;
use Reflinks\Exceptions\NoSuchOptionException;

class UserOptions {
	public $provider;
	private $rawOptions;
	function __construct( UserOptionsProvider $provider = null ) {
		if ( $provider ) {
			$this->provider = $provider;
		} else {
			$this->provider = new UserOptionsProvider();
		}
	}
	public function load( array $array ) {
		foreach ( $this->provider->listOptions() as $option ) {
			if ( isset( $array[$option] ) ) {
				$this->set( $option, $array[$option] );
			}
		}
		if ( isset( $array['defaults'] ) ) {
			$this->rawOptions['defaults'] = true;
		}
	}
	public function set( $option, $value ) {
		$details = $this->provider->getDetails( $option );
		switch ( $details['type'] ) {
			case UserOptionsProvider::TYPE_CHECKBOX:
				$this->rawOptions[$option] = true;
				break;
			case UserOptionsProvider::TYPE_SELECT:
				if ( in_array( $value, $details['options'] ) ) {
					$this->rawOptions[$option] = $value;
				}
				break;
			default:
				$this->rawOptions[$option] = $value;
				break;
		}
	}
	public function get( $option ) {
		if ( null !== $details = $this->provider->getDetails( $option ) ) {
			switch ( $details['type'] ) {
				case UserOptionsProvider::TYPE_CHECKBOX:
					if ( !isset( $this->rawOptions[$option] ) ) { // not specified - return false (not selected)
						if ( isset( $this->rawOptions['defaults'] ) && isset( $details['default'] ) ) {
							return $details['default'];
						}
						return false;
					} else { // specified
						return $this->rawOptions[$option];
					}
					break;
				default:
				case UserOptionsProvider::TYPE_SELECT:
					if ( !isset( $this->rawOptions[$option] ) ) { // not specified - use the default value
						if ( isset( $details['default'] ) ) {
							return $details['default'];
						} else {
							return null;
						}
					} else {
						return $this->rawOptions[$option];
					}
					break;
				
			}
		} else {
			throw new NoSuchOptionException( $option );
		}
	}
}
