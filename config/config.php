<?php

/**
 * Contao Open Source CMS
 *
 * Fallback Articles Extension by Qbus
 *
 * @author  Alex Wuttke <alw@qbus.de>
 * @license LGPL-3.0+
 */

$GLOBALS['TL_HOOKS']['generatePage'][] = [
	\Qbus\FallbackArticles\Hooks\GeneratePage::class,
	'getFallbackArticles'
];
