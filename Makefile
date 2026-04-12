default: start

.PHONY: setup
setup:
	pushd refill-api.toolforge.org/refill/backend/ && pipenv install --dev && cp celeryconfig.example.py celeryconfig.py && popd
	pushd web/ && npm install && popd

.PHONY: start
start:
	./start.sh

.PHONY: web
web:
	cd web/ && npm run dev

.PHONY: celery
celery:
	cd refill-api.toolforge.org/refill/backend/ && celery --autoscale=100,10 worker

.PHONY: flask
flask:
	cd refill-api.toolforge.org/refill/backend/ && FLASK_APP=app.py FLASK_DEBUG=1 flask run
