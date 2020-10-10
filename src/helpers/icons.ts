import {cilPencil, cilPlus, cilPowerStandby, cilReload, cilTrash} from '@coreui/icons';

export function getCoreIcon(icon: string): string[]|void {
	switch(icon) {
		case 'add':
			return cilPlus;
		case 'edit':
			return cilPencil;
		case 'powerStandby':
			return cilPowerStandby;
		case 'reload':
			return cilReload;
		case 'remove':
			return cilTrash;
		default:
			break;
	}
}