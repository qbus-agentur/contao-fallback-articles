<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Library
	'Qbus\FallbackArticles\Hooks\GeneratePage' => 'system/modules/fallback_articles/library/FallbackArticles/Hooks/GeneratePage.php',
	'Qbus\FallbackArticles\Dca\Layout'         => 'system/modules/fallback_articles/library/FallbackArticles/Dca/Layout.php',
));
