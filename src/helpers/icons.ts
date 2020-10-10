import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';

export function getCoreIcon(icon: string): string[]|void {
	switch(icon) {
		case 'add':
			return cilPlus;
		case 'edit':
			return cilPencil;
		case 'remove':
			return cilTrash;
		default:
			break;
	}
}