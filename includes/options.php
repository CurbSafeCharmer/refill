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

require_once __DIR__ . "/constants.php";
$alloptions = array(
	"page" => array(
		"type" => OPTION_TYPE_SPECIAL,
		"name" => "Page name",
	),
	"text" => array(
		"type" => OPTION_TYPE_SPECIAL,
		"name" => "Raw wikitext",
	),
	"plainlink" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Use plain formatting instead of <code>{{cite web}}</code>",
		"description" => "If selected, bare references will be expanded without using <code>{{cite web}}</code>. This is discouraged since cite templates enable easy parsing by programs.",
		"advanced" => false,
		"default" => false,
	),
	"nofixuplain" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Do not expand uncaptioned plain links (surrounded with [ ])",
		"description" => "If selected, references with a URL surrounded by square brackets only will be skipped. This is for debugging onlyand should not be used.",
		"advanced" => true,
		"default" => false,
	),
	"nofixcplain" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Do not expand references with a captioned external link only",
		"description" => "If selected, references consisting of a captioned wxternal link only will be skilled.",
		"advanced" => false,
		"default" => false,
	),
	"nouseoldcaption" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Do not reuse existing captions from original references",
		"If selected, the tool will use the page title fetched from the server as the caption, overwriting the existing ones.",
		"advanced" => false,
		"default" => false,
	),
	"noremovetag" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Do not remove link rot tags",
		"description" => "If selected, link rot tags will be kept even if no reference is skipped enexpecedly during the process.",
		"advanced" => false,
		"default" => false,
	),
	"nofixutemplate" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Do not fix <code>{{cite web}}</code> templates with a URL only",
		"description" => "If selected, Reflinks will skip any reference consisting of a URL only. This is for debugging only and should not be used.",
		"advanced" => true,
		"default" => false,
	),
	"nowatch" => array(
		"type" => OPTION_TYPE_CHECKBOX, // This is automatically set by wikitoolbox.js
		"name" => "Do not watch the page",
		"description" => "If selected, the 'Watch this page' checkbox on the on-wiki editing interface will be unticked by default.",
		"advanced" => true,
		"default" => false,
	),
	"addblankmetadata" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Add blank metadata fields when the information is unavailable",
		"advanced" => false,
		"default" => false,
	),
	"noaccessdate" => array(
		"type" => OPTION_TYPE_CHECKBOX,
		"name" => "Do not add access dates",
		"description" => "If selected, dates of access will be omitted in the result.",
		"advanced" => false,
		"default" => true,
	),
);

function getOption( $option, $details ) {
	if ( isset( $_GET[$option] ) ) {
		$o = $_GET[$option];
	} elseif ( isset( $_POST[$option] ) ) {
		$o = $_POST[$option];
	} elseif ( $details['type'] !== OPTION_TYPE_CHECKBOX ) {
		return $details['default'];
	} else {
		return false;
	}
	if ( $details['type'] === OPTION_TYPE_CHECKBOX ) {
		return true;
	} else {
		return $o;
	}
}

function getOptions() {
	global $alloptions;
	$options = array();
	foreach( $alloptions as $option => $details ) {
		$options[$option] = getOption( $option, $details );
	}
	return $options;
}

function generateForm( $suffix, $advanced = false ) {
	global $alloptions;
	$result = "<ul id='form-$suffix-optuons' class='optionul'>";
	foreach( $alloptions as $option => $details ) {
		switch( $details['type'] ) {
			case OPTION_TYPE_CHECKBOX:
				if ( !$details['advanced'] || $advanced ) {
					$result .= "<li><input type='checkbox' name='$option' id='checkbox-$option-$suffix' ";
					if ( $details['default'] ) {
						$result .= "checked=''";
					}
					$result .= "/>";
					$result .= "<label for='checkbox-$option-$suffix'>{$details['name']}</label></li>";
				} elseif ( $details['default'] ) {
					$result .= "<input type='hidden' name='$option' id='checkbox-$option-$suffix' value='ok'/>";
				}
				break;
		}
	}
	$result .= "</ul>";
	return $result;
}
