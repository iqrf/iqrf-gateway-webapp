import {AxiosResponse} from 'axios';

/**
 * Creates a new file download element 
 * @param {AxiosResponse} response Axios request response
 * @param {string} contentType Response content MIME
 * @param {string} fileName Name of downloaded file
 * @returns {HTMLAnchorElement} New file download element
 */
export function fileDownloader(response: AxiosResponse, contentType: string, fileName: string): HTMLAnchorElement {
	const contentDisposition = response.headers['content-disposition'];
	if (contentDisposition) {
		const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
		if (fileNameMatch.length === 2) {
			fileName = fileNameMatch[1];
		}
	}
	let data = response.data;
	if (contentType === 'application/json') { // default processing is for binary files
		data = JSON.stringify(data);
	}
	const blob = new Blob([data], {type: contentType});
	const fileUrl = window.URL.createObjectURL(blob);
	const file = document.createElement('a');
	file.href = fileUrl;
	file.setAttribute('download', fileName);
	document.body.appendChild(file);
	return file;
}
