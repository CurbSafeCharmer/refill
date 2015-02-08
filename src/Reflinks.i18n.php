<?php
/**
 * Interface messages for Reflinks.
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
	'appname' => 'Reflinks',
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
	'summary' => 'Filled in $1 bare reference(s) with $3',
	'toollink' => '[[User:Zhaofeng Li/Reflinks]]',
	
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
	'appname' => '来源扩充',
	'tagline' => '轻松填充裸露来源链接',
	
	// 题头
	'heading-fetchfromwiki' => '从维基获取页面',
	'heading-rawwikitext' => '输入维基代码',
	'heading-options' => '选项',
	'heading-citegen' => '生成来源',
	'heading-result' => '结果',
	'heading-newwikitext' => '新维基代码',
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
	'summary' => '用$3填充了$1个裸露来源链接',
	'toollink' => '[[User:Zhaofeng Li/Reflinks]]',
	
	// 选项
	'option-plainlink' => '手工实现格式而不使用 {{cite web}}',
	
	// 仅供 Labs (不被GitHub上的原版本使用)
	'wmflabs-thankyoutest' => '感谢你帮助测试$1。请报告你遇到的错误，谢谢。',
	'wmflabs-latestcommit' => '最新提交：$1',
	'wmflabs-poweredby' => '由维基媒体实验室驱动',
	'wmflabs-testsummary' => '用测试版的$3填充了$1个裸露来源链接',
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
