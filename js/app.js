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

import 'jquery';
import 'nette.ajax.js';
import 'ublaboo-datagrid';
import Nette from 'nette-forms';

import store from './store';

import './main';

Nette.initOnLoad();

$(function () {
	$.nette.init();
});

$.nette.ext('spinner', {
	start: function () {
		store.commit('spinner/SHOW');
	},
	complete: function () {
		store.commit('spinner/HIDE');
	}
});

$.nette.ext('confirm', {
	before: function (xhr, settings) {
		if (!settings.nette) {
			return;
		}
		let question = settings.nette.el.data('confirm');
		if (question) {
			let retVal = confirm(question);
			if (retVal) {
				store.commit('spinner/HIDE');
			}
			return retVal;
		}
	}
});
