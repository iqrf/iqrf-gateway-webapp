/**
 * Embedded IO triplet interface
 */
export interface IoTriplet {
	/// Bit mask
	mask: number;
	/// Port number
	port: number;
	/// Value
	value: number;
}

/**
 * Embedded IO params interface
 */
export interface IoParams {
	/// Ports
	ports: IoTriplet[];
}
