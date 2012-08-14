<?php

require_once 'abstract.php';

class Aoe_AsyncCache_Shell_Cleaner extends Mage_Shell_Abstract {

	/**
	 * Run script
	 *
	 * @return void
	 */
	public function run() {
		$action = $this->getArg('action');
		if (empty($action)) {
			echo $this->usageHelp();
		} else {
			$actionMethodName = $action.'Action';
			if (method_exists($this, $actionMethodName)) {
				$this->$actionMethodName();
			} else {
				echo "Action $action not found!\n";
				echo $this->usageHelp();
				exit(1);
			}
		}
	}

	/**
	 * Retrieve Usage Help Message
	 */
	public function usageHelp() {
		$help = 'Available actions: ' . "\n";
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (substr($method, -6) == 'Action') {
				$help .= '    -action ' . substr($method, 0, -6);
				$helpMethod = $method.'Help';
				if (method_exists($this, $helpMethod)) {
					$help .= $this->$helpMethod();
				}
				$help .= "\n";
			}
		}
		return $help;
	}



	/**
	 * Clear cache
	 *
	 * @return void
	 */
	public function processQueueAction() {
        $cleaner = Mage::getModel('aoeasynccache/cleaner'); /* @var Aoe_AsyncCache_Model_Cleaner $cleaner */
        $cleaner->processQueue();
	}
}

$shell = new Aoe_AsyncCache_Shell_Cleaner();
$shell->run();