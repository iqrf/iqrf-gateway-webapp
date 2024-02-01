/**
 * Standard BinaryOutput parameters interface
 */
export interface BinoutParams {
	/// Binary output index
	index: number;
	/// On/Off state
	state: boolean;
	/// Optional time (in seconds)
	time?: number;
}

/**
 * Standard BinaryOutput SetOutput parameters interface
 */
export interface SetOutputParams {
	/// Binary outputs parameters
	binOuts: BinoutParams[];
}
