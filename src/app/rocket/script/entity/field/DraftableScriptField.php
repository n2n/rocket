<?php
namespace rocket\script\entity\field;

use n2n\persistence\Pdo;

interface DraftableScriptField extends EditableScriptField, MappableScriptField {
	public function isDraftEnabled();
	/**
	 * The name of the column which should be cloned to draft table
	 * @return string null if no column should be adopted to draft table
	 */
	public function getDraftColumnName();
	
	public function checkDraftMeta(Pdo $dbh);
	
	public function getDraftable();
}