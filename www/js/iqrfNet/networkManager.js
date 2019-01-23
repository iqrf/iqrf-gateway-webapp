/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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
		let checked = event.currentTarget.checked;
		document.getElementById('frm-iqrfNetBondingForm-address').disabled = checked;
		document.getElementById('frm-iqrfNetBondingForm-rebond').disabled = checked;
		document.getElementById('frm-iqrfNetBondingForm-remove').disabled = checked;
	});
}

// Enable or disable IQRF Smart Connect Code input
let bondingMethod = document.getElementById('frm-iqrfNetBondingForm-method');
if (bondingMethod !== null) {
	bondingMethod.addEventListener('change', function (event) {
		let disabled = event.currentTarget.value !== 'smartConnect';
		document.getElementById('frm-iqrfNetBondingForm-smartConnectCode').disabled = disabled;
	});
}

let smartConnectCode = document.getElementById('frm-iqrfNetBondingForm-smartConnectCode');
if (smartConnectCode !== null) {
	smartConnectCode.disabled = true;
}
