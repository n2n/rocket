<?php
namespace rocket\script\entity\adaptive;

use n2n\persistence\orm\criteria\querypoint\QueryPoint;
use n2n\persistence\orm\EntityModel;
use n2n\persistence\orm\criteria\querypoint\MetaGenerator;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\meta\data\QueryComparator;

class AdaptiveQueryPoint implements QueryPoint {
	private $decoratedQueryPoint;
	private $columnNamePrefix;
	
	public function __construct(QueryPoint $decoratedQueryPoint, $tableNamePrefix, $columnNamePrefix, $idColumnName) {
		$this->decoratedQueryPoint = $decoratedQueryPoint;
		$this->columnNamePrefix = $columnNamePrefix;
		
		$decoratedQueryPoint->setIdColumnName($idColumnName);
	}
	
	public function registerMetaColumn($columnName) {
		return $this->decoratedQueryPoint->registerColumn($this->decoratedQueryPoint->getEntityModel()->getTopEntityModel(), $columnName);
	}
	
	public function getMetaQueryColumnByName($columnName) {
		return $this->decoratedQueryPoint->getQueryColumnByName($this->decoratedQueryPoint->getEntityModel()->getTopEntityModel(), $columnName);
	}
	
	public function getMetaColumnAliases() {
		return $this->decoratedQueryPoint->getMetaColumnAliases();
	}
	
	public function registerColumn(EntityModel $entityModel, $columnName) {
		return $this->decoratedQueryPoint->registerColumn($entityModel, $this->columnNamePrefix . $columnName);
	}
	
	public function getQueryColumnByName(EntityModel $entityModel, $columnName) {
		return $this->decoratedQueryPoint->getQueryColumnByName($entityModel, $this->columnNamePrefix . $columnName);
	}
	
	public function applyAsFrom(SelectStatementBuilder $selectStatementBuilder) {
		$this->decoratedQueryPoint->applyAsFrom($selectStatementBuilder);
	}
	
	public function applyAsJoin(SelectStatementBuilder $selectStatementBuilder, $joinType, 
			QueryComparator $onComparator = null) {
		$this->decoratedQueryPoint->applyAsJoin($selectStatementBuilder, $joinType, $onComparator);
	}
	
	public function makeIdentifiable() {
		$this->decoratedQueryPoint->makeIdentifiable();
	}
	
	public function identifyEntityModel(array $result) {
		return $this->decoratedQueryPoint->identifyEntityModel($result);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\querypoint\QueryPoint::getEntityModel()
	 */
	public function getEntityModel() {
		return $this->decoratedQueryPoint->getEntityModel();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\querypoint\QueryPoint::setIdColumnName()
	 */
	public function setIdColumnName($idColumnname) {
		$this->decoratedQueryPoint->setIdColumnName($idColumnname);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\querypoint\QueryPoint::setMetaGenerator()
	 */
	public function setMetaGenerator(MetaGenerator $metaGenerator = null) {
		$this->decoratedQueryPoint->setMetaGenerator($metaGenerator);
	}	
}