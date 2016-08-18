<?php
namespace rocket\script\entity\field;

use n2n\persistence\Pdo;

interface TranslatableScriptField extends EditableScriptField, MappableScriptField {
	
	public function isTranslationEnabled();
	/**
	 * The name of the column which should be cloned to translation table
	 * @return string null if no column should be adopted to translation table
	 */
	public function getTranslationColumnName();
	
	public function checkTranslationMeta(Pdo $dbh);
	
	public function getTranslatable();
	
}