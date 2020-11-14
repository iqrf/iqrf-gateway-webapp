/* eslint-disable no-empty */
/* eslint-disable no-redeclare */
/* eslint-disable no-constant-condition */
'use strict';
var validate = (function() {
	var refVal = [];
	return function validate(data, dataPath, parentData, parentDataProperty, rootData) {
		'use strict'; /*# sourceURL=genericDaemonRequest.json */
		var vErrors = null;
		var errors = 0;
		if (rootData === undefined) rootData = data;
		if ((data && typeof data === 'object' && !Array.isArray(data))) {
			var errs__0 = errors;
			var valid1 = true;
			for (var key0 in data) {
				var isAdditional0 = !(false || key0 == 'mType' || key0 == 'data');
				if (isAdditional0) {
					valid1 = false;
					var err = {
						keyword: 'additionalProperties',
						dataPath: (dataPath || '') + '',
						schemaPath: '#/additionalProperties',
						params: {
							additionalProperty: '' + key0 + ''
						},
						message: 'should NOT have additional properties',
						schema: false,
						parentSchema: validate.schema,
						data: data
					};
					if (vErrors === null) vErrors = [err];
					else vErrors.push(err);
					errors++;
				}
			}
			var data1 = data.mType;
			if (data1 === undefined) {
				valid1 = false;
				var err = {
					keyword: 'required',
					dataPath: (dataPath || '') + '',
					schemaPath: '#/required',
					params: {
						missingProperty: 'mType'
					},
					message: 'should have required property \'mType\'',
					schema: validate.schema.properties,
					parentSchema: validate.schema,
					data: data
				};
				if (vErrors === null) vErrors = [err];
				else vErrors.push(err);
				errors++;
			} else {
				var errs_1 = errors;
				if (typeof data1 !== 'string') {
					var err = {
						keyword: 'type',
						dataPath: (dataPath || '') + '.mType',
						schemaPath: '#/properties/mType/type',
						params: {
							type: 'string'
						},
						message: 'should be string',
						schema: validate.schema.properties.mType.type,
						parentSchema: validate.schema.properties.mType,
						data: data1
					};
					if (vErrors === null) vErrors = [err];
					else vErrors.push(err);
					errors++;
				}
				var valid1 = errors === errs_1;
			}
			var data1 = data.data;
			if (data1 === undefined) {
				valid1 = false;
				var err = {
					keyword: 'required',
					dataPath: (dataPath || '') + '',
					schemaPath: '#/required',
					params: {
						missingProperty: 'data'
					},
					message: 'should have required property \'data\'',
					schema: validate.schema.properties,
					parentSchema: validate.schema,
					data: data
				};
				if (vErrors === null) vErrors = [err];
				else vErrors.push(err);
				errors++;
			} else {
				var errs_1 = errors;
				if ((data1 && typeof data1 === 'object' && !Array.isArray(data1))) {
					var errs__1 = errors;
					var valid2 = true;
					for (var key1 in data1) {
						var isAdditional1 = !(false || key1 == 'msgId' || key1 == 'req' || key1 == 'returnVerbose' || key1 == 'timeout' || key1 == 'repeat');
						if (isAdditional1) {
							valid2 = false;
							var err = {
								keyword: 'additionalProperties',
								dataPath: (dataPath || '') + '.data',
								schemaPath: '#/properties/data/additionalProperties',
								params: {
									additionalProperty: '' + key1 + ''
								},
								message: 'should NOT have additional properties',
								schema: false,
								parentSchema: validate.schema.properties.data,
								data: data1
							};
							if (vErrors === null) vErrors = [err];
							else vErrors.push(err);
							errors++;
						}
					}
					var data2 = data1.msgId;
					if (data2 === undefined) {
						valid2 = false;
						var err = {
							keyword: 'required',
							dataPath: (dataPath || '') + '.data',
							schemaPath: '#/properties/data/required',
							params: {
								missingProperty: 'msgId'
							},
							message: 'should have required property \'msgId\'',
							schema: validate.schema.properties.data.properties,
							parentSchema: validate.schema.properties.data,
							data: data1
						};
						if (vErrors === null) vErrors = [err];
						else vErrors.push(err);
						errors++;
					} else {
						var errs_2 = errors;
						if (typeof data2 !== 'string') {
							var err = {
								keyword: 'type',
								dataPath: (dataPath || '') + '.data.msgId',
								schemaPath: '#/properties/data/properties/msgId/type',
								params: {
									type: 'string'
								},
								message: 'should be string',
								schema: validate.schema.properties.data.properties.msgId.type,
								parentSchema: validate.schema.properties.data.properties.msgId,
								data: data2
							};
							if (vErrors === null) vErrors = [err];
							else vErrors.push(err);
							errors++;
						}
						var valid2 = errors === errs_2;
					}
					var data2 = data1.req;
					if (data2 === undefined) {
						valid2 = false;
						var err = {
							keyword: 'required',
							dataPath: (dataPath || '') + '.data',
							schemaPath: '#/properties/data/required',
							params: {
								missingProperty: 'req'
							},
							message: 'should have required property \'req\'',
							schema: validate.schema.properties.data.properties,
							parentSchema: validate.schema.properties.data,
							data: data1
						};
						if (vErrors === null) vErrors = [err];
						else vErrors.push(err);
						errors++;
					} else {
						var errs_2 = errors;
						if ((data2 && typeof data2 === 'object' && !Array.isArray(data2))) {
							var errs__2 = errors;
							var valid3 = true;
							var data3 = data2.nAdr;
							if (data3 !== undefined) {
								var errs_3 = errors;
								if ((typeof data3 !== 'number' || (data3 % 1) || data3 !== data3)) {
									var err = {
										keyword: 'type',
										dataPath: (dataPath || '') + '.data.req.nAdr',
										schemaPath: '#/properties/data/properties/req/properties/nAdr/type',
										params: {
											type: 'integer'
										},
										message: 'should be integer',
										schema: validate.schema.properties.data.properties.req.properties.nAdr.type,
										parentSchema: validate.schema.properties.data.properties.req.properties.nAdr,
										data: data3
									};
									if (vErrors === null) vErrors = [err];
									else vErrors.push(err);
									errors++;
								}
								if ((typeof data3 === 'number')) {
									if (data3 > 239 || data3 !== data3) {
										var err = {
											keyword: 'maximum',
											dataPath: (dataPath || '') + '.data.req.nAdr',
											schemaPath: '#/properties/data/properties/req/properties/nAdr/maximum',
											params: {
												comparison: '<=',
												limit: 239,
												exclusive: false
											},
											message: 'should be <= 239',
											schema: 239,
											parentSchema: validate.schema.properties.data.properties.req.properties.nAdr,
											data: data3
										};
										if (vErrors === null) vErrors = [err];
										else vErrors.push(err);
										errors++;
									}
									if (data3 < 0 || data3 !== data3) {
										var err = {
											keyword: 'minimum',
											dataPath: (dataPath || '') + '.data.req.nAdr',
											schemaPath: '#/properties/data/properties/req/properties/nAdr/minimum',
											params: {
												comparison: '>=',
												limit: 0,
												exclusive: false
											},
											message: 'should be >= 0',
											schema: 0,
											parentSchema: validate.schema.properties.data.properties.req.properties.nAdr,
											data: data3
										};
										if (vErrors === null) vErrors = [err];
										else vErrors.push(err);
										errors++;
									}
								}
								var valid3 = errors === errs_3;
							}
						} else {
							var err = {
								keyword: 'type',
								dataPath: (dataPath || '') + '.data.req',
								schemaPath: '#/properties/data/properties/req/type',
								params: {
									type: 'object'
								},
								message: 'should be object',
								schema: validate.schema.properties.data.properties.req.type,
								parentSchema: validate.schema.properties.data.properties.req,
								data: data2
							};
							if (vErrors === null) vErrors = [err];
							else vErrors.push(err);
							errors++;
						}
						var valid2 = errors === errs_2;
					}
					var data2 = data1.returnVerbose;
					if (data2 !== undefined) {
						var errs_2 = errors;
						if (typeof data2 !== 'boolean') {
							var err = {
								keyword: 'type',
								dataPath: (dataPath || '') + '.data.returnVerbose',
								schemaPath: '#/properties/data/properties/returnVerbose/type',
								params: {
									type: 'boolean'
								},
								message: 'should be boolean',
								schema: validate.schema.properties.data.properties.returnVerbose.type,
								parentSchema: validate.schema.properties.data.properties.returnVerbose,
								data: data2
							};
							if (vErrors === null) vErrors = [err];
							else vErrors.push(err);
							errors++;
						}
						var valid2 = errors === errs_2;
					}
					var data2 = data1.timeout;
					if (data2 !== undefined) {
						var errs_2 = errors;
						if ((typeof data2 !== 'number' || (data2 % 1) || data2 !== data2)) {
							var err = {
								keyword: 'type',
								dataPath: (dataPath || '') + '.data.timeout',
								schemaPath: '#/properties/data/properties/timeout/type',
								params: {
									type: 'integer'
								},
								message: 'should be integer',
								schema: validate.schema.properties.data.properties.timeout.type,
								parentSchema: validate.schema.properties.data.properties.timeout,
								data: data2
							};
							if (vErrors === null) vErrors = [err];
							else vErrors.push(err);
							errors++;
						}
						var valid2 = errors === errs_2;
					}
					var data2 = data1.repeat;
					if (data2 !== undefined) {
						var errs_2 = errors;
						if ((typeof data2 !== 'number' || (data2 % 1) || data2 !== data2)) {
							var err = {
								keyword: 'type',
								dataPath: (dataPath || '') + '.data.repeat',
								schemaPath: '#/properties/data/properties/repeat/type',
								params: {
									type: 'integer'
								},
								message: 'should be integer',
								schema: validate.schema.properties.data.properties.repeat.type,
								parentSchema: validate.schema.properties.data.properties.repeat,
								data: data2
							};
							if (vErrors === null) vErrors = [err];
							else vErrors.push(err);
							errors++;
						}
						var valid2 = errors === errs_2;
					}
				} else {
					var err = {
						keyword: 'type',
						dataPath: (dataPath || '') + '.data',
						schemaPath: '#/properties/data/type',
						params: {
							type: 'object'
						},
						message: 'should be object',
						schema: validate.schema.properties.data.type,
						parentSchema: validate.schema.properties.data,
						data: data1
					};
					if (vErrors === null) vErrors = [err];
					else vErrors.push(err);
					errors++;
				}
				var valid1 = errors === errs_1;
			}
		} else {
			var err = {
				keyword: 'type',
				dataPath: (dataPath || '') + '',
				schemaPath: '#/type',
				params: {
					type: 'object'
				},
				message: 'should be object',
				schema: validate.schema.type,
				parentSchema: validate.schema,
				data: data
			};
			if (vErrors === null) vErrors = [err];
			else vErrors.push(err);
			errors++;
		}
		validate.errors = vErrors;
		return errors === 0;
	};
})();
validate.schema = {
	'$schema': 'http://json-schema.org/draft-07/schema',
	'$id': 'genericDaemonRequest.json',
	'type': 'object',
	'title': 'Generic Daemon API request schema',
	'required': ['mType', 'data'],
	'properties': {
		'mType': {
			'$id': '#/properties/mType',
			'type': 'string',
			'title': 'Request type',
			'example': 'iqrfRaw'
		},
		'data': {
			'$id': '#/properties/data',
			'type': 'object',
			'title': 'Request data',
			'required': ['msgId', 'req'],
			'properties': {
				'msgId': {
					'$id': '#/properties/data/msgId',
					'type': 'string',
					'title': 'Request message ID',
					'example': 'testMessageId'
				},
				'req': {
					'$id': '#/properties/data/req',
					'type': 'object',
					'title': 'Request values',
					'properties': {
						'nAdr': {
							'$id': '#/properties/data/req/nAdr',
							'type': 'integer',
							'title': 'Device address',
							'minimum': 0,
							'maximum': 239,
							'example': 0
						}
					}
				},
				'returnVerbose': {
					'$id': '#/properties/data/returnVerbose',
					'type': 'boolean',
					'title': 'Verbose response',
					'example': true
				},
				'timeout': {
					'$id': '#/properties/data/timeout',
					'type': 'integer',
					'title': 'Request timeout in milliseconds',
					'example': 1000
				},
				'repeat': {
					'$id': '#/properties/data/repeat',
					'type': 'integer',
					'title': 'Repeat request',
					'example': 1
				}
			},
			'additionalProperties': false
		}
	},
	'additionalProperties': false
};
validate.errors = null;
module.exports = validate;