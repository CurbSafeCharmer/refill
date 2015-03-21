<?php
/**
 * Interface messages for reFill (formerly Reflinks).
 *
 * @toolowner Zhaofeng Li
 */
 
$url = '//tools.wmflabs.org/fengtools/reflinks/';

$messages = array();

/**
 * English
 * @author Contributors
 */
$messages['en'] = array(
	'appname' => 'reFill',
	'tagline' => 'Expand bare references with ease',
	
	// Headings
	'heading-fetchfromwiki' => 'Fetch content from a wiki',
	'heading-rawwikitext' => 'Enter wiki markup',
	'heading-options' => 'Options',
	'heading-citegen' => 'Generate a reference',
	'heading-result' => 'Result',
	'heading-newwikitext' => 'New wiki markup',
	'heading-error' => 'Error',
	'heading-manual' => 'Manual',
	'heading-acknowledgements' => 'Acknowledgements',
	'heading-reportbugs' => 'Report bugs',
	
	// Input placeholders
	'placeholder-pagename' => 'Page name',
	'placeholder-url' => 'URL',
	
	// Labels
	'label-fixpage' => 'Fix page',
	'label-fixwikitext' => 'Fix wikitext',
	'label-toggleadv' => 'Toggle advanced input',
	'label-generate' => 'Generate',
	'label-manual' => 'Manual',
	'label-sourcecode' => 'Source',
	'label-acknowledgements' => 'Acknowledgements',
	'label-reportbugs' => 'Report bugs',
	'label-switchlang' => 'Languages',
	'label-save' => 'Preview / Save on Wiki',
	'label-gadgetoptions' => '(options)',
	'label-homepage' => 'Tool homepage',
	
	// General messages
	'fixed' => '$1 reference(s) fixed!',
	'nofixed' => 'No references fixed.',
	'skipped' => 'The following reference(s) could not be filled:',
	'responsibility' => 'You are responsible for every edit you make.',
	'pleasedoublecheck' => 'Please double-check the edit before saving!',
	'noaccessdate' => 'Note: Dates of access are omitted in the result. Please verify whether the references still support the statements, and add the dates where appropriate.',
	'chancetoreview' => 'You will have a chance to review the edit before it\'s saved.',
	'unfinished' => 'Due to limits, the program was\'t able to fix all references. Try saving and running the tool again.',
	'colourlegend' => 'Colours:',
	'colour-insert' => 'Blue',
	'colour-delete' => 'Orange',
	'diff-insert' => 'Added',
	'diff-delete' => 'Removed',
	'loadingoptions' => 'Loading options...',
	
	// Misc.
	'comingsoon' => 'Coming soon',
	'uhoh' => 'Uh-oh!',
	'developedby' => 'by $1',
	'translatedby' => 'This tool is translated by:',
	'summary' => 'Filled in $1 bare reference(s) with $3',
	'toollink' => '[[:en:WP:REFILL|reFill]]',
	
	// Options
	'option-plainlink' => 'Use plain formatting instead of {{cite web}}',
	'option-plainlink-description' => 'If selected, bare references will be expanded without using {{cite web}}. This is discouraged since cite templates provide a consistent citation style and enable easy parsing by programs.',
	'option-noremovetag' => 'Do not remove link rot tags',
	'option-noremovetag-description' => 'If selected, link rot tags will be kept even if no reference is skipped unexpectedly during the process.',
	'option-nowatch' => 'Do not watch the page when using Wiki as the source',
	'option-nowatch-description' => 'If selected, the \'Watch this page\' checkbox on the on-wiki editing interface will be unticked by default.',
	'option-addblankmetadata' => 'Add blank metadata fields when the information is unavailable',
	'option-noaccessdate' => 'Do not add access dates',
	'option-noaccessdate-description' => 'If selected, dates of access will be omitted in the result.',
	'option-usedomainaswork' => 'Use the base domain name as work when this information cannot be parsed',
	
	// Labs-specific (i.e. not used on the vanilla version on GitHub)
	'wmflabs-thankyoutest' => 'Thank you for testing the experimental version of $1. Please report the bugs you encounter.',
	'wmflabs-latestcommit' => 'Latest commit: $1',
	'wmflabs-poweredby' => 'Powered by Wikimedia Labs',
	'wmflabs-testsummary' => 'Filled in $1 bare reference(s) with the test version of $3',
);

