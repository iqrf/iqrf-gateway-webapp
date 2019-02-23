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

import cronstrue from 'cronstrue';

/**
 * Parse cron and displays time
 */
function parseCron() {
	let text = '';
	let cron = document.getElementById('frm-configSchedulerForm-cron');
	let time = document.getElementById('frm-configSchedulerForm-timeSpec-cronTime');
	let value = time.value;
	let len = value.split(' ').length;
	if (len === 1) {
		value = getCronAlias(value);
		if (value === undefined) {
			if (cron !== null) {
				cron.style.visibility = 'hidden';
			}
			return;
		} else {
			text = cronstrue.toString(value);
			return printCronDescription(text);
		}
	}
	if (len > 4 && len < 8) {
		try {
			text = cronstrue.toString(value);
			printCronDescription(text);
		} catch (e) {
			if (cron !== null) {
				cron.style.visibility = 'hidden';
			}
			console.error(e);
		}
	} else if (cron !== null) {
		cron.style.visibility = 'hidden';
	}
}

/**
 * Returns CRON expresion from an alias
 * @param input CRON alias
 * @returns string CRON expresion
 */
function getCronAlias(input) {
	let aliases = new Map();
	aliases.set('@reboot', '');
	aliases.set('@yearly', '0 0 0 1 1 * *');
	aliases.set('@annually', '0 0 0 1 1 * *');
	aliases.set('@monthly', '0 0 0 1 * * *');
	aliases.set('@weekly', '0 0 0 * * 0 *');
	aliases.set('@daily', '0 0 0 * * * *');
	aliases.set('@hourly', '0 0 * * * * *');
	aliases.set('@minutely', '0 * * * * * *');
	return aliases.get(input);
}

/**
 * Prints CRON expresion description
 * @param expresion CRON expresion
 */
function printCronDescription(expresion) {
	let cron = document.getElementById('frm-configSchedulerForm-cron');
	if (cron === null) {
		let time = document.getElementById('frm-configSchedulerForm-timeSpec-cronTime');
		let label = document.createElement('span');
		label.id = 'frm-configSchedulerForm-cron';
		label.innerText = expresion;
		label.className = 'label label-info';
		time.insertAdjacentHTML('beforebegin', label.outerHTML);
	} else {
		cron.innerText = expresion;
		cron.style.visibility = 'visible';
	}
}

/**
 * Disables other time specification
 * @param event Event
 */
function disableOtherTimeScpec(event) {
	let cronTime = document.getElementById('frm-configSchedulerForm-timeSpec-cronTime');
	let exactTime = document.getElementById('frm-configSchedulerForm-timeSpec-exactTime');
	let periodic = document.getElementById('frm-configSchedulerForm-timeSpec-periodic');
	let period = document.getElementById('frm-configSchedulerForm-timeSpec-period');
	let startTime = document.getElementById('frm-configSchedulerForm-timeSpec-startTime');
	let target = event.currentTarget;
	let disabled = target.checked;
	if (cronTime !== null) {
		cronTime.disabled = disabled;
	}
	if (exactTime !== null && target === periodic) {
		exactTime.disabled = disabled;
	}
	if (periodic !== null && target === exactTime) {
		periodic.disabled = disabled;
	}
	if (period !== null) {
		period.disabled = (target === periodic) ? !disabled : true;

	}
	if (startTime !== null) {
		startTime.disabled = (target === exactTime) ? !disabled : true;
	}
}

let cronTime = document.getElementById('frm-configSchedulerForm-timeSpec-cronTime');
let exactTime = document.getElementById('frm-configSchedulerForm-timeSpec-exactTime');
let periodic = document.getElementById('frm-configSchedulerForm-timeSpec-periodic');

if (exactTime !== null) {
	exactTime.addEventListener('change', disableOtherTimeScpec);
}
if (periodic !== null) {
	periodic.addEventListener('change', disableOtherTimeScpec);
}
if (cronTime !== null) {
	parseCron();
	cronTime.addEventListener('keyup', function () {
		parseCron();
	});
}
