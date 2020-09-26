<template>
	<div class='c-app'>
		<LoadingSpinner />
		<TheSidebar />
		<CWrapper>
			<TheHeader />
			<div class='c-body'>
				<main class='c-main'>
					<CContainer fluid>
						<div class='fade-in'>
							<slot />
						</div>
					</CContainer>
				</main>
			</div>
		</CWrapper>
	</div>
</template>

<script>
import {CContainer, CWrapper} from '@coreui/vue';
import LoadingSpinner from './LoadingSpinner';
import TheHeader from './TheHeader';
import TheSidebar from './TheSidebar';

export default {
	name: 'TheDashboard',
	components: {
		CContainer,
		CWrapper,
		LoadingSpinner,
		TheHeader,
		TheSidebar,
	},
};
</script>
