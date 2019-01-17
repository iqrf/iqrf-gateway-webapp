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

import cronstrue from 'cronstrue';

/**
 * Parse cron and displays time
 */
function parseCron() {
	let text = '';
	let cron = document.getElementById('frm-configSchedulerForm-cron');
	let time = document.getElementById('frm-configSchedulerForm-time');
	let value = time.value;
	let len = value.split(' ').length;
	let aliases = new Map();
	aliases.set('@reboot', '');
	aliases.set('@yearly', '0 0 0 0 1 1 *');
	aliases.set('@annually', '0 0 0 0 1 1 *');
	aliases.set('@monthly', '0 0 0 0 1 * *');
	aliases.set('@weekly', '0 0 0 * * * 0');
	aliases.set('@daily', '0 0 0 * * * *');
	aliases.set('@hourly', '0 0 * * * * *');
	aliases.set('@minutely', '0 * * * * * *');
	if (len === 1) {
		value = aliases.get(value);
		if (value === undefined) {
			return;
		}
		len = value.split(' ').length;
	}
	if (len > 5 && len < 8) {
		try {
			text = cronstrue.toString(value);
			if (cron === null) {
				let label = document.createElement('span');
				label.id = 'frm-configSchedulerForm-cron';
				label.innerText = text;
				label.className = 'label label-info';
				time.insertAdjacentHTML('beforebegin', label.outerHTML);
			} else {
				cron.innerText = text;
			}
		} catch (e) {
			console.error(e);
		}
	}
}

let time = document.getElementById('frm-configSchedulerForm-time');
if (time !== null) {
	parseCron();
	time.addEventListener('keyup', function () {
		parseCron();
	});
}
