<?php
/*
	Copyright (c) 2016, Zhaofeng Li
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
	Citoid Link Handler

	This LinkHandler uses Citoid to extract the metadata from a webpage.
	See https://www.mediawiki.org/wiki/Citoid for details.
*/

namespace Reflinks\LinkHandlers;

use Reflinks\LinkHandler;
use Reflinks\Spider;
use Reflinks\Metadata;
use Reflinks\Exceptions\LinkHandlerException;

class CitoidLinkHandler extends LinkHandler {
	const BADDATA = [
        "browse publications",
        "central authentication service",
        "zbmath - the first resource for mathematics",
        "mr: matches for:",
        "log in",
        "sign in",
        "bookmarkable url intermediate page",
        "shibboleth authentication request",
        "domain for sale",
        "website for sale",
        "domain is for sale",
        "website is for sale",
        "lease this domain",
        "domain available",
        "metatags",
        "an error occurred",
        "user cookie",
        "cookies disabled",
        "page not found",
        "411 error",
        "url not found",
        "limit exceeded",
        "error page",
        "eu login",
        "bad gateway",
        "captcha",
        "view pdf",
        "wayback machine",
        "does not exist",
        "subscribe to read",
        "wiley online library",
        "pagina is niet gevonden",
        "zoeken in over na",
        "na een 404",
        "404 error",
        "account suspended",
        "error 404",
        "ezproxy",
        "ebscohost login",
        "404 - not found",
        "404!",
        "temporarily unavailable",
        "has expired",
        "not longer available",
        "article expired",
        "openid transaction in progress",
        "download limit exceeded",
        "internet archive wayback machine",
        "url（アドレス）が変わりました",
        "404エラ",
        "お探しのページは見つかりませんでした",
        "privacy settings",
        "cookie settings",
        "webcite query",
        "ой!",
        "untitled-1",
        "untitled-2",
        "untitled-3",
        "untitled-4",
        "untitled-5",
        "untitled-6",
        "untitled-7",
        "untitled-8",
        "untitled-9",
        "are you a robot",
        "aanmelden of registreren om te bekijken",
        "register to view",
        "being redirected",
        "aanmelden bij facebook",
        "einloggen",
        "the times & the sunday times",
        "login • instagram",
        "subscriber to read",
        "has now officially closed",
        "an error has occured",
        "an error has occurred",
        "youtube, a google company",
        "seite nicht gefunden",
        "página no encontrada",
        "الصفحة غير موجودة",
        "找不到网页",
        "страница не найдена",
        "page non trouvée",
        "an error occured",
        "compare payday loans",
        "find the best loan deal",
        "..::.. error",
        "pagina inicia",
        "help center - the arizona republic",
        "404 error",
        "404 - url invalid",
        "404. that's an error",
        "404 - page not found",
        "página não existe",
        "this is not the page you requested",
        "page not found",
        "404 - -",
        "sex cams",
        "404 &#124;",
        "missing page",
        "404 - file or directory not found",
        "错误页面",
        "404 page -",
        "404: page not found",
        "404: page not found",
        "404 error",
        "404 |",
        "页面不存在",
        "de pagina is niet gevonden",
        "404 -",
        "stranica nije pronađena",
        "404 page",
        "404. the page",
        "wasn't found on this server",
        "404. the url",
        "shieldsquare",
        "404 not found",
        "404页面",
        "sign up | linkedin",
        "the-star.co.kr",
        "connecting to the itunes store",
        "500 internal server error",
        "domainmarket.com",
        "bluehost.com",
        "unknown",
        "missing",
        "arxiv e-prints",
        "arxiv mathematics e-prints",
        "ssrn electronic journal",
        "dissertations available from proquest",
        "ebscohost login",
        "library login",
        "google groups",
        "sciencedirect",
        "cur_title",
        "wordpress › error",
        "ssrn temporarily unavailable",
        "log in - proquest",
        "shibboleth authentication request",
        "nookmarkable url intermediate page",
        "google books",
        "rte.ie",
        "loading",
        "google book",
        "the article you have been looking for has expired and is not longer available on our system. this is due to newswire licensing terms.",
        "openid transaction in progress",
        "download limit exceeded",
        "privacy settings",
        "untitled-1",
        "untitled-2",
        "professional paper",
        "zbmath",
        "theses and dissertations available from proquest",
        "proquest ebook central",
        "report",
        "bloomberg - are you a robot?",
        "page not found",
        "free live sex cams",
        "breaking news, analysis, politics, blogs, news photos, video, tech reviews",
        "breaking news, analysis, politics, blogs, news photos, video, tech reviews - time.com",
        "redirect notice",
        "oxford music online",
        "trove - archived webpage",
        "pagina inicia",
        "404 not found",
        "404页面",
        "sign up",
        "index of /home",
        "usa today - today's breaking news, us & world news",
        "403 unauthorized",
        "404错误",
        "internal server error",
        "error",
        "404",
        "error - lexisnexis® publisher",
        "optica publishing group",
	"validate user"
        ];
	private $spider = null;
	public $api = "https://en.wikipedia.org/api/rest_v1/data/citation";
	public static $mapping = array(
		"default" => array(
			// Metadata => Citoid
			'url' => "url",
			'title' => "title",
			"authors" => "author",
			"editors" => "editor",
			"publisher" => "publisher",
			"date" => "date",
			"volume" => "volume",
			"issue" => "issue",
			"pages" => "pages",
			"pmid" => "PMID",
			"pmc" => "PMCID",
			"doi" => "DOI",
			"via" => "libraryCatalog",
			"work" => "websiteTitle",
		),
		"book" => array(
			"title" => "bookTitle",
		),
		"journal" => array(
			"journal" => "publicationTitle",
		)
	);
	public static $typeMapping = array(
		"journalArticle" => "journal",
		"bookSection" => "book",
	);