/**
 * Simplified Chinese
 * @author Zhaofeng Li
 */
$messages['zh-hans'] = array(
	'appname' => 'reFill',
	'tagline' => '轻松填充裸露来源链接',
	
	// 题头
	'heading-fetchfromwiki' => '从维基获取页面',
	'heading-rawwikitext' => '输入维基标记',
	'heading-options' => '选项',
	'heading-citegen' => '生成来源',
	'heading-result' => '结果',
	'heading-newwikitext' => '新维基标记',
	'heading-error' => '错误',
	'heading-manual' => '帮助',
	'heading-acknowledgements' => '致谢',
	'heading-reportbugs' => '报告错误',	
	
	// 输入提示
	'placeholder-pagename' => '页面名称',
	'placeholder-url' => 'URL',
	
	// 标签
	'label-fixpage' => '修复页面',
	'label-fixwikitext' => '修复维基代码',
	'label-toggleadv' => '显示高级选项',
	'label-generate' => '生成',
	'label-manual' => '帮助',
	'label-sourcecode' => '源代码',
	'label-acknowledgements' => '致谢',
	'label-reportbugs' => '报告错误',
	'label-switchlang' => '切换语言',
	'label-save' => '在维基上预览/保存',
	'label-gadgetoptions' => '(选项)',
	'label-homepage' => '工具主页',
	
	// 一般信息
	'fixed' => '扩展了 $1 个引证！',
	'nofixed' => '没有修复引证。',
	'skipped' => '下列引证无法被扩充：',
	'responsibility' => '你对自己作出的每一个编辑负责。',
	'pleasedoublecheck' => '请在保存前再三检查！',
	'noaccessdate' => '注：在结果中没有添加存取日期。请确认提供的来源还能支持页面中的论述，并补充存取日期。',
	'chancetoreview' => '你在保存前将有机会再次检查修改。',
	'colourlegend' => '颜色：',
	'colour-insert' => '蓝色',
	'colour-delete' => '橙色',
	'diff-insert' => '增加',
	'diff-delete' => '删除',
	'loadingoptions' => '正在载入……',
	
	// 其他
	'comingsoon' => '敬请期待',
	'uhoh' => '噢，不！',
	'developedby' => '由 $1 开发',
        'translatedby' => '翻译者：',
	'summary' => '用$3填充了$1个裸露来源链接',
	'toollink' => '[[User:Zhaofeng Li/Reflinks]]',
	
	// 选项
	'option-plainlink' => '手工实现格式而不使用 {{cite web}}',
	'option-plainlink-description' => '选取的话，裸露的引用将不会使用{{cite web}}。不建议使用此选项，因为引用模板已提供一致的引用样式和能够轻松解析的程序。',
	'option-noremovetag' => '不要删除链接失效的标签',
	'option-noremovetag-description' => '选取的话，即使在过程中没有引用因错误而跳过，失效的标签也会被保留。',
	'option-nowatch' => '使用维基作为来源的时候不监视页面',
	'option-nowatch-description' => '选取的话，维基编辑模式的「监视本页」核取方块会设置为默认不选取。',
	'option-addblankmetadata' => '资料不可用时添加空白的元数据',
	'option-noaccessdate' => '不要添加访问日期',
	'option-noaccessdate-description' => '选取的话，访问日期将在结果中省略。',
	'option-usedomainaswork' => '这个资料不能被解析的时候使用基本域名',
	
	// 仅供 Labs (不被GitHub上的原版本使用)
	'wmflabs-thankyoutest' => '感谢你帮助测试$1。请报告你遇到的错误，谢谢。',
	'wmflabs-latestcommit' => '最新提交：$1',
	'wmflabs-poweredby' => '由维基媒体实验室给予技术支持',
	'wmflabs-testsummary' => '用测试版的$3填充了$1个裸露来源链接',
);

/**
 * Traditional Chinese
 * @author Pak Long Wu
 */
