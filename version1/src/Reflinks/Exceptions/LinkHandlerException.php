<?php
/*
	LinkHandler exception

	Thrown by LinkHandlers when they cannot successfully
	generate Metadata.
*/

namespace Reflinks\Exceptions;

class LinkHandlerException extends \Exception {
	protected $extra = array();
	function __construct( $message, $code = 0, array $extra = null ) {
		if ( is_array( $extra ) ) {
			$this->extra = $extra;
		}
		parent::__construct( $message, $code );
	}
	public function getExtra() {
		return $this->extra;
	}
}
