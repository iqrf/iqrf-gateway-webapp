import httpx


class RestApiClient:
	"""Simple REST API client for use in proxy testing."""

	def __init__(self, client: httpx.AsyncClient, base_url: str):
		"""Constructs a REST API client object."""
		self.client = client
		self.base_url = base_url

	async def sign_in(self, username: str = None, password: str = None) -> str:
		"""Signs in and returns access token."""
		rsp = await self.client.post(
			url=f'{self.base_url}/user/signIn',
			json={"username": username or "admin", "password": password or "9vG$kdP&!zX@rL#Y"}
		)

		assert rsp.status_code == 200
		data = rsp.json()
		return data['token']

	async def refresh_session(self, access_token: str) -> str:
		"""Performs a session refresh to get a new, valid access token."""
		rsp = await self.client.post(
			url=f'{self.base_url}/user/refreshToken',
			headers={
				'Authorization': f'Bearer {access_token}'
			}
		)

		assert rsp.status_code == 200
		data = rsp.json()
		return data['token']
