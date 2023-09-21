import 'vue3-toastify/dist/index.css';
import Vue3Toastify, {type ToastContainerOptions} from 'vue3-toastify';

export const ToastOptions: ToastContainerOptions = {
	/// base options
	position: 'top-right',
	autoClose: 5000,
	closeButton: true,
	pauseOnHover: true,
	pauseOnFocusLoss: true,
	closeOnClick: true,
	theme: 'colored',
	/// extended options
	multiple: true,
	limit: 5,
	newestOnTop: true,
};

export default Vue3Toastify;
