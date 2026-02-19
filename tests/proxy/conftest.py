from typing import Any, AsyncGenerator

import httpx
import pytest
import pytest_asyncio

from rest_api_client import RestApiClient

REST_API_URL = 'http://backend:8080/api/v0'

@pytest_asyncio.fixture
async def api() -> AsyncGenerator[RestApiClient, Any]:
	"""Creates a rest API client for use in test cases."""
	async with httpx.AsyncClient() as client:
		yield RestApiClient(client=client, base_url=REST_API_URL)

@pytest.fixture
def invalid_token() -> str:
	"""Returns a token for a nonexistent user for use in test cases."""
	return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NzE0ODk0MTYsIm5iZiI6MTc3MTQ4OTQxNiwiZXhwIjoxNzcxNDk0ODE2LCJhdWQiOiJpcXJmLWdhdGV3YXktd2ViYXBwIiwidWlkIjoyLCJpc3MiOiIwMTIzNDU2Nzg5QUJDREVGIn0.DVNH0KXyBINJEsWwJV8ZVA1imzkyE_zlsTitS6GhaXs'
