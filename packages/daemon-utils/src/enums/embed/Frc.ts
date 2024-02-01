/**
 * Embedded FRC response time options
 */
export enum FrcResponseTime {
	/// 40 ms
	MS40 = 0,
	/// 360 ms
    MS360 = 16,
	/// 680 ms
    MS680 = 32,
	/// 1320 ms
    MS1320 = 48,
	/// 2600 ms
    MS2600 = 64,
	/// 5160 ms
    MS5160 = 80,
	/// 10280 ms
    MS10280 = 96,
	/// 20520 ms
    MS20520 = 112,
}

/**
 * Embedded FRC command options
 */
export enum FrcCommand {
	/// IQRF 2 bits command (0x10)
	Iqrf2Bits = 16,
	/// IQRF byte command (0x90)
	Iqrf1Byte = 144,
	/// IQRF 2 bytes command (0xE0)
	Iqrf2Bytes = 224,
	/// IQRF 4 bytes command (0xF9)
	Iqrf4Bytes = 249,
	/// User 2 bits command (0x40)
	User2Bits = 64,
	/// User byte command (0xC0)
	User1Byte = 192,
	/// User 2 bytes command (0xF0)
	User2Bytes = 240,
	/// User 4 bytes command (0xFC)
	User4Bytes = 252,
}