$messages['zh-hant'] = array(
	'appname' => 'reFill',
	'tagline' => '輕鬆補充裸露來源連結',
	
	// 標題
	'heading-fetchfromwiki' => '從維基獲取內容',
	'heading-rawwikitext' => '輸入維基標記',
	'heading-options' => '選項',
	'heading-citegen' => '產生參考來源',
	'heading-result' => '結果',
	'heading-newwikitext' => '新維基標記',
	'heading-error' => '錯誤',
	'heading-manual' => '說明',
	'heading-acknowledgements' => '鳴謝',
	'heading-reportbugs' => '報告錯誤',	
	
	// 輸入提示
	'placeholder-pagename' => '頁面名稱',
	'placeholder-url' => 'URL',
	
	// 標籤
	'label-fixpage' => '修復頁面',
	'label-fixwikitext' => '修復維基代碼',
	'label-toggleadv' => '顯示進階選項',
	'label-generate' => '產生',
	'label-manual' => '說明',
	'label-sourcecode' => '來源',
	'label-acknowledgements' => '鳴謝',
	'label-reportbugs' => '報告錯誤',
	'label-switchlang' => '語言',
	'label-save' => '預覽/在維基保存',
	'label-gadgetoptions' => '(選項)',
	'label-homepage' => '工具主頁',
	
	// 一般信息
	'fixed' => '修復了$1 個來源！',
	'nofixed' => '沒有來源被修復。',
	'skipped' => '下列的引用來源未能補充：',
	'responsibility' => '您須要對每個你做的編輯負責。',
	'pleasedoublecheck' => '請在保存前再三檢查！',
	'noaccessdate' => '注意：訪問日期會在結果中忽略。請驗證引用是否仍然支持內容，並在適當情況添加日期。',
	'chancetoreview' => '你有機會在保存之前再查看編輯。',
	'colourlegend' => '顏色：',
	'colour-insert' => '藍',
	'colour-delete' => '橙',
	'diff-insert' => '已添加',
	'diff-delete' => '已清除',
	'loadingoptions' => '正在載入選項。。。',
	
	// 其他
	'comingsoon' => '即將推出',
	'uhoh' => '哎喲！',
	'developedby' => '由 $1 開發',
        'translatedby' => '翻譯者：',
	'summary' => '已用 $3 補充了 $1 個裸露來源連結',
	'toollink' => '[[User:Zhaofeng Li/Reflinks]]',
	
	// 選項
	'option-plainlink' => '使用普通的格式取代{{cite web}}',
	'option-plainlink-description' => '選取的話，裸露的引用將不會使用{{cite web}}。不建議使用此選項，因為引用模板已提供一致的引用樣式和能夠輕鬆解析的程序。',
	'option-noremovetag' => '不要刪除鏈接失效的標籤',
	'option-noremovetag-description' => '選取的話，即使在過程中沒有引用因錯誤而跳過，失效的標籤也會被保留。',
	'option-nowatch' => '使用維基作為來源的時候不監視頁面',
	'option-nowatch-description' => '選取的話，維基編輯模式的「監視本頁」核取方塊會設置為默認不選取。',
	'option-addblankmetadata' => '資料不可用時添加空白的元數據',
	'option-noaccessdate' => '不要添加訪問日期',
	'option-noaccessdate-description' => '選取的話，訪問日期將在結果中省略。',
	'option-usedomainaswork' => '這個資料不能被解析的時候使用基本域名',
	
	// 只供 Labs (不被GitHub上的原版本使用)
	'wmflabs-thankyoutest' => '感謝您幫助測試 $1 測試版本。請回報你遇到的問題。',
	'wmflabs-latestcommit' => '最後提交: $1',
	'wmflabs-poweredby' => '由維基媒體實驗室給予技術支持',
	'wmflabs-testsummary' => '已用 $3 的測試版本補充了 $1 個裸露來源連結',
);

/**
 * Portuguese
 * @author Victor Lopes
 */
