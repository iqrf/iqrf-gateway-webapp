
function showSpinner() {
	let spinner = document.createElement('div');
	spinner.className = 'spinner';
	let loading = document.createElement('div');
	loading.className = 'loading';
	loading.appendChild(spinner);
	document.querySelector('body')
		.insertAdjacentElement('afterbegin', loading);
}

function hideSpinner() {
	let elements = document.getElementsByClassName('loading');
	for (let i = 0; i < elements.length; i++) {
		elements[i].remove();
	}
}

export default {
	showSpinner,
	hideSpinner
};
