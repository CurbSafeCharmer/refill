FROM python:3.6-alpine3.8

# Build context: backend/

WORKDIR /opt/app

COPY . .

RUN apk add --no-cache --virtual .build-deps build-base linux-headers
RUN python -m pip --no-cache-dir install --upgrade pip wheel setuptools
RUN pip --no-cache-dir install -r requirements.txt
RUN pip --no-cache-dir install uwsgi
RUN apk del .build-deps
RUN rm -rf /var/cache/apk/*

RUN chgrp users /opt/app
RUN chmod g+rwx /opt/app
RUN cd /opt/app
RUN cp celeryconfig.docker.py celeryconfig.py
RUN rm -rf pywikibot.lwp throttle.ctrl apicache-py3

ENV UWSGI_ARGS="--http 0.0.0.0:8001"

EXPOSE 8001

USER guest:users
CMD [ "sh", "-c", "uwsgi --plugins python3 --wsgi app:app --chdir /opt/app $UWSGI_ARGS" ]