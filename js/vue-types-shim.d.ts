import Vue from 'vue';
import {ToastApi} from 'vue-toast-notification';

declare module 'vue/types/vue' {
	interface Vue {
		$toast: ToastApi
	}
}
