<?php

namespace rocket\ui\gui\field\impl\file;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ParamQuery;
use n2n\io\managed\img\ImageDimension;
use n2n\web\http\BadRequestException;
use n2n\io\managed\img\ImageFile;
use n2n\io\managed\File;
use n2n\io\managed\impl\TmpFileManager;
use n2n\io\managed\impl\engine\QualifiedNameFormatException;
use rocket\user\model\LoginContext;
use n2n\context\attribute\SessionScoped;
use n2n\context\attribute\Inject;
use n2n\util\crypt\TokenUtils;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\StringUtils;
use n2n\util\ex\ExUtils;
use n2n\web\http\ForbiddenException;
use n2n\io\managed\FileManager;

class SiFileScrController extends ControllerAdapter {

	#[Inject]
	private LoginContext $loginContext;

	#[SessionScoped]
	private ?int $grantedUserId = null;
	#[SessionScoped]
	private array $grantedTokens = [];

	function grantFileId(SiFileId $fileId): string {
		$userId = $this->loginContext->getCurrentUser()->getId();
		if ($this->grantedUserId !== $userId) {
			$this->grantedUserId = $userId;
			$this->grantedTokens = [];
		}

		$fileIdStr = json_encode($fileId);
		return $this->grantedTokens[$fileIdStr]
				?? $this->grantedTokens[$fileIdStr] = TokenUtils::randomToken();
	}

	/**
	 * @throws BadRequestException
	 * @throws ForbiddenException
	 */
	private function valToken(string $token): SiFileId {
		foreach ($this->grantedTokens as $fileIdStr => $grantedToken) {
			if ($token !== $grantedToken) {
				continue;
			}

			try {
				return SiFileId::parse(ExUtils::try(fn () => StringUtils::jsonDecode($fileIdStr, true)));
			} catch (CorruptedSiDataException $e) {
				throw new BadRequestException(previous: $e);
			}
		}

		throw new ForbiddenException('Token not granted: ' . $token);
	}

	/**
	 * @throws PageNotFoundException
	 * @throws BadRequestException
	 * @throws ForbiddenException
	 */
	private function lookupFile(string $token): File {
		$fileId = $this->valToken($token);

		$n2nContext = $this->getN2nContext();
		$fileManager = $n2nContext->lookup($fileId->getFileManagerName(), false);
		if ($fileManager === null) {
			throw new BadRequestException('FileManager name does not exist: ' . $fileId->getFileManagerName());
		}

		assert($fileManager instanceof FileManager);
		$file = $fileManager->getByQualifiedName($fileId->getQualifiedName());
		if ($file === null) {
			throw new PageNotFoundException();
		}

		return $file;
	}

	/**
	 * @throws PageNotFoundException
	 * @throws BadRequestException
	 * @throws ForbiddenException
	 */
	function doFile(ParamQuery $token): void {
		$this->sendFile($this->lookupFile($token));
	}

	/**
	 * @throws PageNotFoundException
	 * @throws BadRequestException
	 * @throws ForbiddenException
	 */
	function doThumb(ParamQuery $token, ParamQuery $imgDim): void {
		$file = $this->lookupFile($token);

		$imageDimension = null;
		try {
			$imageDimension = ImageDimension::createFromString($imgDim->__toString());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}

		$thumbFile = null;
		try {
			$thumbFile = (new ImageFile($file))->getThumbFile($imageDimension);
		} catch (\Exception $e) {
			throw new PageNotFoundException(null, 0, $e);
		}

		if ($thumbFile === null) {
			throw new PageNotFoundException();
		}

		$this->sendFile($thumbFile);
	}
//
//	/**
//	 * @param string $qualifiedName
//	 * @throws BadRequestException
//	 * @return File|null
//	 */
//	private function lookupTmpFile(string $qualifiedName) {
//		$tmpFileManager = $this->getN2nContext()->lookup(TmpFileManager::class);
//		try {
//			return $tmpFileManager->getSessionFile($qualifiedName, $this->getHttpContext()->getSession());
//		} catch (QualifiedNameFormatException $e) {
//			throw new BadRequestException(null, 0, $e);
//		}
//
//	}
//
//	function doTmp(ParamQuery $qn) {
//		$file = $this->lookupTmpFile((string) $qn);
//		if ($file !== null) {
//			$this->sendFile($file);
//			return;
//		}
//
//		throw new PageNotFoundException();
//	}
//
//	function doTmpThumb(ParamQuery $qn, ParamQuery $imgDim) {
//		$file = $this->lookupTmpFile((string) $qn);
//		if ($file === null || !$file->getFileSource()->getAffiliationEngine()->hasThumbSupport()) {
//			throw new PageNotFoundException();
//		}
//
//		$imageDimension = null;
//		try {
//			$imageDimension = ImageDimension::createFromString((string) $imgDim);
//		} catch (\InvalidArgumentException $e) {
//			throw new BadRequestException(null, 0, $e);
//		}
//
//		$thumbFile = (new ImageFile($file))->getThumbFile($imageDimension);
//		if ($thumbFile === null) {
//			throw new PageNotFoundException();
//		}
//
//		$this->sendFile($thumbFile);
//	}

}