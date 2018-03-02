<?php

/**
 * Contao Open Source CMS
 *
 * Fallback Articles Extension by Qbus
 *
 * @author  Alex Wuttke <alw@qbus.de>
 * @license LGPL-3.0+
 */

namespace Qbus\FallbackArticles\Dca;

/*
 * Provide methods that are used by the tl_layout data configuration array.
 */
class Layout
{

	/*
	 * Get all sections: Default columns and custom sections
	 */
	public function getSections(\MultiColumnWizard $mcw) {
		$cols = ['header', 'left', 'right', 'main', 'footer'];
		$sections = trimsplit(',', $mcw->activeRecord->sections);
		if (!empty($sections) && is_array($sections)) {
			$cols = array_merge($cols, $sections);
		}
		$colsWithLabels = [];
		foreach ($cols as $col) {
			$colsWithLabels[$col] = (isset($GLOBALS['TL_LANG']['COLS'][$col]) && !is_array($GLOBALS['TL_LANG']['COLS'][$col]))
				? $GLOBALS['TL_LANG']['COLS'][$col]
				: $col;
		}
		return $colsWithLabels;
	}

	public function getFallbackMethods(\MultiColumnWizard $mcw) {
		\System::loadLanguageFile('fallback_articles_methods');
		$methods = [];
		if (
			isset($GLOBALS['TL_HOOKS']['getFallbackArticles'])
			&& is_array($GLOBALS['TL_HOOKS']['getFallbackArticles'])
		) {
			foreach (array_keys($GLOBALS['TL_HOOKS']['getFallbackArticles']) as $key) {
				$methods[$key] = $GLOBALS['TL_LANG']['fallback_articles_methods'][$key] ?: $key;
			}
		}
		return $methods;
	}

}

