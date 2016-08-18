<?php
namespace rocket\core\model;

use n2n\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use rocket\user\model\UserDao;
use n2n\N2N;

class DeleteLoginModel implements Dispatchable {
	
	private static function _annotations(AnnotationSet $as) {
		$as->m('delete', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private function _validation() {
	
	}
	
	public function delete(UserDao $userDao) {
		$tx = N2N::createTransaction();
		$userDao->deleteFailedLogins();
		$tx->commit();
	}
}
