import time
import json

from collections import OrderedDict

from flask import Flask, Response, request, abort, url_for
from flask_cors import CORS
from flask_restplus import Resource, Api, fields
from werkzeug.contrib.fixers import ProxyFix

from refill.tasks import TASK_MAPPING, fixWikicode, fixWikipage


app = Flask(__name__)
app.config.SWAGGER_UI_DOC_EXPANSION = 'list'
app.config.SWAGGER_UI_JSONEDITOR = True
app.wsgi_app = ProxyFix(app.wsgi_app)
CORS(app)
api = Api(
    app,
    title='reFill 2',
    description='A set of APIs to interact with reFill, the citation-fixer'
)


taskInfoModel = api.model('taskInfo', OrderedDict([
    ('taskName', fields.String(
        required=True,
        description='Name of the submitted task',
        example='doSomething',
    )),
    ('taskId', fields.String(
        required=True,
        description='ID of the created task',
        example='942b3fb5-fe63-49cd-9b6d-230c36070d8f',
    )),
]))

taskResponseModel = api.inherit('taskResponse', taskInfoModel, OrderedDict([
    ('statusUrl', fields.String(
        required=True,
        description='URL to receive the status of the task',
        example='/status/doSomething/942b3fb5-fe63-49cd-9b6d-230c36070d8f',
    )),
    ('statusStreamUrl', fields.String(
        description='URL to receive a stream of the task status',
        example='/statusStream/doSomething/942b3fb5-fe63-49cd-9b6d-230c36070d8',
    )),
]))

taskStateModel = api.model('taskState', OrderedDict([
    ('state', fields.String(
        required=True,
        description='Celery state of the job',
        example='STARTED',
    )),
    ('info', fields.Raw(
        required=True,
        description='Data related to its execution',
    )),
]))

fixWikicodeModel = api.model('fixWikicode', {
    'wikicode': fields.String(
        required=True,
        description='The input wikicode',
        example='<ref>http://example.org</ref>',
    ),
})

fixWikipageModel = api.model('fixWikipage', {
    'page': fields.String(
        required=True,
        description='Title of the page',
        example='Device 6',
    ),
    'code': fields.String(
        description='Language code or other identifier of the wiki',
        default='en',
        example='en',
    ),
    'fam': fields.String(
        description='The wiki family',
        default='wikipedia',
        example='wikipedia',
    ),
})


@api.route('/fixWikicode', methods=['post'])
class FixWikicode(Resource):
    @api.expect(fixWikicodeModel)
    @api.marshal_with(taskResponseModel, code=202)
    def post(self, **kwargs):
        """Fix raw wikicode

        When called, this API fires off a task to fix the citations in the
        supplied wikicode.
        """
        if 'wikicode' not in request.json:
            abort(400)

        wikicode = request.json['wikicode']
        result = fixWikicode.delay(wikicode)
        response = {
            'taskName': 'fixWikicode',
            'taskId': result.id,
            'statusUrl': url_for('status', taskName='fixWikicode', taskId=result.id),
            'statusStreamUrl': url_for('status_stream', taskName='fixWikicode', taskId=result.id),
        }, 202, {'X-Accel-Buffering': 'no'}

        return response


@api.route('/fixWikipage', methods=['post'])
class FixWikipage(Resource):
    @api.expect(fixWikipageModel)
    @api.marshal_with(taskResponseModel, code=202)
    def post(self, **kwargs):
        """Fix a wiki page

        When called, this API fires off a task to fix the citations on a
        wiki page. In order to fetch the page, the `fam` and `code`
        parameters are sent to pywikibot to identify the wiki.

        See https://phabricator.wikimedia.org/diffusion/PWBC/browse/master/pywikibot/families
        for a full list of supported wiki families and codes.
        """
        if 'page' not in api.payload or not api.payload['page']:
            abort(400)

        page = api.payload['page']
        code = 'en' if 'code' not in api.payload else api.payload['code']
        fam = 'wikipedia' if 'fam' not in api.payload else api.payload['fam']

        result = fixWikipage.delay(page=page, fam=fam, code=code)
        response = {
            'taskName': 'fixWikipage',
            'taskId': result.id,
            'statusUrl': url_for('status', taskName='fixWikicode', taskId=result.id),
            'statusStreamUrl': url_for('status_stream', taskName='fixWikicode', taskId=result.id),
        }, 202

        return response


@api.route('/status/<string:taskName>/<string:taskId>', methods=['get'])
class Status(Resource):
    @api.marshal_with(taskStateModel)
    def get(self, taskName, taskId):
        """Retrieve status of a submitted task

        This API can be used to check the status of a task. You may
        want to looks at `statusStream` which returns a JSON stream
        of the current status of a task.
        """
        if taskName not in TASK_MAPPING:
            abort(400)

        result = TASK_MAPPING[taskName].AsyncResult(taskId)
        if result:
                return {
                    'state': result.state,
                    'info': result.info,
                }

        abort(404)


@api.route('/statusStream/<string:taskName>/<string:taskId>', methods=['get'])
class StatusStream(Resource):
    @api.response(200, 'Success (stream)', [taskStateModel])
    def get(self, taskName, taskId):
        """Retrieve status stream of a submitted task

        This API returns a JSON stream of the current status of
        a task, with a indefinitely-long list of objects. It
        lasts for at most 20 seconds, and you will need to make
        another request to continue, in case the task has not
        finished. Note that `origWikicode` will be sent at most
        once in `info` in a stream.

        [Oboe.js](http://oboejs.com/) may be used to decode the
        stream in real time.
        """
        if taskName not in TASK_MAPPING:
            abort(400)

        result = TASK_MAPPING[taskName].AsyncResult(taskId)
        if result:
            def generate():
                yield '['
                countdown = 20
                sendOnce = ['origWikicode', 'wikipage']
                sentOnce = []

                while countdown:
                    if type(result.info) is not dict:
                        info = {}
                    else:
                        info = result.info

                    for field in sentOnce:
                        info.pop(field, None)

                    for field in info.keys():
                        if field in sendOnce:
                            sendOnce.remove(field)
                            sentOnce.append(field)

                    yield json.dumps({
                        'state': result.state,
                        'info': info
                    }) + ','

                    if result.state in ['SUCCESS', 'FAILURE', 'REVOKED']:
                        break

                    time.sleep(1)
                    countdown -= 1

                yield '{"flag": "END"}]'

            return Response(generate(), content_type='application/json', headers={'X-Accel-Buffering': 'no'})

        abort(404)


@api.route('/sandbox', doc=False)
class Sandbox(Resource):
    def get(self):
        """A sandbox page with jQuery and Oboe.js
        """
        return Response("<!doctype html><script src='https://cdnjs.cloudflare.com/ajax/libs/oboe.js/2.1.3/oboe-browser.min.js'></script><script src='https://code.jquery.com/jquery-3.2.1.min.js'></script><h1>Sandbox</h1>", content_type='text/html')
