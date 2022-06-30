'use strict';

module.exports = validate20;
module.exports.default = validate20;
const schema22 = {
	$schema: 'http://json-schema.org/draft-07/schema',
	$id: 'genericDaemonRequest.json',
	type: 'object',
	title: 'Generic Daemon API request schema',
	required: ['mType', 'data'],
	additionalProperties: false,
	properties: {
		mType: { $id: '#/properties/mType', type: 'string' },
		data: {
			$id: '#/properties/data',
			type: 'object',
			additionalProperties: false,
			properties: {
				msgId: { $id: '#/properties/data/msgId', type: 'string' },
				req: {
					$id: '#/properties/data/req',
					type: 'object',
					properties: {
						deviceAddr: {
							$id: '#/properties/data/req/deviceAddr',
							type: 'integer',
							anyOf: [{ minimum: 0, maximum: 239 }, { const: 255 }],
						},
						nAdr: {
							$id: '#/properties/data/req/nAdr',
							type: 'integer',
							minimum: 0,
							maximum: 239,
						},
						hwpId: {
							$id: '#/properties/data/req/hwpId',
							type: 'integer',
							minimum: 0,
							maximum: 65535,
						},
					},
				},
				returnVerbose: {
					$id: '#/properties/data/returnVerbose',
					type: 'boolean',
				},
				repeat: {
					$id: '#/properties/data/repeat',
					type: 'integer',
					minimum: 1,
				},
				timeout: {
					$id: '#/properties/data/timeout',
					type: 'integer',
					minimum: 500,
				},
			},
		},
	},
};
function validate20(
	data,
	{ instancePath = '', parentData, parentDataProperty, rootData = data } = {}
) {
	/*# sourceURL="genericDaemonRequest.json" */ let vErrors = null;
	let errors = 0;
	if (data && typeof data == 'object' && !Array.isArray(data)) {
		if (data.mType === undefined) {
			const err0 = {
				instancePath,
				schemaPath: '#/required',
				keyword: 'required',
				params: { missingProperty: 'mType' },
				message: 'must have required property \'' + 'mType' + '\'',
			};
			if (vErrors === null) {
				vErrors = [err0];
			} else {
				vErrors.push(err0);
			}
			errors++;
		}
		if (data.data === undefined) {
			const err1 = {
				instancePath,
				schemaPath: '#/required',
				keyword: 'required',
				params: { missingProperty: 'data' },
				message: 'must have required property \'' + 'data' + '\'',
			};
			if (vErrors === null) {
				vErrors = [err1];
			} else {
				vErrors.push(err1);
			}
			errors++;
		}
		for (const key0 in data) {
			if (!(key0 === 'mType' || key0 === 'data')) {
				const err2 = {
					instancePath,
					schemaPath: '#/additionalProperties',
					keyword: 'additionalProperties',
					params: { additionalProperty: key0 },
					message: 'must NOT have additional properties',
				};
				if (vErrors === null) {
					vErrors = [err2];
				} else {
					vErrors.push(err2);
				}
				errors++;
			}
		}
		if (data.mType !== undefined) {
			if (typeof data.mType !== 'string') {
				const err3 = {
					instancePath: instancePath + '/mType',
					schemaPath: '#/properties/mType/type',
					keyword: 'type',
					params: { type: 'string' },
					message: 'must be string',
				};
				if (vErrors === null) {
					vErrors = [err3];
				} else {
					vErrors.push(err3);
				}
				errors++;
			}
		}
		if (data.data !== undefined) {
			let data1 = data.data;
			if (data1 && typeof data1 == 'object' && !Array.isArray(data1)) {
				for (const key1 in data1) {
					if (
						!(
							key1 === 'msgId' ||
							key1 === 'req' ||
							key1 === 'returnVerbose' ||
							key1 === 'repeat' ||
							key1 === 'timeout'
						)
					) {
						const err4 = {
							instancePath: instancePath + '/data',
							schemaPath: '#/properties/data/additionalProperties',
							keyword: 'additionalProperties',
							params: { additionalProperty: key1 },
							message: 'must NOT have additional properties',
						};
						if (vErrors === null) {
							vErrors = [err4];
						} else {
							vErrors.push(err4);
						}
						errors++;
					}
				}
				if (data1.msgId !== undefined) {
					if (typeof data1.msgId !== 'string') {
						const err5 = {
							instancePath: instancePath + '/data/msgId',
							schemaPath: '#/properties/data/properties/msgId/type',
							keyword: 'type',
							params: { type: 'string' },
							message: 'must be string',
						};
						if (vErrors === null) {
							vErrors = [err5];
						} else {
							vErrors.push(err5);
						}
						errors++;
					}
				}
				if (data1.req !== undefined) {
					let data3 = data1.req;
					if (data3 && typeof data3 == 'object' && !Array.isArray(data3)) {
						if (data3.deviceAddr !== undefined) {
							let data4 = data3.deviceAddr;
							if (
								!(
									typeof data4 == 'number' &&
									!(data4 % 1) &&
									!isNaN(data4) &&
									isFinite(data4)
								)
							) {
								const err6 = {
									instancePath: instancePath + '/data/req/deviceAddr',
									schemaPath:
										'#/properties/data/properties/req/properties/deviceAddr/type',
									keyword: 'type',
									params: { type: 'integer' },
									message: 'must be integer',
								};
								if (vErrors === null) {
									vErrors = [err6];
								} else {
									vErrors.push(err6);
								}
								errors++;
							}
							const _errs13 = errors;
							let valid3 = false;
							const _errs14 = errors;
							if (typeof data4 == 'number' && isFinite(data4)) {
								if (data4 > 239 || isNaN(data4)) {
									const err7 = {
										instancePath: instancePath + '/data/req/deviceAddr',
										schemaPath:
											'#/properties/data/properties/req/properties/deviceAddr/anyOf/0/maximum',
										keyword: 'maximum',
										params: { comparison: '<=', limit: 239 },
										message: 'must be <= 239',
									};
									if (vErrors === null) {
										vErrors = [err7];
									} else {
										vErrors.push(err7);
									}
									errors++;
								}
								if (data4 < 0 || isNaN(data4)) {
									const err8 = {
										instancePath: instancePath + '/data/req/deviceAddr',
										schemaPath:
											'#/properties/data/properties/req/properties/deviceAddr/anyOf/0/minimum',
										keyword: 'minimum',
										params: { comparison: '>=', limit: 0 },
										message: 'must be >= 0',
									};
									if (vErrors === null) {
										vErrors = [err8];
									} else {
										vErrors.push(err8);
									}
									errors++;
								}
							}
							var _valid0 = _errs14 === errors;
							valid3 = valid3 || _valid0;
							if (!valid3) {
								const _errs15 = errors;
								if (255 !== data4) {
									const err9 = {
										instancePath: instancePath + '/data/req/deviceAddr',
										schemaPath:
											'#/properties/data/properties/req/properties/deviceAddr/anyOf/1/const',
										keyword: 'const',
										params: { allowedValue: 255 },
										message: 'must be equal to constant',
									};
									if (vErrors === null) {
										vErrors = [err9];
									} else {
										vErrors.push(err9);
									}
									errors++;
								}
								_valid0 = _errs15 === errors;
								valid3 = valid3 || _valid0;
							}
							if (!valid3) {
								const err10 = {
									instancePath: instancePath + '/data/req/deviceAddr',
									schemaPath:
										'#/properties/data/properties/req/properties/deviceAddr/anyOf',
									keyword: 'anyOf',
									params: {},
									message: 'must match a schema in anyOf',
								};
								if (vErrors === null) {
									vErrors = [err10];
								} else {
									vErrors.push(err10);
								}
								errors++;
							} else {
								errors = _errs13;
								if (vErrors !== null) {
									if (_errs13) {
										vErrors.length = _errs13;
									} else {
										vErrors = null;
									}
								}
							}
						}
						if (data3.nAdr !== undefined) {
							let data5 = data3.nAdr;
							if (
								!(
									typeof data5 == 'number' &&
									!(data5 % 1) &&
									!isNaN(data5) &&
									isFinite(data5)
								)
							) {
								const err11 = {
									instancePath: instancePath + '/data/req/nAdr',
									schemaPath:
										'#/properties/data/properties/req/properties/nAdr/type',
									keyword: 'type',
									params: { type: 'integer' },
									message: 'must be integer',
								};
								if (vErrors === null) {
									vErrors = [err11];
								} else {
									vErrors.push(err11);
								}
								errors++;
							}
							if (typeof data5 == 'number' && isFinite(data5)) {
								if (data5 > 239 || isNaN(data5)) {
									const err12 = {
										instancePath: instancePath + '/data/req/nAdr',
										schemaPath:
											'#/properties/data/properties/req/properties/nAdr/maximum',
										keyword: 'maximum',
										params: { comparison: '<=', limit: 239 },
										message: 'must be <= 239',
									};
									if (vErrors === null) {
										vErrors = [err12];
									} else {
										vErrors.push(err12);
									}
									errors++;
								}
								if (data5 < 0 || isNaN(data5)) {
									const err13 = {
										instancePath: instancePath + '/data/req/nAdr',
										schemaPath:
											'#/properties/data/properties/req/properties/nAdr/minimum',
										keyword: 'minimum',
										params: { comparison: '>=', limit: 0 },
										message: 'must be >= 0',
									};
									if (vErrors === null) {
										vErrors = [err13];
									} else {
										vErrors.push(err13);
									}
									errors++;
								}
							}
						}
						if (data3.hwpId !== undefined) {
							let data6 = data3.hwpId;
							if (
								!(
									typeof data6 == 'number' &&
									!(data6 % 1) &&
									!isNaN(data6) &&
									isFinite(data6)
								)
							) {
								const err14 = {
									instancePath: instancePath + '/data/req/hwpId',
									schemaPath:
										'#/properties/data/properties/req/properties/hwpId/type',
									keyword: 'type',
									params: { type: 'integer' },
									message: 'must be integer',
								};
								if (vErrors === null) {
									vErrors = [err14];
								} else {
									vErrors.push(err14);
								}
								errors++;
							}
							if (typeof data6 == 'number' && isFinite(data6)) {
								if (data6 > 65535 || isNaN(data6)) {
									const err15 = {
										instancePath: instancePath + '/data/req/hwpId',
										schemaPath:
											'#/properties/data/properties/req/properties/hwpId/maximum',
										keyword: 'maximum',
										params: { comparison: '<=', limit: 65535 },
										message: 'must be <= 65535',
									};
									if (vErrors === null) {
										vErrors = [err15];
									} else {
										vErrors.push(err15);
									}
									errors++;
								}
								if (data6 < 0 || isNaN(data6)) {
									const err16 = {
										instancePath: instancePath + '/data/req/hwpId',
										schemaPath:
											'#/properties/data/properties/req/properties/hwpId/minimum',
										keyword: 'minimum',
										params: { comparison: '>=', limit: 0 },
										message: 'must be >= 0',
									};
									if (vErrors === null) {
										vErrors = [err16];
									} else {
										vErrors.push(err16);
									}
									errors++;
								}
							}
						}
					} else {
						const err17 = {
							instancePath: instancePath + '/data/req',
							schemaPath: '#/properties/data/properties/req/type',
							keyword: 'type',
							params: { type: 'object' },
							message: 'must be object',
						};
						if (vErrors === null) {
							vErrors = [err17];
						} else {
							vErrors.push(err17);
						}
						errors++;
					}
				}
				if (data1.returnVerbose !== undefined) {
					if (typeof data1.returnVerbose !== 'boolean') {
						const err18 = {
							instancePath: instancePath + '/data/returnVerbose',
							schemaPath: '#/properties/data/properties/returnVerbose/type',
							keyword: 'type',
							params: { type: 'boolean' },
							message: 'must be boolean',
						};
						if (vErrors === null) {
							vErrors = [err18];
						} else {
							vErrors.push(err18);
						}
						errors++;
					}
				}
				if (data1.repeat !== undefined) {
					let data8 = data1.repeat;
					if (
						!(
							typeof data8 == 'number' &&
							!(data8 % 1) &&
							!isNaN(data8) &&
							isFinite(data8)
						)
					) {
						const err19 = {
							instancePath: instancePath + '/data/repeat',
							schemaPath: '#/properties/data/properties/repeat/type',
							keyword: 'type',
							params: { type: 'integer' },
							message: 'must be integer',
						};
						if (vErrors === null) {
							vErrors = [err19];
						} else {
							vErrors.push(err19);
						}
						errors++;
					}
					if (typeof data8 == 'number' && isFinite(data8)) {
						if (data8 < 1 || isNaN(data8)) {
							const err20 = {
								instancePath: instancePath + '/data/repeat',
								schemaPath: '#/properties/data/properties/repeat/minimum',
								keyword: 'minimum',
								params: { comparison: '>=', limit: 1 },
								message: 'must be >= 1',
							};
							if (vErrors === null) {
								vErrors = [err20];
							} else {
								vErrors.push(err20);
							}
							errors++;
						}
					}
				}
				if (data1.timeout !== undefined) {
					let data9 = data1.timeout;
					if (
						!(
							typeof data9 == 'number' &&
							!(data9 % 1) &&
							!isNaN(data9) &&
							isFinite(data9)
						)
					) {
						const err21 = {
							instancePath: instancePath + '/data/timeout',
							schemaPath: '#/properties/data/properties/timeout/type',
							keyword: 'type',
							params: { type: 'integer' },
							message: 'must be integer',
						};
						if (vErrors === null) {
							vErrors = [err21];
						} else {
							vErrors.push(err21);
						}
						errors++;
					}
					if (typeof data9 == 'number' && isFinite(data9)) {
						if (data9 < 500 || isNaN(data9)) {
							const err22 = {
								instancePath: instancePath + '/data/timeout',
								schemaPath: '#/properties/data/properties/timeout/minimum',
								keyword: 'minimum',
								params: { comparison: '>=', limit: 500 },
								message: 'must be >= 500',
							};
							if (vErrors === null) {
								vErrors = [err22];
							} else {
								vErrors.push(err22);
							}
							errors++;
						}
					}
				}
			} else {
				const err23 = {
					instancePath: instancePath + '/data',
					schemaPath: '#/properties/data/type',
					keyword: 'type',
					params: { type: 'object' },
					message: 'must be object',
				};
				if (vErrors === null) {
					vErrors = [err23];
				} else {
					vErrors.push(err23);
				}
				errors++;
			}
		}
	} else {
		const err24 = {
			instancePath,
			schemaPath: '#/type',
			keyword: 'type',
			params: { type: 'object' },
			message: 'must be object',
		};
		if (vErrors === null) {
			vErrors = [err24];
		} else {
			vErrors.push(err24);
		}
		errors++;
	}
	validate20.errors = vErrors;
	return errors === 0;
}
