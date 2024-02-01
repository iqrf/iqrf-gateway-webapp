/**
 * IQMESH config service actions
 */
export enum IqmeshConfigAction {
	/// Get value
	Get = 'get',
	/// Set value
	Set = 'set',
}

/**
 * IQMESH Test RF signal measurement time options
 */
export enum IqmeshTestRfMeasurementTime {
	/// 40 milliseconds
	MS40 = 40,
	/// 360 milliseconds
	MS360 = 360,
	/// 680 milliseconds
	MS680 = 680,
	/// 1320 milliseconds
	MS1320 = 1320,
	/// 2600 milliseconds
	MS2600 = 2600,
	/// 5160 milliseconds
	MS5160 = 5160,
	/// 10280 milliseconds
	MS10280 = 10280,
	/// 20620 milliseconds
	MS20620 = 20620,
}
