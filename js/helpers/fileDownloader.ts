import {AxiosResponse} from 'axios';

export function fileDownloader(response: AxiosResponse, contentType: string, fileName: string) {
	const contentDisposition = response.headers['content-disposition'];
	if (contentDisposition) {
		const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
		if (fileNameMatch.length === 2) {
			fileName = fileNameMatch[1];
		}
	}
	const blob = new Blob([response.data], {type: contentType});
	const fileUrl = window.URL.createObjectURL(blob);
	const file = document.createElement('a');
	file.href = fileUrl;
	file.setAttribute('download', fileName);
	document.body.appendChild(file);
	return file;
}
