/**
 * Standard Sensor quantities enum
 */
export enum Quantities {
	Temperature = 1,
	Carbon_Dioxide,
	Volatile_Organic_Compounds,
	Extra_Low_Voltage,
	Earths_Magnetic_Field,
	Low_Voltage,
	Current,
	Power,
	Mains_Frequency,
	Timespan,
	Illuminance,
	Nitrogen_Dioxide,
	Sulfur_Dioxide,
	Carbon_Monoxide,
	Ozone,
	Atmospheric_Pressure,
	Color_Temperature,
	Particulates_PM2_5,
	Sound_Pressure_Level,
	Altitude,
	Acceleration,
	Ammonia,
	Methane,
	Short_Length,
	Particulates_PM1,
	Particulates_PM4,
	Particulates_PM10,
	Total_Volatile_Organic_Compound,
	Nitrogen_Oxides,
	Activity_Concentration,
	Relative_Humidity = 128,
	Binary_Data7,
	Power_Factor,
	UV_Index,
	PH,
	Rssi,
	Binary_Data30 = 160,
	Consumption,
	Datetime,
	Timespan_Long,
	Latitude,
	Longitude,
	Temperature_Float,
	Length,
	Data_Block = 192,
}

/**
 * Quantity units enum
 */
export enum Units {
	Acceleration = 'm/s2',
	Activity_Concentration = 'Bq/m3',
	Altitude = 'm',
	Ammonia = 'ppm',
	Atmospheric_Pressure = 'Pa',
	Carbon_Dioxide = 'ppm',
	Carbon_Monoxide = 'ppm',
	Color_Temperature = 'K',
	Consumption = 'W',
	Current = 'A',
	Datetime = 's',
	Earths_Magnetic_Field = 'T',
	Extra_Low_Voltage = 'V',
	Illuminance = 'lx',
	Length = 'm',
	Low_Voltage = 'V',
	Mains_Frequency = 'Hz',
	Methane = '%',
	Nitrogen_Dioxide = 'ppm',
	Ozone = 'ppm',
	Particulates_PM1 = 'μg/m3',
	Particulates_PM10 = 'μg/m3',
	Particulates_PM2_5 = 'μg/m3',
	Particulates_PM4 = 'μg/m3',
	Power = 'W',
	Relative_Humidity = '%',
	Rssi = 'dBm',
	Short_Length = 'm',
	Sound_Pressure_Level = 'dB',
	Sulfur_Dioxide = 'ppm',
	Temperature = '°C',
	Temperature_Float = '°C',
	Timespan = 's',
	Timespan_Long = 's',
	Total_Volatile_Organic_Compound = 'μg/m3',
	Volatile_Organic_Compounds = 'ppm',
}
