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
		return $cols;
	}

	public function getFallbackMethods(\MultiColumnWizard $mcw) {
		$methods = [];
		if (
			isset($GLOBALS['TL_HOOKS']['getFallbackArticles'])
			&& is_array($GLOBALS['TL_HOOKS']['getFallbackArticles'])
		) {
			$methods = array_keys($GLOBALS['TL_HOOKS']['getFallbackArticles']);
		}
		return $methods;
	}

}

