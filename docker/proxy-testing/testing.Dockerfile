FROM python:3.12-slim

COPY docker/proxy-testing/requirements.txt /tests/requirements.txt
COPY docker/proxy-testing/tests/* /tests
WORKDIR /tests

RUN pip install --no-cache-dir -r requirements.txt

CMD ["pytest", "-vv"]
