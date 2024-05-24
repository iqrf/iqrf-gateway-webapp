import { Quantities, Units } from '../enums';

/**
 * Standard sensor quantities utility class
 */
export class SensorQuantities {

	/**
	 * Get unit by quantity
	 * @param {Quantities} quantity Quantity type
	 * @return {string | null} Quantity unit if it exists
	 */
	public static getQuantityUnit(quantity: Quantities): string | null {
		switch (quantity) {
			case Quantities.Temperature:
				return Units.Temperature;
			case Quantities.Carbon_Dioxide:
				return Units.Carbon_Dioxide;
			case Quantities.Volatile_Organic_Compounds:
				return Units.Volatile_Organic_Compounds;
			case Quantities.Extra_Low_Voltage:
				return Units.Extra_Low_Voltage;
			case Quantities.Earths_Magnetic_Field:
				return Units.Earths_Magnetic_Field;
			case Quantities.Low_Voltage:
				return Units.Low_Voltage;
			case Quantities.Current:
				return Units.Current;
			case Quantities.Power:
				return Units.Power;
			case Quantities.Mains_Frequency:
				return Units.Mains_Frequency;
			case Quantities.Timespan:
				return Units.Timespan;
			case Quantities.Illuminance:
				return Units.Illuminance;
			case Quantities.Nitrogen_Dioxide:
				return Units.Nitrogen_Dioxide;
			case Quantities.Sulfur_Dioxide:
				return Units.Sulfur_Dioxide;
			case Quantities.Ozone:
				return Units.Ozone;
			case Quantities.Atmospheric_Pressure:
				return Units.Atmospheric_Pressure;
			case Quantities.Color_Temperature:
				return Units.Color_Temperature;
			case Quantities.Particulates_PM2_5:
				return Units.Particulates_PM2_5;
			case Quantities.Sound_Pressure_Level:
				return Units.Sound_Pressure_Level;
			case Quantities.Altitude:
				return Units.Altitude;
			case Quantities.Acceleration:
				return Units.Acceleration;
			case Quantities.Ammonia:
				return Units.Ammonia;
			case Quantities.Methane:
				return Units.Methane;
			case Quantities.Short_Length:
				return Units.Short_Length;
			case Quantities.Particulates_PM1:
				return Units.Particulates_PM1;
			case Quantities.Particulates_PM4:
				return Units.Particulates_PM4;
			case Quantities.Particulates_PM10:
				return Units.Particulates_PM10;
			case Quantities.Total_Volatile_Organic_Compound:
				return Units.Total_Volatile_Organic_Compound;
			case Quantities.Relative_Humidity:
				return Units.Relative_Humidity;
			case Quantities.Rssi:
				return Units.Rssi;
			case Quantities.Consumption:
				return Units.Consumption;
			case Quantities.Datetime:
				return Units.Datetime;
			case Quantities.Timespan_Long:
				return Units.Timespan_Long;
			case Quantities.Temperature_Float:
				return Units.Temperature_Float;
			case Quantities.Length:
				return Units.Length;
			default:
				return null;
		}
	}
}
