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

		// Backwards compatibility. Before the introduction of $fallbackInCol,
		// all fallback methods were checked and the last one won. To still
		// produce the same output, reverse the order of the configured methods.
		// TODO: Drop in next major version.
		$fallbackConfTable = array_reverse($fallbackConfTable);
		$fallbackInCol = [];
		foreach ($fallbackConfTable as $fallbackConf) {
			$blnFallbackNeeded = false;
			$col = $fallbackConf['col'];

			$blnFallbackNeeded = $this->isFallbackNeeded($col, $objPageRegular);
			if (!$blnFallbackNeeded || (isset($fallbackInCol[$col]) && $fallbackInCol[$col])) {
				continue;
			}

			// HOOK: Get fallback articles via specified method
			$fallbackMethod = $fallbackConf['fallback'];
			if (
				isset($GLOBALS['TL_HOOKS']['getFallbackArticles'][$fallbackMethod])
				&& is_array($GLOBALS['TL_HOOKS']['getFallbackArticles'][$fallbackMethod])
			) {
				$callback = $GLOBALS['TL_HOOKS']['getFallbackArticles'][$fallbackMethod];
				$fallbackArticles[$col] = static::importStatic($callback[0])->{$callback[1]}($objPage->id, $col);
				// Check for false because an article might be an empty string
				$fallbackInCol[$col] = ($fallbackArticles[$col] !== false);
			}
		}
		$this->insertFallbackArticles($objLayout, $objPageRegular, $fallbackArticles);
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

	protected function insertFallbackArticles($objLayout, $objPageRegular, $fallbackArticles) {
		// Replicate most of the core's module assembly because the generatePage
		// hook is too late.
		// TODO: Drop 3.5 support and use getArticles hook

		// Initialize modules and sections
		$arrCustomSections = array();
		$arrSections = $this->defaultSections;
		$arrModules = deserialize($objLayout->modules);

		$arrModuleIds = array();

		// Filter the disabled modules
		foreach ($arrModules as $module) {
			if ($module['enable']) {
				$arrModuleIds[] = $module['mod'];
			}
		}

		// Get all modules in a single DB query
		$objModules = \ModuleModel::findMultipleByIds($arrModuleIds);

		if ($objModules !== null || $arrModules[0]['mod'] == 0) { // see #4137
			$arrMapper = array();

			// Create a mapper array in case a module is included more than once (see #4849)
			if ($objModules !== null) {
				while ($objModules->next()) {
					$arrMapper[$objModules->id] = $objModules->current();
				}
			}

			foreach ($arrModules as $arrModule) {
				// Disabled module
				if (!$arrModule['enable']) {
					continue;
				}

				// Replace the module ID with the module model
				if ($arrModule['mod'] > 0 && isset($arrMapper[$arrModule['mod']])) {
					$arrModule['mod'] = $arrMapper[$arrModule['mod']];
				}

				// 0 means article
				$addFallback = ($arrModule['mod'] == 0 && $fallbackArticles[$arrModule['col']]);

				// Generate the modules
				if (in_array($arrModule['col'], $arrSections)) {
					// Filter active sections (see #3273)
					if ($arrModule['col'] == 'header' && $objLayout->rows != '2rwh' && $objLayout->rows != '3rw') {
						continue;
					}
					if ($arrModule['col'] == 'left' && $objLayout->cols != '2cll' && $objLayout->cols != '3cl') {
						continue;
					}
					if ($arrModule['col'] == 'right' && $objLayout->cols != '2clr' && $objLayout->cols != '3cl') {
						continue;
					}
					if ($arrModule['col'] == 'footer' && $objLayout->rows != '2rwf' && $objLayout->rows != '3rw') {
						continue;
					}

					if ($this->isFallbackNeeded($arrModule['col'], $objPageRegular)) {
						$objPageRegular->Template->{$arrModule['col']} .= $addFallback
							? $fallbackArticles[$arrModule['col']]
							: \Controller::getFrontendModule($arrModule['mod'], $arrModule['col']);
					}
				}
				else {
					$arrCustomSections[$arrModule['col']] .= $addFallback
						? $fallbackArticles[$arrModule['col']]
						: \Controller::getFrontendModule($arrModule['mod'], $arrModule['col']);
				}
			}
		}

		$objPageRegular->Template->sections = $arrCustomSections;
	}

}
