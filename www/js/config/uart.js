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

// Select IQRF UART interface port from list
let uartPorts = document.getElementsByClassName('btn-uart-pin');
for (let i = 0; i < uartPorts.length; i++) {
	uartPorts[i].addEventListener('click', function (event) {
		document.getElementById('frm-configIqrfUartForm-IqrfInterface').value = event.currentTarget.dataset.port;
	});
}

// Select UART port and pins from list of supported boards
let uartPins = document.getElementsByClassName('btn-uart-pin');
for (let i = 0; i < uartPins.length; i++) {
	uartPins[i].addEventListener('click', function (event) {
		let data = event.currentTarget.dataset;
		document.getElementById('frm-configIqrfUartForm-IqrfInterface').value = data.iqrfinterface;
		document.getElementById('frm-configIqrfUartForm-baudRate').value = data.baudrate;
		document.getElementById('frm-configIqrfUartForm-powerEnableGpioPin').value = data.powerenablegpiopin;
		document.getElementById('frm-configIqrfUartForm-busEnableGpioPin').value = data.busenablegpiopin;
		document.getElementById('frm-configIqrfUartForm-pgmSwitchGpioPin').value = data.pgmswitchgpiopin;
	});
}
