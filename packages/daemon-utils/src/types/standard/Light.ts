/**
 * Standard Light parameters interface
 */
export interface LightParams {
	/// Light index
	index: number;
	/// Light power to set
	power: number;
	/// Optional time (in seconds)
	time?: number;
}

/**
 * Standard Light Power parameters interface
 */
export interface LightPowerParams {
	/// Lights parameters
	lights: LightParams[];
}
