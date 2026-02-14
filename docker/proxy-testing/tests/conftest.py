import pytest
import httpx
from typing import Any, Dict, Type

REST_API_URL = 'http://backend:8080/api/v0/'

@pytest.fixture(scope='session')
async def get_auth_token() -> str:

	async with (httpx.AsyncClient() as client):
		rsp = await client.post(
			f'{REST_API_URL}/user/signIn',
			json={"username": "admin", "password": "9vG$kdP&!zX@rL#Y"}
		)

		assert rsp.status_code == 200
		data = await rsp.json()
		return data['token']
