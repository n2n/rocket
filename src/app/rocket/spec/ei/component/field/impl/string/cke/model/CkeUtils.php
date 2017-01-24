<?php
namespace rocket\spec\ei\component\field\impl\string\cke\model;

use n2n\core\container\N2nContext;
use n2n\reflection\ArgUtils;

class CkeUtils {
	
	/**
	 * @param string $ckeLinkProviderLookupId
	 * @param N2nContext $n2nContext
	 * @throws \InvalidArgumentException
	 * @return \rocket\spec\ei\component\field\impl\string\cke\model\CkeLinkProvider|mixed
	 */
	public static function lookupCkeLinkProvider(string $ckeLinkProviderLookupId = null, N2nContext $n2nContext) {
		if ($ckeLinkProviderLookupId === null) return null;
		
		$ckeLinkProvider = null;
		try {
			$ckeLinkProvider = $n2nContext->lookup($ckeLinkProviderLookupId);
		} catch (\n2n\context\LookupFailedException $e) {
			throw new \InvalidArgumentException('Could not lookup CkeLinkProvider with lookup id: ' . $ckeLinkProviderLookupId);
		}
		
		if (!($ckeLinkProvider instanceof CkeLinkProvider)) {
			throw new \InvalidArgumentException('Provided CkeLinkProvider ' . get_class($ckeLinkProvider) 
					. ' does not implement Interface ' . CkeLinkProvider::class);
		}
		
		return $ckeLinkProvider;
	}
	
	/**
	 * @param array $ckeLinkProviderLookupIds
	 * @param N2nContext $n2nContext
	 * @return \rocket\spec\ei\component\field\impl\string\cke\model\CkeLinkProvider[]
	 */
	public static function lookupCkeLinkProviders(array $ckeLinkProviderLookupIds = null, N2nContext $n2nContext) {
		ArgUtils::valArray($ckeLinkProviderLookupIds, 'string', true);
		
		$ckeLinkProviders = array();
		foreach ((array) $ckeLinkProviderLookupIds as $ckeLinkProviderLookupId) {
			$ckeLinkProviders[$ckeLinkProviderLookupId] = self::lookupCkeLinkProvider($ckeLinkProviderLookupId, $n2nContext);
		}
		return $ckeLinkProviders;
	}
	
	/**
	 * @param string $ckeCssConfigLookupId
	 * @param N2nContext $n2nContext
	 * @throws \InvalidArgumentException
	 * @return \rocket\spec\ei\component\field\impl\string\cke\model\CkeCssConfig
	 */
	public static function lookupCkeCssConfig(string $ckeCssConfigLookupId = null, N2nContext $n2nContext) {
		if ($ckeCssConfigLookupId === null) return null;
		
		$ckeCssConfig = null;
		try {
			$ckeCssConfig = $n2nContext->lookup($ckeCssConfigLookupId);
		} catch (\n2n\context\LookupFailedException $e) {
			throw new \InvalidArgumentException('Could not lookup CkeCssConfig with lookup id: ' 
					. $ckeCssConfigLookupId);
		}
		
		if (!($ckeCssConfig instanceof CkeCssConfig)) {
			throw new \InvalidArgumentException('Provided CkeCssConfig ' . get_class($ckeCssConfig)
					. ' does not implement Interface ' . CkeCssConfig::class);
		}
		
		return $ckeCssConfig;
	}
}

