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

declare(strict_types=1);

namespace App\IqrfAppModule\Model;

use App\IqrfAppModule\Model\IqrfAppManager;
use Nette;
use Nette\Utils\Strings;

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
	 * The command removes all nodes from the list of bonded nodes at coordinator memory.
	 * It actually destroys the network from the coordinator point of view.
	 * @return array DPA request and response
	 */
	public function clearAllBonds() {
		$packet = '00.00.00.03.ff.ff';
		return $this->iqrfAppManager->sendRaw($packet);
	}

	/**
	 * This command bonds a new node by the coordinator.
	 * There is a maximum approx. 12 s blocking delay when this function is called.
	 * @param string $address A requested address for the bonded node. The address must not be used (bonded) yet. If this parameter equals to 0, then the 1 free address is assigned to the node.
	 * @return array DPA request and response
	 */
	public function bondNode(string $address = '00') {
		$packet = '00.00.00.04.ff.ff.' . $address . '.00';
		$timeout = 12000;
		return $this->iqrfAppManager->sendRaw($packet, $timeout);
	}

	/**
	 * Runs IQMESH discovery process.
	 * The time when the response is delivered depends highly on the number of network devices, the network topology, and RF mode, thus, it is not predictable. It can take from a few seconds to many minutes.
	 * @param string $txPower TX Power used for discovery.
	 * @param string $maxAddress Nonzero value specifies maximum node address to be part of the discovery process. This feature allows splitting all node devices into two parts: [1] devices having an address from 1 to MaxAddr will be part of the discovery process thus they become routers, [2] devices having an address from MaxAddr+1 to 239 will not be routers. See IQRF OS documentation for more information. The value of this parameter is ignored at demo version. A value 5 is always used instead.
	 * @return array DPA request and response
	 */
	public function discovery(string $txPower = '00', string $maxAddress = '00') {
		$packet = '00.00.00.07.ff.ff.' . Strings::padLeft($txPower, 2, '0')
				. '.' . dechex($maxAddress);
		$timeout = 0;
		return $this->iqrfAppManager->sendRaw($packet, $timeout);
	}

	/**
	 * Removes already bonded node from the list of bonded nodes at coordinator memory.
	 * @param string $address Address of the node to remove the bond to
	 * @return array DPA request and response
	 */
	public function removeNode(string $address) {
		$packet = '00.00.00.05.ff.ff.' . $address;
		return $this->iqrfAppManager->sendRaw($packet);
	}

	/**
	 * Puts specified node back to the list of bonded nodes in the coordinator memory.
	 * @param string $address Number of bonded network nodes
	 * @return array DPA request and response
	 */
	public function rebondNode(string $address) {
		$packet = '00.00.00.06.ff.ff.' . $address;
		return $this->iqrfAppManager->sendRaw($packet);
	}

}
