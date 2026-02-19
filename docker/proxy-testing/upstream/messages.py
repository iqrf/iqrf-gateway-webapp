import json
import time


def auth_success() -> str:
	"""Returns a JSON string representation of upstream auth success message."""
	msg = {
		'type': 'auth_success',
		'expiration': int(time.time()) + 3600,
		'service': False,
	}
	return json.dumps(msg)
