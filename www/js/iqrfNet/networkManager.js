/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

'use strict';

// Enable or disable auto addressing in bonding new nodes
let autoAddress = document.getElementById('frm-iqrfNetBondingForm-autoAddress');
if (autoAddress !== null) {
	autoAddress.addEventListener('click', function (event) {
		let elementIds = [
			'frm-iqrfNetBondingForm-address',
			'frm-iqrfNetBondingForm-coordinatorOnly',
			'frm-iqrfNetBondingForm-clearAllBonds',
			'frm-iqrfNetBondingForm-removeBond',
		];
		for (let elementId of elementIds) {
			document.getElementById(elementId).disabled = event.currentTarget.checked;
		}
	});
}

// Add warning if the interoperability will be violated
let stdAndLpNetwork = document.getElementById('frm-iqrfNetOsForm-stdAndLpNetwork');
if (stdAndLpNetwork !== null) {
	stdAndLpNetwork.addEventListener('change', function (event) {
		let message = document.createElement('span');
		message.id = 'frm-iqrfNetOsForm-stdAndLpNetwork-warning';
		message.className = 'label label-warning';
		message.innerText = stdAndLpNetwork.dataset.warning;
		if (event.currentTarget.checked) {
			let warning = document.getElementById('frm-iqrfNetOsForm-stdAndLpNetwork-warning');
			if (warning !== null) {
				warning.parentNode.removeChild(warning);
			}
		} else {
			stdAndLpNetwork.parentElement.insertAdjacentHTML('afterend', message.outerHTML);
		}
	});
}
