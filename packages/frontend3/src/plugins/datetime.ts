import VueDatePicker from '@vuepic/vue-datepicker';
import { type App } from 'vue';
import '@vuepic/vue-datepicker/dist/main.css';

export default function registerDatetime(app: App) {
	app.component('VueDatePicker', VueDatePicker);
}
