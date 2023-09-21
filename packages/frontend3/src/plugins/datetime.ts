import {App} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

export default function registerDatetime(app: App) {
	app.component('VueDatePicker', VueDatePicker);
}
