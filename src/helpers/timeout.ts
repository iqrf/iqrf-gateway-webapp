import i18n from '../i18n';
import store from '../store';
import Vue from 'vue';

export function timeout(message: string, length: number) {
	return setTimeout(() => {
		store.commit('spinner/HIDE');
		Vue.$toast.error(i18n.t(message).toString());
	}, length);
}
