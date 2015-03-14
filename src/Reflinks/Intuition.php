<?php
/*
	This file is exempt from LICENSE!
	Licensed under CC-BY-SA 3.0, like the Intuition project.

	The text of CC-BY-SA 3.0 can be found on:
	https://creativecommons.org/licenses/by/3.0/

	This file creates Reflinks\Intuition class which
	extends the \Intuition class, adding reFill-specific
	tweaks. We manage i18n elsewhere, not on Translatewiki.

	This assumes the \Intuition is already available, and does
	not try to load it.
*/

namespace Reflinks;

class Intuition extends \Intuition {
	public function ensureLoaded( $domain, $lang ) {
		$domain = strtolower( $domain );
		if ( $domain == "reflinks" ) {
			// Apply reFill-specific routine
			if ( isset( $this->loadedDomains[$domain][$lang] ) ) {
				// Already tried
				return $this->loadedDomains[$domain][$lang];
			}
			// Validate input and protect against path traversal
			if ( !\IntuitionUtil::nonEmptyStrs( $domain, $lang ) ||
				strcspn( $domain, ":/\\\000" ) !== strlen( $domain ) ||
				strcspn( $lang, ":/\\\000" ) !== strlen( $lang )
			) {
				$this->errTrigger( 'Illegal domain or lang', __METHOD__, E_NOTICE );
				return false;
			}
			$this->loadedDomains[$domain][$lang] = false;
			$dir = __DIR__ . '/../../language/';
			if ( !is_dir( $dir ) ) {
				// Domain does not exist
				return false;
			}
			if ( !is_readable( $dir ) ) {
				// Directory is unreadable
				$this->errTrigger( "Unable to open messages directory for \"$domain\".",
					__METHOD__, E_NOTICE, __FILE__, __LINE__ );
				return false;
			}
			$file = "$dir/$lang.json";
			$loaded = $this->loadMessageFile( $domain, $lang, $file );
			if ( !$loaded ) {
				return false;
			}
			$this->loadedDomains[$domain][$lang] = true;
			foreach ( $this->getLangFallbacks( $lang ) as $fallbackLang ) {
				$this->loadedDomains[$domain][$fallbackLang] = false;
				$file = "$dir/$fallbackLang.json";
				$loaded = $this->loadMessageFile( $domain, $fallbackLang, $file );
				if ( $loaded ) {
					return $domain;
				}
			}
			return true;
		} else {
			// Fall back to the parent's method
			return parent::ensureLoaded( $domain, $lang );
		}
	}
	public function getLangFallbacks( $lang ) {
		$fallbacks = parent::getLangFallbacks( $lang );
		$fallbacks[] = "en"; // Ensure en is always available
		return $fallbacks;
	}
}
