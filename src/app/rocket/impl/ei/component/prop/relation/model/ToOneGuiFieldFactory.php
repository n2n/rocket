<?php

namespace rocket\impl\ei\component\prop\relation\model;

use rocket\op\ei\util\Eiu;
use rocket\ui\gui\field\BackableGuiField;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\ui\si\content\SiObjectQualifier;
use n2n\bind\mapper\impl\Mappers;
use rocket\ui\si\content\SiField;
use rocket\ui\si\content\impl\SiFields;
use rocket\ui\gui\field\GuiField;

class ToOneGuiFieldFactory {

	function __construct(private RelationModel $relationModel) {
	}

	function createInGuiField(Eiu $eiu): BackableGuiField {
		$targetEiu = $eiu->frame()->forkSelect($eiu->prop()->getPath(), $eiu->entry());
		$targetEiu->frame()->exec($this->relationModel->getTargetReadEiCmdPath());

		$values = [];
		if (null !== ($eiuEntry = $eiu->field()->getValue())) {
			assert($eiuEntry instanceof EiuEntry);
			$values[] = $eiuEntry->createSiObjectQualifier();
		}

		return GuiFields::objectQualifiersSelectIn($targetEiu->frame()->createSiFrame(),
				($this->relationModel->isMandatory() ? 1 : 0), 1,
				$this->readPickableQualifiers($targetEiu, $this->relationModel->getMaxPicksNum()))
				->setValue($values)
				->setModel($eiu->field()->asGuiFieldModel(
						Mappers::valueClosure(fn ($v) => $this->mapInput($v, $targetEiu))));
	}

	private function mapInput(array $siObjectQualifiers, Eiu $targetEiu): ?EiuEntry {
//		$siQualifiers = $this->siField->getValues();
		ArgUtils::valArray($siObjectQualifiers, SiObjectQualifier::class);

		if (empty($siObjectQualifiers)) {
			return null;
		}

		$siObjectQualifier = current($siObjectQualifiers);
		assert($siObjectQualifier instanceof SiObjectQualifier);

		$id = $targetEiu->frame()->siQualifierToId(current($siObjectQualifiers));
		try {
			return $targetEiu->frame()->lookupEntry($id);
		} catch (UnknownEiObjectException $e) {
			return null;
		}
	}

	private function readPickableQualifiers(Eiu $targetEiu, int $maxNum): ?array {
		if ($maxNum <= 0) {
			return null;
		}

		$num = $targetEiu->frame()->count();
		if ($num > $maxNum) {
			return null;
		}

		$siEntryQualifiers = [];
		foreach ($targetEiu->frame()->lookupObjects() as $eiuObject) {
			$siEntryQualifiers[] = $eiuObject->createSiObjectQualifier();
		}
		return $siEntryQualifiers;
	}

	function createOutGuiField(Eiu $eiu): BackableGuiField {
		$value = $eiu->field()->getValue();
		if ($value === null) {
			return GuiFields::out(SiFields::stringOut(null));
		}

		CastUtils::assertTrue($value instanceof EiuEntry);
		$label = $value->createIdentityString();

		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->entry())->frame();
		$targetEiuFrame->exec($this->relationModel->getTargetReadEiCmdPath());

		if (null !== ($detailNavPoint = $targetEiuFrame->getDetailNavPoint($value, false))) {
			return GuiFields::out(SiFields::linkOut($detailNavPoint, $label));
		}

		return GuiFields::out(SiFields::stringOut($label));
	}
}