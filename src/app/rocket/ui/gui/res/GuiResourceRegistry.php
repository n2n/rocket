<?php

namespace rocket\ui\gui\res;

use n2n\context\attribute\ThreadScoped;
use n2n\context\attribute\SessionScoped;
use n2n\core\container\N2nContext;
use n2n\context\attribute\Inject;
use n2n\io\managed\FileManager;
use n2n\util\crypt\TokenUtils;
use n2n\io\managed\File;
use n2n\util\StringUtils;
use n2n\util\JsonDecodeFailedException;
use n2n\util\type\attrs\DataMap;
use n2n\util\type\attrs\AttributesException;
use n2n\io\managed\img\ImageDimension;
use n2n\io\managed\impl\TmpFileManager;
use n2n\io\managed\img\ImageFile;

#[ThreadScoped]
class GuiResourceRegistry {

	#[SessionScoped]
	private array $fileAccessTokens = [];

	#[Inject]
	private N2nContext $n2nContext;

	function registerFile(string $fileManagerLookupId, string $qualifiedName, ImageDimension $imageDimension = null): string {
		$key = $this->createKey($fileManagerLookupId, $qualifiedName, $imageDimension);
		if (isset($this->fileAccessTokens[$key])) {
			return $this->fileAccessTokens[$key];
		}

		$fileManager = $this->n2nContext->lookup($fileManagerLookupId);
		if (!($fileManager instanceof FileManager) && !($fileManager instanceof TmpFileManager)) {
			throw new \InvalidArgumentException('Lookupable with id "' . $fileManagerLookupId
					. '" does not implement ' . FileManager::class);
		}

		if ($imageDimension !== null && !($fileManager instanceof TmpFileManager) && !$fileManager->hasThumbSupport()) {
			throw new \InvalidArgumentException('FileManager "' . $fileManagerLookupId
					. '" does not have thumb support');
		}

		return $this->fileAccessTokens[$key] = TokenUtils::randomToken();
	}

	function lookupFile(string $fileAccessToken): ?File {
		$key = array_search($fileAccessToken, $this->fileAccessTokens, true);
		if ($key === false) {
			return null;
		}

		try {
			$dataMap = new DataMap(StringUtils::jsonDecode($key, true));
			$fileManagerId = $dataMap->reqString('fm');
			$qualifiedName = $dataMap->reqString('qn');
			$imageDimension = null;
			if (null !== ($imageDimensionStr = $dataMap->optString('id'))) {
				$imageDimension = ImageDimension::createFromString($imageDimensionStr);
			}
		} catch (JsonDecodeFailedException|AttributesException|\InvalidArgumentException $e) {
			return null;
		}

		$fileManager = $this->n2nContext->lookup($fileManagerId);
		if ($fileManager instanceof FileManager) {
			$file = $fileManager->getByQualifiedName($qualifiedName);
		} else if ($fileManager instanceof TmpFileManager) {
			$file = $fileManager->getSessionFile($qualifiedName, $this->n2nContext->getHttp()->getLookupSession());
		} else {
			return null;
		}

		if ($file === null || $imageDimension === null) {
			return $file;
		}

		return (new ImageFile($file))->getThumbFile($imageDimension);
	}

	private function createKey(string $fileManagerLookupId, string $qualifiedName, ?ImageDimension $imageDimension): string {
		return json_encode(['fm' => $fileManagerLookupId, 'qn' => $qualifiedName, 'id' => $imageDimension?->__toString()]);
	}
}