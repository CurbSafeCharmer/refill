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
	Link handler model

	A link handler takes URLs to webpages, and returns
	a populated Metadata object. It's not to be confused
	with a MetadataParser that takes DOMDocument objects as
	input.

	For example, it can fetch the HTML of a webpage from
	the web, generate a DOMDocument object, then feed it
	into a MetadataParserChain.

	Citoid works well for academic journals, so maybe we
	can implement CitoidLinkHandler. But first we need to solve
	the problem of switching between multiple LinkHandlers,
	since the Zotero translator sucks at non-journal
	sources and thus we cannot replace the internal parsers
	entirely.
*/

namespace Reflinks;

use Reflinks\Exceptions\LinkHandlerException;

abstract class LinkHandler {
	abstract function __construct( Spider $spider );
	abstract public function getMetadata( $url, Metadata $baseMetadata =null );
	public static function explainErrorCode( $code ) {}
}
