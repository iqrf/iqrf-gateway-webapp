/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

'use strict';

import '@fortawesome/fontawesome-free/js/solid';
import 'admin-lte';
import 'autosize';
import 'bootstrap';
import 'jquery';
import 'nette.ajax.js';
import 'ublaboo-datagrid';
import autosize from 'autosize';
import Nette from 'nette-forms';
import * as Sentry from '@sentry/browser';
import hljs from 'highlight.js/lib/highlight';
import json from 'highlight.js/lib/languages/json';

import 'highlight.js/styles/github.css';
import '../css/app.css';

Sentry.init({
	dsn: 'https://4f490a0339024820b1f3321491bd6b23@sentry.iqrf.org/2'
});

Nette.initOnLoad();

$(function () {
	$.nette.init();
});

autosize(document.querySelectorAll('textarea'));

hljs.initHighlightingOnLoad();
hljs.registerLanguage('json', json);

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

$.nette.ext({
	before: function () {
		showSpinner();
	},
	complete: function () {
		hideSpinner();
		document.querySelectorAll('pre code').forEach((block) => {
			hljs.highlightBlock(block);
		});
	}
});
