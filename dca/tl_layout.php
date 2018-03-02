<?php

/**
 * Contao Open Source CMS
 *
 * Fallback Articles Extension by Qbus
 *
 * @author  Alex Wuttke <alw@qbus.de>
 * @license LGPL-3.0+
 */

use Qbus\FallbackArticles\Dca\Layout;

$GLOBALS['TL_DCA']['tl_layout']['palettes']['__selector__'][] = 'setFallbackArticles';

// Account for potential previous modifications of the palette:
// - There may be other field names that contain the string "modules";
// - Fields may or may not have been added after 'modules';
// - 'modules' may have been removed from the palette (in which case we don't
//   show any FallbackArticles fields).
$pattern = '/(,modules)([;,])/';
if (preg_match($pattern, $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']) === 1) {
	$GLOBALS['TL_DCA']['tl_layout']['palettes']['default'] = preg_replace(
		$pattern,
		'$1,setFallbackArticles$2',
		$GLOBALS['TL_DCA']['tl_layout']['palettes']['default']
	);
}

$GLOBALS['TL_DCA']['tl_layout']['subpalettes']['setFallbackArticles'] = 'fallbackArticles';

$GLOBALS['TL_DCA']['tl_layout']['fields']['setFallbackArticles'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_layout']['setFallbackArticles'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['submitOnChange' => true],
	'sql'       => "char(1) NOT NULL default ''"
];
$GLOBALS['TL_DCA']['tl_layout']['fields']['fallbackArticles'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_layout']['fallbackArticles'],
	'exclude'   => true,
	'inputType' => 'multiColumnWizard',
	'eval'      => [
		'columnFields' => [
			'col'      => [
				'label'            => &$GLOBALS['TL_LANG']['tl_layout']['fallbackArticles_col'],
				'inputType'        => 'select',
				'options_callback' => [Layout::class, 'getSections'],
				'eval'             => [
					// MultiColumnWizard doesn't set tl_select_column class
					'style'  => 'width: 140px;',
					// see https://github.com/menatwork/MultiColumnWizard/issues/188
					'chosen' => true
				]
			],
			'fallback' => [
				'label'            => &$GLOBALS['TL_LANG']['tl_layout']['fallbackArticles_fallback'],
				'inputType'        => 'select',
				'options_callback' => [Layout::class, 'getFallbackMethods'],
				'eval'             => [
					// MultiColumnWizard doesn't set tl_select_column class
					'style'  => 'width: 140px;',
					// see https://github.com/menatwork/MultiColumnWizard/issues/188
					'chosen' => true
				]
			]
		]
	],
	'sql'       => "blob NULL"
];
