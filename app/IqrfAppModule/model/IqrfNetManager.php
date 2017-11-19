<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\IqrfAppModule\Model;

use App\IqrfAppModule\Model\IqrfAppManager;
use Nette;

/**
 * Tool for managing IQMESH network.
 */
class IqrfNetManager {

	use Nette\SmartObject;

	/**
	 * @var IqrfAppManager
	 */
	private $iqrfAppManager;

	/**
	 * Constructor
	 * @param IqrfAppManager $iqrfAppManager
	 */
	public function __construct(IqrfAppManager $iqrfAppManager) {
		$this->iqrfAppManager = $iqrfAppManager;
	}

	/**
	 * This command bonds a new node by the coordinator.
	 * There is a maximum approx. 12 s blocking delay when this function is called.
	 * @param string $address A requested address for the bonded node. The address must not be used (bonded) yet. If this parameter equals to 0, then the 1 free address is assigned to the node.
	 * @return array DPA request and response
	 */
	public function bondNode($address = '00') {
		$packet = '00.00.00.04.ff.ff.' . $address . '.00';
		$timeout = 12000;
		return $this->iqrfAppManager->sendRaw($packet, $timeout);
	}

	/**
	 * Removes already bonded node from the list of bonded nodes at coordinator memory.
	 * @param string $address Address of the node to remove the bond to
	 * @return array DPA request and response
	 */
	public function removeNode($address) {
		$packet = '00.00.00.05.ff.ff.' . $address;
		return $this->iqrfAppManager->sendRaw($packet);
	}

	/**
	 * Puts specified node back to the list of bonded nodes in the coordinator memory.
	 * @param string $address Number of bonded network nodes
	 * @return array DPA request and response
	 */
	public function rebondNode($address) {
		$packet = '00.00.00.06.ff.ff.' . $address;
		return $this->iqrfAppManager->sendRaw($packet);
	}

}
