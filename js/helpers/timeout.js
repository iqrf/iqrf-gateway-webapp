import Vue from 'vue';
import i18n from '../i18n.ts';
import store from '../store';

export function timeout(message, length) {
	return setTimeout(() => {
		store.commit('spinner/HIDE');
		Vue.$toast.error(i18n.t(message).toString());
	}, length);
}