$messages['pt'] = array(
	'tagline' => 'Formate referências com facilidade',

	// Headings
	'heading-fetchfromwiki' => 'Obtenha conteúdo de uma wiki',
	'heading-rawwikitext' => 'Entre na marcação Wiki',
	'heading-options' => 'Opções',
	'heading-citegen' => 'Gerar uma referência',
	'heading-result' => 'Resultado',
	'heading-newwikitext' => 'Nova marcação Wiki',
	'heading-error' => 'Erro',
	'heading-manual' => 'Manual',
	'heading-acknowledgements' => 'Agradecimentos',
	'heading-reportbugs' => 'Relatar bugs',

	// Input placeholders
	'placeholder-pagename' => 'Nome da página',
	'placeholder-url' => 'URL',

	// Labels
	'label-fixpage' => 'Consertar página',
	'label-fixwikitext' => 'Consertar texto Wiki',
	'label-toggleadv' => 'Ativar entradas avançadas',
	'label-generate' => 'Gerar',
	'label-manual' => 'Manual',
	'label-sourcecode' => 'Fonte',
	'label-acknowledgements' => 'Agradecimentos',
	'label-reportbugs' => 'Relatar bugs',
	'label-switchlang' => 'Idiomas',
	'label-save' => 'Pré-visualizar / Salvar na Wiki',
	'label-gadgetoptions' => '(opções)',

	// General messages
	'fixed' => '$1 referência(s) consertada(s)!',
	'nofixed' => 'Nenhuma referência consertada.',
	'skipped' => 'A(s) referência(s) a seguir não pôde/puderam ser formatada(s):',
	'responsibility' => 'Você é responsável por toda edição que fizer.',
	'pleasedoublecheck' => 'Favor conferir a edição antes de salvar!',
	'noaccessdate' => 'Nota: Datas de acesso são omitidas do resultado. Favor verificar se as referências ainda validam as afirmações e adicionar as datas quando apropriado.',
	'chancetoreview' => 'Você poderá revisar a edição antes de salvá-la.',
	'colourlegend' => 'Cores:',
	'colour-insert' => 'Azul',
	'colour-delete' => 'Laranja',
	'diff-insert' => 'Adicionado',
	'diff-delete' => 'Removido',

	// Misc.
	'comingsoon' => 'Em breve',
	'uhoh' => 'Oh-oh!',
	'developedby' => 'por $1',
	'summary' => 'Formatando $1 referência(s) com $3',
	'toollink' => '[[en:User:Zhaofeng Li/Reflinks|User:Zhaofeng Li/Reflinks]]',

	// Options
	'option-plainlink' => 'Usar formatação simples em vez de {{citar web}}',
	'option-plainlink-description' => 'Se selecionado, as referências não formatadas serão expandidas sem a prédefinição {{citar web}}. Isso é desencorajado uma vez que as prédefinições de citação garantem um estilo de citação consistente e permitem fácil análise por parte de programas.',
	'option-noremovetag' => 'Não remover marcações de referências não formatadas',
	'option-noremovetag-description' => 'Se selecionado, as marcações de referências não formatadas serão mantidas mesmo que todas as referências tenham sido consertadas.',
	'option-nowatch' => 'Não vigiar a página se a ferramenta for utilizada na Wikipédia',
	'option-nowatch-description' => 'Se selecionado, a opção \'Vigiar esta página\' na interface de edição da Wikipédia será desmarcada por definição.',
	'option-addblankmetadata' => 'Adicionar campos em branco de metadados quando a informação estiver indisponível.',
	'option-noaccessdate' => 'Não adicionar datas de acesso',
	'option-noaccessdate-description' => 'Se selecionado, as datas de acesso serão omitidas por definição.',
	'option-usedomainaswork' => 'Usar o nome básico do domínio quando esta informação não puder ser analisada',

	// Labs-specific (i.e. not used on the vanilla version on GitHub)
	'wmflabs-thankyoutest' => 'Obrigado por testar a versão experimental de $1. Favor relatar bugs que você encontrar.',
	'wmflabs-latestcommit' => 'Commit mais recente: $1',
	'wmflabs-poweredby' => 'Distribuído por Wikimedia Labs',
	'wmflabs-testsummary' => 'Formatando $1 referência(s) com a versão teste de $3',
);

/**
 * Dutch
 * @author Frank Geerlings
 */
