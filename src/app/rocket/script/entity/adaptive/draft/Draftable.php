<?php

namespace rocket\script\entity\adaptive\draft;

use n2n\persistence\orm\store\PersistingJob;
use n2n\persistence\orm\store\RemovingJob;
use n2n\persistence\orm\store\MappingJob;

interface Draftable {
// 	public function getDraftableEntityProperty();
	
// 	public function draftCopy($value);
	
// 	public function publishCopy($value);
	
	public function mapDraftValue($draftId, MappingJob $mappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues);
	
	public function supplyDraftPersistingJob($mappedValue, PersistingJob $persistingJob);
	
	public function supplyDraftRemovingJob($mappedValue, RemovingJob $removingJob);
}