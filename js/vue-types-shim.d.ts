import Vue from 'vue';
import {ToastApi} from 'vue-toast-notification';
import VueI18n from 'vue-i18n';

declare module 'vue/types/vue' {
	interface Vue {
		$toast: ToastApi,
		$t: typeof VueI18n.prototype.t,
	}
}
