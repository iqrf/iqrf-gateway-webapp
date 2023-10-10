<template>
	<v-dialog
		v-model='showDialog'
		persistent
		scrollable
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-icon
				v-bind='props'
				color='error'
				size='large'
			>
				{{ mdiDelete }}
			</v-icon>
		</template>
		<Card header-color='primary'>
			<template #title>
				{{ $t('components.accessControl.users.delete.title') }}
			</template>
			{{ $t('components.accessControl.users.delete.prompt', {user: user.username}) }}
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					@click='onSubmit'
				>
					{{ $t('common.buttons.delete') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					@click='close'
				>
					{{ $t('common.buttons.cancel') }}
				</v-btn>
			</template>
		</Card>
	</v-dialog>
</template>

<script lang='ts' setup>
import { UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { AxiosError } from 'axios';
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import { useUserStore } from '@/store/user';

import Card from '@/components/Card.vue';

import { basicErrorToast } from '@/helpers/errorToast';
import { getModalWidth } from '@/helpers/modal';
import { useApiClient } from '@/services/ApiClient';
import { mdiDelete } from '@mdi/js';

interface Props {
	user: UserInfo
	onlyUser: boolean
}

const userStore = useUserStore();
const i18n = useI18n();
const router = useRouter();
const emit = defineEmits(['refresh']);
const props = defineProps<Props>();
const showDialog: Ref<boolean> = ref(false);
const width = getModalWidth();

function onSubmit(): void {
	useApiClient().getUserService().delete(props.user.id)
		.then(async () => {
			toast.success(
				i18n.t('components.accessControl.users.messages.delete.success', {user: props.user.username})
			);
			if (props.user.id === userStore.getId) {
				close();
				await userStore.signOut();
				if (props.onlyUser) {
					await router.push('/install/');
				}
			} else {
				close();
				emit('refresh');
			}
		})
		.catch((error: AxiosError) => {
			basicErrorToast(error, 'components.accessControl.users.messages.delete.failure', {user: props.user.username});
		});
}

function close(): void {
	showDialog.value = false;
}

</script>
