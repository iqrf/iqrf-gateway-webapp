import 'admin-lte';
import 'autosize';
import 'bootstrap';
import 'cronstrue';
import './iqrfApp';
import 'jquery';
import 'nette-forms';
import 'nette.ajax.js';
import 'ublaboo-datagrid';
import * as Sentry from '@sentry/browser';

import '../css/app.css';

Sentry.init({
	dsn: 'https://31687391bccd475da4e2082861076d65@sentry.iqrf.org/2',
});

const autosize = require('autosize');
const Nette = require('nette-forms');

Nette.initOnLoad();

$(function () {
	$.nette.init();
});

autosize(document.querySelectorAll('textarea'));
