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
	Constants
*/

/*
	Reasons for skipping references
*/

// [UNUSED] Not a bare reference
define( "SKIPPED_NOTBARE", 1 );
// HTTP Error
define( "SKIPPED_HTTPERROR", 2 );
// Empty response or not HTML
define( "SKIPPED_EMPTY", 3 );
// No title found
define( "SKIPPED_NOTITLE", 4 );
// Host blacklisted in $config['hostblacklist']
define( "SKIPPED_HOSTBL", 5 );
// Spam blacklist
define( "SKIPPED_SPAM", 6 );

/*
	Date formats
*/

// [DEFAULT] DMY (e.g. 15 January 2001)
define( "DATE_DMY", false );
// MDY (e.g. January 15, 2001)
define( "DATE_MDY", true );

/*
	Option types
*/
define( "OPTION_TYPE_SPECIAL", 0 );
define( "OPTION_TYPE_CHECKBOX", 1 );