$messages['nl'] = array(
	'tagline' => 'Eenvoudig kale verwijzingen uitbreiden',
	
	// Headings
	'heading-fetchfromwiki' => 'Haal inhoud op van een wiki',
	'heading-rawwikitext' => 'Voer wiki-opmaak in',
	'heading-options' => 'Opties',
	'heading-citegen' => 'Een verwijzing aanmaken',
	'heading-result' => 'Resultaat',
	'heading-newwikitext' => 'Nieuwe wiki-opmaak',
	'heading-error' => 'Foutmelding',
	'heading-manual' => 'Handleiding',
	'heading-acknowledgements' => 'Dankwoord',
	'heading-reportbugs' => 'Bugs melden',
	
	// Input placeholders
	'placeholder-pagename' => 'Naam van het artikel',
	'placeholder-url' => 'URL',
	
	// Labels
	'label-fixpage' => 'Verbeter pagina',
	'label-fixwikitext' => 'Verbeter wikitekst',
	'label-toggleadv' => 'Instellingen voor gevorderden weergeven',
	'label-generate' => 'Aanmaken',
	'label-manual' => 'Handleiding',
	'label-sourcecode' => 'Broncode',
	'label-acknowledgements' => 'Dankwoord',
	'label-reportbugs' => 'Bugs melden',
	'label-switchlang' => 'Taalkeuze',
	'label-save' => 'Voorvertoning / Opslaan op Wiki',
	'label-gadgetoptions' => '(opties)',
	'label-homepage' => 'Tool-website',
	
	// General messages
	'fixed' => '$1 verwijzing(en) verbeterd!',
	'nofixed' => 'Geen verwijzingen verbeterd.',
	'skipped' => 'De volgende verwijzing(en) kon(den) niet worden aangevuld:',
	'responsibility' => 'U bent zelf verantwoordelijk voor iedere aanpassing die u aanbrengt.',
	'pleasedoublecheck' => 'Gelieve zorgvuldig de bewerking te controleren voordat u opslaat!',
	'noaccessdate' => 'Let op: De datum van raadpleging is weggelaten in het resultaat. Deze kunt u weer toevoegen als u bent nagegegaan of hetgeen in het artikel wordt beweerd door de bron gestaafd wordt.',
	'chancetoreview' => 'U heeft de mogelijkheid uw wijzigingen te controleren voor ze worden opgeslagen.',
	'colourlegend' => 'Kleuren:',
	'colour-insert' => 'Blauw',
	'colour-delete' => 'Oranje',
	'diff-insert' => 'Toegevoegd',
	'diff-delete' => 'Verwijderd',
	'loadingoptions' => 'Opties worden geladen...',
	
	// Misc.
	'comingsoon' => 'Verschijnt binnenkort',
	'uhoh' => 'O jee!',
	'developedby' => 'door $1',
	'summary' => '$1 kale verwijzing(en) aangevuld met $3',
	'toollink' => '[[:en:User:Zhaofeng Li/Reflinks]]',
	
	// Options
	'option-plainlink' => 'Gebruik eenvoudige opmaak in plaats van {{cite web}}',
	'option-plainlink-description' => 'Indien aangevinkt zullen kale verwijzingen worden uitgebreid zonder {{cite web}} te gebruiken. Dit dient te worden vermeden omdat het citeer-sjabloon een consistente stijl voor verwijzingen afdwingt en geautomatiseerde verwerking vereenvoudigdt.',
	'option-noremovetag' => 'Laat link rot-tags staan',
	'option-noremovetag-description' => 'Indien aangevinkt zullen bestaande link rot-tags behouden blijven, ook als de verwijzing dit maal kon worden aangevuld.',
	'option-nowatch' => 'Blijf de pagina niet volgen als Wiki als bron wordt gebruikt',
	'option-nowatch-description' => 'Indien aangevinkt zal \'Deze pagina volgen\' niet worden aangevinkt op de bewerkingspagina op de wiki.',
	'option-addblankmetadata' => 'Voeg lege metadata-velden toe als de informatie ontbreekt',
	'option-noaccessdate' => 'Voeg geen datum van raadpleging toe',
	'option-noaccessdate-description' => 'Indien aangevinkt zal de \'laatst geraadpleegd\'-datum niet in het resultaat worden opgenomen.',
	'option-usedomainaswork' => 'Gebruik de domeinnaam als \'werk\' als deze informatie niet kan worden afgeleid',
	
	// Labs-specific (i.e. not used on the vanilla version on GitHub)
	'wmflabs-thankyoutest' => 'Dank u voor het testen van de experimentele versie van $1. Het is fijn als u het meldt als u een bug vindt.',
	'wmflabs-latestcommit' => 'Laatste commit: $1',
	'wmflabs-poweredby' => 'Aangedreven door Wikimedia Labs',
	'wmflabs-testsummary' => '$1 kale verwijzing(en) aangevuld met de testversie van $3',
);

