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

/**
 * Fix DPA packet
 * @param {string} packet DPA packet to fix
 * @returns {string} Fixed DPA packet
 */
function fixPacket(packet) {
	var arr = packet.split(".");
	var nadrLo = arr.shift();
	var nadrHi = arr.shift();
	if (nadrHi === "00" && nadrLo !== "00") {
		arr.unshift(nadrLo, nadrHi);
	} else {
		arr.unshift(nadrHi, nadrLo);
	}
	return arr.join(".");
}

/**
 * Parse DPA packet
 * @param {string} packet DPA packet to parse
 * @returns {array} Parsed DPA packet
 */
function parsePacket(packet) {
	var arr = packet.split(".");
	var parsed = {
		nadrLo: arr.shift(),
		nadrHi: arr.shift(),
		pnum: arr.shift(),
		pcmd: arr.shift(),
		hwpidLo: arr.shift(),
		hwpidHi: arr.shift(),
		pdata: (arr.join(".")).split(".")
	};
	return parsed;
}

/**
 * Validate DPA packet
 * @param {string} packet DPA packet to validate
 * @returns {Boolean} Is valid DPA packet?
 */
function validatePacket(packet) {
	var re = new RegExp(/^([0-9a-fA-F]{1,2}\.){4,62}[0-9a-fA-F]{1,2}(\.|)$/, "i");
	return packet.match(re) !== null;
}

/**
 * Detect timeout from parsed DPA packet
 * @param {array} packet Parsed DPA packet
 * @returns {Number}
 */
function detectTimeout(packet) {
	var timeout = null;
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
 * @param {array} packet Parsed DPA packet
 */
function setTimeout(packet) {
	var timeout = detectTimeout(packet);
	if (timeout === null) {
		$("#frm-iqrfAppSendRawForm-timeoutEnabled").prop("checked", false);
		$("#frm-iqrfAppSendRawForm-timeout").prop("disabled", true);
	} else {
		$("#frm-iqrfAppSendRawForm-timeoutEnabled").prop("checked", true);
		$("#frm-iqrfAppSendRawForm-timeout").prop("disabled", false);
		$("#frm-iqrfAppSendRawForm-timeout").val(timeout);
	}
}

/**
 * Set DPA packet
 * @param {string} packet DPA packet to set
 */
function setPacket(packet) {
	if (validatePacket(packet)) {
		setTimeout(parsePacket(packet));
		$("#frm-iqrfAppSendRawForm-packet").val(packet);
	} else {
		$("#frm-iqrfAppSendRawForm-timeout").prop("disabled", true);
	}
}

// Select IQRF interface port from list
$(".btn-port").click(function () {
	$("#frm-configIqrfForm-IqrfInterface").val($(this).data("port"));
});

// Select DPA packet form list of macros from IQRF IDE
$(".btn-packet").click(function () {
	var packet = fixPacket($(this).data("packet"));
	setPacket(packet);
});

// Validate and fix DPA packet and set DPA timeout
$("#frm-iqrfAppSendRawForm-packet").keypress(function () {
	var packet = fixPacket($(this).val());
	setPacket(packet);
});

// Enable or disable DPA timeout
$("#frm-iqrfAppSendRawForm-timeoutEnabled").click(function () {
	if ($(this).is(":checked")) {
		$("#frm-iqrfAppSendRawForm-timeout").prop("disabled", false);
	} else {
		$("#frm-iqrfAppSendRawForm-timeout").prop("disabled", true);
	}
});
