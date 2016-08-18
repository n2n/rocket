<?php
namespace rocket\script\entity\command\impl\tree;

use rocket\script\entity\command\impl\tree\field\TreeRootIdScriptField;
use rocket\script\entity\command\impl\tree\field\TreeRightScriptField;
use rocket\script\entity\command\impl\tree\field\TreeLeftScriptField;
use rocket\script\entity\command\impl\tree\controller\TreeController;
use rocket\script\entity\command\ScriptCommand;
use rocket\script\core\IncompatibleScriptException;
use rocket\script\entity\EntityScript;

class TreeUtils {
	public static function findTreeField(EntityScript $entityScript, &$treeLeftScriptField = null, 
				&$treeRightScriptField = null, &$treeRootIdScriptField = null) {

		foreach ($entityScript->getFieldCollection()->toArray() as  $scriptField) {
			if ($scriptField instanceof TreeLeftScriptField) {
				$treeLeftScriptField = $scriptField;
			} else if ($scriptField instanceof TreeRightScriptField) {
				$treeRightScriptField = $scriptField;
			} else if ($scriptField instanceof TreeRootIdScriptField) {
				$treeRootIdScriptField = $scriptField;
			}
		}
	}
	
	public static function initializeController(ScriptCommand $scriptCommand, TreeController $treeController) {
		$entityScript = $scriptCommand->getEntityScript();
		$treeLeftScriptField = null;
		$treeRightScriptField = null;
		$treeRootIdScriptField = null;
		foreach ($entityScript->getFieldCollection()->toArray() as  $scriptField) {
			if ($scriptField instanceof TreeLeftScriptField) {
				$treeLeftScriptField = $scriptField;
			} else if ($scriptField instanceof TreeRightScriptField) {
				$treeRightScriptField = $scriptField;
			} else if ($scriptField instanceof TreeRootIdScriptField) {
				$treeRootIdScriptField = $scriptField;
			}
		}

		if (null === $treeLeftScriptField) {
			throw self::createIncompatibleScriptException($scriptCommand, 'rocket\script\entity\command\impl\tree\field\TreeLeftScriptField');
		}

		if (null === $treeRightScriptField) {
			throw self::createIncompatibleScriptException($scriptCommand, 'rocket\script\entity\command\impl\tree\field\TreeRightScriptField');
		}

		if (null === $treeRootIdScriptField) {
			throw self::createIncompatibleScriptException($scriptCommand, 'rocket\script\entity\command\impl\tree\field\TreeRootIdScriptField');
		}

		$treeController->initialize($treeLeftScriptField, $treeRightScriptField, $treeRootIdScriptField);
	}

	private static function createIncompatibleScriptException(ScriptCommand $scriptCommand, $missingScriptField) {
		return new IncompatibleScriptException('Command \'' . get_class($scriptCommand) . '\' requires a field of type \''
				. $missingScriptField . '\'.');
	}
}