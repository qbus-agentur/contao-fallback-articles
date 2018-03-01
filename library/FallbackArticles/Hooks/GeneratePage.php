<?php

/**
 * Contao Open Source CMS
 *
 * Fallback Articles Extension by Qbus
 *
 * @author  Alex Wuttke <alw@qbus.de>
 * @license LGPL-3.0+
 */

namespace Qbus\FallbackArticles\Hooks;

/*
 * Provide methods that get called in \PageRegular::generate
 */
class GeneratePage extends \System
{

	protected $defaultSections = ['header', 'left', 'right', 'main', 'footer'];

	/*
	 * Provide a hook for modules to generate fallback articles if there is no
	 * article in a layout section on the current page.
	 *
	 * @param TODO
	 */
	public function getFallbackArticles($objPage, $objLayout, $objPageRegular) {
		if (!$objLayout->setFallbackArticles) {
			return;
		}

		$fallbackConfTable = deserialize($objLayout->fallbackArticles);
		if (empty($fallbackConfTable)) {
			return;
		}

		foreach ($fallbackConfTable as $fallbackConf) {
			$blnFallbackNeeded = false;
			$col = $fallbackConf['col'];

			$blnFallbackNeeded = $this->isFallbackNeeded($col, $objPageRegular);
			if (!$blnFallbackNeeded) {
				continue;
			}

			// HOOK: Get fallback articles via specified method
			$fallbackMethod = $fallbackConf['fallback'];
			if (
				isset($GLOBALS['TL_HOOKS']['getFallbackArticles'][$fallbackMethod])
				&& is_array($GLOBALS['TL_HOOKS']['getFallbackArticles'][$fallbackMethod])
			) {
				$callback = $GLOBALS['TL_HOOKS']['getFallbackArticles'][$fallbackMethod];
				$fallbackArticles = static::importStatic($callback[0])->{$callback[1]}($objPage->id, $col);
				if (in_array($col, $this->defaultSections)) {
					$objPageRegular->Template->$col = $fallbackArticles;
				}
				else {
					$objPageRegular->Template->sections[$col] = $fallbackArticles;
				}
			}
		}
	}

	/*
	 * Check if fallback articles are required, i. e. if there are no articles
	 * in the section.
	 *
	 * @param TODO
	 */
	protected function isFallbackNeeded($col, $objPageRegular) {
		// Check if the section is completely empty before we check for
		// articles specifically.
		$sectionContent = in_array($col, $this->defaultSections)
			? $objPageRegular->Template->$col
			: $objPageRegular->Template->sections[$col];
		if (empty($sectionContent)) {
			return true;
		}
		// 0 means article
		$sectionContent = \Controller::getFrontendModule(0, $col);
		if (empty($sectionContent)) {
			return true;
		}

		return false;
	}

}
