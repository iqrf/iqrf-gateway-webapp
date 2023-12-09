interface DaemonApiMessage {
	mType: string;
}

interface RequestData {
	msgId?: string;
	req?: Record<string, any>;
	timeout?: number;
	repeat?: number;
	returnVerbose?: boolean;
}

export interface ResponseRaw {
	request: string,
	requestTs: string,
	confirmation: string,
	confirmationTs: string,
	response: string,
	responseTs: string,
}

export interface ResponseData {
	msgId: string;
	rsp: Record<string, any>;
	raw?:ResponseRaw[],
	status: number,
	errorStr?: string
	insId?: string
	statusStr?: string
}

export interface DaemonApiRequest extends DaemonApiMessage {
	data: RequestData
}

export interface DaemonApiResponse extends DaemonApiMessage {
	data: ResponseData
}

export interface DpaPacketMessage {
	msgId: string,
	request: string,
	requestTs: string,
	confirmation?: string,
	response?: string,
}
