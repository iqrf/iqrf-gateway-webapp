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

/**
 * Parse DPA packet
 * @param {string} packet DPA packet to parse
 * @returns {{nadrLo: string, nadrHi: string, pnum: string, pcmd: string, hwpidLo: string, hwpidHi: string, pdata: string[]}} Parsed DPA packet
 */
function parsePacket(packet) {
	let arr = packet.split(".");
	return {
		nadrLo: arr.shift(),
		nadrHi: arr.shift(),
		pnum: arr.shift(),
		pcmd: arr.shift(),
		hwpidLo: arr.shift(),
		hwpidHi: arr.shift(),
		pdata: (arr.join(".")).split(".")
	};
}

/**
 * Validate DPA packet
 * @param {string} packet DPA packet to validate
 * @returns {boolean} Is valid DPA packet?
 */
function validatePacket(packet) {
	let re = new RegExp("^([0-9a-fA-F]{1,2}\.){4,62}[0-9a-fA-F]{1,2}(\.|)$", "i");
	return packet.match(re) !== null;
}

/**
 * Detect timeout from parsed DPA packet
 * @param {{nadrLo: string, nadrHi: string, pnum: string, pcmd: string, hwpidLo: string, hwpidHi: string, pdata: string[]}} packet Parsed DPA packet
 * @returns {Number}
 */
function detectTimeout(packet) {
	let timeout = null;
	if (packet.pnum === "00" && packet.pcmd === "04") {
		timeout = 12000;
	} else if (packet.pnum === "00" && packet.pcmd === "07") {
		timeout = 0;
	} else if (packet.pnum === "0D" && packet.pcmd === "00") {
		timeout = 6000;
	}
	return timeout;
}

/**
 * Set DPA timeout
 * @param {{nadrLo: string, nadrHi: string, pnum: string, pcmd: string, hwpidLo: string, hwpidHi: string, pdata: string[]}} packet Parsed DPA packet
 */
function setTimeout(packet) {
	let timeout = detectTimeout(packet);
	if (timeout === null) {
		$("#frm-sendRawForm-timeoutEnabled").prop("checked", false);
		$("#frm-sendRawForm-timeout").prop("disabled", true);
	} else {
		$("#frm-sendRawForm-timeoutEnabled").prop("checked", true);
		$("#frm-sendRawForm-timeout").prop("disabled", false).val(timeout);
	}
}

/**
 * Set DPA packet
 * @param {string} packet DPA packet to set
 */
function setPacket(packet) {
	if (validatePacket(packet)) {
		setTimeout(parsePacket(packet));
		$("#frm-sendRawForm-packet").val(packet);
	} else {
		$("#frm-sendRawForm-timeout").prop("disabled", true);
	}
}

// Select IQRF CDC interface port from list
$(".btn-cdc-port").click(function () {
	$("#frm-configIqrfCdcForm-IqrfInterface").val($(this).data("port"));
});

// Select IQRF SPI interface port from list
$(".btn-spi-port").click(function () {
	$("#frm-configIqrfSpiForm-IqrfInterface").val($(this).data("port"));
});

// Select SPI port and pins from list of supported boards
$(".btn-spi-pin").click(function () {
	$("#frm-configIqrfSpiForm-IqrfInterface").val($(this).data("iqrfinterface"));
	$("#frm-configIqrfSpiForm-powerEnableGpioPin").val($(this).data("powerenablegpiopin"));
	$("#frm-configIqrfSpiForm-busEnableGpioPin").val($(this).data("busenablegpiopin"));
	$("#frm-configIqrfSpiForm-pgmSwitchGpioPin").val($(this).data("pgmswitchgpiopin"));
});

// Select IQRF UART interface port from list
$(".btn-uart-port").click(function () {
	$("#frm-configIqrfUartForm-IqrfInterface").val($(this).data("port"));
});

// Select UART port and pins from list of supported boards
$(".btn-uart-pin").click(function () {
	$("#frm-configIqrfUartForm-IqrfInterface").val($(this).data("iqrfinterface"));
	$("#frm-configIqrfUartForm-baudRate").val($(this).data("baudrate"));
	$("#frm-configIqrfUartForm-powerEnableGpioPin").val($(this).data("powerenablegpiopin"));
	$("#frm-configIqrfUartForm-busEnableGpioPin").val($(this).data("busenablegpiopin"));
	$("#frm-configIqrfUartForm-pgmSwitchGpioPin").val($(this).data("pgmswitchgpiopin"));
});

// Select DPA packet form list of macros from IQRF IDE
$(".btn-packet").click(function () {
	setPacket($(this).data("packet"));
});

// Disable custom NADR input
$("#frm-sendRawForm-address").prop("disabled", true);

// Validate and fix DPA packet and set DPA timeout
$("#frm-sendRawForm-packet").keypress(function () {
	setPacket($(this).val());
});

// Enable or disable DPA timeout
$("#frm-sendRawForm-timeoutEnabled").click(function () {
	let enabled = $(this).is(":checked");
	$("#frm-sendRawForm-timeout").prop("disabled", !enabled);
});

// Enable or disable overwrite NADR
$("#frm-sendRawForm-overwriteAddress").click(function () {
	let enabled = !$(this).is(":checked");
	$("#frm-sendRawForm-address").prop("disabled", enabled);
});

// Enable or disable auto addressing in bonding new nodes
$("#frm-iqrfNetBondingForm-autoAddress").click(function () {
	let checked = $(this).is(":checked");
	$("#frm-iqrfNetBondingForm-address").prop("disabled", checked);
	$("#frm-iqrfNetBondingForm-rebond").prop("disabled", checked);
	$("#frm-iqrfNetBondingForm-remove").prop("disabled", checked);
});
