FROM python:3.6-alpine3.8

# Build context: backend/

WORKDIR /opt/app

COPY . .

RUN apk add --no-cache --virtual .build-deps build-base redis
RUN python -m pip --no-cache-dir install --upgrade pip wheel setuptools
RUN pip --no-cache-dir install -r requirements.txt
RUN apk del .build-deps
RUN rm -rf /var/cache/apk/*

RUN chgrp users /opt/app
RUN chmod g+rwx /opt/app
RUN cd /opt/app
RUN cp celeryconfig.docker.py celeryconfig.py
RUN rm -rf pywikibot.lwp throttle.ctrl apicache-py3

USER guest:users
CMD [ "celery", "worker", "--autoscale=100,10"]