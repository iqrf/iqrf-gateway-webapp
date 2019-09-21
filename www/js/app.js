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

import 'admin-lte';
import 'autosize';
import 'bootstrap';
import 'jquery';
import 'nette.ajax.js';
import 'ublaboo-datagrid';
import autosize from 'autosize';
import Nette from 'nette-forms';
import * as Sentry from '@sentry/browser';

import '../css/app.css';

Sentry.init({
	dsn: 'https://dcc3c60024154484afbca5e250a861a9@sentry.iqrf.org/2'
});

Nette.initOnLoad();

$(function () {
	$.nette.init();
});

autosize(document.querySelectorAll('textarea'));
