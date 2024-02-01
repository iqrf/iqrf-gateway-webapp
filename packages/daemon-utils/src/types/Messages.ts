/**
 * JSON API message base interface
 */
interface DaemonApiMessage {
	/// Message type
	mType: string;
}

/**
 * JSON API request data interface
 */
interface RequestData {
	/// Message ID
	msgId?: string;
	/// Maximum number of repeat transactions
	repeat?: number;
	/// Request parameters object
	req?: Record<string, any>;
	/// Return verbose response
	returnVerbose?: boolean;
	/// Request timeout
	timeout?: number;
}

/**
 * JSON API response raw data
 */
export interface ResponseRaw {
	/// DPA confirmation
	confirmation: string;
	/// DPA confirmation timestamp
	confirmationTs: string;
	/// DPA request
	request: string;
	/// DPA request timestamp
	requestTs: string;
	/// DPA repsonse
	response: string;
	/// DPA response timestamp
	responseTs: string;
}

/**
 * JSON API response data interface
 */
export interface ResponseData {
	/// Verbose error message
	errorStr?: string;
	/// Instance ID
	insId?: string;
	/// Message ID
	msgId: string;
	/// Raw DPA message data
	raw?:ResponseRaw[];
	/// Response object
	rsp: Record<string, any>;
	/// Status code
	status: number;
	/// Verbose status message
	statusStr?: string;
}

/**
 * JSON API request interface
 */
export interface DaemonApiRequest extends DaemonApiMessage {
	/// Request data
	data: RequestData;
}

/**
 * JSON API response interface
 */
export interface DaemonApiResponse extends DaemonApiMessage {
	/// Response data
	data: ResponseData;
}

/**
 * DPA packet interface
 */
export interface DpaPacketMessage {
	/// Confirmation message
	confirmation?: string;
	/// Message ID
	msgId: string;
	/// Request message
	request: string;
	/// Request timestamp
	requestTs: string;
	/// Response message
	response?: string;
}

/**
 * JSON message interface
 */
export interface JsonMessage {
	/// Message type
	mType: string;
	/// Message ID
	msgId: string;
	/// Request message
	request: string;
	/// List of response messages
	response: string[];
	/// Request timestamp
	timestamp: string;
}
