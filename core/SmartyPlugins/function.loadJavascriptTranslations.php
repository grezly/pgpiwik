<?php

/**
 *	inserts javascript translation array into the template from given plugins
 *  must be called with 'plugins' argument which consists of space-separated module names (i.e. plugins)
 *
 *
 *  Example (use in template):
 *
 *  {loadJavascriptTranslations plugins='SitesManager CoreHome General'}
 *
 *  loads javascript array translations from main translation file ('General')
 *  and both 'CoreHome' and 'SitesManager' plugins translations
 *
 *  Note: You can put noHtml=1 option in order to output pure JS code
 * 
 *  only translations with '_fs' suffix will be loaded
 *
 *  in order to use translation in your javascript use _pk_translate function
 *  (it is always loaded with translations):
 *
 *  <script type="text/javascript">
 *     alert(_pk_translate('MY_TRANSLATION_STRING'))
 *  </script>
 *
 *  Note: Use translation string from your translation file WITHOUT '_js' suffix.
 * 
 * _pk_translate DOES NOT support printf() arguments, but you can call:
 *
 *   sprintf(_pk_translate('_NB_OF_EGGS'),'ten')
 *   (where _NB_OF_EGGS is defined in translation file as i.e. 'There is %s eggs on the table')
 * 
 * sprintf() function is by default included when loading translations
 */

function smarty_function_loadJavascriptTranslations($params, &$smarty) 
{
	if(!isset($params['plugins']))
	{
		throw new Exception("The smarty function loadJavascriptTranslations needs a 'plugins' parameter.");
	}
	$translate = Piwik_Translate::getInstance();
	$jsTranslations = $translate->getJavascriptTranslations(explode(' ',$params['plugins']));
	
	$jsCode = "";
	
	if( isset($params['noHtml']) )
	{
		$jsCode .= $jsTranslations;
	}
	else
	{
		$jsCode .= '<script type="text/javascript">';
		$jsCode .= $jsTranslations;
		$jsCode .= '</script>';
	}
	
	return $jsCode;
}