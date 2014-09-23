// <syntaxhighlight lang="JavaScript">

// ==UserScript==
// @name        wDiff
// @version     1.1.2
// @date        September 22, 2014
// @description improved word-based diff library with block move detection
// @homepage    https://en.wikipedia.org/wiki/User:Cacycle/diff
// @source      https://en.wikipedia.org/wiki/User:Cacycle/diff.js
// @author      Cacycle (https://en.wikipedia.org/wiki/User:Cacycle)
// @license     released into the public domain
// ==/UserScript==

/*

Improved JavaScript diff library that returns html/css-formatted new text version with highlighted deletions, inserts, and block moves.
It is compatible with all browsers and is not dependent on external libraries.
An implementation of the word-based algorithm from:

Communications of the ACM 21(4):264 (1978)
http://doi.acm.org/10.1145/359460.359467

Additional features:

* Word (token) types have been optimized for MediaWiki source texts
* Resolution down to characters level
* Highlighting of moved blocks and their original position marks
* Stepwise split (paragraphs, sentences, words, chars)
* Recursive diff
* Additional post-pass-5 code for resolving islands caused by common tokens at the border of sequences of common tokens
* Block move detection and visualization
* Minimizing length of moved vs. static blocks
* Sliding of ambiguous unresolved regions to next line break
* Optional omission of unchanged irrelevant parts from the output
* Fully customizable
* Well commented and documented code

This code is used by the MediaWiki in-browser text editors [[en:User:Cacycle/editor]] and [[en:User:Cacycle/wikEd]]
and the enhanced diff view tool wikEdDiff [[en:User:Cacycle/wikEd]].

Usage:
var diffHtml = wDiff.diff(oldString, newString, full);

Datastructures (abbreviations from publication):

wDiff:               namespace object (global)
	.configurations      see top of code below for configuration and customization options

class Text:        diff text object (new or old version)
	.string:           text
	.words{}:          word count hash
	.first:            index of first token in tokens list
	.last:             index of last token in tokens list

	.tokens[]:         token list for new or old string (doubly-linked list) (N and O)
		.prev:             previous list item
		.next:             next list item
		.token:            token string
		.link:             index of corresponding token in new or old text (OA and NA)
		.number:           list enumeration number
		.unique:           token is unique word in text

class TextDiff:    diff object
	.newText,          new text
	.oldText:          old text
	.html:             diff html

	.symbols:          object for symbols table data
		.token[]:          associative array (hash) of parsed tokens for passes 1 - 3, points to symbol[i]
		.symbol[]:         array of objects that hold token counters and pointers:
			.newCount:         new text token counter (NC)
			.oldCount:         old text token counter (OC)
			.newToken:         token index in text.newText.tokens
			.oldToken:         token index in text.oldText.tokens
		.linked:           flag: at least one unique token pair has been linked

	.blocks[]:         array of objects that holds block (consecutive text tokens) data in order of the new text
		.oldBlock:         number of block in old text order
		.newBlock:         number of block in new text order
		.oldNumber:        old text token number of first token
		.newNumber:        new text token number of first token
		.oldStart:         old text token index of first token
		.count             number of tokens
		.unique:           contains unique matched token
		.words:            word count
		.chars:            char length
		.type:             'same', 'del', 'ins', 'mark'
		.section:          section number
		.group:            group number of block
		.fixed:            belongs to a fixed (not moved) group
		.moved:            'mark' block associated moved block group number
		.string:           string of block tokens

	.groups[]:         section blocks that are consecutive in old text
		.oldNumber:        first block oldNumber
		.blockStart:       first block index
		.blockEnd:         last block index
		.unique:           contains unique matched token
		.maxWords:         word count of longest block
		.words:            word count
		.chars:            char count
		.fixed:            not moved from original position
		.movedFrom:        group position this group has been moved from
		.color:            color number of moved group

*/

// JSHint options: W004: is already defined, W100: character may get silently deleted
/* jshint -W004, -W100, newcap: false, browser: true, jquery: true, sub: true, bitwise: true, curly: true, evil: true, forin: true, freeze: true, globalstrict: true, immed: true, latedef: true, loopfunc: true, quotmark: single, strict: true, undef: true */
/* global console */

// turn on ECMAScript 5 strict mode
'use strict';

// define global objects
var wDiff; if (wDiff === undefined) { wDiff = {}; }
var WED;

//
// start of configuration and customization settings
//

//
// core diff settings
//

// enable block move layout with highlighted blocks and marks at their original positions
if (wDiff.showBlockMoves === undefined) { wDiff.showBlockMoves = true; }

// minimal number of real words for a moved block (0 for always showing highlighted blocks)
if (wDiff.blockMinLength === undefined) { wDiff.blockMinLength = 3; }

// further resolve replacements character-wise from start and end
if (wDiff.charDiff === undefined) { wDiff.charDiff = true; }

// enable recursive diff to resolve problematic sequences
if (wDiff.recursiveDiff === undefined) { wDiff.recursiveDiff = true; }

// display blocks in different colors
if (wDiff.coloredBlocks === undefined) { wDiff.coloredBlocks = false; }

// show debug infos and stats
if (wDiff.debug === undefined) { wDiff.debug = false; }

// show debug infos and stats
if (wDiff.debugTime === undefined) { wDiff.debugTime = false; }

// run unit tests
if (wDiff.unitTesting === undefined) { wDiff.unitTesting = false; }

// UniCode letter support for regexps, from http://xregexp.com/addons/unicode/unicode-base.js v1.0.0
if (wDiff.letters === undefined) { wDiff.letters = 'a-zA-Z0-9' + '00AA00B500BA00C0-00D600D8-00F600F8-02C102C6-02D102E0-02E402EC02EE0370-037403760377037A-037D03860388-038A038C038E-03A103A3-03F503F7-0481048A-05270531-055605590561-058705D0-05EA05F0-05F20620-064A066E066F0671-06D306D506E506E606EE06EF06FA-06FC06FF07100712-072F074D-07A507B107CA-07EA07F407F507FA0800-0815081A082408280840-085808A008A2-08AC0904-0939093D09500958-09610971-09770979-097F0985-098C098F09900993-09A809AA-09B009B209B6-09B909BD09CE09DC09DD09DF-09E109F009F10A05-0A0A0A0F0A100A13-0A280A2A-0A300A320A330A350A360A380A390A59-0A5C0A5E0A72-0A740A85-0A8D0A8F-0A910A93-0AA80AAA-0AB00AB20AB30AB5-0AB90ABD0AD00AE00AE10B05-0B0C0B0F0B100B13-0B280B2A-0B300B320B330B35-0B390B3D0B5C0B5D0B5F-0B610B710B830B85-0B8A0B8E-0B900B92-0B950B990B9A0B9C0B9E0B9F0BA30BA40BA8-0BAA0BAE-0BB90BD00C05-0C0C0C0E-0C100C12-0C280C2A-0C330C35-0C390C3D0C580C590C600C610C85-0C8C0C8E-0C900C92-0CA80CAA-0CB30CB5-0CB90CBD0CDE0CE00CE10CF10CF20D05-0D0C0D0E-0D100D12-0D3A0D3D0D4E0D600D610D7A-0D7F0D85-0D960D9A-0DB10DB3-0DBB0DBD0DC0-0DC60E01-0E300E320E330E40-0E460E810E820E840E870E880E8A0E8D0E94-0E970E99-0E9F0EA1-0EA30EA50EA70EAA0EAB0EAD-0EB00EB20EB30EBD0EC0-0EC40EC60EDC-0EDF0F000F40-0F470F49-0F6C0F88-0F8C1000-102A103F1050-1055105A-105D106110651066106E-10701075-1081108E10A0-10C510C710CD10D0-10FA10FC-1248124A-124D1250-12561258125A-125D1260-1288128A-128D1290-12B012B2-12B512B8-12BE12C012C2-12C512C8-12D612D8-13101312-13151318-135A1380-138F13A0-13F41401-166C166F-167F1681-169A16A0-16EA1700-170C170E-17111720-17311740-17511760-176C176E-17701780-17B317D717DC1820-18771880-18A818AA18B0-18F51900-191C1950-196D1970-19741980-19AB19C1-19C71A00-1A161A20-1A541AA71B05-1B331B45-1B4B1B83-1BA01BAE1BAF1BBA-1BE51C00-1C231C4D-1C4F1C5A-1C7D1CE9-1CEC1CEE-1CF11CF51CF61D00-1DBF1E00-1F151F18-1F1D1F20-1F451F48-1F4D1F50-1F571F591F5B1F5D1F5F-1F7D1F80-1FB41FB6-1FBC1FBE1FC2-1FC41FC6-1FCC1FD0-1FD31FD6-1FDB1FE0-1FEC1FF2-1FF41FF6-1FFC2071207F2090-209C21022107210A-211321152119-211D212421262128212A-212D212F-2139213C-213F2145-2149214E218321842C00-2C2E2C30-2C5E2C60-2CE42CEB-2CEE2CF22CF32D00-2D252D272D2D2D30-2D672D6F2D80-2D962DA0-2DA62DA8-2DAE2DB0-2DB62DB8-2DBE2DC0-2DC62DC8-2DCE2DD0-2DD62DD8-2DDE2E2F300530063031-3035303B303C3041-3096309D-309F30A1-30FA30FC-30FF3105-312D3131-318E31A0-31BA31F0-31FF3400-4DB54E00-9FCCA000-A48CA4D0-A4FDA500-A60CA610-A61FA62AA62BA640-A66EA67F-A697A6A0-A6E5A717-A71FA722-A788A78B-A78EA790-A793A7A0-A7AAA7F8-A801A803-A805A807-A80AA80C-A822A840-A873A882-A8B3A8F2-A8F7A8FBA90A-A925A930-A946A960-A97CA984-A9B2A9CFAA00-AA28AA40-AA42AA44-AA4BAA60-AA76AA7AAA80-AAAFAAB1AAB5AAB6AAB9-AABDAAC0AAC2AADB-AADDAAE0-AAEAAAF2-AAF4AB01-AB06AB09-AB0EAB11-AB16AB20-AB26AB28-AB2EABC0-ABE2AC00-D7A3D7B0-D7C6D7CB-D7FBF900-FA6DFA70-FAD9FB00-FB06FB13-FB17FB1DFB1F-FB28FB2A-FB36FB38-FB3CFB3EFB40FB41FB43FB44FB46-FBB1FBD3-FD3DFD50-FD8FFD92-FDC7FDF0-FDFBFE70-FE74FE76-FEFCFF21-FF3AFF41-FF5AFF66-FFBEFFC2-FFC7FFCA-FFCFFFD2-FFD7FFDA-FFDC'.replace(/(\w{4})/g, '\\u$1'); }

// new line characters without and with '\n' and '\r'
if (wDiff.newLines === undefined) { wDiff.newLines = '\\u0085\\u2028'; }
if (wDiff.newLinesAll === undefined) { wDiff.newLinesAll = '\\n\\r\\u0085\\u2028'; }

// full stops without '.'
if (wDiff.fullStops === undefined) { wDiff.fullStops = '058906D40701070209640DF41362166E180318092CF92CFE2E3C3002A4FFA60EA6F3FE52FF0EFF61'.replace(/(\w{4})/g, '\\u$1'); }

// new paragraph characters without '\n' and '\r'
if (wDiff.newParagraph === undefined) { wDiff.newParagraph = '\\u2029'; }

// exclamation marks without '!'
if (wDiff.exclamationMarks === undefined) { wDiff.exclamationMarks = '01C301C301C3055C055C07F919441944203C203C20482048FE15FE57FF01'.replace(/(\w{4})/g, '\\u$1'); }

// question marks without '?'
if (wDiff.questionMarks === undefined) { wDiff.questionMarks = '037E055E061F13671945204720492CFA2CFB2E2EA60FA6F7FE56FF1F'.replace(/(\w{4})/g, '\\u$1') + '\\u11143'; }

