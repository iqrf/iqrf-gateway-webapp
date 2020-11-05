/**
 * JSON API MetaData configuration interface
 */
export interface IJsonMetaData {
	instance: string
	metaDataToMessages: boolean
}

/**
 * JSON Raw API configuration interface
 */
export interface IJsonRaw {
	instance: string
	asyncDpaMessage: boolean
}

/**
 * JSON Splitter configuration interface
 */
export interface IJsonSplitter {
	instance: string
	validateJsonResponse: boolean
}