$messages['qqq'] = array(
	'appname' => '{{Optional|Translate it only if you have a good name that is concise and gives a basic idea of what the tool does in your language.}}',
	'tagline' => 'A brief explanation of the tool\'s purpose. This is displayed below its name on the main page of the tool. Please keep it concise.',
	
	// Headings
	'heading-fetchfromwiki' => 'The heading of the form of the \'Fetch from wiki\' mode. Used on the main page.',
	'heading-rawwikitext' => 'The heading of the form of the \'Enter wikitext\' mode. Used on the main page.',
	'heading-options' => 'The heading of the form sections containing checkboxes of options. Used on the main page.',
	'heading-citegen' => 'The heading of the form of the citation generator tool. Used on the main page.',
	'heading-result' => 'The heading of the result page. This is also used as the browser title of the page.',
	'heading-newwikitext' => 'The heading of the section containing the resulting wikitext. Used on the result page.',
	'heading-error' => 'The heading of the error page. This is also used as the browser title of the page.',
	'heading-manual' => 'The heading of the manual page. This is also used as the browser title of the page.',
	'heading-acknowledgements' => 'The heading of the acknowledgements page. This is also used as the browser title of the page.',
	'heading-reportbugs' => 'The heading of the bug reporting page. This is also used as the browser title of the page.',
	
	// Input placeholders
	'placeholder-pagename' => 'The placeholder text shown in an input field expecting a name of a wiki page.',
	'placeholder-url' => 'The placeholder text shown in an input field expecting a URL.',
	
	// Labels
	'label-fixpage' => 'This is used as the button label.',
	'label-fixwikitext' => 'This is used as the button label.',
	'label-toggleadv' => 'This is used as the button label.',
	'label-generate' => 'This is used as the button label.',
	'label-manual' => 'This is used as the label of a link in the navigation bar.',
	'label-sourcecode' => 'This is used as the label of a link in the navigation bar.',
	'label-acknowledgements' => 'This is used as the label of a link in the navigation bar.',
	'label-reportbugs' => 'This is used as the label of a link in the navigation bar.',
	'label-switchlang' => 'This is used as the label of a link in the navigation bar.',
	'label-save' => 'This is used as the button label.',
	
	// General messages
	/*
		Well, I remember the the translation interface in Launchpad gives an automatic hint about how to deal with the parameters.
		Maybe the Translate extension can do the same? (Or am I missing something?)
	*/
	'fixed' => 'Please keep the $1 intact. It\'ll be replaced by a number at run-time.',
	'colourlegend' => 'This will be followed by a colour legend.',
	'colour-insert' => 'This is used in the diff highlighting colour legend.',
	'colour-delete' => 'This is used in the diff highlighting colour legend.',
	'diff-insert' => 'This is used in the diff highlighting colour legend for added text.',
	'diff-delete' => 'This is used in the diff highlighting colour legend for removed text.',
	
	// Misc.
	'developedby' => 'The $1 will be replaced by the names of the developers. Keep it intact.',
	'summary' => 'The default edit summary. $1 will be replaced by the fixed count, $2 by the skipped count and $3 by a wikilink to the manual page',
	'toollink' => 'A wikilink to the documentation page',
	
	// Options
	'option-plainlink' => 'Label of an option checkbox. Plain formatting means manually using wiki markup to format the reference instead of citation templates. The type of the formatting is undetermined and may depend on the wiki.',
	'option-noremovetag' => 'This may not be applicable to all wikis.',
	
	// Labs-specific (i.e. not used on the vanilla version on GitHub)
	'wmflabs-thankyoutest' => 'Used on the main page of the experimental version on Tool Labs. The $1 will be replaced by the application name at run-time.',
	'wmflabs-latestcommit' => 'Information about the software commit. The $1 will be replaced by the actual commit information. Keep it intact.',
);
