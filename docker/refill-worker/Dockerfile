FROM python:3.6-alpine3.8

# Build context: backend/

WORKDIR /opt/app

COPY . .

RUN apk add --no-cache --virtual .build-deps build-base && \
	pip --no-cache-dir install -r requirements.txt && \
	apk del .build-deps && \
	rm -rf /var/cache/apk/*

RUN chgrp users /opt/app && \
	chmod g+rwx /opt/app && \
	cd /opt/app && \
	cp celeryconfig.docker.py celeryconfig.py && \
	rm -rf pywikibot.lwp throttle.ctrl apicache-py3

USER guest:users
CMD [ "celery", "--autoscale=100,10", "worker"]
