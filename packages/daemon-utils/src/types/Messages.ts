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

export interface ResponseData {
	msgId: string;
	rsp: Record<string, any>;
	status: number
}

export interface DaemonApiRequest extends DaemonApiMessage {
	data: RequestData
}

export interface DaemonApiResponse extends DaemonApiMessage {
	data: ResponseData
}

