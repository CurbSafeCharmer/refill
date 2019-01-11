FROM python:3.6-alpine3.8

# Build context: backend/

WORKDIR /opt/app

COPY . .

RUN apk add --no-cache --virtual .build-deps build-base linux-headers && \
	pip --no-cache-dir install -r requirements.txt && \
	pip --no-cache-dir install uwsgi && \
	apk del .build-deps && \
	rm -rf /var/cache/apk/*

RUN chgrp users /opt/app && \
	chmod g+rwx /opt/app && \
	cd /opt/app && \
	cp celeryconfig.docker.py celeryconfig.py && \
	rm -rf pywikibot.lwp throttle.ctrl apicache-py3

ENV UWSGI_ARGS="--http 127.0.0.1:8001"

USER guest:users
CMD [ "sh", "-c", "uwsgi --plugins python3 --wsgi app:app --chdir /opt/app $UWSGI_ARGS" ]
