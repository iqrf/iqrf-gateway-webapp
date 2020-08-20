import Vue from 'vue';
import VueI18n from 'vue-i18n';

import en from './locales/en.json';

const messages = {
	'en': {
		...en
	}
};

Vue.use(VueI18n);

export default new VueI18n({
	locale: 'en',
	fallbackLocale: 'en',
	messages: messages
});
