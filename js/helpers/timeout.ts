import i18n from '../i18n';
import store from '../store';
import Vue from 'vue';
import {ToastApi} from 'vue-toast-notification';

declare module 'vue/types/vue' {
	interface VueConstructor {
		$toast: ToastApi
	}
}

export function timeout(message: string, length: number) {
	return setTimeout(() => {
		store.commit('spinner/HIDE');
		Vue.$toast.error(i18n.t(message).toString());
	}, length);
}