// regExps for splitting text (included separators)
if (wDiff.regExpSplit === undefined) {
	wDiff.regExpSplit = {

		// paragraphs: after double newlines
		paragraph: new RegExp('(.|\\n)*?((\\r\\n|\\n|\\r){2,}|[' + wDiff.newParagraph + '])+', 'g'),

		// sentences: after newlines and .spaces
		sentence: new RegExp('[^' + wDiff.newLinesAll + ']*?([.!?;]+[^\\S' + wDiff.newLinesAll + ']+|[' + wDiff.fullStops + wDiff.exclamationMarks + wDiff.questionMarks + ']+[^\\S' + wDiff.newLinesAll + ']*|[' + wDiff.newLines + ']|\\r\\n|\\n|\\r)', 'g'),

		// inline chunks
		//       [[wiki link]]    | {{template}}     | [ext. link]  |<html>               | [[wiki link|         | {{template|      | url
		chunk: /\[\[[^\[\]\n]+\]\]|\{\{[^\{\}\n]+\}\}|\[[^\[\]\n]+\]|<\/?[^<>\[\]\{\}\n]+>|\[\[[^\[\]\|\n]+\]\]\||\{\{[^\{\}\|\n]+\||\b((https?:|)\/\/)[^\x00-\x20\s"\[\]\x7f]+/g,

		// words, multi-char markup, and chars
		word: new RegExp('[' + wDiff.letters + ']+([\'’_]?[' + wDiff.letters + ']+)*|\\[\\[|\\]\\]|\\{\\{|\\}\\}|&\\w+;|\'\'\'|\'\'|==+|\\{\\||\\|\\}|\\|-|.', 'g'),

		// chars
		character: /./g
	};
}

// regExps for sliding gaps: newlines and space/word breaks
if (wDiff.regExpSlideStop === undefined) { wDiff.regExpSlideStop = new RegExp('[\\n\\r' + wDiff.newLines + ']$'); }
if (wDiff.regExpSlideBorder === undefined) { wDiff.regExpSlideBorder = new RegExp('[ \\t' + wDiff.newLinesAll + wDiff.newParagraph + '\\x0C\\x0b]$'); }

// regExps for counting words
if (wDiff.regExpWord === undefined) { wDiff.regExpWord = new RegExp('[' + wDiff.letters + ']+([\'’_]?[' + wDiff.letters + ']+)*', 'g'); }
if (wDiff.regExpChunk === undefined) { wDiff.regExpChunk = wDiff.regExpSplit.chunk; }

// regExp detecting blank-only and single-char blocks
if (wDiff.regExpBlankBlock === undefined) { wDiff.regExpBlankBlock = /^([^\t\S]+|[^\t])$/; }

//
// shorten output settings
//

// characters before diff tag to search for previous heading, paragraph, line break, cut characters
if (wDiff.headingBefore      === undefined) { wDiff.headingBefore      = 1500; }
if (wDiff.paragraphBeforeMax === undefined) { wDiff.paragraphBeforeMax = 1500; }
if (wDiff.paragraphBeforeMin === undefined) { wDiff.paragraphBeforeMin =  500; }
if (wDiff.lineBeforeMax      === undefined) { wDiff.lineBeforeMax      = 1000; }
if (wDiff.lineBeforeMin      === undefined) { wDiff.lineBeforeMin      =  500; }
if (wDiff.blankBeforeMax     === undefined) { wDiff.blankBeforeMax     = 1000; }
if (wDiff.blankBeforeMin     === undefined) { wDiff.blankBeforeMin     =  500; }
if (wDiff.charsBefore        === undefined) { wDiff.charsBefore        =  500; }

// characters after diff tag to search for next heading, paragraph, line break, or characters
if (wDiff.headingAfter      === undefined) { wDiff.headingAfter      = 1500; }
if (wDiff.paragraphAfterMax === undefined) { wDiff.paragraphAfterMax = 1500; }
if (wDiff.paragraphAfterMin === undefined) { wDiff.paragraphAfterMin =  500; }
if (wDiff.lineAfterMax      === undefined) { wDiff.lineAfterMax      = 1000; }
if (wDiff.lineAfterMin      === undefined) { wDiff.lineAfterMin      =  500; }
if (wDiff.blankAfterMax     === undefined) { wDiff.blankAfterMax     = 1000; }
if (wDiff.blankAfterMin     === undefined) { wDiff.blankAfterMin     =  500; }
if (wDiff.charsAfter        === undefined) { wDiff.charsAfter        =  500; }

// lines before and after diff tag to search for previous heading, paragraph, line break, cut characters
if (wDiff.linesBeforeMax === undefined) { wDiff.linesBeforeMax = 10; }
if (wDiff.linesAfterMax  === undefined) { wDiff.linesAfterMax  = 10; }

// maximal fragment distance to join close fragments
if (wDiff.fragmentJoinLines === undefined) { wDiff.fragmentJoinLines = 5; }
if (wDiff.fragmentJoinChars === undefined) { wDiff.fragmentJoinChars = 1000; }

//
// css classes
//

if (wDiff.symbolMarkLeft === undefined) { wDiff.symbolMarkLeft = '◀'; }
if (wDiff.symbolMarkRight === undefined) { wDiff.symbolMarkRight = '▶'; }
if (wDiff.stylesheet === undefined) {
	wDiff.stylesheet =

	// insert
	'.wDiffInsert { font-weight: bold; background-color: #bbddff; color: #222; border-radius: 0.25em; padding: 0.2em 1px; }' +
	'.wDiffInsertBlank { background-color: #66bbff; }' +
	'.wDiffFragment:hover .wDiffInsertBlank { background-color: #bbddff; }' +

	// delete
	'.wDiffDelete { font-weight: bold; background-color: #ffe49c; color: #222; border-radius: 0.25em; padding: 0.2em 1px; }' +
	'.wDiffDeleteBlank { background-color: #ffd064; }' +
	'.wDiffFragment:hover .wDiffDeleteBlank { background-color: #ffe49c; }' +

	// block
	'.wDiffBlockLeft, .wDiffBlockRight { font-weight: bold; background-color: #e8e8e8; border-radius: 0.25em; padding: 0.2em 1px; margin: 0 1px; }' +
	'.wDiffBlock { }' +
	'.wDiffBlock0 { background-color: #ffff80; }' +
	'.wDiffBlock1 { background-color: #d0ff80; }' +
	'.wDiffBlock2 { background-color: #ffd8f0; }' +
	'.wDiffBlock3 { background-color: #c0ffff; }' +
	'.wDiffBlock4 { background-color: #fff888; }' +
	'.wDiffBlock5 { background-color: #bbccff; }' +
	'.wDiffBlock6 { background-color: #e8c8ff; }' +
	'.wDiffBlock7 { background-color: #ffbbbb; }' +
	'.wDiffBlock8 { background-color: #a0e8a0; }' +
	'.wDiffBlockHighlight { background-color: #777; color: #fff; border: solid #777; border-width: 1px 0; }' +

	// mark
	'.wDiffMarkLeft, .wDiffMarkRight { font-weight: bold; background-color: #ffe49c; color: #666; border-radius: 0.25em; padding: 0.2em; margin: 0 1px; }' +
	'.wDiffMarkRight:before { content: "' + wDiff.symbolMarkRight + '"; }' +
	'.wDiffMarkLeft:before { content: "' + wDiff.symbolMarkLeft + '"; }' +
	'.wDiffMark { background-color: #e8e8e8; color: #666; }' +
	'.wDiffMark0 { background-color: #ffff60; }' +
	'.wDiffMark1 { background-color: #c8f880; }' +
	'.wDiffMark2 { background-color: #ffd0f0; }' +
	'.wDiffMark3 { background-color: #a0ffff; }' +
	'.wDiffMark4 { background-color: #fff860; }' +
	'.wDiffMark5 { background-color: #b0c0ff; }' +
	'.wDiffMark6 { background-color: #e0c0ff; }' +
	'.wDiffMark7 { background-color: #ffa8a8; }' +
	'.wDiffMark8 { background-color: #98e898; }' +
	'.wDiffMarkHighlight { background-color: #777; color: #fff; }' +

	// wrappers
	'.wDiffContainer { }' +
	'.wDiffFragment { white-space: pre-wrap; background: #fff; border: #bbb solid; border-width: 1px 1px 1px 0.5em; border-radius: 0.5em; font-family: sans-serif; font-size: 88%; line-height: 1.6; box-shadow: 2px 2px 2px #ddd; padding: 1em; margin: 0; }' +
	'.wDiffNoChange { white-space: pre-wrap; background: #f0f0f0; border: #bbb solid; border-width: 1px 1px 1px 0.5em; border-radius: 0.5em; font-family: sans-serif; font-size: 88%; line-height: 1.6; box-shadow: 2px 2px 2px #ddd; padding: 0.5em; margin: 1em 0; }' +
	'.wDiffSeparator { margin-bottom: 1em; }' +
	'.wDiffOmittedChars { }' +

	// newline
	'.wDiffNewline:before { content: "¶"; color: transparent; }' +
	'.wDiffBlockHighlight .wDiffNewline:before { color: transparent; }' +
	'.wDiffBlockHighlight:hover .wDiffNewline:before { color: #ccc; }' +
	'.wDiffBlockHighlight:hover .wDiffInsert .wDiffNewline:before, .wDiffInsert:hover .wDiffNewline:before { color: #999; }' +
	'.wDiffBlockHighlight:hover .wDiffDelete .wDiffNewline:before, .wDiffDelete:hover .wDiffNewline:before { color: #aaa; }' +

	// tab
	'.wDiffTab { position: relative; }' +
	'.wDiffTabSymbol { position: absolute; top: -0.2em; }' +
	'.wDiffTabSymbol:before { content: "→"; font-size: smaller; color: transparent; color: #ccc; }' +
	'.wDiffBlockLeft .wDiffTabSymbol:before, .wDiffBlockRight .wDiffTabSymbol:before { color: #aaa; }' +
	'.wDiffBlockHighlight .wDiffTabSymbol:before { color: #aaa; }' +
	'.wDiffInsert .wDiffTabSymbol:before { color: #aaa; }' +
	'.wDiffDelete .wDiffTabSymbol:before { color: #bbb; }' +

	// space
	'.wDiffSpace { position: relative; }' +
	'.wDiffSpaceSymbol { position: absolute; top: -0.2em; left: -0.05em; }' +
	'.wDiffSpaceSymbol:before { content: "·"; color: transparent; }' +
	'.wDiffBlockHighlight .wDiffSpaceSymbol:before { color: transparent; }' +
	'.wDiffBlockHighlight:hover .wDiffSpaceSymbol:before { color: #ddd; }' +
	'.wDiffBlockHighlight:hover .wDiffInsert .wDiffSpaceSymbol:before, .wDiffInsert:hover .wDiffSpaceSymbol:before { color: #888; }' +
	'.wDiffBlockHighlight:hover .wDiffDelete .wDiffSpaceSymbol:before, .wDiffDelete:hover .wDiffSpaceSymbol:before { color: #999; }';
}

//
// css styles
//

if (wDiff.styleInsert === undefined) { wDiff.styleInsert = ''; }
if (wDiff.styleDelete === undefined) { wDiff.styleDelete = ''; }
if (wDiff.styleInsertBlank === undefined) { wDiff.styleInsertBlank = ''; }
if (wDiff.styleDeleteBlank === undefined) { wDiff.styleDeleteBlank = ''; }
if (wDiff.styleBlock === undefined) { wDiff.styleBlock = ''; }
if (wDiff.styleBlockLeft === undefined) { wDiff.styleBlockLeft = ''; }
if (wDiff.styleBlockRight === undefined) { wDiff.styleBlockRight = ''; }
if (wDiff.styleBlockHighlight === undefined) { wDiff.styleBlockHighlight = ''; }
if (wDiff.styleBlockColor === undefined) { wDiff.styleBlockColor = []; }
if (wDiff.styleMark === undefined) { wDiff.styleMark = ''; }
if (wDiff.styleMarkLeft === undefined) { wDiff.styleMarkLeft = ''; }
if (wDiff.styleMarkRight === undefined) { wDiff.styleMarkRight = ''; }
if (wDiff.styleMarkColor === undefined) { wDiff.styleMarkColor = []; }
if (wDiff.styleContainer === undefined) { wDiff.styleContainer = ''; }
if (wDiff.styleFragment === undefined) { wDiff.styleFragment = ''; }
if (wDiff.styleNoChange === undefined) { wDiff.styleNoChange = ''; }
if (wDiff.styleSeparator === undefined) { wDiff.styleSeparator = ''; }
if (wDiff.styleOmittedChars === undefined) { wDiff.styleOmittedChars = ''; }
if (wDiff.styleNewline === undefined) { wDiff.styleNewline = ''; }
if (wDiff.styleTab === undefined) { wDiff.styleTab = ''; }
if (wDiff.styleTabSymbol === undefined) { wDiff.styleTabSymbol = ''; }
if (wDiff.styleSpace === undefined) { wDiff.styleSpace = ''; }
if (wDiff.styleSpaceSymbol === undefined) { wDiff.styleSpaceSymbol = ''; }

//
// output html
//

// dynamic replacements: {block}: block number style, {mark}: mark number style, {class}: class number, {number}: block number, {title}: title attribute (popup)
// class plus html comment are required indicators for TextDiff.shortenOutput()

if (wDiff.blockEvent === undefined) {	wDiff.blockEvent = ' onmouseover="wDiff.blockHandler(undefined, this, \'mouseover\');"'; }

if (wDiff.htmlContainerStart === undefined) { wDiff.htmlContainerStart = '<div class="wDiffContainer" id="wDiffContainer" style="' + wDiff.styleContainer + '">'; }
if (wDiff.htmlContainerEnd === undefined) { wDiff.htmlContainerEnd = '</div>'; }

if (wDiff.htmlInsertStart === undefined) { wDiff.htmlInsertStart = '<span class="wDiffInsert" style="' + wDiff.styleInsert + '" title="+">'; }
if (wDiff.htmlInsertStartBlank === undefined) { wDiff.htmlInsertStartBlank = '<span class="wDiffInsert wDiffInsertBlank" style="' + wDiff.styleInsertBlank + '" title="+">'; }
if (wDiff.htmlInsertEnd === undefined) { wDiff.htmlInsertEnd = '</span><!--wDiffInsert-->'; }

if (wDiff.htmartlDeleteSt === undefined) { wDiff.htmlDeleteStart = '<span class="wDiffDelete" style="' + wDiff.styleDelete + '" title="−">'; }
if (wDiff.htmlDeleteStartBlank === undefined) { wDiff.htmlDeleteStartBlank = '<span class="wDiffDelete wDiffDeleteBlank" style="' + wDiff.styleDeleteBlank + '" title="−">'; }
if (wDiff.htmlDeleteEnd === undefined) { wDiff.htmlDeleteEnd = '</span><!--wDiffDelete-->'; }

if (wDiff.htmlBlockLeftStart === undefined) {
	if (wDiff.coloredBlocks === false) {
		wDiff.htmlBlockLeftStart = '<span class="wDiffBlockLeft" style="' + wDiff.styleBlockLeft + '" title="' + wDiff.symbolMarkLeft + '" id="wDiffBlock{number}"' + wDiff.blockEvent + '>';
	}
	else {
		wDiff.htmlBlockLeftStart = '<span class="wDiffBlockLeft wDiffBlock wDiffBlock{class}" style="' + wDiff.styleBlockLeft + wDiff.styleBlock + '{block}" title="' + wDiff.symbolMarkLeft + '" id="wDiffBlock{number}"' + wDiff.blockEvent + '>';
	}
}
if (wDiff.htmlBlockLeftEnd === undefined) { wDiff.htmlBlockLeftEnd = '</span><!--wDiffBlockLeft-->'; }

if (wDiff.htmlBlockRightStart === undefined) {
	if (wDiff.coloredBlocks === false) {
		wDiff.htmlBlockRightStart = '<span class="wDiffBlockRight" style="' + wDiff.styleBlockRight + '" title="' + wDiff.symbolMarkRight + '" id="wDiffBlock{number}"' + wDiff.blockEvent + '>';
	}
	else {
		wDiff.htmlBlockRightStart = '<span class="wDiffBlockRight wDiffBlock wDiffBlock{class}" style="' + wDiff.styleBlockRight + wDiff.styleBlock + '{block}" title="' + wDiff.symbolMarkRight + '" id="wDiffBlock{number}"' + wDiff.blockEvent + '>';
	}
}
if (wDiff.htmlBlockRightEnd === undefined) { wDiff.htmlBlockRightEnd = '</span><!--wDiffBlockRight-->'; }

if (wDiff.htmlMarkLeft === undefined) {
	if (wDiff.coloredBlocks === false) {
		wDiff.htmlMarkLeft = '<span class="wDiffMarkLeft" style="' + wDiff.styleMarkLeft + '"{title} id="wDiffMark{number}"' + wDiff.blockEvent + '></span><!--wDiffMarkLeft-->';
	}
	else {
		wDiff.htmlMarkLeft = '<span class="wDiffMarkLeft wDiffMark wDiffMark{class}" style="' + wDiff.styleMarkLeft + wDiff.styleMark + '{mark}"{title} id="wDiffMark{number}"' + wDiff.blockEvent + '></span><!--wDiffMarkLeft-->';
	}
}
if (wDiff.htmlMarkRight === undefined) {
	if (wDiff.coloredBlocks === false) {
		wDiff.htmlMarkRight = '<span class="wDiffMarkRight" style="' + wDiff.styleMarkRight + '"{title} id="wDiffMark{number}"' + wDiff.blockEvent + '></span><!--wDiffMarkRight-->';
	}
	else {
		wDiff.htmlMarkRight = '<span class="wDiffMarkRight wDiffMark wDiffMark{class}" style="' + wDiff.styleMarkRight + wDiff.styleMark + '{mark}"{title} id="wDiffMark{number}"' + wDiff.blockEvent + '></span><!--wDiffMarkRight-->';
	}
}

if (wDiff.htmlNewline === undefined) { wDiff.htmlNewline = '<span class="wDiffNewline" style="' + wDiff.styleNewline + '">\n</span>'; }
if (wDiff.htmlTab === undefined) { wDiff.htmlTab = '<span class="wDiffTab" style="' + wDiff.styleTab + '"><span class="wDiffTabSymbol" style="' + wDiff.styleTabSymbol + '"></span>\t</span>'; }
if (wDiff.htmlSpace === undefined) { wDiff.htmlSpace = '<span class="wDiffSpace" style="' + wDiff.styleSpace + '"><span class="wDiffSpaceSymbol" style="' + wDiff.styleSpaceSymbol + '"></span> </span>'; }

// shorten output

if (wDiff.htmlFragmentStart === undefined) { wDiff.htmlFragmentStart = '<pre class="wDiffFragment" style="' + wDiff.styleFragment + '">'; }
if (wDiff.htmlFragmentEnd === undefined) { wDiff.htmlFragmentEnd = '</pre>'; }

if (wDiff.htmlNoChange === undefined) { wDiff.htmlNoChange = '<pre class="wDiffNoChange" style="' + wDiff.styleNoChange + '" title="="></pre>'; }
if (wDiff.htmlSeparator === undefined) { wDiff.htmlSeparator = '<div class="wDiffSeparator" style="' + wDiff.styleSeparator + '"></div>'; }
if (wDiff.htmlOmittedChars === undefined) { wDiff.htmlOmittedChars = '<span class="wDiffOmittedChars" style="' + wDiff.styleOmittedChars + '">…</span>'; }

//
// javascript handler for output code, IE 8 compatible
//

// wDiff.blockHandler: event handler for block and mark elements
if (wDiff.blockHandler === undefined) {	wDiff.blockHandler = function (event, element, type) {

	// IE compatibility
	if ( (event === undefined) && (window.event !== undefined) ) {
		event = window.event;
	}

	// get mark/block elements
	var number = element.id.replace(/\D/g, '');
	var block = document.getElementById('wDiffBlock' + number);
	var mark = document.getElementById('wDiffMark' + number);

	// highlight corresponding mark/block pairs
	if (type == 'mouseover') {
		element.onmouseover = null;
		element.onmouseout = function (event) { wDiff.blockHandler(event, element, 'mouseout'); };
		element.onclick = function (event) { wDiff.blockHandler(event, element, 'click'); };
		block.className += ' wDiffBlockHighlight';
		mark.className += ' wDiffMarkHighlight';
	}

	// remove mark/block highlighting
	if ( (type == 'mouseout') || (type == 'click') ) {
		element.onmouseout = null;
		element.onmouseover = function (event) { wDiff.blockHandler(event, element, 'mouseover'); };

		// getElementsByClassName
		var container = document.getElementById('wDiffContainer');
		var spans = container.getElementsByTagName('span');
		for (var i = 0; i < spans.length; i ++) {
			if ( ( (spans[i] != block) && (spans[i] != mark) ) || (type != 'click') ) {
				if (spans[i].className.indexOf(' wDiffBlockHighlight') != -1) {
					spans[i].className = spans[i].className.replace(/ wDiffBlockHighlight/g, '');
				}
				else if (spans[i].className.indexOf(' wDiffMarkHighlight') != -1) {
					spans[i].className = spans[i].className.replace(/ wDiffMarkHighlight/g, '');
				}
			}
		}
	}

	// scroll to corresponding mark/block element
	if (type == 'click') {

		// get corresponding element
		var corrElement;
		if (element == block) {
			corrElement = mark;
		}
		else {
			corrElement = block;
		}

		// get element height (getOffsetTop)
		var corrElementPos = 0;
		var node = corrElement;
		do {
			corrElementPos += node.offsetTop;
		} while ( (node = node.offsetParent) !== null );

		// get scroll height
		var top;
		if (window.pageYOffset !== undefined) {
			top = window.pageYOffset;
		}
		else {
			top = document.documentElement.scrollTop;
		}

		// get cursor pos
		var cursor;
		if (event.pageY !== undefined) {
			cursor = event.pageY;
		}
		else if (event.clientY !== undefined) {
			cursor = event.clientY + top;
		}

		// get line height
		var line = 12;
		if (window.getComputedStyle !== undefined) {
			line = parseInt(window.getComputedStyle(corrElement).getPropertyValue('line-height'));
		}

		// scroll element under mouse cursor
		window.scroll(0, corrElementPos + top - cursor + line / 2);
	}
	return;
}; }


//
// end of configuration and customization settings
//


// wDiff.init(): initialize wDiff
//   called from: on code load
//   calls: .addStyleSheet(), .addScript()

wDiff.init = function () {

	// legacy for short time
	wDiff.Diff = wDiff.diff;

	// add styles to head
	wDiff.addStyleSheet(wDiff.stylesheet);

	// add block handler to head if running under Greasemonkey
	if (typeof GM_info == 'object') {
		var script = 'var wDiff; if (wDiff === undefined) { wDiff = {}; } wDiff.blockHandler = ' + wDiff.blockHandler.toString();
		wDiff.addScript(script);
	}
	return;
};


// wDiff.diff(): main method of wDiff, runs the diff and shortens the output
//   called from: user land
//   calls: new TextDiff, TextDiff.shortenOutput(), this.unitTests()

wDiff.diff = function (oldString, newString, full) {

	// create text diff object
	var textDiff = new wDiff.TextDiff(oldString, newString, this);

	// legacy for short time
	wDiff.textDiff = textDiff;
	wDiff.ShortenOutput = wDiff.textDiff.shortenOutput;

	// start timer
	if (wDiff.debugTime === true) {
		console.time('diff');
	}

	// run the diff
	textDiff.diff();

	// start timer
	if (wDiff.debugTime === true) {
		console.timeEnd('diff');
	}

	// shorten output
	if (full !== true) {

		// start timer
		if (wDiff.debugTime === true) {
			console.time('shorten');
		}

		textDiff.shortenOutput();

		// stop timer
		if (wDiff.debugTime === true) {
			console.timeEnd('shorten');
		}
	}

	// stop timer
	if (wDiff.debugTime === true) {
		console.timeEnd('diff');
	}

	// run unit tests
	if (wDiff.unitTesting === true) {
		wDiff.unitTests(textDiff);
	}
	return textDiff.html;
};


// wDiff.unitTests(): test diff for consistency between input and output
//   input: textDiff: text diff object after calling .diff()
//   called from: .diff()

wDiff.unitTests = function (textDiff) {

	// start timer
	if (wDiff.debugTime === true) {
		console.time('unit tests');
	}

	var html = textDiff.html;

	// check if output is consistent with new text
	textDiff.assembleDiff('new');
	var diff = textDiff.html.replace(/<[^>]*>/g, '');
	var text = textDiff.htmlEscape(textDiff.newText.string);
	if (diff != text) {
		console.log('Error: wDiff unit test failure: output not consistent with new text');
		console.log('new text:\n', text);
		console.log('new diff:\n', diff);
	}
	else {
		console.log('OK: wDiff unit test passed: output consistent with new text');
	}

	// check if output is consistent with old text
	textDiff.assembleDiff('old');
	var diff = textDiff.html.replace(/<[^>]*>/g, '');
	var text = textDiff.htmlEscape(textDiff.oldText.string);
	if (diff != text) {
		console.log('Error: wDiff unit test failure: output not consistent with old text');
		console.log('old text:\n', text);
		console.log('old diff:\n', diff);
	}
	else {
		console.log('OK: wDiff unit test passed: output consistent with old text');
	}

	textDiff.html = html;

	// stop timer
	if (wDiff.debugTime === true) {
		console.timeEnd('unit tests');
	}
	return;
};


//
// wDiff.Text class: data and methods for single text version (old or new)
//   called from: TextDiff.init()
//

wDiff.Text = function (string, parent) {

	this.parent = parent;
	this.string = null;
	this.tokens = [];
	this.first = null;
	this.last = null;
	this.words = {};


	//
	// Text.init(): initialize text object
	//

	this.init = function () {

		if (typeof string != 'string') {
			string = string.toString();
		}

		// IE / Mac fix
		this.string = string.replace(/\r\n?/g, '\n');

		this.wordParse(wDiff.regExpWord);
		this.wordParse(wDiff.regExpChunk);
		return;
	};


	// Text.wordParse(): parse and count words and chunks for identification of unique words
	//   called from: .init()
	//   changes: .words

	this.wordParse = function (regExp) {

		var regExpMatch;
		while ( (regExpMatch = regExp.exec(this.string)) !== null) {
			var word = regExpMatch[0];
			if (this.words[word] === undefined) {
				this.words[word] = 1;
			}
			else {
				this.words[word] ++;
			}
		}
		return;
	};


	// Text.split(): split text into paragraph, sentence, or word tokens
	//   input: regExp, regular expression for splitting text into tokens; token, tokens index of token to be split
	//   called from: TextDiff.diff(), .splitRefine()
	//   changes: .tokens list, .first, .last

	this.split = function (level, token) {

		var prev = null;
		var next = null;
		var current = this.tokens.length;
		var first = current;
		var string = '';

		// split full text or specified token
		if (token === undefined) {
			string = this.string;
		}
		else {
			prev = this.tokens[token].prev;
			next = this.tokens[token].next;
			string = this.tokens[token].token;
		}

		// split text into tokens, regExp match as separator
		var number = 0;
		var split = [];
		var regExpMatch;
		var lastIndex = 0;
		while ( (regExpMatch = wDiff.regExpSplit[level].exec(string)) !== null) {
			if (regExpMatch.index > lastIndex) {
				split.push(string.substring(lastIndex, regExpMatch.index));
			}
			split.push(regExpMatch[0]);
			lastIndex = wDiff.regExpSplit[level].lastIndex;
		}
		if (lastIndex < string.length) {
			split.push(string.substring(lastIndex));
		}

		// cycle trough new tokens
		for (var i = 0; i < split.length; i ++) {

		// insert current item, link to previous
			this.tokens[current] = {
				token:   split[i],
				prev:    prev,
				next:    null,
				link:    null,
				number:  null,
				unique:  false
			};
			number ++;

			// link previous item to current
			if (prev !== null) {
				this.tokens[prev].next = current;
			}
			prev = current;
			current ++;
		}

		// connect last new item and existing next item
		if ( (number > 0) && (token !== undefined) ) {
			if (prev !== null) {
				this.tokens[prev].next = next;
			}
			if (next !== null) {
				this.tokens[next].prev = prev;
			}
		}

		// set text first and last token index
		if (number > 0) {

			// initial text split
			if (token === undefined) {
				this.first = 0;
				this.last = prev;
			}

			// first or last token has been split
			else {
				if (token == this.first) {
					this.first = first;
				}
				if (token == this.last) {
					this.last = prev;
				}
			}
		}
		return;
	};


	// Text.splitRefine(): split unique unmatched tokens into smaller tokens
	//   changes: text (text.newText or text.oldText) .tokens list
	//   called from: TextDiff.diff()
	//   calls: .split()

	this.splitRefine = function (regExp) {

		// cycle through tokens list
		var i = this.first;
		while ( (i !== null) && (this.tokens[i] !== null) ) {

			// refine unique unmatched tokens into smaller tokens
			if (this.tokens[i].link === null) {
				this.split(regExp, i);
			}
			i = this.tokens[i].next;
		}
		return;
	};


	// Text.enumerateTokens(): enumerate text token list
	//   called from: TextDiff.diff()
	//   changes: .tokens list

	this.enumerateTokens = function () {

		// enumerate tokens list
		var number = 0;
		var i = this.first;
		while ( (i !== null) && (this.tokens[i] !== null) ) {
			this.tokens[i].number = number;
			number ++;
			i = this.tokens[i].next;
		}
		return;
	};


	// Text.debugText(): dump text object for debugging
	//   input: text: title

	this.debugText = function (text) {

		var dump = 'first: ' + this.first + '\tlast: ' + this.last + '\n';
		dump += '\ni \tlink \t(prev \tnext) \tuniq \t#num \t"token"\n';
		var i = this.first;
		while ( (i !== null) && (this.tokens[i] !== null) ) {
			dump += i + ' \t' + this.tokens[i].link + ' \t(' + this.tokens[i].prev + ' \t' + this.tokens[i].next + ') \t' + this.tokens[i].unique + ' \t#' + this.tokens[i].number + ' \t' + parent.debugShortenString(this.tokens[i].token) + '\n';
			i = this.tokens[i].next;
		}
		console.log(text + ':\n' + dump);
		return;
	};


	// initialize text object
	this.init();
};


//
// wDiff.TextDiff class: main wDiff class, includes all data structures and methods required for a diff
//   called from: wDiff.diff()
//

wDiff.TextDiff = function (oldString, newString) {

	this.newText = null;
	this.oldText = null;
	this.blocks = [];
	this.groups = [];
	this.sections = [];
	this.html = '';


	//
	// TextDiff.init(): initialize diff object
	//

	this.init = function () {

		this.newText = new wDiff.Text(newString, this);
		this.oldText = new wDiff.Text(oldString, this);
		return;
	};


	// TextDiff.diff(): main method
	//   input: version: 'new', 'old', show only one marked-up version, .oldString, .newString
	//   called from: wDiff.diff()
	//   calls: Text.split(), Text.splitRefine(), .calculateDiff(), .slideGaps(), .enumerateTokens(), .detectBlocks(), .assembleDiff()
	//   changes: .html

	this.diff = function (version) {

		// trap trivial changes: no change
		if (this.newText.string == this.oldText.string) {
			return;
		}

		// trap trivial changes: old text deleted
		if ( (this.oldText.string === '') || ( (this.oldText.string == '\n') && (this.newText.string.charAt(this.newText.string.length - 1) == '\n') ) ) {
			this.html = wDiff.htmlInsertStart + this.htmlEscape(this.newText.string) + wDiff.htmlInsertEnd;
			return;
		}

		// trap trivial changes: new text deleted
		if ( (this.newText.string === '') || ( (this.newText.string == '\n') && (this.oldText.string.charAt(this.oldText.string.length - 1) == '\n') ) ) {
			this.html = wDiff.htmlDeleteStart + this.htmlEscape(this.oldText.string) + wDiff.htmlDeleteEnd;
			return;
		}

		// new symbols object
		var symbols = {
			token:  [],
			hash:   {},
			linked: false
		};

		// split new and old text into paragraps
		this.newText.split('paragraph');
		this.oldText.split('paragraph');

		// calculate diff
		this.calculateDiff(symbols, 'paragraph');

		// refine different paragraphs into sentences
		this.newText.splitRefine('sentence');
		this.oldText.splitRefine('sentence');

		// calculate refined diff
		this.calculateDiff(symbols, 'sentence');

		// refine different paragraphs into chunks
		this.newText.splitRefine('chunk');
		this.oldText.splitRefine('chunk');

		// calculate refined diff
		this.calculateDiff(symbols, 'chunk');

		// refine different sentences into words
		this.newText.splitRefine('word');
		this.oldText.splitRefine('word');

		// calculate refined diff information with recursion for unresolved gaps
		this.calculateDiff(symbols, 'word', true);

		// slide gaps
		this.slideGaps(this.newText, this.oldText);
		this.slideGaps(this.oldText, this.newText);

		// split tokens into chars in selected unresolved gaps
		if (wDiff.charDiff === true) {
			this.splitRefineChars();

			// calculate refined diff information with recursion for unresolved gaps
			this.calculateDiff(symbols, 'character', true);

			// slide gaps
			this.slideGaps(this.newText, this.oldText);
			this.slideGaps(this.oldText, this.newText);
		}

		// enumerate token lists
		this.newText.enumerateTokens();
		this.oldText.enumerateTokens();

		// detect moved blocks
		this.detectBlocks();

		// assemble diff blocks into formatted html
		this.assembleDiff(version);

		if (wDiff.debug === true) {
			console.log('HTML:\n', this.html);
		}
		return;
	};


	// TextDiff.splitRefineChars(): split tokens into chars in the following unresolved regions (gaps):
	//   - one token became connected or separated by space or dash (or any token)
	//   - same number of tokens in gap and strong similarity of all tokens:
	//     - addition or deletion of flanking strings in tokens
	//     - addition or deletion of internal string in tokens
	//     - same length and at least 50 % identity
	//     - same start or end, same text longer than different text
	//     - same length and at least 50 % identity
	//   identical tokens including space separators will be linked, resulting in word-wise char-level diffs
	//   changes: text (text.newText or text.oldText) .tokens list
	//   called from: .diff()
	//   calls: Text.split()
	//   steps:
	//     find corresponding gaps
	//     select gaps of identical token number and strong similarity in all tokens
	//     refine words into chars in selected gaps

	this.splitRefineChars = function () {

		//
		// find corresponding gaps
		//

		// cycle trough new text tokens list
		var gaps = [];
		var gap = null;
		var i = this.newText.first;
		var j = this.oldText.first;
		while ( (i !== null) && (this.newText.tokens[i] !== null) ) {

			// get token links
			var newLink = this.newText.tokens[i].link;
			var oldLink = null;
			if (j !== null) {
				oldLink = this.oldText.tokens[j].link;
			}

			// start of gap in new and old
			if ( (gap === null) && (newLink === null) && (oldLink === null) ) {
				gap = gaps.length;
				gaps.push({
					newFirst:  i,
					newLast:   i,
					newTokens: 1,
					oldFirst:  j,
					oldLast:   j,
					oldTokens: null,
					charSplit: null
				});
			}

			// count chars and tokens in gap
			else if ( (gap !== null) && (newLink === null) ) {
				gaps[gap].newLast = i;
				gaps[gap].newTokens ++;
			}

			// gap ended
			else if ( (gap !== null) && (newLink !== null) ) {
				gap = null;
			}

			// next list elements
			if (newLink !== null) {
				j = this.oldText.tokens[newLink].next;
			}
			i = this.newText.tokens[i].next;
		}

		// cycle trough gaps and add old text gap data
		for (var gap = 0; gap < gaps.length; gap ++) {

			// cycle trough old text tokens list
			var j = gaps[gap].oldFirst;
			while ( (j !== null) && (this.oldText.tokens[j] !== null) && (this.oldText.tokens[j].link === null) ) {

				// count old chars and tokens in gap
				gaps[gap].oldLast = j;
				gaps[gap].oldTokens ++;

				j = this.oldText.tokens[j].next;
			}
		}

		//
		// select gaps of identical token number and strong similarity of all tokens
		//

		for (var gap = 0; gap < gaps.length; gap ++) {
			var charSplit = true;

			// not same gap length
			if (gaps[gap].newTokens != gaps[gap].oldTokens) {

				// one word became separated by space, dash, or any string
				if ( (gaps[gap].newTokens == 1) && (gaps[gap].oldTokens == 3) ) {
					var token = this.newText.tokens[ gaps[gap].newFirst ].token;
					var tokenFirst = this.oldText.tokens[ gaps[gap].oldFirst ].token;
					var tokenLast = this.oldText.tokens[ gaps[gap].oldLast ].token;
					if ( (token.indexOf(tokenFirst) !== 0) || (token.indexOf(tokenLast) != token.length - tokenLast.length) ) {
						continue;
					}
				}
				else if ( (gaps[gap].oldTokens == 1) && (gaps[gap].newTokens == 3) ) {
					var token = this.oldText.tokens[ gaps[gap].oldFirst ].token;
					var tokenFirst = this.newText.tokens[ gaps[gap].newFirst ].token;
					var tokenLast = this.newText.tokens[ gaps[gap].newLast ].token;
					if ( (token.indexOf(tokenFirst) !== 0) || (token.indexOf(tokenLast) != token.length - tokenLast.length) ) {
						continue;
					}
				}
				else {
					continue;
				}
				gaps[gap].charSplit = true;
			}

			// cycle trough new text tokens list and set charSplit
			else {
				var i = gaps[gap].newFirst;
				var j = gaps[gap].oldFirst;
				while (i !== null) {
					var newToken = this.newText.tokens[i].token;
					var oldToken = this.oldText.tokens[j].token;

					// get shorter and longer token
					var shorterToken;
					var longerToken;
					if (newToken.length < oldToken.length) {
						shorterToken = newToken;
						longerToken = oldToken;
					}
					else {
						shorterToken = oldToken;
						longerToken = newToken;
					}

					// not same token length
					if (newToken.length != oldToken.length) {

						// test for addition or deletion of internal string in tokens

						// find number of identical chars from left
						var left = 0;
						while (left < shorterToken.length) {
							if (newToken.charAt(left) != oldToken.charAt(left)) {
								break;
							}
							left ++;
						}

						// find number of identical chars from right
						var right = 0;
						while (right < shorterToken.length) {
							if (newToken.charAt(newToken.length - 1 - right) != oldToken.charAt(oldToken.length - 1 - right)) {
								break;
							}
							right ++;
						}

						// no simple insertion or deletion of internal string
						if (left + right != shorterToken.length) {

							// not addition or deletion of flanking strings in tokens (smaller token not part of larger token)
							if (longerToken.indexOf(shorterToken) == -1) {

								// same text at start or end shorter than different text
								if ( (left < shorterToken.length / 2) && (right < shorterToken.length / 2) ) {

									// do not split into chars this gap
									charSplit = false;
									break;
								}
							}
						}
					}

					// same token length
					else if (newToken != oldToken) {

						// tokens less than 50 % identical
						var ident = 0;
						for (var pos = 0; pos < shorterToken.length; pos ++) {
							if (shorterToken.charAt(pos) == longerToken.charAt(pos)) {
								ident ++;
							}
						}
						if (ident/shorterToken.length < 0.49) {

							// do not split into chars this gap
							charSplit = false;
							break;
						}
					}

					// next list elements
					if (i == gaps[gap].newLast) {
						break;
					}
					i = this.newText.tokens[i].next;
					j = this.oldText.tokens[j].next;
				}
				gaps[gap].charSplit = charSplit;
			}
		}

		//
		// refine words into chars in selected gaps
		//

		for (var gap = 0; gap < gaps.length; gap ++) {
			if (gaps[gap].charSplit === true) {

				// cycle trough new text tokens list, link spaces, and split into chars
				var i = gaps[gap].newFirst;
				var j = gaps[gap].oldFirst;
				var newGapLength = i - gaps[gap].newLast;
				var oldGapLength = j - gaps[gap].oldLast;
				while ( (i !== null) || (j !== null) ) {

					// link identical tokens (spaces) to keep char refinement to words
					if ( (newGapLength == oldGapLength) && (this.newText.tokens[i].token == this.oldText.tokens[j].token) ) {
						this.newText.tokens[i].link = j;
						this.oldText.tokens[j].link = i;
					}

					// refine words into chars
					else {
						if (i !== null) {
							this.newText.split('character', i);
						}
						if (j !== null) {
							this.oldText.split('character', j);
						}
					}

					// next list elements
					if (i == gaps[gap].newLast) {
						i = null;
					}
					if (j == gaps[gap].oldLast) {
						j = null;
					}
					if (i !== null) {
						i = this.newText.tokens[i].next;
					}
					if (j !== null) {
						j = this.oldText.tokens[j].next;
					}
				}
			}
		}
		return;
	};


	// TextDiff.slideGaps(): move gaps with ambiguous identical fronts to last newline or, if absent, last word border
	//   called from: .diff(), .detectBlocks()
	//   changes: .newText/.oldText .tokens list

	this.slideGaps = function (text, textLinked) {

		// cycle through tokens list
		var i = text.first;
		var gapStart = null;
		while ( (i !== null) && (text.tokens[i] !== null) ) {

			// remember gap start
			if ( (gapStart === null) && (text.tokens[i].link === null) ) {
				gapStart = i;
			}

			// find gap end
			else if ( (gapStart !== null) && (text.tokens[i].link !== null) ) {
				var gapFront = gapStart;
				var gapBack = text.tokens[i].prev;

				// slide down as deep as possible
				var front = gapFront;
				var back = text.tokens[gapBack].next;
				if (
					(front !== null) && (back !== null) &&
					(text.tokens[front].link === null) && (text.tokens[back].link !== null) &&
					(text.tokens[front].token === text.tokens[back].token)
				) {
					text.tokens[front].link = text.tokens[back].link;
					textLinked.tokens[ text.tokens[front].link ].link = front;
					text.tokens[back].link = null;

					gapFront = text.tokens[gapFront].next;
					gapBack = text.tokens[gapBack].next;

					front = text.tokens[front].next;
					back = text.tokens[back].next;
				}

				// test slide up, remember last line break or word border
				var	front = text.tokens[gapFront].prev;
				var	back = gapBack;
				var frontStop = null;
				while (
					(front !== null) && (back !== null) &&
					(text.tokens[front].link !== null) && (text.tokens[back].link === null) &&
					(text.tokens[front].token == text.tokens[back].token)
				) {

					// stop at line break
					if (wDiff.regExpSlideStop.test(text.tokens[front].token) === true) {
						frontStop = front;
						break;
					}

					// stop at first space/word break
					else if ( (frontStop === null) && (wDiff.regExpSlideBorder.test(text.tokens[front].token) === true) ) {
						frontStop = front;
					}
					front = text.tokens[front].prev;
					back = text.tokens[back].prev;
				}

				// actually slide up to stop
				var	front = text.tokens[gapFront].prev;
				var	back = gapBack;
				while (
					(front !== null) && (back !== null) && (front !== frontStop) &&
					(text.tokens[front].link !== null) && (text.tokens[back].link === null) &&
					(text.tokens[front].token == text.tokens[back].token)
				) {
					text.tokens[back].link = text.tokens[front].link;
					textLinked.tokens[ text.tokens[back].link ].link = back;
					text.tokens[front].link = null;

					front = text.tokens[front].prev;
					back = text.tokens[back].prev;
				}
				gapStart = null;
			}
			i = text.tokens[i].next;
		}
		return;
	};


	// TextDiff.calculateDiff(): calculate diff information, can be called repeatedly during refining
	//   input: level: 'paragraph', 'sentence', 'chunk', 'word', or 'character'
	//     optionally for recursive calls: recurse, newStart, newEnd, oldStart, oldEnd (tokens list indexes), recursionLevel
	//   called from: .diff()
	//   calls: itself recursively
	//   changes: .oldText/.newText.tokens[].link, links corresponding tokens from old and new text
	//   steps:
	//     pass 1: parse new text into symbol table
	//     pass 2: parse old text into symbol table
	//     pass 3: connect unique matched tokens
	//     pass 4: connect adjacent identical tokens downwards
	//     pass 5: connect adjacent identical tokens upwards
	//     recursively diff still unresolved regions downwards
	//     recursively diff still unresolved regions upwards

	this.calculateDiff = function (symbols, level, recurse, newStart, newEnd, oldStart, oldEnd, recursionLevel) {

		// start timer
		if ( (wDiff.debugTime === true) && (recursionLevel === undefined) ) {
			console.time(level);
		}

		// set defaults
		if (newStart === undefined) { newStart = this.newText.first; }
		if (newEnd === undefined) { newEnd = this.newText.last; }
		if (oldStart === undefined) { oldStart = this.oldText.first; }
		if (oldEnd === undefined) { oldEnd = this.oldText.last; }
		if (recursionLevel === undefined) { recursionLevel = 0; }

		// limit recursion depth
		if (recursionLevel > 10) {
			return;
		}

		//
		// pass 1: parse new text into symbol table
		//

		// cycle trough new text tokens list
		var i = newStart;
		while ( (i !== null) && (this.newText.tokens[i] !== null) ) {

			// add new entry to symbol table
			var token = this.newText.tokens[i].token;
			if (Object.prototype.hasOwnProperty.call(symbols.hash, token) === false) {
				var current = symbols.token.length;
				symbols.hash[token] = current;
				symbols.token[current] = {
					newCount: 1,
					oldCount: 0,
					newToken: i,
					oldToken: null
				};
			}

			// or update existing entry
			else {

				// increment token counter for new text
				var hashToArray = symbols.hash[token];
				symbols.token[hashToArray].newCount ++;
			}

			// next list element
			if (i == newEnd) {
				break;
			}
			i = this.newText.tokens[i].next;
		}

		//
		// pass 2: parse old text into symbol table
		//

		// cycle trough old text tokens list
		var j = oldStart;
		while ( (j !== null) && (this.oldText.tokens[j] !== null) ) {

			// add new entry to symbol table
			var token = this.oldText.tokens[j].token;
			if (Object.prototype.hasOwnProperty.call(symbols.hash, token) === false) {
				var current = symbols.token.length;
				symbols.hash[token] = current;
				symbols.token[current] = {
					newCount: 0,
					oldCount: 1,
					newToken: null,
					oldToken: j
				};
			}

			// or update existing entry
			else {

				// increment token counter for old text
				var hashToArray = symbols.hash[token];
				symbols.token[hashToArray].oldCount ++;

				// add token number for old text
				symbols.token[hashToArray].oldToken = j;
			}

			// next list element
			if (j === oldEnd) {
				break;
			}
			j = this.oldText.tokens[j].next;
		}

		//
		// pass 3: connect unique tokens
		//

		// cycle trough symbol array
		for (var i = 0; i < symbols.token.length; i ++) {

			// find tokens in the symbol table that occur only once in both versions
			if ( (symbols.token[i].newCount == 1) && (symbols.token[i].oldCount == 1) ) {
				var newToken = symbols.token[i].newToken;
				var oldToken = symbols.token[i].oldToken;

				// do not use spaces as unique markers
				if (/^\s+$/.test(this.newText.tokens[newToken].token) === false) {

					// connect from new to old and from old to new
					if (this.newText.tokens[newToken].link === null) {
						this.newText.tokens[newToken].link = oldToken;
						this.oldText.tokens[oldToken].link = newToken;
						symbols.linked = true;

						// check if token contains unique word
						if (recursionLevel === 0) {
							var unique = false;
							if (level == 'character') {
								unique = true;
							}
							else {
								var token = this.newText.tokens[newToken].token;
								var words = (token.match(wDiff.regExpWord) || []).concat(token.match(wDiff.regExpChunk) || []);

								// unique if longer than min block length
								if (words.length >= wDiff.blockMinLength) {
									unique = true;
								}

								// unique if it contains at least one unique word
								else {
									for (var word = 0; word < words.length; word ++) {
										if ( (this.oldText.words[ words[word] ] == 1) && (this.newText.words[ words[word] ] == 1) ) {
											unique = true;
											break;
										}
									}
								}
							}

							// set unique
							if (unique === true) {
								this.newText.tokens[newToken].unique = true;
								this.oldText.tokens[oldToken].unique = true;
							}
						}
					}
				}
			}
		}

		// continue passes only if unique tokens have been linked previously
		if (symbols.linked === true) {

			//
			// pass 4: connect adjacent identical tokens downwards
			//

			// get surrounding connected tokens
			var i = newStart;
			if (this.newText.tokens[i].prev !== null) {
				i = this.newText.tokens[i].prev;
			}
			var iStop = newEnd;
			if (this.newText.tokens[iStop].next !== null) {
				iStop = this.newText.tokens[iStop].next;
			}
			var j = null;

			// cycle trough new text tokens list down
			do {

				// connected pair
				var link = this.newText.tokens[i].link;
				if (link !== null) {
					j = this.oldText.tokens[link].next;
				}

				// connect if tokens are the same
				else if ( (j !== null) && (this.oldText.tokens[j].link === null) && (this.newText.tokens[i].token == this.oldText.tokens[j].token) ) {
					this.newText.tokens[i].link = j;
					this.oldText.tokens[j].link = i;
					j = this.oldText.tokens[j].next;
				}

				// not same
				else {
					j = null;
				}
				i = this.newText.tokens[i].next;
			} while (i !== iStop);

			//
			// pass 5: connect adjacent identical tokens upwards
			//

			// get surrounding connected tokens
			var i = newEnd;
			if (this.newText.tokens[i].next !== null) {
				i = this.newText.tokens[i].next;
			}
			var iStop = newStart;
			if (this.newText.tokens[iStop].prev !== null) {
				iStop = this.newText.tokens[iStop].prev;
			}
			var j = null;

			// cycle trough new text tokens list up
			do {

				// connected pair
				var link = this.newText.tokens[i].link;
				if (link !== null) {
					j = this.oldText.tokens[link].prev;
				}

				// connect if tokens are the same
				else if ( (j !== null) && (this.oldText.tokens[j].link === null) && (this.newText.tokens[i].token == this.oldText.tokens[j].token) ) {
					this.newText.tokens[i].link = j;
					this.oldText.tokens[j].link = i;
					j = this.oldText.tokens[j].prev;
				}

				// not same
				else {
					j = null;
				}
				i = this.newText.tokens[i].prev;
			} while (i !== iStop);

			//
			// connect adjacent identical tokens downwards from text start, treat boundary as connected, stop after first connected token
			//

			// only for full text diff
			if ( (newStart == this.newText.first) && (newEnd == this.newText.last) ) {

				// from start
				var i = this.newText.first;
				var j = this.oldText.first;

				// cycle trough new text tokens list down, connect identical tokens, stop after first connected token
				while ( (i !== null) && (j !== null) && (this.newText.tokens[i].link === null) && (this.oldText.tokens[j].link === null) && (this.newText.tokens[i].token == this.oldText.tokens[j].token) ) {
					this.newText.tokens[i].link = j;
					this.oldText.tokens[j].link = i;
					j = this.oldText.tokens[j].next;
					i = this.newText.tokens[i].next;
				}

				// from end
				var i = this.newText.last;
				var j = this.oldText.last;

				// cycle trough old text tokens list up, connect identical tokens, stop after first connected token
				while ( (i !== null) && (j !== null) && (this.newText.tokens[i].link === null) && (this.oldText.tokens[j].link === null) && (this.newText.tokens[i].token == this.oldText.tokens[j].token) ) {
					this.newText.tokens[i].link = j;
					this.oldText.tokens[j].link = i;
					j = this.oldText.tokens[j].prev;
					i = this.newText.tokens[i].prev;
				}
			}

			//
			// refine by recursively diffing unresolved regions caused by addition of common tokens around sequences of common tokens, only at word level split
			//

			if ( (recurse === true) && (wDiff.recursiveDiff === true) ) {

				//
				// recursively diff still unresolved regions downwards
				//

				// cycle trough new text tokens list
				var i = newStart;
				var j = oldStart;

				while (	(i !== null) && (this.newText.tokens[i] !== null) ) {

					// get j from previous tokens match
					var iPrev = this.newText.tokens[i].prev;
					if (iPrev !== null) {
						var jPrev = this.newText.tokens[iPrev].link;
						if (jPrev !== null) {
							j = this.oldText.tokens[jPrev].next;
						}
					}

					// check for the start of an unresolved sequence
					if ( (j !== null) && (this.oldText.tokens[j] !== null) && (this.newText.tokens[i].link === null) && (this.oldText.tokens[j].link === null) ) {

						// determine the limits of the unresolved new sequence
						var iStart = i;
						var iEnd = null;
						var iLength = 0;
						var iNext = i;
						while ( (iNext !== null) && (this.newText.tokens[iNext].link === null) ) {
							iEnd = iNext;
							iLength ++;
							if (iEnd == newEnd) {
								break;
							}
							iNext = this.newText.tokens[iNext].next;
						}

						// determine the limits of the unresolved old sequence
						var jStart = j;
						var jEnd = null;
						var jLength = 0;
						var jNext = j;
						while ( (jNext !== null) && (this.oldText.tokens[jNext].link === null) ) {
							jEnd = jNext;
							jLength ++;
							if (jEnd == oldEnd) {
								break;
							}
							jNext = this.oldText.tokens[jNext].next;
						}

						// recursively diff the unresolved sequence
						if ( (iLength > 1) || (jLength > 1) ) {

							// new symbols object for sub-region
							var symbolsRecurse = {
								token:  [],
								hash:   {},
								linked: false
							};
							this.calculateDiff(symbolsRecurse, level, true, iStart, iEnd, jStart, jEnd, recursionLevel + 1);
						}
						i = iEnd;
					}

					// next list element
					if (i == newEnd) {
						break;
					}
					i = this.newText.tokens[i].next;
				}

				//
				// recursively diff still unresolved regions upwards
				//

				// cycle trough new text tokens list
				var i = newEnd;
				var j = oldEnd;
				while (	(i !== null) && (this.newText.tokens[i] !== null) ) {

					// get j from next matched tokens
					var iPrev = this.newText.tokens[i].next;
					if (iPrev !== null) {
						var jPrev = this.newText.tokens[iPrev].link;
						if (jPrev !== null) {
							j = this.oldText.tokens[jPrev].prev;
						}
					}

					// check for the start of an unresolved sequence
					if ( (j !== null) && (this.oldText.tokens[j] !== null) && (this.newText.tokens[i].link === null) && (this.oldText.tokens[j].link === null) ) {

						// determine the limits of the unresolved new sequence
						var iStart = null;
						var iEnd = i;
						var iLength = 0;
						var iNext = i;
						while ( (iNext !== null) && (this.newText.tokens[iNext].link === null) ) {
							iStart = iNext;
							iLength ++;
							if (iStart == newStart) {
								break;
							}
							iNext = this.newText.tokens[iNext].prev;
						}

						// determine the limits of the unresolved old sequence
						var jStart = null;
						var jEnd = j;
						var jLength = 0;
						var jNext = j;
						while ( (jNext !== null) && (this.oldText.tokens[jNext].link === null) ) {
							jStart = jNext;
							jLength ++;
							if (jStart == oldStart) {
								break;
							}
							jNext = this.oldText.tokens[jNext].prev;
						}

						// recursively diff the unresolved sequence
						if ( (iLength > 1) || (jLength > 1) ) {

							// new symbols object for sub-region
							var symbolsRecurse = {
								token:  [],
								hash:   {},
								linked: false
							};
							this.calculateDiff(symbolsRecurse, level, true, iStart, iEnd, jStart, jEnd, recursionLevel + 1);
						}
						i = iStart;
					}

					// next list element
					if (i == newStart) {
						break;
					}
					i = this.newText.tokens[i].prev;
				}
			}
		}

		// stop timer
		if ( (wDiff.debugTime === true) && (recursionLevel === 0) ) {
			console.timeEnd(level);
		}
		return;
	};


	// TextDiff.detectBlocks(): main method for extracting deleted, inserted, and moved blocks from raw diff data
	//   called from: .diff()
	//   calls: .getSameBlocks(), .getSections(), .getGroups(), .setFixed(), getDelBlocks(), .positionDelBlocks(), .unlinkBlocks(), .getInsBlocks(), .setInsGroups(), .insertMarks()
	//   input:
	//     text: object containing text tokens list
	//     blocks: empty array for block data
	//     groups: empty array for group data
	//   changes: .text, .blocks, .groups
	//   scheme of blocks, sections, and groups (old block numbers):
	//     old:      1    2 3D4   5E6    7   8 9 10  11
	//               |    ‾/-/_    X     |    >|<     |
	//     new:      1  I 3D4 2  E6 5  N 7  10 9  8  11
	//     section:       0 0 0   1 1       2 2  2
	//     group:    0 10 111 2  33 4 11 5   6 7  8   9
	//     fixed:    +    +++ -  ++ -    +   + -  -   +
	//     type:     =  + =-= =  -= =  + =   = =  =   =

	this.detectBlocks = function () {

		if (wDiff.debug === true) {
			this.oldText.debugText('Old text');
			this.newText.debugText('New text');
		}

		// collect identical corresponding ('same') blocks from old text and sort by new text
		this.getSameBlocks();

		// collect independent block sections (no old/new crosses outside section) for per-section determination of non-moving (fixed) groups
		this.getSections();

		// find groups of continuous old text blocks
		this.getGroups();

		// set longest sequence of increasing groups in sections as fixed (not moved)
		this.setFixed();

		// collect deletion ('del') blocks from old text
		this.getDelBlocks();

		// position 'del' blocks into new text order
		this.positionDelBlocks();

		// convert groups to insertions/deletions if maximal block length is too short
		var unlink = 0;
		if (wDiff.blockMinLength > 0) {

			// repeat as long as unlinking is possible
			var unlinked = false;
			do {

				// convert 'same' to 'ins'/'del' pairs
				unlinked = this.unlinkBlocks();

				// start over after conversion
				if (unlinked === true) {
					unlink ++;
					this.slideGaps(this.newText, this.oldText);
					this.slideGaps(this.oldText, this.newText);

					// repeat block detection from start
					this.getSameBlocks();
					this.getSections();
					this.getGroups();
					this.setFixed();
					this.getDelBlocks();
					this.positionDelBlocks();
				}
			} while (unlinked === true);
		}

		// collect insertion ('ins') blocks from new text
		this.getInsBlocks();

		// set group numbers of 'ins' blocks
		this.setInsGroups();

		// mark original positions of moved groups
		this.insertMarks();

		if (wDiff.debug === true) {
			console.log('Unlinked: ', unlink);
			this.debugGroups('Groups');
			this.debugBlocks('Blocks');
		}
		return;
	};


	// TextDiff.getSameBlocks(): collect identical corresponding ('same') blocks from old text and sort by new text
	//   called from: .detectBlocks()
	//   calls: .wordCount()
	//   changes: .blocks

	this.getSameBlocks = function () {

		var blocks = this.blocks;

		// clear blocks array
		blocks.splice(0);

		// cycle through old text to find matched (linked) blocks
		var j = this.oldText.first;
		var i = null;
		while (j !== null) {

			// skip 'del' blocks
			while ( (j !== null) && (this.oldText.tokens[j].link === null) ) {
				j = this.oldText.tokens[j].next;
			}

			// get 'same' block
			if (j !== null) {
				i = this.oldText.tokens[j].link;
				var iStart = i;
				var jStart = j;

				// detect matching blocks ('same')
				var count = 0;
				var unique = false;
				var string = '';
				while ( (i !== null) && (j !== null) && (this.oldText.tokens[j].link == i) ) {
					var token = this.oldText.tokens[j].token;
					count ++;
					if (this.newText.tokens[i].unique === true) {
						unique = true;
					}
					string += token;
					i = this.newText.tokens[i].next;
					j = this.oldText.tokens[j].next;
				}

				// save old text 'same' block
				blocks.push({
					oldBlock:  blocks.length,
					newBlock:  null,
					oldNumber: this.oldText.tokens[jStart].number,
					newNumber: this.newText.tokens[iStart].number,
					oldStart:  jStart,
					count:     count,
					unique:    unique,
					words:     this.wordCount(string),
					chars:     string.length,
					type:      'same',
					section:   null,
					group:     null,
					fixed:     null,
					moved:     null,
					string:    string
				});
			}
		}

		// sort blocks by new text token number
		blocks.sort(function(a, b) {
			return a.newNumber - b.newNumber;
		});

		// number blocks in new text order
		for (var block = 0; block < blocks.length; block ++) {
			blocks[block].newBlock = block;
		}
		return;
	};


	// TextDiff.getSections(): collect independent block sections (no old/new crosses outside section) for per-section determination of non-moving (fixed) groups
	//   called from: .detectBlocks()
	//   changes: creates sections, blocks[].section

	this.getSections = function () {

		var blocks = this.blocks;
		var sections = this.sections;

		// clear sections array
		sections.splice(0);

		// cycle through blocks
		for (var block = 0; block < blocks.length; block ++) {

			var sectionStart = block;
			var sectionEnd = block;

			var oldMax = blocks[sectionStart].oldNumber;
			var sectionOldMax = oldMax;

			// check right
			for (var j = sectionStart + 1; j < blocks.length; j ++) {

				// check for crossing over to the left
				if (blocks[j].oldNumber > oldMax) {
					oldMax = blocks[j].oldNumber;
				}
				else if (blocks[j].oldNumber < sectionOldMax) {
					sectionEnd = j;
					sectionOldMax = oldMax;
				}
			}

			// save crossing sections
			if (sectionEnd > sectionStart) {

				// save section to block
				for (var i = sectionStart; i <= sectionEnd; i ++) {
					blocks[i].section = sections.length;
				}

				// save section
				sections.push({
					blockStart:  sectionStart,
					blockEnd:    sectionEnd
				});
				block = sectionEnd;
			}
		}
		return;
	};


	// TextDiff.getGroups(): find groups of continuous old text blocks
	//   called from: .detectBlocks()
	//   calls: .wordCount()
	//   changes: creates .groups, .blocks[].group

	this.getGroups = function () {

		var blocks = this.blocks;
		var groups = this.groups;

		// clear groups array
		groups.splice(0);

		// cycle through blocks
		for (var block = 0; block < blocks.length; block ++) {
			var groupStart = block;
			var groupEnd = block;
			var oldBlock = blocks[groupStart].oldBlock;

			// get word and char count of block
			var words = this.wordCount(blocks[block].string);
			var maxWords = words;
			var unique = blocks[block].unique;
			var chars = blocks[block].chars;

			// check right
			for (var i = groupEnd + 1; i < blocks.length; i ++) {

				// check for crossing over to the left
				if (blocks[i].oldBlock != oldBlock + 1) {
					break;
				}
				oldBlock = blocks[i].oldBlock;

				// get word and char count of block
				if (blocks[i].words > maxWords) {
					maxWords = blocks[i].words;
				}
				if (blocks[i].unique === true) {
					unique = true;
				}
				words += blocks[i].words;
				chars += blocks[i].chars;
				groupEnd = i;
			}

			// save crossing group
			if (groupEnd >= groupStart) {

				// set groups outside sections as fixed
				var fixed = false;
				if (blocks[groupStart].section === null) {
					fixed = true;
				}

				// save group to block
				for (var i = groupStart; i <= groupEnd; i ++) {
					blocks[i].group = groups.length;
					blocks[i].fixed = fixed;
				}

				// save group
				groups.push({
					oldNumber:  blocks[groupStart].oldNumber,
					blockStart: groupStart,
					blockEnd:   groupEnd,
					unique:     unique,
					maxWords:   maxWords,
					words:      words,
					chars:      chars,
					fixed:      fixed,
					movedFrom:  null,
					color:      null
				});
				block = groupEnd;
			}
		}
		return;
	};


	// TextDiff.setFixed(): set longest sequence of increasing groups in sections as fixed (not moved)
	//   called from: .detectBlocks()
	//   calls: .findMaxPath()
	//   changes: .groups[].fixed, .blocks[].fixed

	this.setFixed = function () {

		var blocks = this.blocks;
		var groups = this.groups;
		var sections = this.sections;

		// cycle through sections
		for (var section = 0; section < sections.length; section ++) {
			var blockStart = sections[section].blockStart;
			var blockEnd = sections[section].blockEnd;

			var groupStart = blocks[blockStart].group;
			var groupEnd = blocks[blockEnd].group;

			// recusively find path of groups in increasing old group order with longest char length
			var cache = [];
			var maxChars = 0;
			var maxPath = null;

			// start at each group of section
			for (var i = groupStart; i <= groupEnd; i ++) {
				var pathObj = this.findMaxPath(i, groupEnd, cache);
				if (pathObj.chars > maxChars) {
					maxPath = pathObj.path;
					maxChars = pathObj.chars;
				}
			}

			// mark fixed groups
			for (var i = 0; i < maxPath.length; i ++) {
				var group = maxPath[i];
				groups[group].fixed = true;

				// mark fixed blocks
				for (var block = groups[group].blockStart; block <= groups[group].blockEnd; block ++) {
					blocks[block].fixed = true;
				}
			}
		}
		return;
	};


	// TextDiff.findMaxPath(): recusively find path of groups in increasing old group order with longest char length
	//   input: start: path start group, path: array of path groups, chars: char count of path, cache: cached sub-path lengths, groupEnd: last group
	//   called from: .setFixed()
	//   calls: itself recursively
	//   returns: returnObj, contains path and length

	this.findMaxPath = function (start, groupEnd, cache) {

		var groups = this.groups;

		// find longest sub-path
		var maxChars = 0;
		var oldNumber = groups[start].oldNumber;
		var returnObj = { path: [], chars: 0};
		for (var i = start + 1; i <= groupEnd; i ++) {

			// only in increasing old group order
			if (groups[i].oldNumber < oldNumber) {
				continue;
			}

			// get longest sub-path from cache (deep copy)
			var pathObj;
			if (cache[i] !== undefined) {
				pathObj = { path: cache[i].path.slice(), chars: cache[i].chars };
			}

			// get longest sub-path by recursion
			else {
				pathObj = this.findMaxPath(i, groupEnd, cache);
			}

			// select longest sub-path
			if (pathObj.chars > maxChars) {
				maxChars = pathObj.chars;
				returnObj = pathObj;
			}
		}

		// add current start to path
		returnObj.path.unshift(start);
		returnObj.chars += groups[start].chars;

		// save path to cache (deep copy)
		if (cache[start] === undefined) {
			cache[start] = { path: returnObj.path.slice(), chars: returnObj.chars };
		}

		return returnObj;
	};


	// TextDiff.getDelBlocks(): collect deletion ('del') blocks from old text
	//   called from: .detectBlocks()
	//   changes: .blocks

	this.getDelBlocks = function () {

		var blocks = this.blocks;

		// cycle through old text to find matched (linked) blocks
		var j = this.oldText.first;
		var i = null;
		while (j !== null) {

			// collect 'del' blocks
			var oldStart = j;
			var count = 0;
			var string = '';
			while ( (j !== null) && (this.oldText.tokens[j].link === null) ) {
				count ++;
				string += this.oldText.tokens[j].token;
				j = this.oldText.tokens[j].next;
			}

			// save old text 'del' block
			if (count !== 0) {
				blocks.push({
					oldBlock:  null,
					newBlock:  null,
					oldNumber: this.oldText.tokens[oldStart].number,
					newNumber: null,
					oldStart:  oldStart,
					count:     count,
					unique:    false,
					words:     null,
					chars:     string.length,
					type:      'del',
					section:   null,
					group:     null,
					fixed:     null,
					moved:     null,
					string:    string
				});
			}

			// skip 'same' blocks
			if (j !== null) {
				i = this.oldText.tokens[j].link;
				while ( (i !== null) && (j !== null) && (this.oldText.tokens[j].link == i) ) {
					i = this.newText.tokens[i].next;
					j = this.oldText.tokens[j].next;
				}
			}
		}
		return;
	};


	// TextDiff.positionDelBlocks(): position 'del' blocks into new text order
	//   called from: .detectBlocks()
	//   calls: .sortBlocks()
	//   changes: .blocks[].section/group/fixed/newNumber
	//
	//   deletion blocks move with fixed neighbor (new number +/- 0.1):
	//     old:          1 D 2      1 D 2
	//                  /     \    /   \ \
	//     new:        1 D     2  1     D 2
	//     fixed:      *                  *
	//     new number: 1 1.1          1.9 2

	this.positionDelBlocks = function () {

		var blocks = this.blocks;
		var groups = this.groups;

		// sort shallow copy of blocks by oldNumber
		var blocksOld = blocks.slice();
		blocksOld.sort(function(a, b) {
			return a.oldNumber - b.oldNumber;
		});

		// cycle through blocks in old text order
		for (var block = 0; block < blocksOld.length; block ++) {
			var delBlock = blocksOld[block];

			// 'del' block only
			if (delBlock.type != 'del') {
				continue;
			}

			// get old text prev block
			var prevBlockNumber;
			var prevBlock;
			if (block > 0) {
				prevBlockNumber = blocksOld[block - 1].newBlock;
				prevBlock = blocks[prevBlockNumber];
			}

			// get old text next block
			var nextBlockNumber;
			var nextBlock;
			if (block < blocksOld.length - 1) {
				nextBlockNumber = blocksOld[block + 1].newBlock;
				nextBlock = blocks[nextBlockNumber];
			}

			// move after prev block if fixed
			var neighbor;
			if ( (prevBlock !== undefined) && (prevBlock.fixed === true) ) {
				neighbor = prevBlock;
				delBlock.newNumber = neighbor.newNumber + 0.1;
			}

			// move before next block if fixed
			else if ( (nextBlock !== undefined) && (nextBlock.fixed === true) ) {
				neighbor = nextBlock;
				delBlock.newNumber = neighbor.newNumber - 0.1;
			}

			// move after prev block if not start of group
			else if ( (prevBlock !== undefined) && (prevBlockNumber != groups[ prevBlock.group ].blockEnd) ) {
				neighbor = prevBlock;
				delBlock.newNumber = neighbor.newNumber + 0.1;
			}

			// move before next block if not start of group
			else if ( (nextBlock !== undefined) && (nextBlockNumber != groups[ nextBlock.group ].blockStart) ) {
				neighbor = nextBlock;
				delBlock.newNumber = neighbor.newNumber - 0.1;
			}

			// move after closest previous fixed block
			else {
				for (var fixed = block; fixed >= 0; fixed --) {
					if (blocksOld[fixed].fixed === true) {
						neighbor = blocksOld[fixed];
						delBlock.newNumber = neighbor.newNumber + 0.1;
						break;
					}
				}
			}

			// move before first block
			if (neighbor === undefined) {
				delBlock.newNumber =  -0.1;
			}

			// update 'del' block data
			else {
				delBlock.section = neighbor.section;
				delBlock.group = neighbor.group;
				delBlock.fixed = neighbor.fixed;
			}
		}

		// sort 'del' blocks in and update groups
		this.sortBlocks();

		return;
	};


	// TextDiff.unlinkBlocks(): convert 'same' blocks in groups into 'ins'/'del' pairs if too short
	//   called from: .detectBlocks()
	//   calls: .unlinkSingleBlock()
	//   changes: .newText/oldText[].link
	//   returns: true if text tokens were unlinked

	this.unlinkBlocks = function () {

		var blocks = this.blocks;
		var groups = this.groups;

		// cycle through groups
		var unlinked = false;
		for (var group = 0; group < groups.length; group ++) {
			var blockStart = groups[group].blockStart;
			var blockEnd = groups[group].blockEnd;

			// unlink whole group if no block is at least blockMinLength words long and unique
			if ( (groups[group].maxWords < wDiff.blockMinLength) && (groups[group].unique === false) ) {
				for (var block = blockStart; block <= blockEnd; block ++) {
					if (blocks[block].type == 'same') {
						this.unlinkSingleBlock(blocks[block]);
						unlinked = true;
					}
				}
			}

			// otherwise unlink block flanks
			else {

				// unlink blocks from start
				for (var block = blockStart; block <= blockEnd; block ++) {
					if (blocks[block].type == 'same') {

						// stop unlinking if more than one word or a unique word
						if ( (blocks[block].words > 1) || (blocks[block].unique === true) ) {
							break;
						}
						this.unlinkSingleBlock(blocks[block]);
						unlinked = true;
						blockStart = block;
					}
				}

				// unlink blocks from end
				for (var block = blockEnd; block > blockStart; block --) {
					if ( (blocks[block].type == 'same') ) {

						// stop unlinking if more than one word or a unique word
						if ( (blocks[block].words > 1) || ( (blocks[block].words == 1) && (blocks[block].unique === true) ) ) {
							break;
						}
						this.unlinkSingleBlock(blocks[block]);
						unlinked = true;
					}
				}
			}
		}
		return unlinked;
	};


	// TextDiff.unlinkBlock(): unlink text tokens of single block, converting them into 'ins'/'del' pair
	//   called from: .unlinkBlocks()
	//   changes: text.newText/oldText[].link

	this.unlinkSingleBlock = function (block) {

		// cycle through old text
		var j = block.oldStart;
		for (var count = 0; count < block.count; count ++) {

			// unlink tokens
			this.newText.tokens[ this.oldText.tokens[j].link ].link = null;
			this.oldText.tokens[j].link = null;
			j = this.oldText.tokens[j].next;
		}
		return;
	};


	// TextDiff.getInsBlocks(): collect insertion ('ins') blocks from new text
	//   called from: .detectBlocks()
	//   calls: .sortBlocks()
	//   changes: .blocks

	this.getInsBlocks = function () {

		var blocks = this.blocks;

		// cycle through new text to find insertion blocks
		var i = this.newText.first;
		while (i !== null) {

			// jump over linked (matched) block
			while ( (i !== null) && (this.newText.tokens[i].link !== null) ) {
				i = this.newText.tokens[i].next;
			}

			// detect insertion blocks ('ins')
			if (i !== null) {
				var iStart = i;
				var count = 0;
				var string = '';
				while ( (i !== null) && (this.newText.tokens[i].link === null) ) {
					count ++;
					string += this.newText.tokens[i].token;
					i = this.newText.tokens[i].next;
				}

				// save new text 'ins' block
				blocks.push({
					oldBlock:  null,
					newBlock:  null,
					oldNumber: null,
					newNumber: this.newText.tokens[iStart].number,
					oldStart:  null,
					count:     count,
					unique:    false,
					words:     null,
					chars:     string.length,
					type:      'ins',
					section:   null,
					group:     null,
					fixed:     null,
					moved:     null,
					string:    string
				});
			}
		}

		// sort 'ins' blocks in and update groups
		this.sortBlocks();

		return;
	};


	// TextDiff.sortBlocks(): sort blocks by new text token number and update groups
	//   called from: .positionDelBlocks(), .getInsBlocks(), .insertMarks()
	//   changes: .blocks, .groups

	this.sortBlocks = function () {

		var blocks = this.blocks;
		var groups = this.groups;

		// sort by newNumber, then by old number
		blocks.sort(function(a, b) {
			var comp = a.newNumber - b.newNumber;
			if (comp === 0) {
				comp = a.oldNumber - b.oldNumber;
			}
			return comp;
		});

		// cycle through blocks and update groups with new block numbers
		var group = null;
		for (var block = 0; block < blocks.length; block ++) {
			var blockGroup = blocks[block].group;
			if (blockGroup !== null) {
				if (blockGroup != group) {
					group = blocks[block].group;
					groups[group].blockStart = block;
					groups[group].oldNumber = blocks[block].oldNumber;
				}
				groups[blockGroup].blockEnd = block;
			}
		}
		return;
	};


	// TextDiff.setInsGroups: set group numbers of 'ins' blocks
	//   called from: .detectBlocks()
	//   changes: .groups, .blocks[].fixed/group

	this.setInsGroups = function () {

		var blocks = this.blocks;
		var groups = this.groups;

		// set group numbers of 'ins' blocks inside existing groups
		for (var group = 0; group < groups.length; group ++) {
			var fixed = groups[group].fixed;
			for (var block = groups[group].blockStart; block <= groups[group].blockEnd; block ++) {
				if (blocks[block].group === null) {
					blocks[block].group = group;
					blocks[block].fixed = fixed;
				}
			}
		}

		// add remaining 'ins' blocks to new groups

		// cycle through blocks
		for (var block = 0; block < blocks.length; block ++) {

			// skip existing groups
			if (blocks[block].group === null) {
				blocks[block].group = groups.length;

				// save new single-block group
				groups.push({
					oldNumber:  blocks[block].oldNumber,
					blockStart: block,
					blockEnd:   block,
					unique:     blocks[block].unique,
					maxWords:   blocks[block].words,
					words:      blocks[block].words,
					chars:      blocks[block].chars,
					fixed:      blocks[block].fixed,
					movedFrom:  null,
					color:      null
				});
			}
		}
		return;
	};


	// TextDiff.insertMarks(): mark original positions of moved groups
	//   called from: .detectBlocks()
	//   changes: .groups[].movedFrom
	//   moved block marks at original positions relative to fixed groups:
	//   groups:    3       7
	//           1 <|       |     (no next smaller fixed)
	//           5  |<      |
	//              |>  5   |
	//              |   5  <|
	//              |      >|   5
	//              |       |>  9 (no next larger fixed)
	//   fixed:     *       *
	//   mark direction: .movedGroup.blockStart < .groups[group].blockStart
	//   group side:     .movedGroup.oldNumber  < .groups[group].oldNumber

	this.insertMarks = function () {

		var blocks = this.blocks;
		var groups = this.groups;
		var moved = [];
		var color = 1;

		// make shallow copy of blocks
		var blocksOld = blocks.slice();

		// enumerate copy
		for (var i = 0; i < blocksOld.length; i ++) {
			blocksOld[i].number = i;
		}

		// sort copy by oldNumber
		blocksOld.sort(function(a, b) {
			return a.oldNumber - b.oldNumber;
		});

		// create lookup table: original to sorted
		var lookupSorted = [];
		for (var i = 0; i < blocksOld.length; i ++) {
			lookupSorted[ blocksOld[i].number ] = i;
		}

		// cycle through groups (moved group)
		for (var moved = 0; moved < groups.length; moved ++) {
			var movedGroup = groups[moved];
			if (movedGroup.fixed !== false) {
				continue;
			}
			var movedOldNumber = movedGroup.oldNumber;

			// find closest fixed block to the left
			var fixedLeft = null;
			var leftChars = 0;
			for (var block = lookupSorted[ groups[moved].blockStart ] - 1; block >= 0; block --) {
				leftChars += blocksOld[block].chars;
				if (blocksOld[block].fixed === true) {
					fixedLeft = blocksOld[block];
					break;
				}
			}

			// find closest fixed block to the right
			var fixedRight = null;
			var rightChars = 0;
			for (var block = lookupSorted[ groups[moved].blockEnd ] + 1; block < blocksOld.length; block ++) {
				rightChars += blocksOld[block].chars;
				if (blocksOld[block].fixed === true) {
					fixedLeft = blocksOld[block];
					break;
				}
			}

			// no larger fixed block, moved right
			var fixedBlock = null;
			if (fixedRight === null) {
				fixedBlock = fixedLeft;
			}

			// no smaller fixed block, moved left
			else if (fixedLeft === null) {
				fixedBlock = fixedRight;
			}

			// group moved from between two closest fixed neighbors, moved left or right depending on char distance
			else if (rightChars <= leftChars) {
				fixedBlock = fixedRight;
			}

			// moved left
			else {
				fixedBlock = fixedLeft;
			}

			// from left side of fixed group
			var newNumber;
			if (movedOldNumber < fixedBlock.oldNumber) {
				newNumber = fixedBlock.newNumber - 0.1;
			}

			// from right side of fixed group
			else {
				newNumber = fixedBlock.newNumber + 0.1;
			}

			// insert 'mark' block
			blocks.push({
				oldBlock:  null,
				newBlock:  null,
				oldNumber: movedOldNumber,
				newNumber: newNumber,
				oldStart:  null,
				count:     null,
				unique:    null,
				words:     null,
				chars:     0,
				type:      'mark',
				section:   null,
				group:     fixedBlock.group,
				fixed:     true,
				moved:     moved,
				string:    ''
			});

			// set group color
			movedGroup.color = color;
			movedGroup.movedFrom = fixedBlock.group;
			color ++;
		}

		// sort mark blocks in and update groups
		this.sortBlocks();

		return;
	};


	// TextDiff.assembleDiff(): create html formatted diff text from block and group data
	//   input: version: 'new', 'old', show only one marked-up version
	//   returns: diff html string
	//   called from: .diff()
	//   calls: .htmlCustomize(), .htmlEscape(), .htmlFormatBlock(), .htmlFormat()

	this.assembleDiff = function (version) {

		var blocks = this.blocks;
		var groups = this.groups;

		// make shallow copy of groups and sort by blockStart
		var groupsSort = groups.slice();
		groupsSort.sort(function(a, b) {
			return a.blockStart - b.blockStart;
		});

		//
		// create group diffs
		//

		// cycle through groups
		var htmlFrags = [];
		for (var group = 0; group < groupsSort.length; group ++) {
			var color = groupsSort[group].color;
			var blockStart = groupsSort[group].blockStart;
			var blockEnd = groupsSort[group].blockEnd;

			// check for colored block and move direction
			var moveDir = null;
			if (color !== null) {
				var groupUnSort = blocks[blockStart].group;
				if (groupsSort[group].movedFrom < groupUnSort) {
					moveDir = 'left';
				}
				else {
					moveDir = 'right';
				}
			}

			// add colored block start markup
			if (version != 'old') {
				var html = '';
				if (moveDir == 'left') {
					html = this.htmlCustomize(wDiff.htmlBlockLeftStart, color);
				}
				else if (moveDir == 'right') {
					html = this.htmlCustomize(wDiff.htmlBlockRightStart, color);
				}
				htmlFrags.push(html);
			}

			// cycle through blocks
			for (var block = blockStart; block <= blockEnd; block ++) {
				var html = '';
				var type = blocks[block].type;
				var string = blocks[block].string;

				// html escape text string
				string = this.htmlEscape(string);

				// add 'same' (unchanged) text and moved block
				if (type == 'same') {
					if (color !== null) {
						if (version != 'old') {
							html = this.htmlFormatBlock(string);
						}
					}
					else {
						html = string;
					}
				}

				// add 'del' text && (blocks[block].fixed == true)
				else if ( (type == 'del') && (version != 'new') ) {

					// for old version skip 'del' inside moved group
					if ( (version != 'old') || (color === null) ) {
						if (wDiff.regExpBlankBlock.test(string) === true) {
							html = wDiff.htmlDeleteStartBlank;
						}
						else {
							html = wDiff.htmlDeleteStart;
						}
						html += this.htmlFormatBlock(string) + wDiff.htmlDeleteEnd;
					}
				}

				// add 'ins' text
				else if ( (type == 'ins') && (version != 'old') ) {
					if (wDiff.regExpBlankBlock.test(string) === true) {
						html = wDiff.htmlInsertStartBlank;
					}
					else {
						html = wDiff.htmlInsertStart;
					}
					html += this.htmlFormatBlock(string) + wDiff.htmlInsertEnd;
				}

				// add 'mark' code
				else if ( (type == 'mark') && (version != 'new') ) {
					var moved =  blocks[block].moved;
					var movedGroup = groups[moved];
					var markColor = movedGroup.color;


					// get moved block text ('same' and 'del')
					var string = '';
					for (var mark = movedGroup.blockStart; mark <= movedGroup.blockEnd; mark ++) {
						if ( (blocks[mark].type == 'same') || (blocks[mark].type == 'del') ) {
							string += blocks[mark].string;
						}
					}

					// display as deletion at original position
					if ( (wDiff.showBlockMoves === false) || (version == 'old') ) {
						string = this.htmlEscape(string);
						string = this.htmlFormatBlock(string);
						if (version == 'old') {
							if (movedGroup.blockStart < groupsSort[group].blockStart) {
								html = this.htmlCustomize(wDiff.htmlBlockLeftStart, markColor) + string + wDiff.htmlBlockLeftEnd;
							}
							else {
								html = this.htmlCustomize(wDiff.htmlBlockRightStart, markColor) + string + wDiff.htmlBlockRightEnd;
							}
						}
						else {
							if (wDiff.regExpBlankBlock.test(string) === true) {
								html = wDiff.htmlDeleteStartBlank + string + wDiff.htmlDeleteEnd;
							}
							else {
								html = wDiff.htmlDeleteStart + string + wDiff.htmlDeleteEnd;
							}
						}
					}

					// display as mark, get mark direction
					else {
						if (movedGroup.blockStart < groupsSort[group].blockStart) {
							html = this.htmlCustomize(wDiff.htmlMarkLeft, markColor, string);
						}
						else {
							html = this.htmlCustomize(wDiff.htmlMarkRight, markColor, string);
						}
					}
				}
				htmlFrags.push(html);
			}

			// add colored block end markup
			if (version != 'old') {
				var html = '';
				if (moveDir == 'left') {
					html = wDiff.htmlBlockLeftEnd;
				}
				else if (moveDir == 'right') {
					html = wDiff.htmlBlockRightEnd;
				}
				htmlFrags.push(html);
			}
		}

		// join fragments
		this.html = htmlFrags.join('');

		// markup newlines and spaces in blocks
		this.htmlFormat();

		return;
	};


	//
	// TextDiff.htmlCustomize(): customize move indicator html: {block}: block number style, {mark}: mark number style, {class}: class number, {number}: block number, {title}: title attribute (popup)
	//   input: text (html or css code), number: block number, title: title attribute (popup) text
	//   returns: customized text
	//   called from: .assembleDiff()

	this.htmlCustomize = function (text, number, title) {

		if (wDiff.coloredBlocks === true) {
			var blockStyle = wDiff.styleBlockColor[number];
			if (blockStyle === undefined) {
				blockStyle = '';
			}
			var markStyle = wDiff.styleMarkColor[number];
			if (markStyle === undefined) {
				markStyle = '';
			}
			text = text.replace(/\{block\}/g, ' ' + blockStyle);
			text = text.replace(/\{mark\}/g, ' ' + markStyle);
			text = text.replace(/\{class\}/g, number);
		}
		else {
			text = text.replace(/\{block\}|\{mark\}|\{class\}/g, '');
		}
		text = text.replace(/\{number\}/g, number);

		// shorten title text, replace {title}
		if ( (title !== undefined) && (title !== '') ) {
			var max = 512;
			var end = 128;
			var gapMark = ' [...] ';
			if (title.length > max) {
				title = title.substr(0, max - gapMark.length - end) + gapMark + title.substr(title.length - end);
			}
			title = this.htmlEscape(title);
			title = title.replace(/\t/g, '&nbsp;&nbsp;');
			title = title.replace(/  /g, '&nbsp;&nbsp;');
			text = text.replace(/\{title\}/, ' title="' + title + '"');
		}
		else {
			text = text.replace(/\{title\}/, '');
		}
		return text;
	};


	// TextDiff.htmlEscape(): replace html-sensitive characters in output text with character entities
	//   input: html text
	//   returns: escaped html text
	//   called from: .diff(), .assembleDiff()

	this.htmlEscape = function (html) {

		html = html.replace(/&/g, '&amp;');
		html = html.replace(/</g, '&lt;');
		html = html.replace(/>/g, '&gt;');
		html = html.replace(/"/g, '&quot;');
		return (html);
	};


	// TextDiff.htmlFormatBlock(): markup newlines and spaces in blocks
	//   input: string
	//   returns: formatted string
	//   called from: .diff(), .assembleDiff()

	this.htmlFormatBlock = function (string) {

		// spare blanks in tags
		string = string.replace(/(<[^>]*>)|( )/g, function (p, p1, p2) {
			if (p2 == ' ') {
				return wDiff.htmlSpace;
			}
			return p1;
		});
		string = string.replace(/\n/g, wDiff.htmlNewline);
		return string;
	};


	// TextDiff.htmlFormat(): markup tabs, add container
	//   changes: .diff
	//   called from: .diff(), .assembleDiff()

	this.htmlFormat = function () {

		this.html = this.html.replace(/\t/g, wDiff.htmlTab);
		this.html = wDiff.htmlContainerStart + wDiff.htmlFragmentStart + this.html + wDiff.htmlFragmentEnd + wDiff.htmlContainerEnd;
		return;
	};


	// TextDiff.shortenOutput(): shorten diff html by removing unchanged sections
	// input: diff html string from .diff()
	// returns: shortened html with removed unchanged passages indicated by (...) or separator

	this.shortenOutput = function () {

		var html = this.html;
		var diff = '';

		// remove container by non-regExp replace
		html = html.replace(wDiff.htmlContainerStart, '');
		html = html.replace(wDiff.htmlFragmentStart, '');
		html = html.replace(wDiff.htmlFragmentEnd, '');
		html = html.replace(wDiff.htmlContainerEnd, '');

		// scan for diff html tags
		var regExpDiff = /<\w+\b[^>]*\bclass="[^">]*?\bwDiff(MarkLeft|MarkRight|BlockLeft|BlockRight|Delete|Insert)\b[^">]*"[^>]*>(.|\n)*?<!--wDiff\1-->/g;
		var tagsStart = [];
		var tagsEnd = [];
		var i = 0;
		var regExpMatch;

		// save tag positions
		while ( (regExpMatch = regExpDiff.exec(html)) !== null ) {

			// combine consecutive diff tags
			if ( (i > 0) && (tagsEnd[i - 1] == regExpMatch.index) ) {
				tagsEnd[i - 1] = regExpMatch.index + regExpMatch[0].length;
			}
			else {
				tagsStart[i] = regExpMatch.index;
				tagsEnd[i] = regExpMatch.index + regExpMatch[0].length;
				i ++;
			}
		}

		// no diff tags detected
		if (tagsStart.length === 0) {
			this.html = wDiff.htmlNoChange;
			return;
		}

		// define regexps
		var regExpLine = /^(\n+|.)|(\n+|.)$|\n+/g;
		var regExpHeading = /(^|\n)(<[^>]+>)*(==+.+?==+|\{\||\|\}).*?\n?/g;
		var regExpParagraph = /^(\n\n+|.)|(\n\n+|.)$|\n\n+/g;
		var regExpBlank = /(<[^>]+>)*\s+/g;

		// get line positions
		var regExpMatch;
		var lines = [];
		while ( (regExpMatch = regExpLine.exec(html)) !== null) {
			lines.push(regExpMatch.index);
		}

		// get heading positions
		var headings = [];
		var headingsEnd = [];
		while ( (regExpMatch = regExpHeading.exec(html)) !== null ) {
			headings.push(regExpMatch.index);
			headingsEnd.push(regExpMatch.index + regExpMatch[0].length);
		}

		// get paragraph positions
		var paragraphs = [];
		while ( (regExpMatch = regExpParagraph.exec(html)) !== null ) {
			paragraphs.push(regExpMatch.index);
		}

		// determine fragment border positions around diff tags
		var lineMaxBefore = 0;
		var headingBefore = 0;
		var paragraphBefore = 0;
		var lineBefore = 0;

		var lineMaxAfter = 0;
		var headingAfter = 0;
		var paragraphAfter = 0;
		var lineAfter = 0;

		var rangeStart = [];
		var rangeEnd = [];
		var rangeStartType = [];
		var rangeEndType = [];

		// cycle through diff tag start positions
		for (var i = 0; i < tagsStart.length; i ++) {
			var tagStart = tagsStart[i];
			var tagEnd = tagsEnd[i];

			// maximal lines to search before diff tag
			var rangeStartMin = 0;
			for (var j = lineMaxBefore; j < lines.length - 1; j ++) {
				if (tagStart < lines[j + 1]) {
					if (j - wDiff.linesBeforeMax >= 0) {
						rangeStartMin = lines[j - wDiff.linesBeforeMax];
					}
					lineMaxBefore = j;
					break;
				}
			}

			// find last heading before diff tag
			if (rangeStart[i] === undefined) {
				for (var j = headingBefore; j < headings.length - 1; j ++) {
					if (headings[j] > tagStart) {
						break;
					}
					if (headings[j + 1] > tagStart) {
						if ( (headings[j] > tagStart - wDiff.headingBefore) && (headings[j] > rangeStartMin) ) {
							rangeStart[i] = headings[j];
							rangeStartType[i] = 'heading';
							headingBefore = j;
						}
						break;
					}
				}
			}

			// find last paragraph before diff tag
			if (rangeStart[i] === undefined) {
				for (var j = paragraphBefore; j < paragraphs.length - 1; j ++) {
					if (paragraphs[j] > tagStart) {
						break;
					}
					if (paragraphs[j + 1] > tagStart - wDiff.paragraphBeforeMin) {
						if ( (paragraphs[j] > tagStart - wDiff.paragraphBeforeMax) && (paragraphs[j] > rangeStartMin) ) {
							rangeStart[i] = paragraphs[j];
							rangeStartType[i] = 'paragraph';
							paragraphBefore = j;
						}
						break;
					}
				}
			}

			// find last line break before diff tag
			if (rangeStart[i] === undefined) {
				for (var j = lineBefore; j < lines.length - 1; j ++) {
					if (lines[j + 1] > tagStart - wDiff.lineBeforeMin) {
						if ( (lines[j] > tagStart - wDiff.lineBeforeMax) && (lines[j] > rangeStartMin) ) {
							rangeStart[i] = lines[j];
							rangeStartType[i] = 'line';
							lineBefore = j;
						}
						break;
					}
				}
			}

			// find last blank before diff tag
			if (rangeStart[i] === undefined) {
				var lastPos = tagStart - wDiff.blankBeforeMax;
				if (lastPos < rangeStartMin) {
					lastPos = rangeStartMin;
				}
				regExpBlank.lastIndex = lastPos;
				while ( (regExpMatch = regExpBlank.exec(html)) !== null ) {
					if (regExpMatch.index > tagStart - wDiff.blankBeforeMin) {
						rangeStart[i] = lastPos;
						rangeStartType[i] = 'blank';
						break;
					}
					lastPos = regExpMatch.index;
				}
			}

			// fixed number of chars before diff tag
			if (rangeStart[i] === undefined) {
				if (tagStart - wDiff.charsBefore > rangeStartMin) {
					rangeStart[i] = tagStart - wDiff.charsBefore;
					rangeStartType[i] = 'chars';
				}
			}

			// fixed number of lines before diff tag
			if (rangeStart[i] === undefined) {
				rangeStart[i] = rangeStartMin;
				rangeStartType[i] = 'lines';
			}

			// maximal lines to search after diff tag
			var rangeEndMax = html.length;
			for (var j = lineMaxAfter; j < lines.length; j ++) {
				if (lines[j] > tagEnd) {
					if (j + wDiff.linesAfterMax < lines.length) {
						rangeEndMax = lines[j + wDiff.linesAfterMax];
					}
					lineMaxAfter = j;
					break;
				}
			}

			// find first heading after diff tag
			if (rangeEnd[i] === undefined) {
				for (var j = headingAfter; j < headingsEnd.length; j ++) {
					if (headingsEnd[j] > tagEnd) {
						if ( (headingsEnd[j] < tagEnd + wDiff.headingAfter) && (headingsEnd[j] < rangeEndMax) ) {
							rangeEnd[i] = headingsEnd[j];
							rangeEndType[i] = 'heading';
							paragraphAfter = j;
						}
						break;
					}
				}
			}

			// find first paragraph after diff tag
			if (rangeEnd[i] === undefined) {
				for (var j = paragraphAfter; j < paragraphs.length; j ++) {
					if (paragraphs[j] > tagEnd + wDiff.paragraphAfterMin) {
						if ( (paragraphs[j] < tagEnd + wDiff.paragraphAfterMax) && (paragraphs[j] < rangeEndMax) ) {
							rangeEnd[i] = paragraphs[j];
							rangeEndType[i] = 'paragraph';
							paragraphAfter = j;
						}
						break;
					}
				}
			}

			// find first line break after diff tag
			if (rangeEnd[i] === undefined) {
				for (var j = lineAfter; j < lines.length; j ++) {
					if (lines[j] > tagEnd + wDiff.lineAfterMin) {
						if ( (lines[j] < tagEnd + wDiff.lineAfterMax) && (lines[j] < rangeEndMax) ) {
							rangeEnd[i] = lines[j];
							rangeEndType[i] = 'line';
							lineAfter = j;
						}
						break;
					}
				}
			}

			// find blank after diff tag
			if (rangeEnd[i] === undefined) {
				regExpBlank.lastIndex = tagEnd + wDiff.blankAfterMin;
				if ( (regExpMatch = regExpBlank.exec(html)) !== null ) {
					if ( (regExpMatch.index < tagEnd + wDiff.blankAfterMax) && (regExpMatch.index < rangeEndMax) ) {
						rangeEnd[i] = regExpMatch.index;
						rangeEndType[i] = 'blank';
					}
				}
			}

			// fixed number of chars after diff tag
			if (rangeEnd[i] === undefined) {
				if (tagEnd + wDiff.charsAfter < rangeEndMax) {
					rangeEnd[i] = tagEnd + wDiff.charsAfter;
					rangeEndType[i] = 'chars';
				}
			}

			// fixed number of lines after diff tag
			if (rangeEnd[i] === undefined) {
				rangeEnd[i] = rangeEndMax;
				rangeEndType[i] = 'lines';
			}
		}

		// remove overlaps, join close fragments
		var fragmentStart = [];
		var fragmentEnd = [];
		var fragmentStartType = [];
		var fragmentEndType = [];
		fragmentStart[0] = rangeStart[0];
		fragmentEnd[0] = rangeEnd[0];
		fragmentStartType[0] = rangeStartType[0];
		fragmentEndType[0] = rangeEndType[0];
		var j = 1;
		for (var i = 1; i < rangeStart.length; i ++) {

			// get lines between fragments
			var lines = 0;
			if (fragmentEnd[j - 1] < rangeStart[i]) {
				var join = html.substring(fragmentEnd[j - 1], rangeStart[i]);
				lines = (join.match(/\n/g) || []).length;
			}

			if ( (rangeStart[i] > fragmentEnd[j - 1] + wDiff.fragmentJoinChars) || (lines > wDiff.fragmentJoinLines) ) {
				fragmentStart[j] = rangeStart[i];
				fragmentEnd[j] = rangeEnd[i];
				fragmentStartType[j] = rangeStartType[i];
				fragmentEndType[j] = rangeEndType[i];
				j ++;
			}
			else {
				fragmentEnd[j - 1] = rangeEnd[i];
				fragmentEndType[j - 1] = rangeEndType[i];
			}
		}

		// assemble the fragments
		for (var i = 0; i < fragmentStart.length; i ++) {

			// get text fragment
			var fragment = html.substring(fragmentStart[i], fragmentEnd[i]);
			fragment = fragment.replace(/^\n+|\n+$/g, '');

			// add inline marks for omitted chars and words
			if (fragmentStart[i] > 0) {
				if (fragmentStartType[i] == 'chars') {
					fragment = wDiff.htmlOmittedChars + fragment;
				}
				else if (fragmentStartType[i] == 'blank') {
					fragment = wDiff.htmlOmittedChars + ' ' + fragment;
				}
			}
			if (fragmentEnd[i] < html.length) {
				if (fragmentStartType[i] == 'chars') {
					fragment = fragment + wDiff.htmlOmittedChars;
				}
				else if (fragmentStartType[i] == 'blank') {
					fragment = fragment + ' ' + wDiff.htmlOmittedChars;
				}
			}

			// remove leading and trailing empty lines
			fragment = fragment.replace(/^\n+|\n+$/g, '');

			// add fragment separator
			if (i > 0) {
				diff += wDiff.htmlSeparator;
			}

			// add fragment wrapper
			diff += wDiff.htmlFragmentStart + fragment + wDiff.htmlFragmentEnd;
		}

		// add diff wrapper
		diff = wDiff.htmlContainerStart + diff + wDiff.htmlContainerEnd;

		this.html = diff;
		return;
	};


	// wDiff.wordCount(): count words in string
	//   called from: .getGroups(), .getSameBlocks()
	//

	this.wordCount = function (string) {

		return (string.match(wDiff.regExpWord) || []).length;
	};


	// TextDiff.debugBlocks(): dump blocks object for debugging
	//   input: text: title, group: block object (optional)
	//

	this.debugBlocks = function (text, blocks) {

		if (blocks === undefined) {
			blocks = this.blocks;
		}
		var dump = '\ni \toldBl \tnewBl \toldNm \tnewNm \toldSt \tcount \tuniq \twords \tchars \ttype \tsect \tgroup \tfixed \tmoved \tstring\n';
		for (var i = 0; i < blocks.length; i ++) {
			dump += i + ' \t' + blocks[i].oldBlock + ' \t' + blocks[i].newBlock + ' \t' + blocks[i].oldNumber + ' \t' + (blocks[i].newNumber || 'null').toString().substr(0, 6) + ' \t' + blocks[i].oldStart + ' \t' + blocks[i].count + ' \t' + blocks[i].unique + ' \t' + blocks[i].words + ' \t' + blocks[i].chars + ' \t' + blocks[i].type + ' \t' + blocks[i].section + ' \t' + blocks[i].group + ' \t' + blocks[i].fixed + ' \t' + blocks[i].moved + ' \t' + this.debugShortenString(blocks[i].string) + '\n';
		}
		console.log(text + ':\n' + dump);
	};


	// TextDiff.debugGroups(): dump groups object for debugging
	//   input: text: title, group: group object (optional)
	//

	this.debugGroups = function (text, groups) {

		if (groups === undefined) {
			groups = this.groups;
		}
		var dump = '\ni \toldNm \tblSta \tblEnd \tuniq \tmaxWo \twords \tchars \tfixed \toldNm \tmFrom \tcolor\n';
		for (var i = 0; i < groups.length; i ++) {
			dump += i + ' \t' + groups[i].oldNumber + ' \t' + groups[i].blockStart + ' \t' + groups[i].blockEnd + ' \t' + groups[i].unique + ' \t' + groups[i].maxWords + ' \t' + groups[i].words + ' \t' + groups[i].chars + ' \t' + groups[i].fixed + ' \t' + groups[i].oldNumber + ' \t' + groups[i].movedFrom + ' \t' + groups[i].color + '\n';
		}
		console.log(text + ':\n' + dump);
	};


	// TextDiff.debugShortenString(): shorten string for dumping
	//   called from .debugBlocks, .debugGroups, Text.debugText
	//

	this.debugShortenString = function (string) {

		if (typeof string != 'string') {
			string = string.toString();
		}
		string = string.replace(/\n/g, '\\n');
		string = string.replace(/\t/g, '  ');
		var max = 100;
		if (string.length > max) {
			string = string.substr(0, max - 1 - 30) + '…' + string.substr(string.length - 30);
		}
		return '"' + string + '"';
	};


	// initialze text diff object
	this.init();
};


// wDiff.addScript(): add script to head
//   called from: wDiff.init()
//

wDiff.addScript = function (code) {

	var script = document.createElement('script');
	script.id = 'wDiffBlockHandler';
	if (script.innerText !== undefined) {
		script.innerText = code;
	}
	else {
		script.textContent = code;
	}
	document.getElementsByTagName('head')[0].appendChild(script);
	return;
};


// wDiff.addStyleSheet(): add CSS rules to new style sheet, cross-browser >= IE6
//   called from: wDiff.init()
//

wDiff.addStyleSheet = function (css) {

	var style = document.createElement('style');
	style.type = 'text/css';
	if (style.styleSheet !== undefined) {
		style.styleSheet.cssText = css;
	}
	else {
		style.appendChild( document.createTextNode(css) );
	}
	document.getElementsByTagName('head')[0].appendChild(style);
	return;
};


// initialize wDiff
wDiff.init();

// </syntaxhighlight>

