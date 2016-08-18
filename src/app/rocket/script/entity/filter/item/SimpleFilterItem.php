<?php
namespace rocket\script\entity\filter\item;

use rocket\script\entity\filter\SimpleComparatorConstraint;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\dispatch\option\Option;
use n2n\util\Attributes;
use n2n\dispatch\option\impl\EnumOption;
use n2n\core\DynamicTextCollection;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\l10n\Locale;

class SimpleFilterItem implements FilterItem {
	const OPERATOR_OPTION = 'operator';
	const VALUE_OPTION = 'value';
	
	protected $propertyName;
	protected $label;
	protected $operatorOptions;
	protected $valueOption;
	
	public function __construct($propertyName, $label, array $operatorOptions, Option $valueOption) {
		$this->propertyName = $propertyName;
		$this->label = $label;
		$this->operatorOptions = $operatorOptions;
		$this->valueOption = $valueOption;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		$optionCollection->addOption(self::OPERATOR_OPTION, new EnumOption('Operator', $this->operatorOptions, null, true));
		$optionCollection->addOption(self::VALUE_OPTION, $this->valueOption);
		return $optionCollection;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\item\FilterItem::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes) {
		$operator = $attributes->get(self::OPERATOR_OPTION);
		if ($operator === null) return null;
	
		return new SimpleComparatorConstraint($this->propertyName,
				$attributes->get(self::VALUE_OPTION), $operator);
	}
	
	public static function createOperatorOptions(Locale $locale, array $availableOperators = null) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		
		if ($availableOperators === null) {
			$availableOperators = CriteriaComparator::getOperators();
		}
		
		$operatorOptions = array();
		foreach ($availableOperators as $operator) {
			switch ($operator) {
				case CriteriaComparator::OPERATOR_EQUAL:
					$operatorOptions[$operator] = $dtc->translate('script_filter_operator_equal_label');
					break;
				case CriteriaComparator::OPERATOR_LARGER_THAN:
					$operatorOptions[$operator] = $dtc->translate('script_filter_operator_larger_than_label');
					break;
				case CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO:
					$operatorOptions[$operator] = $dtc->translate('script_filter_operator_larger_than_or_equal_to_label');
					break;
				case CriteriaComparator::OPERATOR_LIKE:
					$operatorOptions[$operator] = $dtc->translate('script_filter_operator_like_label');
					break;
				case CriteriaComparator::OPERATOR_NOT_EQUAL:
					$operatorOptions[$operator] = $dtc->translate('script_filter_operator_not_equal_label');
					break;
				case CriteriaComparator::OPERATOR_SMALLER_THAN:
					$operatorOptions[$operator] = $dtc->translate('script_filter_operator_smaller_than_label');
					break;
				case CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO:
					$operatorOptions[$operator] = $dtc->translate('script_filter_operator_smaller_than_or_equal_to_label');
					break;
				default:
					$operatorOptions[(string) $operator] = (string) $operator;
			}
		}
		
		return $operatorOptions;
	}
}