<template>
	<v-card>
		<v-card-text>{{ $t('install.invitation') }}</v-card-text>
		<v-divider />
		<v-card-actions>
			<v-btn
				color='primary'
				variant='flat'
				to='/install/user/'
			>
				{{ $t('install.createUser.title') }}
			</v-btn>
			<v-btn
				color='primary'
				variant='flat'
				to='/install/gateway/'
			>
				{{ $t('install.gwInfo.button') }}
			</v-btn>
			<v-btn
				color='primary'
				variant='flat'
			>
				{{ $t('install.restore.button') }}
			</v-btn>
		</v-card-actions>
	</v-card>
</template>

<script lang='ts' setup>
</script>