	const ERROR_UNKNOWN = 0;
	const ERROR_FETCH = 1;

	function __construct( Spider $spider ) {
		global $config;
		$this->spider = $spider;
		if ( isset( $config['citoid']['api'] ) ) {
			$this->api = $config['citoid']['api'];
		}
	}

	public function getMetadata( $url, Metadata $baseMetadata = null ) {
		// Call the Citoid API
		$api = $this->api . "/mediawiki/" . urlencode( urldecode( $url ) );
		$response = $this->spider->fetch( $api, "", false );
		if ( !$response->successful ) { // failed
			throw new LinkHandlerException( "Fetching error", self::ERROR_FETCH );
		}
		$json = json_decode( $response->html, true );
		$json = $json[0];

		if ( $baseMetadata ) {
			$metadata = $baseMetadata;
		} else {
			$metadata = new Metadata();
		}
		if ( isset( $this::$typeMapping[$json['itemType']] ) ) {
			$metadata->type = $this::$typeMapping[$json['itemType']];
		}
		$mapping = $this::$mapping['default'];
		if ( isset( $this::$mapping[$metadata->type] ) ) {
			$mapping = array_merge( $mapping, $this::$mapping[$metadata->type] );
		}
		// Citoid key: $mapping[$metadataKey]
		foreach ( $json as $key => $value ) {
			if ( in_array( $key, $mapping ) ) {
				$metadataKey = array_search( $key, $mapping );
				$metadata->set( $metadataKey, $value );
			}
		}

		if ( $metadata->title == $metadata->url || $metadata->title == $url ||
		    strpos($metadata->title, "}}") || strpos($metadata->title, "{{") ||
		    in_array(strtolower($metadata->title), self::BADDATA)) {
			unset( $metadata->title );
		}

		return $metadata;
	}

	public static function explainErrorCode( $code ) {
		switch ( $code ) {
			default:
			case self::ERROR_UNKNOWN:
				return "Unknown error";
			case self::ERROR_FETCH:
				return "Fetching error";
		}
	}
}
